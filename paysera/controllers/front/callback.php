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

require_once(_PS_MODULE_DIR_.'/paysera/vendor/webtopay/libwebtopay/WebToPay.php');

class PayseraCallbackModuleFrontController extends ModuleFrontController {
	public $display_column_left = false;
	public $display_column_right = false;
	public $display_header = false;
	public $display_footer = false;
	public $ssl = true;

	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		try {
			$response = WebToPay::checkResponse($_REQUEST, array(
				'projectid'     => $this->module->project_id,
				'sign_password' => $this->module->signature,
			));

			if ($response['status'] == 1)
			{
				$orderid = $response['orderid'];
				$order        = new Order($orderid);
				$order_amount  = $order->getOrdersTotalPaid();
				$cart_currency = Currency::getCurrency($order->id_currency);

				if (number_format($response['amount'], 0, '', '') < number_format(($order_amount * 100), 0, '', '')) exit('Bad amount: '.$response['amount']);

				if ($response['currency'] != $cart_currency['iso_code']) exit('Bad currency: '.$response['currency']);

				$history = new OrderHistory();
				$history->id_order = $orderid;
				$history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), $response['orderid']);
				$history->addWithemail(true, array(
					'order_name' => $orderid,
				));
				exit('OK');
			}
		} catch (Exception $e) {
			exit(get_class($e).': '.$e->getMessage());
		}
	}
}
