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

class PayseraValidationModuleFrontController extends ModuleFrontController {
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		
	    $response = WebToPay::validateAndParseData($_REQUEST, $this->module->project_id, $this->module->signature);

	    $order = new Order($response['orderid']);
	   

		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer)) Tools::redirect('index.php?controller=order&step=1');

		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$order->id_cart.'&id_module='.$this->module->id.
			'&id_order='.$response['orderid'].'&key='.$customer->secure_key);
	}
}
