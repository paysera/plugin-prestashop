<?php

require_once(_PS_MODULE_DIR_ . '/mokejimai/vendor/webtopay/libwebtopay/WebToPay.php');

class Mokejimai extends PaymentModule {

    private $messages = array();

    public $project_id;
    public $signature;
    public $sandbox;
    public $paymentList;
    public $defaultCountry;

    public function __construct() {
        $this->name    = 'mokejimai';
        $this->tab     = 'payments_gateways';
        $this->version = WebToPay::VERSION;
        $this->author  = 'EVP International';

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
        $this->paymentList    = self::setValue($config, 'PAYSERA_PAYMENTLIST', 1);
        $this->defaultCountry = self::setValue($config, 'PAYSERA_DEFAULT_COUNTRY', 'lt');

        if (!$this->project_id) {
            $this->project_id = '12345';
        }

        parent::__construct();

        $this->page             = basename(__FILE__, '.php');
        $this->displayName      = $this->l('Mokejimai');
        $this->description      = $this->l('Accept payments by Mokejimai.lt system');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

        if (!isset($this->project_id) || !isset($this->signature)) {
            $this->warning = $this->l('Project details must be configured in order to use this module correctly.');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency set for this module');
        }
    }

    public function install() {
        if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn')) {
            return false;
        }

        /* add pending order state */
        $OrderPending              = new OrderState();
        $OrderPending->name        = array_fill(0, 10, 'AWAITING MOKEJIMAI.LT PAYMENT');
        $OrderPending->send_email  = 0;
        $OrderPending->invoice     = 0;
        $OrderPending->color       = 'RoyalBlue';
        $OrderPending->unremovable = false;
        $OrderPending->logable     = 0;

        if ($OrderPending->add()) {
            @copy(_PS_ROOT_DIR_ . '/modules/mokejimai/img/order-logo.gif', _PS_ROOT_DIR_ . '/img/os/' . (int)$OrderPending->id . '.gif');
        }

        Configuration::updateValue('PAYSERA_PENDING', $OrderPending->id);

        return true;
    }

    public function uninstall() {
        $OrderStatePending = new OrderState(Configuration::get('PAYSERA_PENDING'));

        return (
            Configuration::deleteByName('PAYSERA_PROJECT_ID')      AND
            Configuration::deleteByName('PAYSERA_SIGNATURE')       AND
            Configuration::deleteByName('PAYSERA_SANDBOX')         AND
            Configuration::deleteByName('PAYSERA_PAYMENTLIST')     AND
            Configuration::deleteByName('PAYSERA_DEFAULT_COUNTRY') AND
            Configuration::deleteByName('PAYSERA_PENDING')            AND
            $OrderStatePending->delete()                            AND
            parent::uninstall()
        );
    }

    private function validatePostRequest() {
        if (isset($_POST['btnSubmit'])) {
            if (empty($_POST['project_id'])) {
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

    private function processPostRequest() {
        if (isset($_POST['btnSubmit'])) {
            Configuration::updateValue('PAYSERA_PROJECT_ID', intval($_POST['project_id']));
            Configuration::updateValue('PAYSERA_SIGNATURE', $_POST['signature']);
            Configuration::updateValue('PAYSERA_SANDBOX', intval($_POST['sandbox']));
            Configuration::updateValue('PAYSERA_PAYMENTLIST', intval($_POST['paymentList']));
            Configuration::updateValue('PAYSERA_DEFAULT_COUNTRY', $_POST['defaultCountry']);

            $this->project_id     = intval($_POST['project_id']);
            $this->signature      = $_POST['signature'];
            $this->sandbox        = intval($_POST['sandbox']);
            $this->paymentList    = intval($_POST['paymentList']);
            $this->defaultCountry = $_POST['defaultCountry'];

            $this->messages[] = array(
                'class' => 'conf confirm',
                'src'   => '../img/admin/ok.gif',
                'msg'   => $this->l('Settings updated'),
            );
        }
    }

    public function getContent() {
        global $cookie, $smarty;

        if ($this->validatePostRequest()) {
            $this->processPostRequest();
        }

        $methods = WebToPay::getPaymentMethodList($this->project_id)
            ->setDefaultLanguage(Language::getIsoById(intval($cookie->id_lang)))
            ->getCountries();

        $smarty->assign(array(
            'messages'       => $this->messages,
            'requestUrl'     => $_SERVER['REQUEST_URI'],
            'projectId'      => $this->project_id,
            'projectPass'    => $this->signature,
            'defaultCountry' => $this->defaultCountry,
            'sandboxOn'      => ($this->sandbox) ? 'checked="checked"' : '',
            'sandboxOff'     => (!$this->sandbox) ? 'checked="checked"' : '',
            'payListOn'      => ($this->paymentList) ? 'checked="checked"' : '',
            'payListOff'     => (!$this->paymentList) ? 'checked="checked"' : '',
            'countries'      => $methods,
        ));

        return $this->display(__FILE__, 'views/templates/front/configuration.tpl');
    }


    /**
     * NOT IMPLEMENTED
     *
     * @param int $orderId
     * @return string
     */
    private function restoreOrder($orderId) {
        global $smarty, $cookie;

        $order = new Order($orderId);

        if (Validate::isLoadedObject($order) && $order->id_customer == $cookie->id_customer) {
            try {
                $request = WebToPay::buildRepeatRequest(array(
                    'projectid'     => $this->project_id,
                    'sign_password' => $this->signature,
                    'orderid'       => $orderId,
                ));
            } catch (WebToPayException $e) {
                echo get_class($e) . ': ' . $e->getMessage();
            }

            $smarty->assign(array(
                'payUrl'  => WebToPay::PAY_URL,
                'request' => $request,
            ));

            return $this->display(__FILE__, 'views/templates/front/restore.tpl');
        } else {
            exit('Bad restore number!');
        }
    }

    public function hookPayment($params) {
        global $smarty;

        $smarty->assign(array(
            'this_path'     => $this->_path,
            'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    public function hookPaymentReturn($params) {
        global $smarty;

        $state = $params['objOrder']->getCurrentState();
        if ($state == Configuration::get('PAYSERA_ACCEPTED') OR $state == _PS_OS_OUTOFSTOCK_ || 1) {
            $smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false, false),
                'status'       => 'ok',
                'id_order'     => $params['objOrder']->id
            ));
        } else {
            $smarty->assign('status', 'failed');
        }

        return $this->display(__FILE__, 'views/templates/front/payment_return.tpl');
    }

    /**
     * @param int $id_currency : this parameter is optionnal but on 1.5 version of Prestashop, it will be REQUIRED
     * @return Currency
     */
    public function getCurrency($current_id_currency = null) {
        if (!(int)$current_id_currency)
            $current_id_currency = Context::getContext()->currency->id;

        if (!$this->currencies)
            return false;
        if ($this->currencies_mode == 'checkbox') {
            $currencies = Currency::getPaymentCurrencies($this->id);
            return $currencies;
        } elseif ($this->currencies_mode == 'radio') {
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

    public function checkCurrency($cart) {
        $currency_order    = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function setValue($config = array(), $value = '', $default = 0) {
        return (isset($config[$value])) ? $config[$value] : $default;
    }

    /**
     * Debug function
     *
     * @param $var
     */
    private static function d($var) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

}
