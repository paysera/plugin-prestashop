<?php

require_once(_PS_MODULE_DIR_ . '/mokejimai/vendor/webtopay/libwebtopay/WebToPay.php');

/**
 * @since 1.5.0
 */
class MokejimaiCallbackModuleFrontController extends ModuleFrontController {
    public $display_column_left = false;
    public $display_column_right = false;
    public $display_header = false;
    public $display_footer = false;
    public $ssl = true;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {
        try {
            $response = WebToPay::checkResponse($_REQUEST, array(
                'projectid'     => $this->module->project_id,
                'sign_password' => $this->module->signature,
            ));

            if ($response['status'] == 1) {
                $Order        = new Order($response['orderid']);
                $orderAmount  = $Order->getOrdersTotalPaid();
                $cartCurrency = Currency::getCurrency($Order->id_currency);

                if (intval(number_format($response['amount'], 0, '', '')) < intval(number_format(($orderAmount * 100), 0, '', ''))) {
                    exit('Bad amount: ' . $response['amount']);
                }

                if ($response['currency'] != $cartCurrency['iso_code']) {
                    exit('Bad currency: ' . $response['currency']);
                }

                $history           = new OrderHistory();
                $history->id_order = $response['orderid'];
                $history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), $response['orderid']);
                $history->addWithemail(true, array(
                    'order_name' => $response['orderid'],
                ));
            }
            exit('OK');
        } catch (Exception $e) {
            exit(get_class($e) . ': ' . $e->getMessage());
        }
    }
}
