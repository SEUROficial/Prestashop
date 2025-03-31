<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Ebolution TEAM <desarrollo@ebolution.com>
 * @copyright 2022 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

require_once(_PS_MODULE_DIR_ . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PDFMerger.php');
require_once(_PS_MODULE_DIR_ . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SeurOrder.php');
if (!class_exists('SeurCashOnDelivery')) include_once(_PS_MODULE_DIR_.'seurcashondelivery/seurcashondelivery.php');
use PDFMerger\PDFMerger;

class AdminSeurShippingController extends ModuleAdminController
{
    public function __construct()
    {
        $module = Module::getInstanceByName('seur');

        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->addJQuery();
            $this->addJS($module->getPath() . 'views/js/seurController.js');
        }

        $this->bootstrap = true;
        $this->name = 'AdminSeurShipping';
        $this->table = 'seur2_order';
        $this->identifier = "id_seur_order";
        $this->_defaultOrderWay = "DESC";
        $this->className = 'SeurOrder';
        $this->lang = false;
        $this->module = $module;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->show_toolbar = false;
        $this->page_header_toolbar_btn = array();

        $this->bulk_actions = array();

        /*
        'print_labels' => array(
                'text' => $this->l('Print labels selected shiping'),
                'icon' => 'icon-barcode',
            ),
        'manifest' => array(
                'text' => $this->l('Generate manifest selected shiping'),
                'icon' => 'icon-file-o',
            )
        );
        */

        $this->fields_list = array(
            'id_seur_order' => array(
                'title' => $this->module->l('ID seur order'),
                'align' => 'left',
                'orderby' => false,
                'search' => false,
                'class' => 'hidden',
            ),
            'id_order' => array(
                'title' => $this->module->l('ID order'),
                'align' => 'left',
                'orderby' => false,
                'search' => false,
                'filter_key' => 'a!id_order',
                'class' => 'hidden',
            ),
            'order_date' => array(
                'title' => $this->module->l('Date Order'),
                'align' => 'left',
                'orderby' => true,
                'type' => 'datetime',
                'search' => true,
                'filter_key' => 'o!date_add'
            ),
            'reference' => array(
                'title' => $this->module->l('Reference'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'o!reference'
            ),
            'firstname' => array(
                'title' => $this->module->l('Firstname'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'lastname' => array(
                'title' => $this->module->l('Lastname'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'ccc' => array(
                'title' => $this->module->l('CCC'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'sc!ccc'
            ),
            'address1' => array(
                'title' => $this->module->l('Address'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'postcode' => array(
                'title' => $this->module->l('Postal code'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'city' => array(
                'title' => $this->module->l('City'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'state' => array(
                'title' => $this->module->l('State'),
                'align' => 'left',
                'orderby' => true,
                'filter_key' => 'st!name',
                'search' => true,
            ),
            'country' => array(
                'title' => $this->module->l('Country'),
                'align' => 'left',
                'orderby' => true,
                'filter_key' => 'c!name',
                'search' => true,
            ),
            'order_status_name' => array(
                'title' => $this->module->l('Status'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 's!name'
            ),
            'labeled' => array(
                'title' => $this->module->l('Labeled'),
                'align' => 'left',
                'orderby' => true,
                'type' => 'bool',
                'active' => 'status',
                'filter_key' => 'a!labeled'

            ),
            'manifested' => array(
                'title' => $this->module->l('Manifested'),
                'align' => 'left',
                'orderby' => true,
                'type' => 'bool',
                'active' => 'status',
                'filter_key' => 'a!manifested'
            ),
            'date_labeled' => array(
                'title' => $this->module->l('Date labeled'),
                'align' => 'left',
                'orderby' => true,
                'type' => 'date',
            ),
            'ecb' => array(
                'title' => $this->module->l('Tracking N.'),
                'align' => 'left',
                'orderby' => true,
                'search' => true
            ),
        );

        $this->context->smarty->assign('controlador', 'AdminShippingReturns');

        $this->_join .= '
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON a.id_order = o.id_order
			LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` s ON s.id_order_state = o.current_state AND s.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` c ON c.id_country = a.id_country AND c.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'state` st ON st.id_state = a.id_state
            LEFT JOIN `' . _DB_PREFIX_ . 'seur2_ccc` sc ON sc.id_seur_ccc = a.id_seur_ccc';

        $this->_select .= 'a.firstname,a.lastname, IF(a.date_labeled = \'0000-00-00 00:00:00\', NULL, a.date_labeled) as date_labeled, s.name, o.date_add, o.reference, c.name as country, st.name as state, a.ecb,';

        AdminController::__construct();
    }

    public function renderView()
    {
        $seurOrder = new SeurOrder(Tools::getValue('id_seur_order'));
        $url = Context::getContext()->link->getAdminLink(
            'AdminOrders',
            Tools::getAdminTokenLite('AdminOrders'),
            ['action' => 'vieworder', 'orderId' => $seurOrder->id_order]
        );
        Tools::redirectAdmin($url .'&vieworder&id_order='.$seurOrder->id_order);
    }

    public function renderList()
    {
        if (count($this->module->errorConfigure())) {
            Tools::redirectAdmin('index.php?controller=adminmodules&configure=seur&token=' . Tools::getAdminTokenLite('AdminModules') . '&module_name=seur&settings=1');
            die();
        }

        if ((int)Tools::getValue('AddNewOrder')){
            $this->addOrder((int)Tools::getValue('AddNewOrder'),(int)Tools::getValue('seur_carrier'));
            $url = Context::getContext()->link->getAdminLink('AdminOrders');
            Tools::redirectAdmin($url . '&id_order='.(int)Tools::getValue('AddNewOrder').'&vieworder');
            die();
        }

        if (Tools::getValue('action') == "print_label") {
            $this->printLabel((int)Tools::getValue('id_order'));
            die();
        }

        if (Tools::getValue('action') == "print_labels") {
            $this->printLabels(Tools::getValue('id_orders'));
            die();
        }

        if (Tools::getValue('action') == "edit_order" && Tools::getvalue('num_bultos')) {
            $id_order = Tools::getValue('id_order');
            $this->editOrder($id_order);
            $url = Context::getContext()->link->getAdminLink(
                'AdminOrders',
                Tools::getAdminTokenLite('AdminOrders'),
                ['action' => 'vieworder', 'orderId' => $id_order]
            );
            Tools::redirectAdmin($url .'&vieworder&id_order='.$id_order);
            die();
        }

        if (Tools::getValue('massive_action') != '') {
            $orders = Tools::getValue('shippingBox');
            if(version_compare(_PS_VERSION_, '1.6', '<')) {
                $orders = Tools::getValue('seur2_orderBox');
            }
            if (Tools::getValue('massive_action') == "print_labels") {
                $print_labels = $this->printLabels($orders);
            }
            if (Tools::getValue('massive_action') == "manifest") {
                $manifest = $this->manifest($orders);
            }
            if (Tools::getValue('massive_action') == "change_ccc") {
                $ccc_massive_change = Tools::getValue('massive_change_ccc');
                $this->changeCCC($orders, $ccc_massive_change);
            }
        }

        if (Tools::getValue('action') == "send_dd") {
            $this->send_dd((int)Tools::getValue('id_seur_order'));
            //$url = Context::getContext()->link->getAdminLink('AdminOrders');  //comentado porque no muestra notificaciones
            //Tools::redirectAdmin($url . '&id_order='.(int)Tools::getValue('id_order').'&vieworder');
            //die();
        }

        $this->context->smarty->assign(
            array(
                'url_module' => $this->context->link->getAdminLink('AdminModules', true) . "&configure=seur&module_name=seur",
                'url_controller_shipping' => $this->context->link->getAdminLink('AdminSeurShipping', true),
                'url_controller_collecting' => $this->context->link->getAdminLink('AdminSeurCollecting', true),
                'url_controller_tracking' => $this->context->link->getAdminLink('AdminSeurTracking', true),
                'url_controller_returns' => $this->context->link->getAdminLink('AdminSeurReturns', true),
                'img_path' => $this->module->getPath() . 'views/img/',
                'module_path' => 'index.php?controller=AdminModules&configure=' . $this->module->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Tab::getIdFromClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
                'seur_url_basepath' => seurLib::getBaseLink(),
            ));

        $selecttab = "shipping";
        $this->context->smarty->assign(
            array('tabSelect' => $selecttab)
        );

        $smarty = $this->context->smarty;
        $html = "";

        if(isset($print_labels) && count($print_labels))
        {
            $this->context->smarty->assign(
                array('print_labels' => $print_labels)
            );

            if(Configuration::get('SEUR2_SETTINGS_PRINT_TYPE')==2) {
                $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/print_labels_txt.tpl');
            } else {
                $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/print_labels.tpl');
            }
        }

        if(isset($manifest))
        {
            $this->context->smarty->assign(
                array('manifest' => $manifest)
            );

            $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/manifest.tpl');;
        }

		$this->context->smarty->assign('list_ccc', SeurCCC::getListCCC());
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/header.tpl');;
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/tabs.tpl');;
        $html .= parent::renderList();
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/massives.tpl');;

        return $html;
    }

    public function printLabels($id_orders)
    {
        $print_labels = [];

        if(!isset($id_orders) || !is_array($id_orders))
            $id_orders = [];

        foreach ($id_orders as $id_seur_order) {
            $print_labels = array_merge($print_labels, $this->getAllLabels($id_seur_order));
        }

        $this->printAllLabels($print_labels, true);
        return $print_labels;
    }

    public function createLabel($id_seur_order)
    {
        $seur_order = new SeurOrder($id_seur_order);
        $id_order = $seur_order->id_order;
        $order = new Order((int)$id_order);

        /*if (SeurLib::getLabelFileName($order, null)) {
            return (int)$id_seur_order;
        }*/
        $seur_order->labeled = false;

        $versionSpecialClass = '';
        if (!file_exists(_PS_MODULE_DIR_ . 'seur/img/logonew_32.png') && file_exists(_PS_MODULE_DIR_ . 'seur/img/logonew.png'))
            ImageManager::resize(_PS_MODULE_DIR_ . 'seur/img/logonew.png', _PS_MODULE_DIR_ . 'seur/img/logonew_32.png', 32, 32, 'png');
        if (version_compare(_PS_VERSION_, '1.5', '<'))
            $versionSpecialClass = 'ver14';
        SeurLib::displayWarningSeur();

        if ($this->module->isConfigured())
        {
            $cookie = $this->context->cookie;
            $token = Tools::getValue('token');
            $back = Tools::safeOutput($_SERVER['REQUEST_URI']);

            $seur_carriers = SeurLib::getSeurCarriers(false);
            $ids_seur_carriers = array();
            foreach ($seur_carriers as $value) {
                $ids_seur_carriers[] = (int)$value['carrier_reference'];
            }

            if (!Validate::isLoadedObject($order))
                return false;
            $delivery_price = $order_weigth = 0;
            $products = $order->getProductsDetail();

            foreach ($products as $product) {
                $order_weigth += (float)$product['product_weight'] * (float)$product['product_quantity'];
            }

            $order_weigth = ($order_weigth < 1.0 ? 1.0 : (float)$order_weigth);
            $customer = new Customer((int)$order->id_customer);

            $iso_country = Country::getIsoById((int)$seur_order->id_country);
            $post_code = $seur_order->postcode;
            if ($iso_country == 'PT') {
                $post_code = explode(' ', $seur_order->postcode);
                $post_code = $post_code[0];
            }
            if ($iso_country == 'IE') {
                $post_code = '1';
            }

            $carrier = new Carrier((int)$order->id_carrier);

            if (in_array((int)$carrier->id_reference, $ids_seur_carriers))
            {
                if (!SeurLib::getSeurOrder((int)$order->id))
                    SeurLib::setSeurOrder((int)$order->id, '', 1, $order_weigth, null, null, $this->calculateCartAmount(new Cart($order->id_cart)));
                elseif (Tools::getValue('numBultos') && Tools::getValue('pesoBultos'))
                    SeurLib::setSeurOrder((int)$order->id, '', (int)Tools::getValue('numBultos'), str_replace(',', '.', Tools::getValue('pesoBultos')), null);

                $order_data = SeurLib::getSeurOrder((int)$order->id);
                $order_weigth = ((float)$order_weigth != $order_data['peso_bultos'] ? (float)$order_data['peso_bultos'] : (float)$order_weigth);
                $order_weigth = ($order_weigth < 1.0 ? 1.0 : (float)$order_weigth);

                $name = $seur_order->firstname . ' ' . $seur_order->lastname;
                $direccion = $seur_order->address1 . ' ' . $seur_order->address2;
                $newcountry = new Country((int)$seur_order->id_country, (int)$cookie->id_lang);

                $id_seur_ccc = $seur_order->id_seur_ccc;
                $merchant_data = SeurLib::getMerchantData((int)$id_seur_ccc);

                $iso_merchant = $merchant_data['country'];
                $rate_data = array(
                    'town' => $seur_order->city,
                    'peso' => (float)$order_weigth,
                    'post_code' => $post_code,
                    'bultos' => $order_data['numero_bultos'],
                    'ccc' => $merchant_data['ccc'],
                    'franchise' => $merchant_data['franchise'],
                    'iso' => $newcountry->iso_code,
                    'iso_merchant' => $iso_merchant,
                    'id_employee' => $cookie->id_employee,
                    'token' => Tools::getAdminTokenLite('AdminOrders'),
                    'back' => $back,
                    'product' => $seur_order->product,
                    'service' => $seur_order->service
                );

                $order_messages_str = '';
                $info_adicional_str = $seur_order->other;
                $order_messages = Message::getMessagesByOrderId((int)$id_order);
                if (is_array($order_messages)) {
                    foreach ($order_messages as $order_messag_tmp)
                        $order_messages_str .= "\n" . $order_messag_tmp['message'];

                    if (substr_count($order_messages_str, "\n") > 5)
                        $order_messages_str = str_replace(array("\r", "\n"), ' | ', $order_messages_str);

                    if (Tools::strlen($order_messages_str) > 250)
                        $order_messages_str = Tools::substr($order_messages_str, 0, 247) . '...';

                    $order_messages_str = trim($order_messages_str);
                }
                if (!empty($order_messages_str)) {
                    $info_adicional_str = $order_messages_str;
                }
                $info_adicional_str = html_entity_decode($info_adicional_str);

                $label_data = array(
                    'pedido' => SeurLib::getOrderReference($order),
                    'total_bultos' => $order_data['numero_bultos'],
                    'total_kilos' => (float)$order_weigth,
                    'direccion_consignatario' => $direccion,
                    'consignee_town' => $seur_order->city,
                    'codPostal_consignatario' => $post_code,
                    'telefono_consignatario' => SeurLib::cleanPhone(!empty($seur_order->phone) ? $seur_order->phone : $seur_order->phone_mobile),
                    'movil' => SeurLib::cleanPhone(!empty($seur_order->phone_mobile) ? $seur_order->phone_mobile : $seur_order->phone),
                    'name' => $name,
                    'companyia' => (!empty($seur_order->company) ? $seur_order->company : ''),
                    'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
                    'dni' => $seur_order->dni,
                    'info_adicional' => $info_adicional_str,
                    'country' => $newcountry->name,
                    'iso' => $newcountry->iso_code,
                    'iso_merchant' => $iso_merchant,
                    'admin_dir' => utf8_encode(_PS_ADMIN_DIR_),
                    'id_employee' => $cookie->id_employee,
                    'token' => Tools::getAdminTokenLite('AdminOrders'),
                    'back' => $back
                );

                $label_data['clave_reembolso'] = "";
                $label_data['valor_reembolso'] = "0";

                if (Seurlib::AddCOD($order)) {
                    $rate_data['reembolso'] = (float)$order->total_paid;
                    $label_data['reembolso'] = (float)$order->total_paid;
                    $label_data['clave_reembolso'] = "F";
                    $label_data['valor_reembolso'] = (float)$order->total_paid;

                    if (SeurLib::AddAllSeurCODPayments()) {
                        $total_seur_cod_paid = SeurLib::getAllSeurCODPayments($order->reference);
                        $label_data['reembolso'] = (float)$total_seur_cod_paid;
                        $label_data['valor_reembolso'] = (float)$total_seur_cod_paid;
                    }
                }

                /* COMPROBAMOS SI ES UN TRANSPORTISTA DE RECOGIDA EN PUNTO DE VENTA Y REESCRIBIMOS*/
                $servicio = $seur_order->service;
                $producto = $seur_order->product;

                $datospos = SeurLib::getOrderPos((int)$order->id_cart);

                if (!empty($datospos) && $datospos && SeurLib::isPickup($servicio, $producto))
                {
                    $label_data = array(
                        'pedido' => SeurLib::getOrderReference($order),
                        'total_bultos' => $label_data['total_bultos'],
                        'total_kilos' => (float)$label_data['total_kilos'],
                        'direccion_consignatario' => $direccion,
                        'consignee_town' => $datospos['city'],
                        'codPostal_consignatario' => $datospos['postal_code'],
                        'telefono_consignatario' => SeurLib::cleanPhone(!empty($seur_order->phone) ? $seur_order->phone : $seur_order->phone_mobile),
                        'movil' => SeurLib::cleanPhone(!empty($seur_order->phone_mobile) ? $seur_order->phone_mobile : $seur_order->phone),
                        'name' => $name,
                        'companyia' => $datospos['company'],
                        'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
                        'dni' => $seur_order->dni,
                        'info_adicional' => $info_adicional_str,
                        'country' => $newcountry->name,
                        'iso' => $newcountry->iso_code,
                        'cod_centro' => $datospos['id_seur_pos'],
                        'iso_merchant' => $iso_merchant
                    );
                    $rate_data['cod_centro'] = $datospos['id_seur_pos'];
                }

				if ($order->hasInvoice()){
                    if (!SeurLib::isEuropeanShipping($seur_order->id_seur_order)) {
                        SeurLib::invoiceBrexit((int)$seur_order->id_seur_order);
                        SeurLib::invoiceTariff((int)$seur_order->id_seur_order);
                    }
                }
                $is_international = SeurLib::isInternationalShipping($iso_country);
                $is_geolabel = SeurLib::isGeoLabel($id_seur_ccc);
                $label_file = SeurLabel::createLabels((int)$order->id, $label_data, $merchant_data, $is_geolabel, $is_international);

                if ($label_file === false) {
                    SeurLib::showMessageError($this, 'Could not set printed value for this order '. $order->reference);
                    return false;
                }

                $pickup = SeurPickup::getLastPickup($id_seur_ccc);
                if (!empty($pickup)) {
                    $pickup_date = explode(' ', $pickup['date']);
                    $pickup_date = $pickup_date[0];
                }
                $pickup_s = 0;
                if ($pickup && strtotime(date('Y-m-d')) >= strtotime($pickup_date))
                    $pickup_s = 1;

                $address_error = 0;

                /* Consultar estado */
                $state = SeurExpedition::getExpeditions(array(
                    'reference' => SeurLib::getOrderReference($order),
                    'idNumber' => Configuration::get('SEUR2_MERCHANT_NIF_DNI'),
                    'id_seur_ccc' => $id_seur_ccc
                ));
                $is_empty_state = false;
                $xml_s = false;
                if (empty($state->out))
                    $is_empty_state = true;
                else {
                    $string_xml = htmlspecialchars_decode($state->out);
                    $string_xml = str_replace('&', '&amp; ', $string_xml);
                    $xml_s = simplexml_load_string($string_xml);

                    if (!$xml_s->EXPEDICION)
                        $is_empty_state = true;
                }

				$rate = 0;
                $rate_data_ajax = json_encode($rate_data);
                $path = '../modules/seur/js/';
                $file = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/seur/files/deliveries_labels/' . $label_file;
                $filePath = _PS_MODULE_DIR_ . 'seur/files/deliveries_labels/' . $label_file;
                $label_data['file'] = $file;
                $this->context->smarty->assign(array(
                    'path' => $this->module->path,
                    'request_uri' => $_SERVER['REQUEST_URI'],
                    'module_instance' => $this,
                    'address_error' => $address_error,
                    'address_error_message' => $this->l('Addressess error, please check the customer address.'),
                    'pickup_s' => $pickup_s,
                    'pickup' => $pickup,
                    'isEmptyState' => $is_empty_state,
                    'xml' => $xml_s,
                    'order_data' => $order_data,
                    'iso_country' => $iso_country,
                    'order_weigth' => $order_weigth,
                    'delivery_price' => $delivery_price,
                    'delivery_rate' => $rate,
                    'delivery_price_tax_excl' => ($delivery_price - $rate),
                    'rate_data_ajax' => $rate_data_ajax,
                    'js_path' => $path,
                    'token' => $token,
                    'order' => $order,
                    'label_data' => $label_data,
                    'fileExists' => file_exists($filePath),
                    'file' => $file,
                    'datospos' => $datospos,
                    'versionSpecialClass' => $versionSpecialClass,
                    'configured' => (int)Configuration::get('SEUR_Configured'),
                    'printed' => (bool)(SeurLib::isPrinted((int)$order->id))
                ));
                SeurLib::showMessageOK($this, 'Label '.$label_file.' generated');
                return $seur_order->id_seur_order;
            }
            else {
                SeurLib::showMessageError($this, 'Label '.SeurLib::getOrderReference($order).' don\'t generated: Carrier not Seur' );
                return false;
            }
        }
        SeurLib::showMessageError($this, 'Módulo Seur no configurado' );
        return false;
	}

    public function changeCCC($orders, $ccc_massive_change)
    {
        foreach($orders as $id_order)
        {
            $seurOrder = new SeurOrder((int)$id_order);
            $seurOrder->id_seur_ccc = $ccc_massive_change;
            $seurOrder->save();
        }
    }

    public function manifest($id_orders)
    {
        if(!isset($id_orders) || !is_array($id_orders))
            $id_orders = array();

        return $this->generateManifest($id_orders);
    }

    private function editOrder($id_order)
    {
        $num_bultos     = (int)Tools::getvalue('num_bultos');
        $peso           = (float)Tools::getvalue('peso');
        $firstname      = Tools::getvalue('firstname');
        $lastname       = Tools::getvalue('lastname');
        $phone          = SeurLib::cleanPhone(Tools::getvalue('phone'));
        $phone_mobile   = SeurLib::cleanPhone(Tools::getvalue('phone_mobile'));
        $dni            = Tools::getvalue('dni');
        $other          = Tools::getvalue('other');
        $address1       = Tools::getvalue('address1');
        $address2       = Tools::getvalue('address2');
        $postcode       = Tools::getvalue('postcode');
        $city           = Tools::getvalue('city');
        $id_country     = (int)Tools::getvalue('id_country');
        $id_state       = (int)Tools::getvalue('id_state');
        $id_seur_ccc    = (int)Tools::getvalue('id_seur_ccc');
        $product    = (int)Tools::getvalue('product');
        $service    = (int)Tools::getvalue('service');
        $insured    = (int)Tools::getvalue('insured');

        $seur_order = SeurOrder::getByOrder($id_order);
        $seur_order_old = clone $seur_order;
        $seur_order->firstname      = pSQL($firstname);
        $seur_order->lastname       = pSQL($lastname);
        $seur_order->phone          = pSQL($phone);
        $seur_order->phone_mobile   = pSQL($phone_mobile);
        $seur_order->dni            = pSQL($dni);
        $seur_order->other          = pSQL($other);
        $seur_order->address1       = pSQL($address1);
        $seur_order->address2       = pSQL($address2);
        $seur_order->postcode       = pSQL($postcode);
        $seur_order->city           = pSQL($city);
        $seur_order->id_country     = $id_country;
        $seur_order->id_state       = $id_state;
        $seur_order->id_seur_ccc    = $id_seur_ccc;
        $seur_order->product        = $product;
        $seur_order->service        = $service;
        $seur_order->insured        = $insured;

        if (!$seur_order->expeditionCode) {
            // only save changes, shipment not created yet
            $seur_order->numero_bultos  = $num_bultos;
            $seur_order->peso_bultos    = $peso;
            $seur_order->save();
            return true;
        }

        if (SeurLib::ShipmentDataUpdated($seur_order_old, $seur_order)) {
            if (SeurLabel::updateShipments($seur_order)) {
                // Crear dirección de envío con estos datos y asignarla al pedido
                SeurLib::updateOrderAddress($seur_order);
            }
        }

        $packages_old = $seur_order_old->numero_bultos;
        $packages = $num_bultos;
        $peso_packages = $peso;

        if (SeurLib::PackagesDataUpdated($packages_old, $packages) && $seur_order) {
            // Comprobar si el número de bultos ha aumentado antes de actualizar el envío
            if ($packages > $packages_old) {
                $response = SeurLabel::addParcelsToShipment($packages_old, $packages, $peso_packages, $seur_order->expeditionCode);

                // Si la respuesta es válida, actualizar seur_order con los nuevos datos
                if ($response) {
                    $seur_order->numero_bultos = $packages;
                    $seur_order->peso_bultos = $peso_packages;
                    SeurLib::updateSeurOrderWithParcels($seur_order, $response);
                }
            }
        }
    }

    private function addOrder($id_order, $id_seur_carrier)
    {
        try {
            $db = Db::getInstance();
            $link = $db->getLink();

            if ($link instanceof \PDO) {
                $link->beginTransaction();
            } elseif ($link instanceof \mysqli) {
                $link->begin_transaction();
            }

            $order = new Order((int)$id_order);
            $order_payment = OrderPayment::getByOrderReference($order->reference);
            if (isset($order_payment[0])) {
                $order_payment = $order_payment[0];
            } else {
                $order_payment = null;
            }
            $address_delivery = new AddressCore($order->id_address_delivery);
            $cookie = $this->context->cookie;
            $newcountry = new Country($address_delivery->id_country, (int)$cookie->id_lang);
            $seur_carrier = new SeurCarrier($id_seur_carrier);

            $carrier = Carrier::getCarrierByReference($seur_carrier->carrier_reference);
            $order->id_carrier = $carrier->id;
            $order->save();

            $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
            $order_carrier->id_carrier = $carrier->id;
            $order_carrier->update();

            /* --- refreshShippingCost actualiza:-------
                order.total_shipping_tax_excl,
                order.total_shipping_tax_incl,
                order.total_shipping,
                order.total_paid_tax_excl,
                order.total_paid_tax_incl,
                order.total_paid,
                order_carrier.shipping_cost_tax_incl,
                order_carrier.shipping_cost_tax_excl
            */
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->refreshShippingCost($order);
            }
            else {
                $order->refreshShippingCost();
            }

            $seur_order = new SeurOrder();
            $seur_order->id_order = (int)$id_order;
            $seur_order->id_seur_ccc = SeurLib::getCCC($newcountry->iso_code);
            $seur_order->id_seur_carrier = (int)$id_seur_carrier;
            $seur_order->numero_bultos = 1;
            $seur_order->peso_bultos = ($order->getTotalWeight()?$order->getTotalWeight():1);
            $seur_order->firstname = $address_delivery->firstname;
            $seur_order->lastname = $address_delivery->lastname;
            $seur_order->address1 = $address_delivery->address1;
            $seur_order->address2 = $address_delivery->address2;
            $seur_order->postcode = $address_delivery->postcode;
            $seur_order->phone = SeurLib::cleanPhone($address_delivery->phone);
            $seur_order->phone_mobile = SeurLib::cleanPhone($address_delivery->phone_mobile);
            $seur_order->city = $address_delivery->city;
            $seur_order->id_state = $address_delivery->id_state;
            $seur_order->id_country = $address_delivery->id_country;
            $seur_order->dni = $address_delivery->dni;
            $seur_order->other = $address_delivery->other;
            $seur_order->labeled = 0;
            $seur_order->manifested = 0;
            $seur_order->codfee = 0;
            $seur_order->id_address_delivery = $order->id_address_delivery;
            $seur_order->id_status = 0;
            $seur_order->service = $seur_carrier->service;
            $seur_order->product = $seur_carrier->product;
            $seur_order->total_paid = $order->total_paid_real;

            if (SeurLib::isCODPayment($order) && $order->module != SeurLib::CODPaymentModule) {
                //cambiar el método COD seleccionado al de seurcashondelivery
                $order->payment = SeurLib::CODPaymentName;
                $order->module = SeurLib::CODPaymentModule;

                $shipping_amount_tax_incl = $order->total_shipping_tax_incl; //ya actualizados al nuevo carrier (seur)
                $shipping_amount_tax_excl = $order->total_shipping_tax_excl; //ya actualizados al nuevo carrier (seur)

                //recalcular gastos por pago contrareembolso
                $cod_amount = SeurLib::calculateCODAmount($order);
                $order->total_paid_real = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
                $order->total_paid = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
                $order->total_paid_tax_excl = $order->total_products + $shipping_amount_tax_excl + $cod_amount;
                $order->total_paid_tax_incl = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
                $order->total_shipping = $shipping_amount_tax_incl + $cod_amount;

                // PS - Corregir valor total_shipping_tax_excl
                $cod_amount_tax_excl = $cod_amount;
                $percentage_apply = Configuration::get('SEUR2_SETTINGS_COD_FEE_PERCENT');
                if($percentage_apply){
                    $percentage_apply = str_replace(',','.',$percentage_apply);
                    $percentage_apply = $percentage_apply / 100;
                    // Calculamos el número final
                    $cod_amount_tax_excl = $cod_amount_tax_excl * (1 - $percentage_apply);
                }

                $order->total_shipping_tax_excl = $shipping_amount_tax_excl + $cod_amount_tax_excl;
                $order->total_shipping_tax_incl = $shipping_amount_tax_incl + $cod_amount;

                $seur_order->total_paid = $order->total_paid_real;
                $seur_order->cashondelivery = $order->total_paid_real;
                $seur_order->codfee = $cod_amount;

                //actualizar payment
                if (isset($order_payment)) {
                    $order_payment->amount = $order->total_paid;
                    $order_payment->payment_method = $order->payment;
                    $order_payment->id_currency = $order->id_currency;
                    $order_payment->update();
                }

                //actualizar costes envío transportista
                $order_carrier->shipping_cost_tax_excl = $shipping_amount_tax_excl + $cod_amount;
                $order_carrier->shipping_cost_tax_incl =  $shipping_amount_tax_incl + $cod_amount;
                $order_carrier->update();
            }

            $result = $seur_order->save();
            $order->save();

            //obtener datos de la factura y modificar
            if (isset($order_payment)) {
                $order_invoice = $order_payment->getOrderInvoice($id_order);
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
            if ($result) {
                $link->commit();
                SeurLib::showMessageOK($this, 'Se ha cambiado el pedido para envío con Seur');
                return true;
            }

        } catch (Exception $e) {
            SeurLib::log('ADD SEUR ORDER '.$id_order.' - '.$e->getMessage());
        }

        $link->rollback();
        SeurLib::showMessageError($this, 'No se ha podido cambiar el pedido para envío con Seur');
        return false;
    }

    /**
     * Re calculate shipping cost.
     *
     * @return object $order
     */
    public function refreshShippingCost($order)
    {
        if (empty($order->id)) {
            return false;
        }

        if (!Configuration::get('PS_ORDER_RECALCULATE_SHIPPING')) {
            return $order;
        }

        $fake_cart = new Cart((int) $order->id_cart);
        $new_cart = $fake_cart->duplicate();
        $new_cart = $new_cart['cart'];

        // assign order id_address_delivery to cart
        $new_cart->id_address_delivery = (int) $order->id_address_delivery;

        // assign id_carrier
        $new_cart->id_carrier = (int) $order->id_carrier;

        //remove all products : cart (maybe change in the meantime)
        foreach ($new_cart->getProducts() as $product) {
            $new_cart->deleteProduct((int) $product['id_product'], (int) $product['id_product_attribute']);
        }

        // add real order products
        foreach ($this->getProducts() as $product) {
            $new_cart->updateQty(
                $product['product_quantity'],
                (int) $product['product_id'],
                null,
                false,
                'up',
                0,
                null,
                true,
                true
            ); // - skipAvailabilityCheckOutOfStock
        }

        // get new shipping cost
        $base_total_shipping_tax_incl = (float) $new_cart->getPackageShippingCost((int) $new_cart->id_carrier, true, null);
        $base_total_shipping_tax_excl = (float) $new_cart->getPackageShippingCost((int) $new_cart->id_carrier, false, null);

        // calculate diff price, then apply new order totals
        $diff_shipping_tax_incl = $order->total_shipping_tax_incl - $base_total_shipping_tax_incl;
        $diff_shipping_tax_excl = $order->total_shipping_tax_excl - $base_total_shipping_tax_excl;

        $order->total_shipping_tax_excl -= $diff_shipping_tax_excl;
        $order->total_shipping_tax_incl -= $diff_shipping_tax_incl;
        $order->total_shipping = $order->total_shipping_tax_incl;
        $order->total_paid_tax_excl -= $diff_shipping_tax_excl;
        $order->total_paid_tax_incl -= $diff_shipping_tax_incl;
        $order->total_paid = $order->total_paid_tax_incl;
        $order->update();

        // save order_carrier prices, we'll save order right after this in update() method
        $orderCarrierId = (int) $order->getIdOrderCarrier();
        if ($orderCarrierId > 0) {
            $order_carrier = new OrderCarrier($orderCarrierId);
            $order_carrier->shipping_cost_tax_excl = $order->total_shipping_tax_excl;
            $order_carrier->shipping_cost_tax_incl = $order->total_shipping_tax_incl;
            $order_carrier->update();
        }

        // remove fake cart
        $new_cart->delete();

        return $order;
    }

    private function getAllLabels($id_order)
    {
        $seur_order = new SeurOrder($id_order);
        $label_files_result = [];
        if ($this->createLabel($seur_order->id)) {
            $label_files = SeurOrder::getLabelFile($id_order); //Ahora puede ser array
            $extension = '.pdf';
            $aux = explode($extension.'-', $label_files.'-');

            if (strpos($label_files, '.txt') !== false) {
                $extension = '.txt';
                $aux = explode($extension.'-', $label_files.'-');
            }
            for ($k=0; $k < count($aux)-1; $k++) {
                $label_files_result[] = $aux[$k] . $extension;
            }
            return $label_files_result;
        }
        SeurLib::showMessageError($this, 'Document was already printed, but is missing in module directory' );
        return [];
    }

    private function printLabel($id_order)
    {
        $label_files = $this->getAllLabels($id_order);
        $this->printAllLabels($label_files);
    }

    private function printAllLabels($label_files, $massive=false)
    {   $fp = '';
        $type = '';
        $label_file = '';

        $pdf = new PDFMerger;
        $directory = _PS_MODULE_DIR_ . 'seur/files/deliveries_labels/';
        foreach ($label_files as $label_file) {
            $type = substr($label_file, -3); //el tipo de la etiqueta que se generó
            //#TODO Se obtiene el contenido de las etiqueta en cada llamada a labels, no haría falta guardar el fichero...
            if (file_exists($directory . $label_file) && !empty($label_file)) {
                if ($type=='pdf') {
                    $pdf->addPDF($directory . $label_file);
                } else {
                    $fp .= Tools::file_get_contents($directory . $label_file);
                }
            }
        }
        $label_filename = "Orders_".date('YmdHis').'.'.$type;
        if (!$massive) {
            $label_filename = $label_files[0];
        }
        if ($type=='pdf') {
            $pdf->merge('download', $label_filename);
            exit;
        } else {
            if (!empty($fp)) {
                ob_end_clean();
                header('Content-type: application/rtf');
                header('Content-Disposition: inline; filename=' . $label_filename);
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');
                echo $fp;
                exit;
            }
        }
        return false;
    }

    private function generateManifest($id_orders){
        $manifest = SeurManifest::createManifest($id_orders);
    }

    private function send_dd($id_seur_order){
        SeurLib::invoiceBrexit($id_seur_order);
        SeurLib::invoiceTariff($id_seur_order);
    }

}
