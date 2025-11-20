<?php

require_once(_PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SeurOrder.php');

class AdminSeurBulkAssignCarrierController extends ModuleAdminController
{

    public function __construct()
    {
        $module = Module::getInstanceByName('seur');

        $this->bootstrap = true;
        $this->name = 'AdminSeurBulkAssignCarrier';
        $this->table = 'orders';
        $this->identifier = "id_order";
        $this->className = 'SeurOrder';
        $this->_defaultOrderWay = "DESC";
        $this->lang = false;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->module = $module;
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->list_no_link = false;
        $this->page_header_toolbar_btn = array();

        $this->bulk_actions = array();

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->module->l('ID order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'a!id_order',
            ),
            'reference' => array(
                'title' => $this->module->l('Reference'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'a!reference'
            ),
            'order_date' => array(
                'title' => $this->module->l('Date Order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'a!date_add'
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
                'search' => true,
            ),
            'country' => array(
                'title' => $this->module->l('Country'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            )
        );

        $this->_join .= ' 			
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON a.current_state = osl.id_order_state AND osl.id_lang=' . (int)Context::getContext()->language->id . '
            LEFT JOIN `' . _DB_PREFIX_ . 'address` ad ON ad.id_address = a.id_address_delivery
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` c ON c.id_country = ad.id_country AND c.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'state` st ON st.id_state = ad.id_state';

        $this->_select .= 'ad.firstname,ad.lastname, a.date_add, a.reference, c.name as country, st.name as state';

        $this->_where = "AND ifnull(a.id_carrier,0) = 0";

        $this->context->smarty->assign('controlador', 'AdminSeurBulkAssignCarrier');

        AdminController::__construct();
    }

    public function renderList(){

        if (count($this->module->errorConfigure())) {
            Tools::redirectAdmin('index.php?controller=adminmodules&configure=seur&token=' . Tools::getAdminTokenLite('AdminModules') . '&module_name=seur&settings=1');
            die();
        }
        if (Tools::getValue('massive_action') != '') {
            $orders = Tools::getValue('orderBox');
            if (strpos(Tools::getValue('massive_action'),  "assign_carrier") !== false) {
                $assign_carrier = $this->assignCarrier($orders, Tools::getValue('massive_action'));
                foreach ($assign_carrier as $message_type => $orders) {
                    if ($message_type == 'success') {
                        $orders_text = implode(", ", array_keys($orders));
                        $success_assignments = implode(", ", $orders); // array of id_seur_order for label creation
                        $text = $this->module->l('Successfully assigned carrier to orders ID: ') . $orders_text;
                        $this->show_messages[] = $text;
                        SeurLib::showMessageOK($this, $text);
                    } elseif ($message_type == 'not_found') {
                        $orders_text = implode(", ", array_keys($orders));
                        $text = $this->module->l('Not found orders ID: ') . $orders_text;
                        $this->show_messages[] = $text;
                        SeurLib::showMessageError($this, $text);
                    } else {
                        $orders_text = array();
                        foreach ($orders as $order_id => $error_message) {
                            $orders_text[] = $order_id . ' ('.$error_message.')';
                        }
                        $orders_text = implode(", ", $orders_text);
                        $text = $this->module->l('Error assigning carrier to order ID: ') .$orders_text;
                        $this->show_messages[] = $text;
                        SeurLib::showMessageError($this, $text);
                    }
                }
                /*if (Tools::getValue('massive_create_label') && count($success_assignments) > 0) {
                    require_once(_PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . 'seur' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'AdminSeurShippingController.php');
                    $AdminSeurShippingController = new AdminSeurShippingController();
                    $print_labels = $AdminSeurShippingController->printLabels($success_assignments);
                }*/
            }
        }
        $this->context->smarty->assign(
            array(
                'url_module' => $this->context->link->getAdminLink('AdminModules', true) . "&configure=seur&module_name=seur",
                'url_controller_shipping' => $this->context->link->getAdminLink('AdminSeurShipping', true),
                'url_controller_collecting' => $this->context->link->getAdminLink('AdminSeurCollecting', true),
                'url_controller_tracking' => $this->context->link->getAdminLink('AdminSeurTracking', true),
                'url_controller_returns' => $this->context->link->getAdminLink('AdminSeurReturns', true),
                'url_controller_bulk_assign_carrier' => $this->context->link->getAdminLink('AdminSeurBulkAssignCarrier', true),
                'url_controller_pickup_locations' => $this->context->link->getAdminLink('AdminSeurPickupLocations', true),
                'img_path' => $this->module->getPath() . 'views/img/',
                'module_path' => 'index.php?controller=AdminModules&configure=' . $this->module->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Seur::findTabIdByClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
                'seur_url_basepath' => seurLib::getBaseLink(),
                'bulk_assign_enabled' => Configuration::get('SEUR2_BULK_ASSIGN_CARRIER'),
                'tabSelect' => 'assignCarrier',
                'seur_active_carriers' => SeurLib::getSeurCarriers(true, true),
            ));

        $html = "";

        if (Tools::getValue('massive_create_label') && isset($success_assignments) && !empty($success_assignments))
        {
            $this->context->smarty->assign(
                array('id_seur_orders' => $success_assignments)
            );
            $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/assignmentsCreateLabel.tpl');;
        }

        $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/header.tpl');;
        $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/tabs.tpl');;
        $html .= parent::renderList();
        $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/massivesAssignCarrier.tpl');;
        return $html;
    }

    public function renderView()
    {
        $id_order = Tools::getValue('id_order');
        $url = Context::getContext()->link->getAdminLink(
            'AdminOrders',
            Tools::getAdminTokenLite('AdminOrders'),
            ['action' => 'vieworder', 'orderId' => $id_order]
        );
        Tools::redirectAdmin($url .'&vieworder&id_order='.$id_order);
    }

    private function assignCarrier($orders, $selectedCarrier)
    {
        $assign_carrier = array();
        $id_carrier = str_replace("assign_carrier_", "", $selectedCarrier);

        foreach ($orders as $order_id) {
            $order = new Order($order_id);
            if (!Validate::isLoadedObject($order)) {
                $assign_carrier['not_found'][$order_id] ='order not found';
                continue;
            }
            if (!self::ValidateCarrierForOrder($id_carrier, $order)) {
                $assign_carrier['error'][$order_id] = 'selected carrier not valid for order';
                continue;
            }

            $id_seur_carrier = SeurLib::getSeurCarrier($id_carrier);

            if ($id_seur_order = SeurOrder::addOrder($order_id, $id_seur_carrier, false)) {
                $assign_carrier['success'][$order_id] = $id_seur_order;
            } else {
                $assign_carrier['error'][$order_id] = 'error assigning carrier';
            }
        }
        return $assign_carrier;
    }

    protected static function ValidateCarrierForOrder($id_carrier, $order): bool
    {
        $cart  = new Cart((int)$order->id_cart);
        $address = new Address((int)$order->id_address_delivery);
        $carrier = new Carrier((int)$id_carrier);

        // Obtener zona desde la dirección
        $idZone = Address::getZoneById($address->id);

        // Validar que el carrier da servicio a esa zona y rango
        $isValid = $carrier->active
            && $carrier->checkCarrierZone($carrier->id, $idZone)
            && $cart->isCarrierInRange($carrier, $idZone);

        return $isValid;
    }
}
