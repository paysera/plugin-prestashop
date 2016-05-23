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
 *  @author    Illicopresta SA <contact@illicopresta.com>
 *  @copyright 2007-2014 Illicopresta
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class PayseraPaymentModuleFrontController extends ModuleFrontController {
	public $display_column_left = false;
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart)) Tools::redirect('index.php?controller=order');

		$pay_currency = Context::getContext()->currency;
		$pay_amount   = $cart->getOrderTotal() * 100;

		//siuo metu pasirinkta kalba
		$language = strtolower(Language::getIsoById($this->context->language->id));
		$language = (!in_array( $language, array('lt', 'en', 'ru', 'lv'))) ? 'en' : $language;

		$methods = WebToPay::getPaymentMethodList($this->module->project_id, $pay_currency->iso_code)->filterForAmount($pay_amount,
			$pay_currency->iso_code)->setDefaultLanguage($language)->getCountries();

		$this->context->smarty->assign(array(
			'this_path_ssl'   => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
			'amount'          => ($pay_amount / 100),
			'currency'        => $pay_currency->iso_code,
			'displayPayments' => $this->module->payment_list,
			'isoCode'         => $language, //Language::getIsoById(intval($cookie->id_lang)),
			'defaultCountry'  => $this->module->default_country,
			'payMethods'      => $methods,
		));

		$this->setTemplate('payment_execution.tpl');
	}
}
