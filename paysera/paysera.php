<?php
/**
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    EVP International, JSC <plugins@paysera.com>
 *  @copyright 2004-2014 EVP International, JSC
 *  @license   http://opensource.org/licenses/GPL-3.0  GNU GENERAL PUBLIC LICENSE (GPL-3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
	exit;

require_once(_PS_MODULE_DIR_.'/paysera/vendor/webtopay/libwebtopay/WebToPay.php');

class Paysera extends PaymentModule {

	private $messages = array();

	public $project_id;
	public $signature;
	public $sandbox;
	public $payment_list;
	public $default_country;

	public function __construct()
	{
		$this->name    = 'paysera';
		$this->tab     = 'payments_gateways';
		$this->version = '1.6';
		$this->author  = 'EVP International';
	$this->module_key = 'b830e1e952dfce7551c31477a86221af';

		$config = Configuration::getMultiple(array(
			'PAYSERA_PROJECT_ID',
			'PAYSERA_SIGNATURE',
			'PAYSERA_SANDBOX',
			'PAYSERA_PAYMENTLIST',
			'PAYSERA_DEFAULT_COUNTRY'
		));

		$this->project_id     = self::setValue($config, 'PAYSERA_PROJECT_ID');
		$this->signature      = self::setValue($config, 'PAYSERA_SIGNATURE', '');
		$this->sandbox        = self::setValue($config, 'PAYSERA_SANDBOX');
		$this->payment_list    = self::setValue($config, 'PAYSERA_PAYMENTLIST', 1);
		$this->default_country = self::setValue($config, 'PAYSERA_DEFAULT_COUNTRY', 'lt');

		if (!$this->project_id) $this->project_id = '12345';

		parent::__construct();

		$this->page             = basename(__FILE__, '.php');
		$this->displayName      = $this->l('Paysera');
		$this->description      = $this->l('Accept payments by Paysera system');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

		if (!isset($this->project_id) || !isset($this->signature))
			$this->warning = $this->l('Project details must be configured in order to use this module correctly.');

		if (!count(Currency::checkPaymentCurrencies($this->id)))    $this->warning = $this->l('No currency set for this module');
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn')) return false;

		/* add pending order state */
		$order_pending              = new OrderState();
		$order_pending->name        = array_fill(0, 10, 'AWAITING PAYSERA PAYMENT');
		$order_pending->send_email  = 0;
		$order_pending->invoice     = 0;
		$order_pending->color       = 'RoyalBlue';
		$order_pending->unremovable = false;
		$order_pending->logable     = 0;

		if ($order_pending->add()) copy(_PS_ROOT_DIR_.'/modules/paysera/img/order-logo.gif', _PS_ROOT_DIR_.'/img/os/'.(int)$order_pending->id.'.gif');

		Configuration::updateValue('PAYSERA_PENDING', $order_pending->id);

		return true;
	}

	public function uninstall()
	{
		$order_state_pending = new OrderState(Configuration::get('PAYSERA_PENDING'));

		return (
			Configuration::deleteByName('PAYSERA_PROJECT_ID') &&
			Configuration::deleteByName('PAYSERA_SIGNATURE') &&
			Configuration::deleteByName('PAYSERA_SANDBOX') &&
			Configuration::deleteByName('PAYSERA_PAYMENTLIST') &&
			Configuration::deleteByName('PAYSERA_DEFAULT_COUNTRY') &&
			Configuration::deleteByName('PAYSERA_PENDING') &&
			$order_state_pending->delete() &&
			parent::uninstall()
		);
	}

	private function validatePostRequest()
	{
		if (Tools::getValue('btnSubmit'))
		{	
			$pro_id = Tools::getValue('project_id');
			if (empty($pro_id))
			{
				$this->messages[] = array(
					'class' => 'warning warn',
					'src'   => '../img/admin/warning.gif',
					'msg'   => $this->l('Project ID is required!'),
				);

				return false;
			}
		}

		return true;
	}

	private function processPostRequest()
	{
		if (Tools::getValue('btnSubmit'))
		{
			Configuration::updateValue('PAYSERA_PROJECT_ID', (int)Tools::getValue('project_id'));
			Configuration::updateValue('PAYSERA_SIGNATURE', Tools::getValue('signature'));
			Configuration::updateValue('PAYSERA_SANDBOX', (int)Tools::getValue('sandbox'));
			Configuration::updateValue('PAYSERA_PAYMENTLIST', (int)Tools::getValue('paymentList'));
			Configuration::updateValue('PAYSERA_DEFAULT_COUNTRY', Tools::getValue('defaultCountry'));

			$this->project_id     = (int)Tools::getValue('project_id');
			$this->signature      = Tools::getValue('signature');
			$this->sandbox        = (int)Tools::getValue('sandbox');
			$this->payment_list    = (int)Tools::getValue('paymentList');
			$this->default_country = Tools::getValue('defaultCountry');

			$this->messages[] = array(
				'class' => 'conf confirm',
				'src'   => '../img/admin/ok.gif',
				'msg'   => $this->l('Settings updated'),
			);
		}
	}

	public function getContent()
	{
		if ($this->validatePostRequest()) $this->processPostRequest();

		$methods = WebToPay::getPaymentMethodList($this->project_id)->setDefaultLanguage(
			Language::getIsoById($this->context->language->id))->getCountries();

		$this->smarty->assign(array(
			'messages'       => $this->messages,
			'lang'           => $this->context->language->id,
			'requestUrl'     => $_SERVER['REQUEST_URI'],
			'projectId'      => $this->project_id,
			'projectPass'    => $this->signature,
			'defaultCountry' => $this->default_country,
			'sandboxOn'      => ($this->sandbox) ? 'checked="checked"' : '',
			'sandboxOff'     => (!$this->sandbox) ? 'checked="checked"' : '',
			'payListOn'      => ($this->payment_list) ? 'checked="checked"' : '',
			'payListOff'     => (!$this->payment_list) ? 'checked="checked"' : '',
			'countries'      => $methods,
		));

		return $this->display(__FILE__, 'views/templates/front/configuration.tpl');
	}


	/**
	 * NOT IMPLEMENTED
	 *
	 * @param int $order_id
	 * @return string
	 */
	private function restoreOrder($order_id)
	{
		$order = new Order($order_id);

		if (Validate::isLoadedObject($order) && $order->id_customer == $this->context->customer->id)
		{
			try {
				$request = WebToPay::buildRepeatRequest(array(
					'projectid'     => $this->project_id,
					'sign_password' => $this->signature,
					'orderid'       => $order_id,
				));
			} catch (WebToPayException $e) {
				echo get_class($e).':'.$e->getMessage();
			}

			$this->smarty->assign(array(
				'payUrl'  => WebToPay::PAY_URL,
				'request' => $request,
			));

			return $this->display(__FILE__, 'views/templates/front/restore.tpl');
		}
		else exit('Bad restore number!');
	}

	public function hookPayment()
	{
		$iso_code = $this->context->language->iso_code;
		$this->smarty->assign(array(
			'lang'          => $iso_code,
			'this_path'     => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').
				htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('PAYSERA_ACCEPTED') || $state == _PS_OS_OUTOFSTOCK_ || 1)
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false, null),
				'status'       => 'ok',
				'id_order'     => $params['objOrder']->id
			));
		}
		else
			$this->smarty->assign('status', 'failed');

		return $this->display(__FILE__, 'payment_return.tpl');
	}

	/**
	 * @param int $id_currency : this parameter is optionnal but on 1.5 version of Prestashop, it will be REQUIRED
	 * @return Currency
	 */
	public function getCurrency($current_id_currency = null)
	{
		if (!(int)$current_id_currency)
			$current_id_currency = Context::getContext()->currency->id;

		if (!$this->currencies)
			return false;
		if ($this->currencies_mode == 'checkbox')
		{
			$currencies = Currency::getPaymentCurrencies($this->id);
			return $currencies;
		}
		elseif ($this->currencies_mode == 'radio')
		{
			$currencies = Currency::getPaymentCurrenciesSpecial($this->id);
			$currency   = $currencies['id_currency'];
			if ($currency == -1)
				$id_currency = (int)$current_id_currency;
			elseif ($currency == -2)
				$id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT'); else
				$id_currency = $currency;
		}
		if (!isset($id_currency) || empty($id_currency))
			return false;
		return (new Currency($id_currency));
	}

	public function checkCurrency($cart)
	{
		$currency_order    = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module) if ($currency_order->id == $currency_module['id_currency']) return true;

		return false;
	}

	private static function setValue($config = array(), $value = '', $default = 0)
	{
		return (isset($config[$value])) ? $config[$value] : $default;
	}

	/**
	 * Debug function
	 *
	 * @param $var
	 */
	private static function d($var)
	{
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}

}
