<?php
/*
* 2007-2012 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 13573 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class MokejimaiPaymentModuleFrontController extends ModuleFrontController {
    public $display_column_left = false;
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        parent::initContent();

        global $cookie;

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        $payCurrency = Context::getContext()->currency;
        $payAmount   = $cart->getOrderTotal() * 100;

        //siuo metu pasirinkta kalba
        $language = Language::getIsoById(intval($cookie->id_lang));
        $language = (!in_array($language, array('lt', 'en', 'ru'))) ? 'en' : $language;

        $methods = WebToPay::getPaymentMethodList($this->module->project_id, $payCurrency->iso_code)
            ->filterForAmount($payAmount, $payCurrency->iso_code)
            ->setDefaultLanguage($language)
            ->getCountries();

        $this->context->smarty->assign(array(
            'this_path_ssl'   => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
            'amount'          => ($payAmount / 100),
            'currency'        => $payCurrency->iso_code,
            'displayPayments' => $this->module->paymentList,
            'isoCode'         => $language, //Language::getIsoById(intval($cookie->id_lang)),
            'defaultCountry'  => $this->module->defaultCountry,
            'payMethods'      => $methods,
        ));

        $this->setTemplate('payment_execution.tpl');
    }
}
