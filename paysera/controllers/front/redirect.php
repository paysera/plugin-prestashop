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

class PayseraRedirectModuleFrontController extends ModuleFrontController {
	public $display_column_left = true;
	public $display_column_right = true;
	public $display_header = true;
	public $display_footer = true;
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart)) Tools::redirect('index.php?controller=order');

		$address      = new Address((int)$cart->id_address_invoice);
		$customer     = new Customer((int)$cart->id_customer);
		$country      = new Country((int)$address->id_country);
		$currency     = Context::getContext()->currency;
		$total        = (float)number_format($cart->getOrderTotal(true, 3), 2, '.', '');
		$language_code = Tools::strtoupper(Language::getIsoById((int)$this->context->language->id));
		$url_language  = ($language_code == 'LT') ? 'LIT' : 'ENG';

		$this->module->validateOrder($cart->id, Configuration::get('PAYSERA_PENDING'), $total, $this->module->displayName, null, null, $currency->id);

		try {
			$request = WebToPay::buildRequest(array(
				'projectid'     => $this->module->project_id,
				'sign_password' => $this->module->signature,

				'orderid'       => $this->module->currentOrder,
				'amount'        => (int)number_format($total, 2, '', ''),
				'currency'      => $currency->iso_code,
				'lang'          => ($language_code == 'LT') ? 'LTU' : 'ENG',
				'country'       => $country->iso_code,
				'accepturl'     => $this->context->link->getModuleLink('paysera', 'validation'),
				'cancelurl'     => $this->context->link->getModuleLink('paysera', 'cancel'),
				'callbackurl'   => $this->context->link->getModuleLink('paysera', 'callback'),
				'payment'       => Tools::getValue('payment_method'),
				'p_firstname'   => $customer->firstname,
				'p_lastname'    => $customer->lastname,
				'p_email'       => $customer->email,
				'p_street'      => $address->address1,
				'p_city'        => $address->city,
				'p_zip'         => $address->postcode,
				'p_countrycode' => $country->iso_code,
				'test'          => $this->module->sandbox,
				'delivery'      => '',
				'system'        => 'Prestashop 1.6',
				'component'     => '',
			));
		} catch (WebToPayException $e) {
			echo get_class($e).': '.$e->getMessage();
		}

		$this->context->smarty->assign(array(
			'payUrl'  => WebToPay::getPaymentUrl($url_language),
			'request' => $request,
		));

		$this->setTemplate('redirect.tpl');
	}
}
