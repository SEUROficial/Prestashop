<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Maria Jose Santos <mariajose.santos@ebolution.com>
 * @copyright 2022 Seur Transporte
 * @license https://seur.com/ Proprietary
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

if (!class_exists('SeurLib'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

if (!class_exists('SeurCCC'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurCCC.php');

if (!class_exists('SeurOrder'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurOrder.php');

if (!class_exists('SeurCarrier'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurCarrier.php');

include_once(_PS_MODULE_DIR_.'seur/classes/Range.php');
include_once(_PS_MODULE_DIR_.'seur/classes/User.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Pickup.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Expedition.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Label.php');
include_once(_PS_MODULE_DIR_.'seur/classes/Manifest.php');
include_once(_PS_MODULE_DIR_.'seur/classes/ProductType.php');
include_once(_PS_MODULE_DIR_.'seur/classes/commands/UpdateShipmentsStatus.php');
include_once(_PS_MODULE_DIR_.'seur/classes/commands/AutoCreateLabel.php');


if (!class_exists('SeurCashOnDelivery'))
    if (file_exists(_PS_MODULE_DIR_.'seurcashondelivery/seurcashondelivery.php'))
        include_once(_PS_MODULE_DIR_.'seurcashondelivery/seurcashondelivery.php');

class Seur extends CarrierModule
{
    protected $config_form = false;
    public $path;
    private $js_url;

    public function __construct()
    {
        $this->name = 'seur';
        $this->tab = 'shipping_logistics';
        $this->version = '2.5.21';
        $this->author = 'Seur';
        $this->need_instance = 0;

        $this->tabs = array();

        $this->js_url = 'https://maps.google.com/maps/api/js?key=' . Configuration::get('SEUR2_GOOGLE_API_KEY');

        $this->tabs['AdminSeurAdmin'] = array(
            'label' => $this->l('Módulo SEUR'),
            'rootClass' => true,
        );
        $this->tabs['AdminSeurConfig'] = array(
            'label' => $this->l('Configuración SEUR'),
            'rootClass' => false,
            'parent' => 'AdminSeurAdmin',
        );
        $this->tabs['AdminSeurShipping'] = array(
            'label' => $this->l('Gestión de pedidos'),
            'rootClass' => false,
            'parent' => 'AdminSeurAdmin',
        );
        $this->tabs['AdminSeurCollecting'] = array(
            'label' => $this->l('Gestión de recogidas'),
            'rootClass' => false,
            'parent' => 'AdminSeurAdmin',
        );
        $this->tabs['AdminSeurTracking'] = array(
            'label' => $this->l('Seguimiento de envíos'),
            'rootClass' => false,
            'parent' => 'AdminSeurAdmin',
        );
        $this->tabs['AdminSeurCarrier'] = array(
            'label' => $this->l('Transportistas SEUR'),
            'rootClass' => false,
            'parent' => 'AdminSeurAdmin',
        );
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->path = $this->_path;

        $this->displayName = $this->l('SEUR');
        $this->description = $this->l('Manage your shipments with SEUR. Leader in the Express Shipping, National or International.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        /** Backward compatibility 1.4 / 1.5 */
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            require_once(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
            $this->context = Context::getContext();
            $this->smarty = $this->context->smarty;
        } else {
            $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        }

        if (!class_exists('SoapClient')) {
            $this->active = 1; //only to display warning before install
            $this->warning = $this->l('The SOAP extension is not enabled on your server, please contact to your hosting provider.');
        } elseif (!$this->isConfigured()) {
            $this->warning = $this->l('Still has not configured their SEUR module.');
        }

        if (!$this->isRegisteredInHook('actionOrderEdited'))
            $this->registerHook('actionOrderEdited');

        if (!$this->isRegisteredInHook('actionAdminControllerSetMedia'))
            $this->registerHook('actionAdminControllerSetMedia');
    }

    /*************************************************************************************
     *                                  INSTALL
     ************************************************************************************/

    public function install()
    {
        if (!extension_loaded('soap') || !class_exists('SoapClient')) {
            $this->_errors[] = $this->l('SOAP extension should be enabled on your server to use this module.');
            return false;
        }

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        if (!parent::install()
            || !$this->registerHook('adminOrder')
            || !$this->registerHook('updateCarrier')
            || !$this->registerHook('header')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('displayAdminOrder')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('actionOrderEdited')
            || !$this->registerHook('actionAdminControllerSetMedia')
        ) {
            $this->l('Hooks not registered');
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            $this->registerHook('extraCarrier');
        } else {
            $this->registerHook('displayAfterCarrier');
            $this->registerHook('actionFrontControllerSetMedia');
        }

        if (version_compare(_PS_VERSION_, '1.5.4', '<')) {
            if (!$this->registerHook('orderDetailDisplayed')) {
                $this->_errors[] = $this->l('Hook orderDetailDisplayed not registered');
                return false;
            }
        } else {
            if (!$this->registerHook('displayOrderDetail')) {
                $this->_errors[] = $this->l('Hook displayOrderDetail not registered');
                return false;
            }
        }

        if (!$this->createAdminTab()) {
            $this->uninstall();
            $this->_errors[] = $this->l('Error to create Tabs');
            return false;
        }

        $this->createOrderStates();

        if (!$this->installSeurCashOnDelivery()
        ) {
            $this->uninstall();
            $this->_errors[] = $this->l('Error to install Cash on delivery');
            return false;
        }

        if (!$this->createDatabases()) {
            $this->uninstall();
            $this->_errors[] = $this->l('Error to create Data Base');
            return false;
        }

        $product_type = new ProductType;
        if (!$product_type->install()) {
            $this->uninstall();
            $this->_errors[] = $this->l('Error to create Product Feature Seur_product_type');
            return false;
        }
        return true;
    }

    public function createDatabases()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        /* Webservices default configuration */
        Configuration::updateValue('SEUR2_URLWS_SP', 'https://ws.seur.com/WSEcatalogoPublicos/servlet/XFireServlet/WSServiciosWebPublicos?wsdl');
        Configuration::updateValue('SEUR2_URLWS_R',  'https://ws.seur.com/webseur/services/WSCrearRecogida?wsdl');
        Configuration::updateValue('SEUR2_URLWS_A',  'https://ws.seur.com/webseur/services/WSConsultaAlbaranes?wsdl');

        Configuration::updateValue('SEUR2_URLWS_M',  'http://cit.seur.com/CIT-war/services/DetalleBultoPDFWebService?wsdl');

        /* Global configuration */
        Configuration::updateValue('SEUR2_REMCAR_CARGO', 5.5);
        Configuration::updateValue('SEUR2_REMCAR_CARGO_MIN', 0);

        Configuration::updateValue('SEUR2_NACIONAL_SERVICE', '031');
        Configuration::updateValue('SEUR2_NACIONAL_PRODUCT', '002');
        Configuration::updateValue('SEUR2_INTERNACIONAL_SERVICE', '077');
        Configuration::updateValue('SEUR2_INTERNACIONAL_PRODUCT', '070');

        Configuration::updateValue('SEUR2_COD_REEMBOLSO', 40);

        Configuration::updateValue('SEUR2_URLWS_ADD_SHIP',    'https://api.seur.com/geolabel/api/shipment/addShipment');
        Configuration::updateValue('SEUR2_URLWS_SHIP_LABEL',  'https://api.seur.com/geolabel/api/shipment/getLabel');
        Configuration::updateValue('SEUR2_URLWS_ET_GEOLABEL', 'https://api.seur.com/geolabel/swagger-ui.html#/add-shipment-controller');

        Configuration::updateValue('SEUR2_URLWS_TOKEN',        'https://servicios.api.seur.io/pic_token');
        Configuration::updateValue('SEUR2_URLWS_BREXIT_INV',   'https://servicios.api.seur.io/pic/v1/brexit/invoices');
        Configuration::updateValue('SEUR2_URLWS_BREXIT_TARIF', 'https://servicios.api.seur.io/pic/v1/brexit/tariff-item');
        Configuration::updateValue('SEUR2_URLWS_PICKUP',       'https://servicios.api.seur.io/pic/v1/collections');
        Configuration::updateValue('SEUR2_URLWS_PICKUP_CANCEL','https://servicios.api.seur.io/pic/v1/collections/cancel');
        Configuration::updateValue('SEUR2_URLWS_PICKUPS',      'https://servicios.api.seur.io/pic/v1/pickups');
        Configuration::updateValue('SEUR2_URLWS_E',            'https://servicios.api.seur.io/pic/v1/tracking-services/simplified');
        Configuration::updateValue('SEUR2_URLWS_ET',             'https://servicios.api.seur.io/pic/v1/shipments');
        Configuration::updateValue('SEUR2_URLWS_SHIPMENT_UPDATE','https://servicios.api.seur.io/pic/v1/shipments/update');
        Configuration::updateValue('SEUR2_URLWS_LABELS',       'https://servicios.api.seur.io/pic/v1/labels');
        Configuration::updateValue('SEUR2_URLWS_UPDATE_SHIPMENTS_ADD_PARCELS', 'https://servicios.api.seur.io/pic/v1/shipments/addpack');

        Configuration::updateValue('SEUR2_PICKUP_SERVICE', '1');
        Configuration::updateValue('SEUR2_PICKUP_PRODUCT', '2');
        Configuration::updateValue('SEUR2_PICKUP_SERVICE_FRIO', '9');
        Configuration::updateValue('SEUR2_PICKUP_PRODUCT_FRIO', '18');

        Configuration::updateValue('SEUR2_PICKUP_SERVICE_INT', '77');
        Configuration::updateValue('SEUR2_PICKUP_PRODUCT_INT', '70');
        Configuration::updateValue('SEUR2_PICKUP_SERVICE_INT_FRIO', '77');
        Configuration::updateValue('SEUR2_PICKUP_PRODUCT_INT_FRIO', '114');

        Configuration::updateValue('SEUR2_PICKUP_SERVICE_INT_NOEUR', '77');
        Configuration::updateValue('SEUR2_PICKUP_PRODUCT_INT_NOEUR', '114');
        Configuration::updateValue('SEUR2_PICKUP_SERVICE_INT_NOEUR_FRIO', '77');
        Configuration::updateValue('SEUR2_PICKUP_PRODUCT_INT_NOEUR_FRIO', '114');

        Configuration::updateValue('SEUR2_PRODS_REFS_IN_COMMENTS', 0);

        Configuration::updateValue('SEUR2_AUTO_CREATE_LABELS', 0);
        Configuration::updateValue('SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE', '');
        Configuration::updateValue('SEUR2_AUTO_CALCULATE_PACKAGES', 0);



        return true;
    }

    public function createAdminTab()
    {
        $this->uninstallTab();

        $flagInstall = true;
        // Build menu tabs
        foreach ($this->tabs as $className => $data) {
            // Check if exists
            if (!$id_tab = Tab::getIdFromClassName($className)) {
                if ($data['rootClass']) {
                    $this->_confirmations[] = "Instalando Tab $className<br>\n";
                    $flagInstall = $flagInstall && $this->installModuleTab($className, $data['label'], 0);
                } else {
                    $this->_confirmations[] = "Instalando Tab $className cuyo padre es " . $data['parent'] . "<br>\n";
                    $flagInstall = $flagInstall && $this->installModuleTab($className, $data['label'], (int)Tab::getIdFromClassName($data['parent']));
                }
            }
        }

        return $flagInstall;
    }


    public function installModuleTab($tabClass, $tabName, $idTabParent)
    {
        Logger::addLog("ADMIN TAB Install. $tabClass, $tabName, $idTabParent", 1);

        // Create tab object
        $tab = new Tab();
        $tab->class_name = $tabClass;
        $tab->id_parent = $idTabParent;
        $tab->module = $this->name;
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l($tabName);
        }

        return $tab->save();
    }

    /**
     * Create new order states
     */
    public function createOrderStates()
    {
        // PEDIDO CON INCIDENCIA
        if (!Configuration::get('SEUR2_STATUS_INCIDENCE')) {
            $order_state = new OrderState();
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'es') {
                    $order_state->name[$language['id_lang']] = 'SEUR: Envío con Incidencia';
                } else {
                    $order_state->name[$language['id_lang']] = 'SEUR: Shipping Incidence';
                }
            }

            $order_state->send_email = false;
            $order_state->color = '#ffd31c';
            $order_state->hidden = true;
            $order_state->delivery = false;
            $order_state->logable = true;
            $order_state->invoice = true;

            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/seur_estado.gif';
                $destination = dirname(__FILE__) . '/../../img/os/' . (int)$order_state->id . '.gif';
                copy($source, $destination);
            }
            Configuration::updateValue('SEUR2_STATUS_INCIDENCE', (int)$order_state->id);
        }

        // PEDIDO EN TRÁNSITO
        if (!Configuration::get('SEUR2_STATUS_IN_TRANSIT')) {
            $order_state = new OrderState();
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'es') {
                    $order_state->name[$language['id_lang']] = 'SEUR: Envío en tránsito';
                } else {
                    $order_state->name[$language['id_lang']] = 'SEUR: Shipping in transit';
                }
            }

            $order_state->send_email = false;
            $order_state->color = '#006aff';
            $order_state->hidden = true;
            $order_state->delivery = false;
            $order_state->logable = true;
            $order_state->invoice = true;

            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/seur_estado.gif';
                $destination = dirname(__FILE__) . '/../../img/os/' . (int)$order_state->id . '.gif';
                copy($source, $destination);
            }
            Configuration::updateValue('SEUR2_STATUS_IN_TRANSIT', (int)$order_state->id);
        }

        // DEVOLUCIÓN EN PROGRESO
        if (!Configuration::get('SEUR2_STATUS_RETURN_IN_PROGRESS')) {
            $order_state = new OrderState();
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'es') {
                    $order_state->name[$language['id_lang']] = 'SEUR: Devolución en progreso';
                } else {
                    $order_state->name[$language['id_lang']] = 'SEUR: Return in progress';
                }
            }

            $order_state->send_email = false;
            $order_state->color = '#bf0044';
            $order_state->hidden = true;
            $order_state->delivery = false;
            $order_state->logable = true;
            $order_state->invoice = true;

            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/seur_estado.gif';
                $destination = dirname(__FILE__) . '/../../img/os/' . (int)$order_state->id . '.gif';

                copy($source, $destination);
            }
            Configuration::updateValue('SEUR2_STATUS_RETURN_IN_PROGRESS', (int)$order_state->id);
        }

        Configuration::updateValue('SEUR2_STATUS_DELIVERED', 5);

        // PEDIDO DISPONIBLE RECOGIDA EN TIENDA
        if (!Configuration::get('SEUR2_STATUS_AVAILABLE_IN_STORE')) {
            $order_state = new OrderState();
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'es') {
                    $order_state->name[$language['id_lang']] = 'SEUR: Disponible en tienda';
                } else {
                    $order_state->name[$language['id_lang']] = 'SEUR: Available in store';
                }
            }

            $order_state->send_email = false;
            $order_state->color = '#00a762';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = true;
            $order_state->invoice = true;

            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/seur_estado.gif';
                $destination = dirname(__FILE__) . '/../../img/os/' . (int)$order_state->id . '.gif';

                copy($source, $destination);
            }
            Configuration::updateValue('SEUR2_STATUS_AVAILABLE_IN_STORE', (int)$order_state->id);
        }

        // PEDIDO REQUIERE INTERVENCIÓN PARA SOLUCIÓN
        if (!Configuration::get('SEUR2_STATUS_CONTRIBUTE_SOLUTION')) {
            $order_state = new OrderState();
            $order_state->name = array();

            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'es') {
                    $order_state->name[$language['id_lang']] = 'SEUR: Intervención requerida';
                } else {
                    $order_state->name[$language['id_lang']] = 'SEUR: Contribute solution';
                }
            }

            $order_state->send_email = false;
            $order_state->color = '#d74900';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = true;
            $order_state->invoice = true;

            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/seur_estado.gif';
                $destination = dirname(__FILE__) . '/../../img/os/' . (int)$order_state->id . '.gif';

                copy($source, $destination);
            }
            Configuration::updateValue('SEUR2_STATUS_CONTRIBUTE_SOLUTION', (int)$order_state->id);
        }

        Configuration::updateValue('SEUR2_STATUS_DELIVERED', Configuration::get("PS_OS_DELIVERED"));

    }

    public function installSeurCashOnDelivery()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (!is_dir(_PS_MODULE_DIR_ . 'seurcashondelivery')) {
                $module_dir = _PS_MODULE_DIR_ . str_replace(array('.', '/', '\\'), array('', '', ''), 'seurcashondelivery');
                $this->recursiveDeleteOnDisk($module_dir);
            }
            $dir = _PS_MODULE_DIR_ . $this->name . '/install/1.5/seurcashondelivery';
            if (!is_dir($dir))
                return false;

            $this->copyDirectory($dir, _PS_MODULE_DIR_ . 'seurcashondelivery');
            $cash_on_delivery = Module::GetInstanceByName('seurcashondelivery');

            return $cash_on_delivery->install();
        } else {
            if (!is_dir(_PS_MODULE_DIR_ . 'seurcashondelivery')) {
                $module_dir = _PS_MODULE_DIR_ . str_replace(array('.', '/', '\\'), array('', '', ''), 'seurcashondelivery');
                $this->recursiveDeleteOnDisk($module_dir);
            }
            $dir = _PS_MODULE_DIR_ . $this->name . '/install/1.7/seurcashondelivery';
            if (!is_dir($dir))
                return false;

            $this->copyDirectory($dir, _PS_MODULE_DIR_ . 'seurcashondelivery');
            $cash_on_delivery = Module::GetInstanceByName('seurcashondelivery');

            return $cash_on_delivery->install();
        }
    }

    public function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false)
            return;
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object)
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir')
                        $this->recursiveDeleteOnDisk($dir . '/' . $object);
                    else
                        unlink($dir . '/' . $object);
                }
            reset($objects);
            rmdir($dir);
        }
    }

    public function copyDirectory($source, $target)
    {
        if (!is_dir($source)) {
            copy($source, $target);
            return null;
        }

        @mkdir($target);
        chmod($target, 0755);
        $d = dir($source);
        $nav_folders = array('.', '..');
        while (false !== ($file_entry = $d->read())) {
            if (in_array($file_entry, $nav_folders))
                continue;

            $s = "$source/$file_entry";
            $t = "$target/$file_entry";
            self::copyDirectory($s, $t);
        }
        $d->close();
    }

    /*************************************************************************************
     *                                  UNINSTALL
     ************************************************************************************/

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        if (!$this->uninstallSeurCashOnDelivery()) {
            $this->_errors[] = $this->l('Error to Uninstall Seur Cash on delivery');
            return false;
        }

        if (!$this->uninstallTab()) {

            $this->_errors[] = $this->l('Error to Uninstall tabs');
            return false;
        }

        /*
        if (!$this->deleteSettings()) {

            $this->_errors[] = $this->l('Error to Delete Settings');
            return false;
        }*/
        return parent::uninstall();
    }

    private function uninstallTab()
    {
        $flagUninstall = true;
        $roots = array();
        $childs = array();

        foreach ($this->tabs as $className => $data) {
            if (isset($data['rootClass']) && $data['rootClass']) {
                $roots[$className] = $data;
            } else {
                $childs[$className] = $data;
            }
        }

        // Unbuild Menu
        foreach ($childs as $className => $data) {
            $this->_confirmations[] = "Desinstalando Tab $className<br>\n";
            $flagUninstall = $flagUninstall && $this->uninstallModuleTab($className);
        }

        foreach ($roots as $className => $data) {

            if (!$this->tabHasChilds($className)) {
                $this->_confirmations[] = "Desinstalando Tab $className<br>\n";
                $flagUninstall = $flagUninstall && $this->uninstallModuleTab($className);
            }
        }

        return true;
    }

    public function uninstallSeurCashOnDelivery()
    {

        if ($module = Module::getInstanceByName('seurcashondelivery')) {
            if (Module::isInstalled($module->name) && !$module->uninstall())
                return false;

            $module_dir = _PS_MODULE_DIR_ . str_replace(array('.', '/', '\\'), array('', '', ''), $module->name);
            $this->recursiveDeleteOnDisk($module_dir);
        }

        return true;
    }

    public function tabHasChilds($className)
    {
        $id_tab = Tab::getIdFromClassName($className);
        if ($id_tab) {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_parent` = ' . $id_tab;
            $hijos = Db::getInstance()->executeS($sql);
            return (bool)count($hijos);
        }
    }

    public function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        Logger::addLog("ADMIN TAB Uninstall. $tabClass, $idTab", 1);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            if ($tab->delete()) {
                return true;
            }
        }
        return false;
    }

    private function deleteSettings()
    {
        $success = true;

        $success &= Configuration::deleteByName('SEUR2_URLWS_SP');
        $success &= Configuration::deleteByName('SEUR2_URLWS_R');
        $success &= Configuration::deleteByName('SEUR2_URLWS_E');
        $success &= Configuration::deleteByName('SEUR2_URLWS_A');
        $success &= Configuration::deleteByName('SEUR2_URLWS_ET');
        $success &= Configuration::deleteByName('SEUR2_URLWS_M');
        $success &= Configuration::deleteByName('SEUR2_URLWS_ET_GEOLABEL');
        $success &= Configuration::deleteByName('SEUR2_URLWS_TOKEN');
        $success &= Configuration::deleteByName('SEUR2_URLWS_ADD_SHIP');
        $success &= Configuration::deleteByName('SEUR2_URLWS_SHIP_LABEL');
        $success &= Configuration::deleteByName('SEUR2_URLWS_BREXIT_INV');
        $success &= Configuration::deleteByName('SEUR2_URLWS_BREXIT_TARIF');
        $success &= Configuration::deleteByName('SEUR2_URLWS_PICKUP');
        $success &= Configuration::deleteByName('SEUR2_URLWS_PICKUP_CANCEL');
        $success &= Configuration::deleteByName('SEUR2_URLWS_PICKUPS');
        $success &= Configuration::deleteByName('SEUR2_URLWS_LABELS');
        $success &= Configuration::deleteByName('SEUR2_URLWS_SHIPMENT_UPDATE');
        $success &= Configuration::deleteByName('SEUR2_URLWS_UPDATE_SHIPMENTS_ADD_PARCELS');

        $success &= Configuration::deleteByName('SEUR2_API_CLIENT_ID');
        $success &= Configuration::deleteByName('SEUR2_API_CLIENT_SECRET');
        $success &= Configuration::deleteByName('SEUR2_API_USERNAME');
        $success &= Configuration::deleteByName('SEUR2_API_PASSWORD');

        $success &= Configuration::deleteByName('SEUR2_PICKUP_SERVICE');
        $success &= Configuration::deleteByName('SEUR2_PICKUP_PRODUCT');
        $success &= Configuration::deleteByName('SEUR2_PICKUP_SERVICE_FRIO');
        $success &= Configuration::deleteByName('SEUR2_PICKUP_PRODUCT_FRIO');

        $success &= Configuration::deleteByName('SEUR2_REMCAR_CARGO');
        $success &= Configuration::deleteByName('SEUR2_REMCAR_CARGO_MIN');
        $success &= Configuration::deleteByName('SEUR2_FREE_PRICE');
        $success &= Configuration::deleteByName('SEUR2_FREE_WEIGTH');
        $success &= Configuration::deleteByName('SEUR2_COD_REEMBOLSO');

        $success &= Configuration::deleteByName('SEUR2_MERCHANT_NIF_DNI');
        $success &= Configuration::deleteByName('SEUR2_MERCHANT_FIRSTNAME');
        $success &= Configuration::deleteByName('SEUR2_MERCHANT_LASTNAME');
        $success &= Configuration::deleteByName('SEUR2_MERCHANT_COMPANY');

        $success &= Configuration::deleteByName('SEUR2_MERCHANT_USER');
        $success &= Configuration::deleteByName('SEUR2_MERCHANT_PASS');
        $success &= Configuration::deleteByName('SEUR2_GOOGLE_API_KEY');

        $success &= Configuration::deleteByName('SEUR2_SETTINGS_COD');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_COD_FEE_PERCENT');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_COD_FEE_MIN');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_COD_MIN');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_COD_MAX');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_NOTIFICATION');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_NOTIFICATION_TYPE');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_ALERT');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_ALERT_TYPE');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_PRINT_TYPE');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_LABEL_REFERENCE_TYPE');
        $success &= Configuration::deleteByName('SEUR2_SETTINGS_PICKUP');
        $success &= Configuration::deleteByName('SEUR2_GOOGLE_API_KEY');

        $success &= Configuration::deleteByName('SEUR2_SENDED_ORDER'); //when create label
        $success &= Configuration::deleteByName('SEUR2_SENDED_IN_MANIFEST'); // when generate manifest

        $success &= Configuration::deleteByName('SEUR2_R_EORI', '');
        $success &= Configuration::deleteByName('SEUR2_D_EORI', '');
        $success &= Configuration::deleteByName('SEUR2_TARIC', '');

        $success &= Configuration::deleteByName('SEUR2_AUTO_CREATE_LABELS');
        $success &= Configuration::deleteByName('SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE');
        $success &= Configuration::deleteByName('SEUR2_AUTO_CALCULATE_PACKAGES');

        return $success;
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (Tools::getValue('ajax', 0)) {
            echo $this->proccessAjax();
            die();
        }

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitMerchantSeur')) == true) {
            $this->postProcessMerchant();
        }

        if (((bool)Tools::isSubmit('submitSettingsSeur')) == true) {
            $this->postProcessSettings();
        }

        $errors = $this->errorConfigure();
        if (count($errors)) {
            $this->context->smarty->assign(
                array(
                    'errors' => $this->displayError($this->errorConfigure()),
                ));
        }

        $this->context->smarty->assign(
            array(
                'url_module' => $this->context->link->getAdminLink('AdminModules', true) . "&configure=seur&module_name=seur",
                'img_path' => $this->_path . 'views/img/',
                'module_path' => 'index.php?controller=AdminModules&configure=' . $this->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Tab::getIdFromClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
                'lista_ccc' =>  SeurCCC::getListCCC(),
                'module_url' => seurLib::getBaseLink(),
                'module_secret' => Configuration::get('SEUR2_API_CLIENT_SECRET'),
                'module_folder' => __DIR__
            ));

        $this->context->smarty->assign('module_dir', $this->_path);

        if (count($this->errorConfigure()) > 0 && !Tools::getValue('settings') && !Tools::getValue('merchant')) {
            $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/welcome.tpl');
            return $output;
        }
        if (count($this->errorConfigure()) == 0 && Tools::getValue('settings')) {
            $this->loadParamsSettingsSmarty();
            $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/settings.tpl');
            return $output;
        }

        $id_ccc = SeurCCC::getCCCDefault();
        if (isset($_GET['ccc'])) {
            $id_ccc = (int) $_GET['ccc'];
        }

        $this->loadParamsMerchantSmarty($id_ccc);
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/merchant.tpl');
        return $output;
    }

    public function getPath()
    {
        return $this->_path;
    }

    /**
     *  DESARROLLO DE MËTODOS ABSTRACTOS OBLIGADOS
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        if ($id_seur_carrier = (int)SeurCarrier::getSeurCarrierByIdCarrier($params->id_carrier))
        {
            $seur_carrier = new SeurCarrier($id_seur_carrier);

            if ($seur_carrier->free_shipping) {

                $min_price = $seur_carrier->free_shipping_price;
                $min_weight = $seur_carrier->free_shipping_weight;

                // CONSULTAMOS EL TOTAL DEL CARRITO CON IVA
                $cart_price = $params->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, $params->getProducts());

                // CONSULTAMOS EL PESO DEL CARRITO CON IVA
                $cart_weight = $params->getTotalWeight($params->getProducts());

                if ($min_price == 0 && $min_weight == 0) {
                    return 0;
                }

                if ($min_price > 1 && $min_price <= $cart_price) {
                    return 0;
                }

                if ($min_weight > 1 && $min_weight <= $cart_weight) {
                    return 0;
                }
            }
        }
        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');

        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->controller->addJS($this->_path . 'views/js/seurController.js');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader() { return $this->_hookHeader();} // PS 1.6 or previous
    public function hookDisplayHeader() { return $this->_hookHeader();} // PS 1.7 or later
    public function _hookHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->context->controller->addCSS($this->_path . 'views/css/front.css');
        }
        $this->context->controller->addJS($this->_path . 'views/js/front.js');

        $this->context->smarty->assign(
            array(
                'img_path' => $this->_path.'views/img/',
        ));

        $seur_carrier_pos = SeurLib::getSeurPOSCarrier();
        $pos_is_enabled = SeurCarrier::isPosActive();

        $this->context->smarty->assign(
            array(
                'id_seur_pos' =>  $seur_carrier_pos?(int)$seur_carrier_pos['id_seur_carrier']:0,
                'seur_dir' => $this->_path,
                'ps_version' => _PS_VERSION_
            )
        );

        if (version_compare(_PS_VERSION_, '1.5', '>='))
        {
            $page = (Tools::getValue('controller') ? Tools::getValue('controller') : null);
        }
        else
        {
            $page = explode('/', $_SERVER['SCRIPT_NAME']);
            $page = end($page);
        }

        if ($pos_is_enabled && ($page == 'order-opc.php' || $page == 'order.php' || $page == 'orderopc' || $page == 'order')) {
            $this->context->controller->addCSS($this->_path . 'views/css/seurGMap.css');

            if (Tools::version_compare(_PS_VERSION_, '1.7', '<'))
            {
                $this->context->controller->addJS($this->js_url);
                $this->context->controller->addJS($this->_path . 'views/js/seurGMap.js');
            }

            $current_customer_addresses_ids = array();
            foreach ($this->context->customer->getAddresses((int)$this->context->language->id) as $address)
                $current_customer_addresses_ids[] = (int)$address['id_address'];
            $this->context->smarty->assign('customer_addresses_ids', $current_customer_addresses_ids);

            return $this->display(__FILE__, 'views/templates/hook/header.tpl');
        }
    }

    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
        */
    }

    public function hookActionValidateOrder($params)
    {
        $cart = $params['cart'];
        $order = $params['order'];
        $orderStatus = $params['orderStatus'];
        $id_carrier = $order->id_carrier;

        $estados_cancelados = array();
        $estados_cancelados[] = Configuration::get('PS_OS_ERROR');
        $estados_cancelados[] = Configuration::get('PS_OS_CANCELED');

        if (SeurLib::isSeurOrder($order->id)) {
            return true;
        }
        if (in_array($orderStatus,$estados_cancelados)) {
            return true;
        }

        //Lineagrafica - F.Torres -> Obtenemos todos los datos del envío.
        $address = new Address((int)$order->id_address_delivery);
        $cookie = $this->context->cookie;
        $newcountry = new Country($address->id_country, (int)$cookie->id_lang);
        $id_ccc = SeurLib::getCCC($newcountry->iso_code);

        $international = SeurLib::isInternationalShipping($newcountry->iso_code);
        if ($international) {
            $id_carrier = $order->id_carrier; //calculado por PS por la dirección de envío
        }
        if (!SeurLib::isSeurCarrier($id_carrier)) {
            return true;
        }

        $seur_carrier_array = SeurLib::getSeurCarrier($id_carrier);
        $seur_carrier = new SeurCarrier($seur_carrier_array['id_seur_carrier']);

        $numero_bultos = 1;
        $auto_calculate_packages = Configuration::get('SEUR2_AUTO_CALCULATE_PACKAGES');

        if($auto_calculate_packages === "1")
        {
            $numero_bultos = 0;
            $products = $order->getProducts();
            foreach ($products as $product)
            {
                $numero_bultos += (int)$product['product_quantity'];
            }
        }

        $seurOrder = new SeurOrder();
        $seurOrder->id_order = $order->id;
        $seurOrder->numero_bultos = $numero_bultos;
        $seurOrder->id_status = 0;
        $seurOrder->id_seur_ccc = $id_ccc;
        $seurOrder->peso_bultos = $order->getTotalWeight();
        $seurOrder->id_address_delivery = $order->id_address_delivery;
        $seurOrder->id_seur_carrier = $seur_carrier->id_seur_carrier;
        $seurOrder->date_labeled = NULL;
        $seurOrder->product = $seur_carrier->product;
        $seurOrder->service = $seur_carrier->service;
        $seurOrder->firstname = $address->firstname;
        $seurOrder->lastname = $address->lastname;
        $seurOrder->id_country = $address->id_country;
        $seurOrder->id_state = $address->id_state;
        $seurOrder->dni = $address->dni;
        $seurOrder->other = $address->other;
        $seurOrder->phone = SeurLib::cleanPhone($address->phone);
        $seurOrder->phone_mobile = SeurLib::cleanPhone($address->phone_mobile);
        $seurOrder->address1 = $address->address1;
        $seurOrder->address2 = $address->address2;
        $seurOrder->postcode = $address->postcode;
        $seurOrder->city = $address->city;

        /* Comprobamos si es una recogida en punto de venta */
        $pickup_point_info = SeurLib::getOrderPos((int)$params['order']->id_cart);
        if (!empty($pickup_point_info) && $pickup_point_info &&
            SeurLib::isPickup($seurOrder->service, $seurOrder->product))
        {
            $pudo_address1 = 'Pickup: ' . $pickup_point_info['company'] .' - ' . $pickup_point_info['id_seur_pos'];
            $pudo_address2 = $pickup_point_info['address'];
            $pudo_city = $pickup_point_info['city'];
            $pudo_postcode = $pickup_point_info['postal_code'];

            // Verificar si ya existe una dirección con los datos deseados
            $id_address_pudo = SeurLib::getCustomerAddressId($order->id_customer, [
                'address1' => $pudo_address1,
                'address2' => $pudo_address2,
                'city' => $pudo_city,
                'postcode' => $pudo_postcode,
                'id_country' => $address->id_country,
            ]);

            if (!$id_address_pudo) {
                $newAddress = new Address();
                $newAddress->id_customer = $order->id_customer;
                $newAddress->company = '';
                $newAddress->lastname = $address->lastname;
                $newAddress->firstname = $address->firstname;
                $newAddress->other = $address->other;
                $newAddress->phone = $address->phone;
                $newAddress->phone_mobile = $address->phone_mobile;
                $newAddress->vat_number = $address->vat_number;
                $newAddress->dni = $address->dni;
                $newAddress->address1 = $pudo_address1;
                $newAddress->address2 = $pudo_address2;
                $newAddress->city = $pudo_city;
                $newAddress->postcode = $pudo_postcode;
                $newAddress->id_country = $address->id_country;
                $newAddress->id_state = 0;
                $newAddress->alias = $pickup_point_info['id_seur_pos'];
                $newAddress->active = 1;
                $newAddress->deleted = 1;

                if ($newAddress->add()) {
                    $id_address_pudo = $newAddress->id;
                }
            }
            $order->id_address_delivery = $id_address_pudo;
            $order->update();

            $seurOrder->id_address_delivery = $id_address_pudo;
            $seurOrder->address1 = $pudo_address1;
            $seurOrder->address2 = $pudo_address2;
            $seurOrder->postcode = $pudo_postcode;
            $seurOrder->city = $pudo_city;
        }

        $seurOrder->cashondelivery = 0;
        $seurOrder->codfee = 0;
        $seurOrder->total_paid = $order->total_paid_real;
        $seurOrder->labeled = 0;
        $seurOrder->manifested = 0;

        $seurOrder->save();

        return true;
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'];

        $auto_create_labels = Configuration::get('SEUR2_AUTO_CREATE_LABELS');
        $list_ccc = SeurCCC::getListCCC();

        $auto_create_labels_payments_methods_available = [];
        if(Configuration::get('SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE'))
            $auto_create_labels_payments_methods_available = explode(",", Configuration::get('SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE'));

        if($auto_create_labels === '1' && count($list_ccc) === 1 && $auto_create_labels_payments_methods_available && in_array($order->module, $auto_create_labels_payments_methods_available))
        {
            $command = new Seur\Prestashop\Commands\AutoCreateLabel($order->id);
            $command->handle();
        }

        return true;
    }

    public function hookDisplayOrderDetail($params)
    {
        return $this->hookOrderDetailDisplayed($params);
    }

    public function hookOrderDetailDisplayed($params)
    {
        if ($this->isConfigured())
        {
            $seur_carriers = SeurLib::getSeurCarriers(false);
            $ids_seur_carriers = array();


            foreach ($seur_carriers as $value) {
                $ids_seur_carriers[] = (int)$value['carrier_reference'];
            }

            $order = new Order((int)$params['order']->id);
            $orderSeur = SeurOrder::getByOrder((int)$params['order']->id);

            if (!Validate::isLoadedObject($orderSeur))
                return false;

            $referencia = SeurLib::getOrderReference($order);
            $iso_country = Country::getIsoById((int)$orderSeur->id_country);
            if (SeurLib::isInternationalShipping($iso_country)) {
                $referencia = $orderSeur->ecb;
            }
            $fecha = $orderSeur->date_labeled;

            $url_tracking = "";
            if($orderSeur->id_status) {
                $url_tracking = "https://www.seur.com/livetracking/pages/seguimiento-online.do?segOnlineIdentificador=" . $referencia . "&segOnlineFecha=" . substr($fecha, 8, 2) . "-" . substr($fecha, 5, 2) . "-" . substr($fecha, 0, 4);
            }

            $this->context->smarty->assign(
                array(
                    'logo' => $this->_path.'views/img/logo_seur.png',
                    'reference' => $referencia,
                    'delivery' => "",
                    'seur_order_state' => (!empty($orderSeur->status_text) ? (string)$orderSeur->status_text : $this->l('Sin estado')),
                    'url_tracking' => $url_tracking
                )
            );
            return $this->display(__FILE__, 'views/templates/hook/orderDetail.tpl');
        }
    }

    public function hookDisplayAfterCarrier($params)
    {
        if ($this->isConfigured())
        {
            $process_type = Configuration::get('PS_ORDER_PROCESS_TYPE');
            $seur_carriers = SeurLib::getSeurCarriers(true);
            $pos_is_enabled = SeurCarrier::isPosActive();

            $seur_carriers_without_pos = '';
            $seur_carriers_pos = '';
            foreach ($seur_carriers as $seur_carrier) {
                if ($seur_carrier['shipping_type'] != 2) {
                    $seur_carriers_without_pos .= (int)$seur_carrier['id_carrier'] . ',';
                }
                else{
                    $seur_carriers_pos .= (int)$seur_carrier['id_carrier'] . ',';
                }
            }

            $seur_carriers_pos = trim($seur_carriers_pos, ',');
            $seur_carriers_without_pos = trim($seur_carriers_without_pos, ',');

            if ($process_type == '0')
                $this->context->smarty->assign('id_address', $this->context->cart->id_address_delivery);

            if ( version_compare(_PS_VERSION_, '1.5', '<') ) {
                $ps_version = 'ps4';
            } elseif ( version_compare(_PS_VERSION_, '1.7', '<') ) {
                $ps_version = 'ps5';
            } else {
                $ps_version = 'ps7';
            }

            $this->context->smarty->assign(
                array(
                    'posEnabled' => $pos_is_enabled,
                    'cookie' => $this->context->cookie,
                    'id_seur_pos' => $seur_carriers_pos,
                    'seur_resto' => $seur_carriers_without_pos,
                    'src' => $this->_path.'img/unknown.gif',
                    'ps_version' => $ps_version,
                    'seur_map_reload_config' => Configuration::get('SEUR2_MAP_RELOAD_CONFIG'),
                    'seurGoogleApiKey' => Configuration::get('SEUR2_GOOGLE_API_KEY'),
                )
            );

            return $this->display(__FILE__, 'views/templates/hook/seur17.tpl');
        }
    }


    public function hookActionFrontControllerSetMedia($params)
    {
        if (!$this->context) {
            $context = Context::getContext();
        } else {
            $context = $this->context;
        }

        $controller = $context->controller;
        if ($controller instanceof OrderController) {
            if (Tools::version_compare(_PS_VERSION_, '1.7.0.2', '>=')) {

                $this->context->controller->registerjavascript(
                    $this->name . '-gmap',
                    $this->js_url,
                    array(
                        'position' => 'head',
                        'priority' => 150,
                        'server' => 'remote',
                        'inline' => false,
                    )
                );

                $this->context->controller->registerjavascript(
                    $this->name . '-seurgmap',
                    'modules/' . $this->name . '/views/js/seurGMap.js',
                    array(
                        'position' => 'bottom',
                        'priority' => 150,
                        'server' => 'local',
                        'inline' => false,
                    )
                );

                $this->context->controller->registerjavascript(
                    $this->name . '-front',
                    'modules/' . $this->name . '/views/js/frontMap.js',
                    array(
                        'position' => 'bottom',
                        'priority' => 150,
                        'server' => 'local',
                        'inline' => false,
                    )
                );
            }
        }

        $this->context->controller->addCSS($this->_path.'views/css/seurGMap.css');
        $this->context->controller->addCSS($this->_path.'views/css/front.css');

        Media::addJsDef(array(
           'baseDir' => __PS_BASE_URI__
        ));
    }

    public function hookExtraCarrier($params)
    {
        if ($this->isConfigured())
        {
            $process_type = Configuration::get('PS_ORDER_PROCESS_TYPE');
            $seur_carriers = SeurLib::getSeurCarriers(true);
            $pos_is_enabled = SeurCarrier::isPosActive();

            $seur_carriers_without_pos = '';
            $seur_carriers_pos = '';
            foreach ($seur_carriers as $seur_carrier) {
                if ($seur_carrier['shipping_type'] != 2) {
                    $seur_carriers_without_pos .= (int)$seur_carrier['id_carrier'] . ',';
                }
                else{
                    $seur_carriers_pos .= (int)$seur_carrier['id_carrier'] . ',';
                }
            }

            $seur_carriers_pos = trim($seur_carriers_pos, ',');
            $seur_carriers_without_pos = trim($seur_carriers_without_pos, ',');

            if ($process_type == '0')
                $this->context->smarty->assign('id_address', $this->context->cart->id_address_delivery);

            $this->context->smarty->assign(
                array(
                    'posEnabled' => $pos_is_enabled,
                    'id_seur_pos' => $seur_carriers_pos,
                    'seur_resto' => $seur_carriers_without_pos,
                    'src' => $this->_path.'img/unknown.gif',
                    'ps_version' => version_compare(_PS_VERSION_, '1.5', '<') ? 'ps4' : 'ps5',
                    'seur_map_reload_config' => Configuration::get('SEUR2_MAP_RELOAD_CONFIG'),
                    'seurGoogleApiKey' => Configuration::get('SEUR2_GOOGLE_API_KEY'),
                )
            );

            return $this->display(__FILE__, 'views/templates/hook/seur.tpl');
        }
    }

    /**
     * Save form data.
     */
    protected function postProcessMerchant()
    {
        Configuration::updateValue("SEUR2_MERCHANT_NIF_DNI", Tools::getValue("SEUR2_MERCHANT_NIF_DNI"));
        Configuration::updateValue("SEUR2_MERCHANT_FIRSTNAME", Tools::getValue("SEUR2_MERCHANT_FIRSTNAME"));
        Configuration::updateValue("SEUR2_MERCHANT_LASTNAME", Tools::getValue("SEUR2_MERCHANT_LASTNAME"));
        Configuration::updateValue("SEUR2_MERCHANT_COMPANY", Tools::getValue("SEUR2_MERCHANT_COMPANY"));
        Configuration::updateValue("SEUR2_MERCHANT_CLICKCOLLECT", Tools::getValue("SEUR2_MERCHANT_CLICKCOLLECT"));

        Configuration::updateValue('SEUR2_API_CLIENT_ID', Tools::getValue("SEUR2_API_CLIENT_ID"));
        Configuration::updateValue('SEUR2_API_CLIENT_SECRET', Tools::getValue("SEUR2_API_CLIENT_SECRET"));
        Configuration::updateValue('SEUR2_API_USERNAME', Tools::getValue("SEUR2_API_USERNAME"));
        Configuration::updateValue('SEUR2_API_PASSWORD', Tools::getValue("SEUR2_API_PASSWORD"));

        Configuration::updateValue('SEUR2_R_EORI', Tools::getValue("SEUR2_R_EORI"));
        Configuration::updateValue('SEUR2_D_EORI', Tools::getValue("SEUR2_D_EORI"));
        Configuration::updateValue('SEUR2_TARIC', Tools::getValue("SEUR2_TARIC"));


        $id_ccc = Tools::getValue("id_seur_ccc");

        $seur_ccc = new SeurCCC($id_ccc);

        if(Tools::getValue("ccc") == '' ||
            Tools::getValue("cit") == '' ||
            Tools::getValue("franchise") == '') {
            return false;
        }

        $seur_ccc->ccc = Tools::getValue("ccc");
        $seur_ccc->cit = Tools::getValue("cit");
        $seur_ccc->franchise = Tools::getValue("franchise");
        $seur_ccc->nombre_personalizado = Tools::getValue("nombre_personalizado").'';
        $seur_ccc->phone = SeurLib::cleanPhone(Tools::getValue("phone"));
        $seur_ccc->fax = Tools::getValue("fax");
        $seur_ccc->email = Tools::getValue("email");
        $seur_ccc->e_devoluciones = Tools::getValue("eDevoluciones");
        $seur_ccc->url_devoluciones = Tools::getValue("urleDevoluciones");
        $seur_ccc->click_connect = Tools::getValue("clickCollect");

        $seur_ccc->post_code = Tools::getValue("post_code");
        $seur_ccc->street_type = Tools::getValue("street_type");
        $seur_ccc->street_name = Tools::getValue("street_name");
        $seur_ccc->town = Tools::getValue("town");
        $seur_ccc->state = Tools::getValue("state");
        $seur_ccc->country = Tools::getValue("country");
        $seur_ccc->street_number = Tools::getValue("street_number");
        $seur_ccc->staircase = Tools::getValue("staircase");
        $seur_ccc->floor = Tools::getValue("floor");
        $seur_ccc->door = Tools::getValue("door");
        $seur_ccc->geolabel = Tools::getValue("geolabel");
        $seur_ccc->id_shop = Tools::getValue("id_shop");

        $seur_ccc->save();
    }

    protected function postProcessSettings()
    {
        Configuration::updateValue("SEUR2_SETTINGS_COD", Tools::getValue("SEUR2_SETTINGS_COD"));
        Configuration::updateValue("SEUR2_SETTINGS_COD_FEE_PERCENT", Tools::getValue("SEUR2_SETTINGS_COD_FEE_PERCENT"));
        Configuration::updateValue("SEUR2_SETTINGS_COD_FEE_MIN", Tools::getValue("SEUR2_SETTINGS_COD_FEE_MIN"));
        Configuration::updateValue("SEUR2_SETTINGS_COD_MIN", Tools::getValue("SEUR2_SETTINGS_COD_MIN"));
        Configuration::updateValue("SEUR2_SETTINGS_COD_MAX", Tools::getValue("SEUR2_SETTINGS_COD_MAX"));
        Configuration::updateValue("SEUR2_SETTINGS_NOTIFICATION", Tools::getValue("SEUR2_SETTINGS_NOTIFICATION"));
        Configuration::updateValue("SEUR2_SETTINGS_NOTIFICATION_TYPE", Tools::getValue("SEUR2_SETTINGS_NOTIFICATION_TYPE"));
        Configuration::updateValue("SEUR2_SETTINGS_ALERT", Tools::getValue("SEUR2_SETTINGS_ALERT"));
        Configuration::updateValue("SEUR2_SETTINGS_ALERT_TYPE", Tools::getValue("SEUR2_SETTINGS_ALERT_TYPE"));
        Configuration::updateValue("SEUR2_SETTINGS_PRINT_TYPE", Tools::getValue("SEUR2_SETTINGS_PRINT_TYPE"));
        Configuration::updateValue("SEUR2_SETTINGS_LABEL_REFERENCE_TYPE", Tools::getValue("SEUR2_SETTINGS_LABEL_REFERENCE_TYPE"));
        Configuration::updateValue("SEUR2_SETTINGS_PICKUP", Tools::getValue("SEUR2_SETTINGS_PICKUP"));
        Configuration::updateValue("SEUR2_GOOGLE_API_KEY", Tools::getValue("SEUR2_GOOGLE_API_KEY"));
        Configuration::updateValue("SEUR2_CAPTURE_ORDER", Tools::getValue("SEUR2_CAPTURE_ORDER"));
        Configuration::updateValue("SEUR2_STATUS_DELIVERED", Tools::getValue("select_status_SEUR2_STATUS_DELIVERED"));
        Configuration::updateValue("SEUR2_STATUS_IN_TRANSIT", Tools::getValue("select_status_SEUR2_STATUS_IN_TRANSIT"));
        Configuration::updateValue("SEUR2_STATUS_INCIDENCE", Tools::getValue("select_status_SEUR2_STATUS_INCIDENCE"));
        Configuration::updateValue("SEUR2_STATUS_RETURN_IN_PROGRESS", Tools::getValue("select_status_SEUR2_STATUS_RETURN_IN_PROGRESS"));
        Configuration::updateValue("SEUR2_STATUS_AVAILABLE_IN_STORE", Tools::getValue("select_status_SEUR2_STATUS_AVAILABLE_IN_STORE"));
        Configuration::updateValue("SEUR2_STATUS_CONTRIBUTE_SOLUTION", Tools::getValue("select_status_SEUR2_STATUS_CONTRIBUTE_SOLUTION"));
        Configuration::updateValue("SEUR2_SENDED_ORDER", Tools::getValue("SEUR2_MARK_SENDED")==1?1:0);
        Configuration::updateValue("SEUR2_SENDED_IN_MANIFEST", Tools::getValue("SEUR2_MARK_SENDED")==2?1:0);
        Configuration::updateValue("SEUR2_AUTO_CREATE_LABELS", Tools::getValue("SEUR2_AUTO_CREATE_LABELS"));
        if(Tools::getValue("SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE"))
            $auto_create_labels_payments_methods_available = implode(',', Tools::getValue("SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE"));
        else
            $auto_create_labels_payments_methods_available = '';
        Configuration::updateValue("SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE", $auto_create_labels_payments_methods_available);
        Configuration::updateValue("SEUR2_AUTO_CALCULATE_PACKAGES", Tools::getValue("SEUR2_AUTO_CALCULATE_PACKAGES"));
    }

    private function loadParamsMerchantSmarty($id_ccc)
    {
        $shop_id = 0;
        $shop_id = Context::getContext()->shop->id;

        if($id_ccc == NULL){
            $id_ccc = SeurCCC::getCCCDefault();
        }

        if($id_ccc)
        {
            $seur_ccc = new SeurCCC($id_ccc);

            $this->context->smarty->assign(
                array(
                'id_seur_ccc' => $seur_ccc->id_seur_ccc,
                'ccc' => $seur_ccc->ccc,
                'cit' => $seur_ccc->cit,
                'franchise' => $seur_ccc->franchise,
                'nombre_personalizado' => $seur_ccc->nombre_personalizado.'',
                'phone' => SeurLib::cleanPhone($seur_ccc->phone),
                'email' => $seur_ccc->email,
                'eDevoluciones' => $seur_ccc->e_devoluciones,
                'urleDevoluciones' => $seur_ccc->url_devoluciones,
                'street_type' => $seur_ccc->street_type,
                'street_name' => $seur_ccc->street_name,
                'post_code' => $seur_ccc->post_code,
                'town' => $seur_ccc->town,
                'state' => $seur_ccc->state,
                'country' => $seur_ccc->country,
                'street_number' => $seur_ccc->street_number,
                'staircase' => $seur_ccc->staircase,
                'floor' => $seur_ccc->floor,
                'door' => $seur_ccc->door,
                'geolabel' => $seur_ccc->geolabel,
                'id_shop' => $seur_ccc->id_shop,
                'shops' => SeurCCC::getShops(),
            ));
        }
        else
        {
            $this->context->smarty->assign(
                array(
                'id_seur_ccc' => '',
                'cit' => '',
                'ccc' => '',
                'franchise' => '',
                'phone' => '',
                'email' => '',
                'eDevoluciones' => '',
                'urleDevoluciones' => '',
                'street_type' =>  '',
                'street_name' =>  '',
                'post_code' =>  '',
                'town' =>  '',
                'state' =>  '',
                'country' =>  '',
                'street_number' =>  '',
                'staircase' =>  '',
                'floor' =>  '',
                'door' =>  '',
                'id_shop' => $shop_id,
                'shops' => SeurCCC::getShops(),
            ));
        }

        $this->context->smarty->assign(
            array(
                'clickCollect' => Configuration::get('SEUR2_MERCHANT_CLICKCOLLECT'),
                'nif_cif' => Configuration::get('SEUR2_MERCHANT_NIF_DNI'),
                'firstname' => Configuration::get('SEUR2_MERCHANT_FIRSTNAME'),
                'lastname' => Configuration::get('SEUR2_MERCHANT_LASTNAME'),
                'company' => Configuration::get('SEUR2_MERCHANT_COMPANY'),
                'api_client_id' => Configuration::get('SEUR2_API_CLIENT_ID'),
                'api_client_secret' => Configuration::get('SEUR2_API_CLIENT_SECRET'),
                'api_username' => Configuration::get('SEUR2_API_USERNAME'),
                'api_password' => Configuration::get('SEUR2_API_PASSWORD'),
                'rEORI' => Configuration::get('SEUR2_R_EORI'),
                'dEORI' => Configuration::get('SEUR2_D_EORI'),
                'taric' => Configuration::get('SEUR2_TARIC'),
            ));
    }

    private function loadParamsSettingsSmarty()
    {
        $status_ps = Db::getInstance()->executeS('SELECT o.*, l.name FROM `' . _DB_PREFIX_ . 'order_state` o LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` l ON o.id_order_state = l.id_order_state AND id_lang=' . Context::getContext()->language->id);
        $module = Module::getInstanceByName('seurcashondelivery');
        $payments_methods = [];
        $paymentModules = PaymentModule::getInstalledPaymentModules();
        foreach ($paymentModules as $paymentModule){
            $module = Module::getInstanceByName($paymentModule['name']);
            $payments_methods[] = [
                'name' => $paymentModule['name'],
                'displayName' => $module->displayName
            ];
        }
        $auto_create_labels_payments_methods_available = explode(",", Configuration::get('SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE'));

        $this->context->smarty->assign(
            array(
                'status_ps' => $status_ps,
                'cashDelivery' => Configuration::get('SEUR2_SETTINGS_COD'), //$module->active
                'payments_methods' => $payments_methods,
                'cod_fee_percent' => Configuration::get('SEUR2_SETTINGS_COD_FEE_PERCENT'),
                'cod_fee_min' => Configuration::get('SEUR2_SETTINGS_COD_FEE_MIN'),
                'cod_min' => Configuration::get('SEUR2_SETTINGS_COD_MIN'),
                'cod_max' => Configuration::get('SEUR2_SETTINGS_COD_MAX'),
                'notification' => Configuration::get('SEUR2_SETTINGS_NOTIFICATION'),
                'notification_type' => Configuration::get('SEUR2_SETTINGS_NOTIFICATION_TYPE'),
                'alerts' => Configuration::get('SEUR2_SETTINGS_ALERT'),
                'alerts_type' => Configuration::get('SEUR2_SETTINGS_ALERT_TYPE'),
                'print_type' => Configuration::get('SEUR2_SETTINGS_PRINT_TYPE'),
                'label_reference_type' => Configuration::get('SEUR2_SETTINGS_LABEL_REFERENCE_TYPE')?Configuration::get('SEUR2_SETTINGS_LABEL_REFERENCE_TYPE'):1,
                'collection_type' => Configuration::get('SEUR2_SETTINGS_PICKUP'),
                'status_in_transit' => Configuration::get('SEUR2_STATUS_IN_TRANSIT'),
                'status_return_in_progress' => Configuration::get('SEUR2_STATUS_RETURN_IN_PROGRESS'),
                'status_available_in_store' => Configuration::get('SEUR2_STATUS_AVAILABLE_IN_STORE'),
                'status_contribute_solution' => Configuration::get('SEUR2_STATUS_CONTRIBUTE_SOLUTION'),
                'status_incidence' => Configuration::get('SEUR2_STATUS_INCIDENCE'),
                'status_delivered' => Configuration::get('SEUR2_STATUS_DELIVERED'),
                'google_key' => Configuration::get('SEUR2_GOOGLE_API_KEY'),
                'capture_order' => Configuration::get('SEUR2_CAPTURE_ORDER'),
                'auto_create_labels' => Configuration::get('SEUR2_AUTO_CREATE_LABELS'),
                'auto_create_labels_payments_methods_available' => $auto_create_labels_payments_methods_available,
                'auto_calculate_packages' => Configuration::get('SEUR2_AUTO_CALCULATE_PACKAGES'),
            )
        );
        if (Configuration::get('SEUR2_SENDED_ORDER')==1) {
            $this->context->smarty->assign('sended_when', 1);
        } elseif (Configuration::get('SEUR2_SENDED_IN_MANIFEST')==1) {
            $this->context->smarty->assign('sended_when', 2);
        } else {
            $this->context->smarty->assign('sended_when', 0);
        }
    }

    public function ajaxProcessActivateCashonDelivery()
    {
        $module = Module::getInstanceByName('seurcashondelivery');
        if (Validate::isLoadedObject($module)) {
            if ($module->getPermission('configure')) {
                $module->enable();
            }
        }
        return true;
    }

    public function ajaxProcessDeactivateCashonDelivery()
    {
        $module = Module::getInstanceByName('seurcashondelivery');
        if (Validate::isLoadedObject($module)) {
            if ($module->getPermission('configure')) {
                $module->disable();
            }
        }
        return true;
    }

    public function proccessAjax()
    {
        $context = Context::getContext();
        $out = array();

        if (Tools::getValue('action', 0) == 'setActiveCarrier') {

            $carrier_reference = Tools::getValue('carrier_reference', 0);
            $sql = "SELECT active FROM `"._DB_PREFIX_."carrier` WHERE id_reference=".(int)$carrier_reference." AND deleted=0" ;

            $active = (int)Db::getInstance()->getValue($sql);


            $out['result'] = 'OK';
            $out['response'] = $active;
        }

        return Tools::jsonEncode($out);
    }

    public function isConfigured()
    {
        return count($this->errorConfigure())==0;
    }

    public function errorConfigure()
    {
        $sql = "SELECT * FROM `"._DB_PREFIX_."seur2_ccc` ORDER BY id_seur_ccc";

        try {
            $ccc = Db::getInstance()->getRow($sql);
        }
        catch(Exception $e){
            return array($this->l("Module not installed"));
        }

        $error = array();
        if (Configuration::get('SEUR2_MERCHANT_NIF_DNI')=='') {
            $error[] = $this->l('Field')." ".$this->l('NIF/DNI')." ".$this->l('is not defined');
        }
        if (Configuration::get('SEUR2_MERCHANT_FIRSTNAME')=='') {
            $error[] = $this->l('Field')." ".$this->l('FIRSTNAME')." ".$this->l('is not defined');
        }
        if (Configuration::get('SEUR2_MERCHANT_LASTNAME')=='') {
            $error[] = $this->l('Field')." ".$this->l('LASTNAME')." ".$this->l('is not defined');
        }
        if (Configuration::get('SEUR2_MERCHANT_COMPANY')=='') {
            $error[] = $this->l('Field')." ".$this->l("COMPANY")." ".$this->l('is not defined');
        }
        if (Configuration::get('SEUR2_API_CLIENT_ID').''=='') {
            $error[] = $this->l('Field')." ".$this->l("API CLIENT ID")." ".$this->l('is not defined');
        }
        if (Configuration::get('SEUR2_API_CLIENT_SECRET').''=='') {
            $error[] = $this->l('Field')." ".$this->l("API CLIENT SECRET")." ".$this->l('is not defined');
        }
        if (Configuration::get('SEUR2_API_USERNAME').''=='') {
            $error[] = $this->l('Field')." ".$this->l("API USERNAME")." ".$this->l('is not defined');
        }
        if (Configuration::get('SEUR2_API_PASSWORD').''=='') {
            $error[] = $this->l('Field')." ".$this->l("API CLIENT PASSWORD")." ".$this->l('is not defined');
        }

        if (!isset($ccc['id_seur_ccc']) || $ccc['id_seur_ccc']==''){
            $error[] = $this->l('Not setup any count ccc yet');
        }
        if (!isset($ccc['ccc']) || $ccc['ccc']==''){
            $error[] = $this->l('Field')." ".$this->l('CCC')." ".$this->l('is not defined');
        }
        if (!isset($ccc['cit']) || $ccc['cit']==''){
            $error[] = $this->l('Field')." ".$this->l('CIT')." ".$this->l('is not defined');
        }
        if (!isset($ccc['franchise']) || $ccc['franchise']==''){
            $error[] = $this->l('Field')." ".$this->l('FRANCHISE')." ".$this->l('is not defined');
        }
        if (!isset($ccc['phone']) || $ccc['phone']==''){
            $error[] = $this->l('Field')." ".$this->l('PHONE')." ".$this->l('is not defined');
        }
        if (!isset($ccc['email']) || $ccc['email']==''){
            $error[] = $this->l('Field')." ".$this->l('EMAIL')." ".$this->l('is not defined');
        }

        if (!isset($ccc['street_type']) || $ccc['street_type']=='') {
            $error[] = $this->l('Field')." ".$this->l('STREET TYPE')." ".$this->l('is not defined');
        }
        if (!isset($ccc['street_name']) || $ccc['street_name']=='') {
            $error[] = $this->l('Field')." ".$this->l('STREET NAME')." ".$this->l('is not defined');
        }
        if (!isset($ccc['post_code']) || $ccc['post_code']=='') {
            $error[] = $this->l('Field')." ".$this->l('POST CODE')." ".$this->l('is not defined');
        }
        if (!isset($ccc['town']) || $ccc['town']=='') {
            $error[] = $this->l('Field')." ".$this->l('CITY')." ".$this->l('is not defined');
        }
        if (!isset($ccc['state']) || $ccc['state']=='') {
            $error[] = $this->l('Field')." ".$this->l('STATE')." ".$this->l('is not defined');
        }
        if (!isset($ccc['country']) || $ccc['country']=='') {
            $error[] = $this->l('Field')." ".$this->l('COUNTRY')." ".$this->l('is not defined');
        }
        if (!isset($ccc['street_number']) || $ccc['street_number']=='') {
            $error[] = $this->l('Field')." ".$this->l('NUMBER')." ".$this->l('is not defined');
        }
        return $error;
    }

    public function hookDisplayAdminOrder($params) {
        return $this->hookAdminOrder($params);
    }

    public function hookActionOrderEdited($params) {
        $order = $params['order'];
        if (SeurLib::isCODPayment($order)) {
            $shipping_amount_tax_incl = $order->total_shipping_tax_incl; //ya actualizados al nuevo carrier (seur)
            $shipping_amount_tax_excl = $order->total_shipping_tax_excl; //ya actualizados al nuevo carrier (seur)
            $cod_amount = SeurLib::calculateCODAmount($order);

            $order->total_paid_real = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
            $order->total_paid = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
            $order->total_paid_tax_excl = $order->total_products + $shipping_amount_tax_excl + $cod_amount;
            $order->total_paid_tax_incl = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
            $order->total_shipping = $shipping_amount_tax_incl + $cod_amount;
            $order->total_shipping_tax_excl = $shipping_amount_tax_excl + $cod_amount;
            $order->total_shipping_tax_incl = $shipping_amount_tax_incl + $cod_amount;

            $seur_order = SeurOrder::getByOrder($order->id);
            $seur_order->total_paid = $order->total_paid_real;
            $seur_order->cashondelivery = $order->total_paid_real;
            $seur_order->codfee = $cod_amount;
            $seur_order->save();

            //actualizar payment
            $order_payment = OrderPayment::getByOrderReference($order->reference);
            if (isset($order_payment[0])) {
                $order_payment = $order_payment[0];
                $order_payment->amount = $order->total_paid;
                $order_payment->payment_method = $order->payment;
                $order_payment->id_currency = $order->id_currency;
                $order_payment->update();

                //obtener datos de la factura y modificar
                $order_invoice = $order_payment->getOrderInvoice($order->id);
                if ($order_invoice) {
                    $order_invoice->total_discount_tax_excl = $order->total_discounts_tax_excl;
                    $order_invoice->total_discount_tax_incl = $order->total_discounts_tax_incl;
                    $order_invoice->total_paid_tax_excl = $order->total_paid_tax_excl;
                    $order_invoice->total_paid_tax_incl = $order->total_paid_tax_incl;
                    $order_invoice->total_products = $order->total_products;
                    $order_invoice->total_products_wt = $order->total_products_wt;
                    $order_invoice->total_shipping_tax_excl = $order->total_shipping_tax_excl;
                    $order_invoice->total_shipping_tax_incl = $order->total_shipping_tax_incl;
                    $order_invoice->total_wrapping = $order->total_wrapping;
                    $order_invoice->total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
                    $order_invoice->total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
                    $order_invoice->update();
                }
            }

            //actualizar costes envío transportista
            $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
            $order_carrier->shipping_cost_tax_excl = $shipping_amount_tax_excl + $cod_amount;
            $order_carrier->shipping_cost_tax_incl = $shipping_amount_tax_incl + $cod_amount;
            $order_carrier->update();

            $order->save();
        }
    }

    public function hookAdminOrder($params)
    {
        if ($this->isConfigured()) {

            $conf = $errors = $warnings = '';
            if (!empty(Context::getContext()->cookie->confirmations_messages)) {
                $conf = Context::getContext()->cookie->confirmations_messages;
                unset(Context::getContext()->cookie->confirmations_messages);
            }
            if (!empty(Context::getContext()->cookie->errors_messages)) {
                $errors = Context::getContext()->cookie->errors_messages;
                unset(Context::getContext()->cookie->errors_messages);
            }
            if (!empty(Context::getContext()->cookie->warnings_messages)) {
                $warnings = Context::getContext()->cookie->warnings_messages;
                unset(Context::getContext()->cookie->warnings_messages);
            }
            Context::getContext()->cookie->write();
            $this->context->smarty->assign('conf', $conf);
            $this->context->smarty->assign('errors', $errors);
            $this->context->smarty->assign('warnings', $warnings);

            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
            $order = new Order((int)$params['id_order']);

            $sql = 'SELECT o.*, ss.*
				FROM `' . _DB_PREFIX_ . 'seur2_order` o
                LEFT JOIN `' . _DB_PREFIX_ . 'seur2_status` ss ON ss.id_status = o.id_status
				WHERE o.id_order = ' . (int)$params['id_order'];

            $seur_order = Db::getInstance()->getRow($sql);

            if ($seur_order!=NULL)
            {
                $grupo = $seur_order['grupo'];
                $texto_grupo = "Pendiente";
                switch ($grupo) {
                    case "ENTREGADO":
                        $texto_grupo = "<span class='badge badge-success'>".$seur_order['status_text']."</span>";
                        break;
                    case "APORTAR SOLUCIÓN":
                    case "INCIDENCIA":
                        $texto_grupo = "<span class='badge badge-danger'>".$seur_order['status_text']."</span>";
                        break;
                    case "EN TRÁNSITO":
                    case "DISPONIBLE PARA RECOGER EN TIENDA":
                        $texto_grupo = "<span class='badge badge-warning'>".$seur_order['status_text']."</span>";
                        break;
                }

                $num_bultos = (int)$seur_order['numero_bultos'];
                $peso = (int)$seur_order['peso_bultos'];
                $ecb = $seur_order['ecb'];

                if($num_bultos == 0 || $seur_order['service']==77)
                    $num_bultos = 1;

                if($peso == 0)
                    $peso = 1;

                $gastos_envio = $order->total_shipping;
                $referencia = SeurLib::getOrderReference($order);
                $iso_country = Country::getIsoById((int)$seur_order['id_country']);
                if (SeurLib::isInternationalShipping($iso_country)) {
                    $referencia = $seur_order['ecb'];
                }
                $fecha = $seur_order['date_labeled'];

                $url_tracking = "https://www.seur.com/livetracking/pages/seguimiento-online.do?segOnlineIdentificador=".$referencia."&segOnlineFecha=".substr($fecha,8,2)."-".substr($fecha,5,2)."-".substr($fecha,0,4);

                $this->context->smarty->assign('shippings', $seur_order);
                $url = $this->context->link->getAdminLink('AdminSeurShipping',true);
                $this->context->smarty->assign('urlmodule', $url);

                $tracking = $seur_order['ecb'];

                $firstname = $seur_order['firstname'];
                $lastname = $seur_order['lastname'];
                $phone = SeurLib::cleanPhone($seur_order['phone']);
                $phone_mobile = SeurLib::cleanPhone($seur_order['phone_mobile']);
                $dni = $seur_order['dni'];
                $address1 = $seur_order['address1'];
                $address2 = $seur_order['address2'];
                $postcode = $seur_order['postcode'];
                $product_code = $seur_order['product'];
                $service_code = $seur_order['service'];
                $city = $seur_order['city'];
                $id_country = $seur_order['id_country'];
                $country_name = Country::getNameById(Context::getContext()->language->id, $id_country);
                $id_state = $seur_order['id_state'];
                if ($id_state == 0) {
                    $address = new Address($seur_order['id_address_delivery']);
                    $state_name = $address->city;
                } else {
                    $state_name = State::getNameById($id_state);
                }
                $other = $seur_order['other'];
                $countries = Country::getCountries(Context::getContext()->language->id);
                $states = State::getStatesByIdCountry($id_country);

                $id_seur_ccc = $seur_order['id_seur_ccc'];

                $this->context->smarty->assign('estado', $texto_grupo);
                $this->context->smarty->assign('num_bultos', $num_bultos);
                $this->context->smarty->assign('peso', $peso);
                $this->context->smarty->assign('gastos_envio', $gastos_envio);
                $this->context->smarty->assign('reference', $referencia);
                $this->context->smarty->assign('tracking', $tracking);
                $this->context->smarty->assign('url_tracking', $url_tracking);
                $this->context->smarty->assign('labeled', $seur_order['labeled']);
                $this->context->smarty->assign('classic', (int)($seur_order['service']==77));

                $this->context->smarty->assign('firstname', $firstname);
                $this->context->smarty->assign('lastname', $lastname);
                $this->context->smarty->assign('phone', $phone);
                $this->context->smarty->assign('phone_mobile', $phone_mobile);
                $this->context->smarty->assign('dni', $dni);
                $this->context->smarty->assign('address1', $address1);
                $this->context->smarty->assign('address2', $address2);
                $this->context->smarty->assign('postcode', $postcode);
                $this->context->smarty->assign('city', $city);
                $this->context->smarty->assign('id_country', $id_country);
                $this->context->smarty->assign('id_state', $id_state);
                $this->context->smarty->assign('country_name', $country_name);
                $this->context->smarty->assign('state_name', $state_name);
                $this->context->smarty->assign('other', $other);
                $this->context->smarty->assign('states', $states);
                $this->context->smarty->assign('countries', $countries);

                $this->context->smarty->assign('list_ccc', SeurCCC::getListCCC());
                $this->context->smarty->assign('id_seur_ccc', $id_seur_ccc);

                $this->context->smarty->assign('print_label', Context::getContext()->link->getAdminLink('AdminSeurShipping')."&action=print_label&id_order=".$seur_order['id_seur_order']);
                $this->context->smarty->assign('url_edit_order', Context::getContext()->link->getAdminLink('AdminSeurShipping')."&action=edit_order&id_order=".$seur_order['id_order']);

                $this->context->smarty->assign('send_to_digital_docu', (!$seur_order['brexit'] || !$seur_order['tariff']) && $order->hasInvoice() && !SeurLib::isEuropeanShipping($seur_order['id_seur_order']));
                $this->context->smarty->assign('send_digital_docu', Context::getContext()->link->getAdminLink('AdminSeurShipping')."&action=send_dd&id_seur_order=".$seur_order['id_seur_order']."&id_order=".$seur_order['id_order']);

                $serviceTypes = SeurLib::getServicesTypes();
                $shipping_type = SeurLib::getServiceType($service_code);

                $this->context->smarty->assign('services_types', $serviceTypes);
                $this->context->smarty->assign('products', []);
                $this->context->smarty->assign('services', []);

                $this->context->smarty->assign('shipping_type', $shipping_type);
                $this->context->smarty->assign('product_code', $product_code);
                $this->context->smarty->assign('service_code', $service_code);

                $this->context->smarty->assign('insured', $seur_order['insured']);
                $this->context->smarty->assign('id_seur_order', $seur_order['id_seur_order']);
                $this->context->smarty->assign('seur_url_basepath', seurLib::getBaseLink());

                return $this->display(__FILE__, 'views/templates/admin/order_data.tpl');

            } else if(Configuration::get('SEUR2_CAPTURE_ORDER')) {

                $url = $this->context->link->getAdminLink('AdminSeurShipping',true) .
                    '&AddNewOrder=' . (int)$params['id_order'];

                $urlCarrier = $this->context->link->getAdminLink('SeurCarrier',true);

                $address_delivery = new AddressCore($order->id_address_delivery);
                $cookie = $this->context->cookie;
                $newcountry = new Country($address_delivery->id_country, (int)$cookie->id_lang);
                $international = SeurLib::isInternationalShipping($newcountry->iso_code);
                $carriers = SeurCarrier::getSeurCarriers(false, $international);

                $this->context->smarty->assign('carriers', $carriers);
                $this->context->smarty->assign('urlmodule', $url);
                $this->context->smarty->assign('urlcarrier', $urlCarrier);

                return $this->display(__FILE__, 'views/templates/admin/order_new_shipping.tpl');
            }
        }
    }

    public function ajaxProcessUpdateShippings()
    {
        $command = new Seur\Prestashop\Commands\UpdateShipmentsStatus;
        $jsondata = $command->handle();

        echo json_encode($jsondata);
        die;  //para que no siga la ejecución y devuelva bien para el ajax
    }
}
