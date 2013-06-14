<?php

/**
 * @since 1.5.0
 */
class MokejimaiRedirectModuleFrontController extends ModuleFrontController {
    public $display_column_left = true;
    public $display_column_right = true;
    public $display_header = true;
    public $display_footer = true;
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        parent::initContent();

        global $link, $cookie;

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        $address      = new Address(intval($cart->id_address_invoice));
        $customer     = new Customer(intval($cart->id_customer));
        $country      = new Country(intval($address->id_country));
        $currency     = Context::getContext()->currency;
        $total        = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
        $languageCode = strtoupper(Language::getIsoById(intval($cookie->id_lang)));
        $urlLanguage  = ($languageCode == 'LT') ? 'LIT' : 'ENG';

        $this->module->validateOrder($cart->id, Configuration::get('PAYSERA_PENDING'), $total, $this->module->displayName, NULL, NULL, $currency->id);

        try {
            $request = WebToPay::buildRequest(array(
                'projectid'     => $this->module->project_id,
                'sign_password' => $this->module->signature,

                'orderid'       => $this->module->currentOrder,
                'amount'        => intval(number_format($total, 2, '', '')),
                'currency'      => $currency->iso_code,
                'lang'          => ($languageCode == 'LT') ? 'LTU' : 'ENG',

                'accepturl'     => $link->getModuleLink('mokejimai', 'validation'),
                'cancelurl'     => $link->getModuleLink('mokejimai', 'cancel'),
                'callbackurl'   => $link->getModuleLink('mokejimai', 'callback'),
                'payment'       => (isset($_POST['payment'])) ? $_POST['payment'] : '',

                'p_firstname'   => $customer->firstname,
                'p_lastname'    => $customer->lastname,
                'p_email'       => $customer->email,
                'p_street'      => $address->address1,
                'p_city'        => $address->city,
                'p_zip'         => $address->postcode,
                'p_countrycode' => $country->iso_code,
                'test'          => $this->module->sandbox,
            ));
        } catch (WebToPayException $e) {
            echo get_class($e) . ': ' . $e->getMessage();
        }

        $this->context->smarty->assign(array(
            'payUrl'  => WebToPay::getPaymentUrl($urlLanguage),
            'request' => $request,
        ));

        $this->setTemplate('redirect.tpl');
    }
}
