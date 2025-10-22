<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Maria Jose Santos <mariajose.santos@ebolution.com>
 * @copyright 2022 Seur Transporte
 * @license https://seur.com/ Proprietary
 */
require_once(_PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SeurOrder.php');
if (!class_exists('SeurLib')) include(_PS_MODULE_DIR_ . 'seur/classes/SeurLib.php');

class AdminSeurTrackingController extends ModuleAdminController
{
    public function __construct()
    {
        $module = Module::getInstanceByName('seur');;

        $this->context->smarty->assign('page', 'AdminSeurReturns');

        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->addJQuery();
            $this->addJS($module->getPath() . 'views/js/seurController.js');
        }

        $this->bootstrap = true;
        $this->name = 'AdminSeurReturns';
        $this->table = 'seur2_order';
        $this->className = 'SeurOrder';
        $this->lang = false;
        $this->module = $module;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();

        AdminController::__construct();

        $this->context->smarty->assign('controlador', 'AdminSeurReturns');

    }

    public function renderList()
    {

        if (count($this->module->errorConfigure())) {
            Tools::redirectAdmin('index.php?controller=adminmodules&configure=seur&token=' . Tools::getAdminTokenLite('AdminModules') . '&module_name=seur&settings=1');
            die();
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

        if (Tools::getValue('action') == "print_label") {
            $this->printLabel((int)Tools::getValue('id_order'),'pdf');
            die();
        }


        if (Tools::getValue('massive_action') == "print_labels") {
            $print_labels = $this->printLabels(Tools::getValue('shippingBox'));
        }

        if (Tools::getValue('massive_action') == "manifest") {
            $this->manifest();
        }


        if (Tools::isSubmit('submitEditShipping')) {
            $this->saveShipping();
        }

        if (Tools::getValue('AddNewOrder')) {
            $selecttab = "shipping";

            $this->addNewOrder((int)Tools::getValue('AddNewOrder'), (int)Tools::getValue('id_seur_carrier'));

            $this->context->smarty->assign(
                array('tabSelect' => $selecttab)
            );
            $page = $this->renderShipping();
        }


        if (Tools::getValue('collecting')) {
            $selecttab = "collecting";
            $this->context->smarty->assign(
                array('tabSelect' => $selecttab)
            );
            $page = $this->renderCollecting();
        }
        if (Tools::getValue('tracking')) {
            $selecttab = "tracking";
            $this->context->smarty->assign(
                array('tabSelect' => $selecttab)
            );
            $page = $this->renderTracking();
        }
        if (Tools::getValue('returning')) {
            $selecttab = "returning";
            $this->context->smarty->assign(
                array('tabSelect' => $selecttab)
            );
            $page = "";
        }

        if (Tools::getValue('shipping') || !isset($selecttab)) {
            $selecttab = "shipping";
            $this->context->smarty->assign(
                array('tabSelect' => $selecttab)
            );
            $page = $this->renderShipping();

        }


        if (Tools::isSubmit('updateseur2_order')) {
            $selecttab = "shipping";
            $this->context->smarty->assign(
                array('tabSelect' => $selecttab)
            );
            $page = $this->renderFormShipping();

        }


        $smarty = $this->context->smarty;
        $html = "";

        if(isset($print_labels) && count($print_labels))
        {
            $this->context->smarty->assign(
                array('print_labels' => $print_labels)
            );

            $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/print_labels.tpl');;
        }

        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/header.tpl');;
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/tabs.tpl');;
        $html .= $page;
        return $html;
    }


    public function renderShipping()
    {
        $this->fields_list = array(
            'id_seur_order' => array(
                'title' => $this->l('ID seur order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'id_order' => array(
                'title' => $this->l('ID order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'o!reference'
            ),
            'order_date' => array(
                'title' => $this->l('Date Order'),
                'align' => 'left',
                'orderby' => true,
                'type' => 'datetime',
                'search' => true,
                'filter_key' => 'a!order_date'
            ),
            'customer_name' => array(
                'title' => $this->l('Name'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'address1' => array(
                'title' => $this->l('Address'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'postcode' => array(
                'title' => $this->l('Postal code'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'city' => array(
                'title' => $this->l('City'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'state' => array(
                'title' => $this->l('State'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'country' => array(
                'title' => $this->l('Country'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'order_status_name' => array(
                'title' => $this->l('Status'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'labeled' => array(
                'title' => $this->l('Labeled'),
                'align' => 'left',
                'orderby' => true,
                'type' => 'bool',
                'active' => 'status',
                'filter_key' => 'a!labeled'

            ),
            'manifested' => array(
                'title' => $this->l('Manifested'),
                'align' => 'left',
                'orderby' => true,
                'type' => 'bool',
                'active' => 'status',
                'filter_key' => 'a!manifested'
            ),
        );

        $helper = new HelperListCore();

        $helper->shopLinkType = '';
        $helper->no_link = true;
        $helper->simple_header = false;

        // Actions to be displayed in the "Actions" column
        $helper->actions = array('edit');

        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        $helper->identifier = 'id_seur_order';
//        $helper->title = $this->l('Shipping manage');
        $helper->_default_pagination = 20;
        $helper->_defaultOrderBy = 'id_seur_order';
        $helper->_defaultOrderWay = 'DESC';
        $helper->show_toolbar = false;
        $helper->table = 'shipping';
        $helper->className = 'SeurOrder';
        $helper->token = Tools::getAdminTokenLite('AdminSeurShipping');
        $helper->currentIndex = AdminController::$currentIndex."&shipping=1";
        $helper->module = $this->module;


        /*

        $sql = 'SELECT a.*, IF(a.status_shipping!=19,labelfile,"") as labelprint, CONCAT(if(r.name is null ,"",r.name)," ",if(r.lastname is null ,"",r.lastname)) as receiver, CONCAT(if(s.name is null ,"",s.name)," ",if(s.lastname is null ,"",s.lastname)) as sender
			FROM `' . _DB_PREFIX_ . 'zeleris_orders` a
			LEFT JOIN `' . _DB_PREFIX_ . 'zeleris_address` r ON a.id_order_address_receiver = r.id_zeleris_address
			LEFT JOIN `' . _DB_PREFIX_ . 'zeleris_address` s ON a.id_order_address_origen = s.id_zeleris_address
			WHERE 1';

        if ($this->context->cookie->{'zeleris_ordersFilter_a!id_order'}) {
            $sql .= ' AND id_order=' . (int)$this->context->cookie->{'zeleris_ordersFilter_a!id_order'};

        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!reference'}) {
            $sql .= ' AND reference like "%' . $this->context->cookie->{'zeleris_ordersFilter_a!reference'} . '%"';
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!expedition'}) {
            $sql .= ' AND expedition like "%' . $this->context->cookie->{'zeleris_ordersFilter_a!expedition'} . '%"';
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!capture'}) {
            $sql .= ' AND capture like "%' . $this->context->cookie->{'zeleris_ordersFilter_a!capture'} . '%"';
        }

        if ( $this->context->cookie->{'zeleris_ordersFilter_a!receiver'}) {
            $sql .= ' AND (r.name like "%' .  $this->context->cookie->{'zeleris_ordersFilter_a!receiver'} . '%" OR r.lastname like "%' .  $this->context->cookie->{'zeleris_ordersFilter_a!receiver'} . '%")';
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!sender'}) {
            $sql .= ' AND (s.name like "%' . $this->context->cookie->{'zzeleris_ordersFilter_a!sender'} . '%" OR s.lastname like "%' . $this->context->cookie->{'zeleris_ordersFilter_a!sender'} . '%")';
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!status_collection'} != "") {
            $sql .= ' AND status_collection=' . (int)$this->context->cookie->{'zeleris_ordersFilter_a!status_collection'};
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!status_shipping'} != "") {
            $sql .= ' AND status_shipping=' . (int)$this->context->cookie->{'zeleris_ordersFilter_a!status_shipping'};
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!returned'} !== "") {
            $sql .= ' AND returned=' . (int)$this->context->cookie->{'zeleris_ordersFilter_a!returned'};
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!date_0'} !== false && $this->context->cookie->{'zeleris_ordersFilter_a!date_0'}!="") {
            $sql .= ' AND date>="' . $this->context->cookie->{'zeleris_ordersFilter_a!date_0'} . '"';
        }

        if ($this->context->cookie->{'zeleris_ordersFilter_a!date_1'} !== false && $this->context->cookie->{'zeleris_ordersFilter_a!date_1'}!="") {
            $sql .= ' AND date<="' . $this->context->cookie->{'zeleris_ordersFilter_a!date_1'} . '"';
        }

        //ORDENACIóN
        if ($this->context->cookie->{'zeleris_ordersOrderby'}) {
            $helper->orderBy = $this->context->cookie->{'zeleris_ordersOrderby'};
            $sql .= " ORDER BY " . $this->context->cookie->{'zeleris_ordersOrderby'};
            if ($this->context->cookie->{'zeleris_ordersOrderway'}) {
                $helper->orderWay =$this->context->cookie->{'zeleris_ordersOrderway'};
                $sql .= " " . $this->context->cookie->{'zeleris_ordersOrderway'}. " ";
            }
        } else {
            $helper->orderBy = 'date';
            $sql .= " ORDER BY date";
            $helper->orderWay = "DESC";
            $sql .= " DESC";
        }

        $num_page = $helper->_default_pagination;
        if ($this->context->cookie->{'zeleris_orders_pagination'}) {
            $num_page = $this->context->cookie->{'zeleris_orders_pagination'};
        }

        // PAGINACION
        $page = 0;
        if ($this->context->cookie->{'submitFilterzeleris_orders'}) {
            $page = $this->context->cookie->{'submitFilterzeleris_orders'} - 1;
        }

        $content = Db::getInstance()->executeS($sql);

        $helper->listTotal = count($content);

        if ($page * $num_page < count($content)) {
            $sql .= " LIMIT " . $page * $num_page . ", " . $num_page;
        } else {
            $sql .= " LIMIT " . $num_page;
        }
        if (Tools::getValue('page')) {
            $helper->page = Tools::getValue('page');
        }

*/


        $sql = 'SELECT a.*,CONCAT(a.firstname," ",a.lastname) as customer_name, s.name as order_status_name, o.date_add as order_date, o.reference, c.name as country, st.name as state
			FROM `' . _DB_PREFIX_ . 'seur2_order` a
			LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON a.id_order = o.id_order
			LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` s ON s.id_order_state = o.current_state AND s.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` c ON c.id_country = a.id_country AND c.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'state` st ON st.id_state = a.id_state
			WHERE true';






        $content = Db::getInstance()->executeS($sql);

        $html = $helper->generateList($content, $this->fields_list);

        $smarty = $this->context->smarty;
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/massives.tpl');;

        return $html;
    }

    public function renderTracking()
    {

        $this->fields_list = array(
            'id_seur_order' => array(
                'title' => $this->l('ID Seur order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'id_order' => array(
                'title' => $this->l('ID order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'o!reference'
            ),
            'order_date' => array(
                'title' => $this->l('Date Order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'customer_name' => array(
                'title' => $this->l('Name'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'address1' => array(
                'title' => $this->l('Address'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'postcode' => array(
                'title' => $this->l('Postal code'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'city' => array(
                'title' => $this->l('City'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'state' => array(
                'title' => $this->l('State'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'country' => array(
                'title' => $this->l('Country'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
            'order_status_name' => array(
                'title' => $this->l('Status'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
            ),
        );


        $helper = new HelperList();


        $helper->shopLinkType = '';
        $helper->no_link = true;
        $helper->simple_header = false;

        // Actions to be displayed in the "Actions" column
        $helper->actions = array('edit');

        $helper->identifier = 'id_seur_order';
        $helper->_default_pagination = 20;
        $helper->_defaultOrderBy = 'date';
        $helper->_defaultOrderWay = 'DESC';
        $helper->show_toolbar = true;
        $helper->table = 'seur2_order';
        $helper->className = 'SeurOrder';
        $helper->title = $this->l('Tracking packages');
        $helper->token = Tools::getAdminTokenLite('AdminSeurShipping');
        $helper->currentIndex = AdminController::$currentIndex . '&shipping=1';
        $helper->module = $this->module;


        $sql = 'SELECT a.*,CONCAT(a.firstname," ",a.lastname) as customer_name, s.name as order_status_name, o.date_add as order_date, o.reference, c.name as country, st.name as state
			FROM `' . _DB_PREFIX_ . 'seur2_order` a
			LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON a.id_order = o.id_order
			LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` s ON s.id_order_state = o.current_state AND s.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` c ON c.id_country = a.id_country AND c.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'state` st ON st.id_state = a.id_state
			WHERE a.id_status != 0';


        $content = Db::getInstance()->executeS($sql);

        $this->context->smarty->assign('has_bulk_actions', true);

        $html = $helper->generateList($content, $this->fields_list);

        return $html;
    }


    public function renderCollecting()
    {
        $pickupFixed = Configuration::get('SEUR2_SETTINGS_PICKUP');
        $pickupSolicited = 0;

        $this->context->smarty->assign(
            array('pickupFixed' => $pickupFixed,
                'pickupSolicited' => $pickupSolicited)
        );


        $smarty = $this->context->smarty;
        $html = $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/collecting.tpl');;

        return $html;

    }


    public function renderFormShipping()
    {
        $types = ImageType::getImagesTypes('products');
        foreach ($types as $key => $type) {
            $types[$key]['label'] = $type['name'] . ' (' . $type['width'] . ' x ' . $type['height'] . ')';
        }

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $str_shop = '-' . (int)$this->context->shop->id;
        } else {
            $str_shop = '';
        }

        $country = array();
        $state = array();


        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Edit Order'),
                ),
                'description' => $this->l(''),
                'input' => array(
                    array(
                        'name' => 'id_seur_order',
                        'type' => 'hidden',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('# order'),
                        'name' => 'id_order',
                        'class' => 'fixed-width-md',
                        'readonly' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('# packages'),
                        'name' => 'numero_bultos',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Weight packages'),
                        'name' => 'peso_bultos',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => "switch",
                        'label' => $this->l('Labeled'),
                        'name' => 'labeled',
                        'class' => 'fixed-width-md',
                        'is_bool' => true,
                        'disabled' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Printed')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Unprinted')
                            ),
                        ),
                    ),
                    array(
                        'type' => "switch",
                        'label' => $this->l('Manifested'),
                        'name' => 'manifested',
                        'class' => 'fixed-width-md',
                        'is_bool' => true,
                        'disabled' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Printed')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Unprinted')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Codfee'),
                        'name' => 'codfee',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Total paid'),
                        'name' => 'total_paid',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('First name'),
                        'name' => 'firstname',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Last name'),
                        'name' => 'lastname',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Country'),
                        'name' => 'id_country',
                        'class' => 'fixed-width-md',
                        'options' => array(
                            'query' => $country,
                            'id' => 'id',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('State'),
                        'name' => 'id_state',
                        'class' => 'fixed-width-md',
                        'options' => array(
                            'query' => $state,
                            'id' => 'id',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Address 1'),
                        'name' => 'address1',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Address 2'),
                        'name' => 'address2',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Postal Code'),
                        'name' => 'postcode',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('City'),
                        'name' => 'city',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Other'),
                        'name' => 'other',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Phone'),
                        'name' => 'phone',
                        'class' => 'fixed-width-md',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Phone mobile'),
                        'name' => 'phone_mobile',
                        'class' => 'fixed-width-md',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitEditShipping',
                    'class' => 'btn btn-default pull-right'
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminSeurShipping', false);
        $helper->token = Tools::getAdminTokenLite('AdminSeurShipping');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValuesShipping(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValuesShipping()
    {

        $id_seur_order = Tools::getValue('id_seur_order');

        $seur_order = new SeurOrder($id_seur_order);

        $config_fields = array(
            'id_seur_order' => $seur_order->id_seur_order,
            'id_order' => $seur_order->id_order,
            'numero_bultos' => $seur_order->numero_bultos,
            'peso_bultos' => $seur_order->peso_bultos,
            'labeled' => $seur_order->labeled,
            'manifested' => $seur_order->manifested,
            'codfee' => $seur_order->codfee,
            'total_paid' => $seur_order->total_paid,
            'firstname' => $seur_order->firstname,
            'lastname' => $seur_order->lastname,
            'id_country' => $seur_order->id_country,
            'address1' => $seur_order->address1,
            'address2' => $seur_order->address2,
            'postcode' => $seur_order->postcode,
            'city' => $seur_order->city,
            'other' => $seur_order->other,
            'phone' => SeurLib::cleanPhone($seur_order->phone),
            'phone_mobile' => SeurLib::cleanPhone($seur_order->phone_mobile),
        );

        return $config_fields;
    }


    public function addNewOrder($id_order, $id_carrier)
    {
        $seurOrder = SeurOrder::getByOrder($id_order);

        $id_ccc = (int)SeurCCC::getCCCDefault();

        if ($seurOrder == NULL) {
            $order = new Order((int)$id_order);
            $address = new Address((int)$order->id_address_delivery);
            $seurOrder = new SeurOrder();

            $seurOrder->id_order = $id_order;
            $seurOrder->id_seur_ccc = $id_ccc;
            $seurOrder->id_status = 0;
            $seurOrder->numero_bultos = 1;
            $seurOrder->peso_bultos = $order->getTotalWeight();
            $seurOrder->id_address_delivery = $order->id_address_delivery;
            $seurOrder->firstname = $address->firstname;
            $seurOrder->lastname = $address->lastname;
            $seurOrder->id_country = $address->id_country;
            $seurOrder->id_state = $address->id_state;
            $seurOrder->address1 = $address->address1;
            $seurOrder->address2 = $address->address2;
            $seurOrder->postcode = $address->postcode;
            $seurOrder->city = $address->city;
            $seurOrder->other = $address->other;
            $seurOrder->phone = SeurLib::cleanPhone($address->phone);
            $seurOrder->phone_mobile = SeurLib::cleanPhone($address->phone_mobile);

            $seurOrder->codfee = 0;
            $seurOrder->total_paid = $order->total_paid_real;

            $seurOrder->labeled = 0;
            $seurOrder->manifested = 0;

            $seurOrder->save();
        }
    }

    public function saveShipping()
    {
        $id_seur_order = (int)Tools::getValue('id_seur_order');
        $seurOrder = new SeurOrder((int)$id_seur_order);

        $seurOrder->numero_bultos = (int)Tools::getValue('numero_bultos');
        $seurOrder->peso_bultos = Tools::getValue('peso_bultos');
        $seurOrder->firstname = Tools::getValue('firstname');
        $seurOrder->lastname = Tools::getValue('lastname');
        $seurOrder->id_country = Tools::getValue('id_country');
        $seurOrder->id_state = Tools::getValue('id_state');
        $seurOrder->address1 = Tools::getValue('address1');
        $seurOrder->address2 = Tools::getValue('address2');
        $seurOrder->postcode = Tools::getValue('postcode');
        /*        $seurOrder->city = $address->city;
                $seurOrder->other = $address->other;
                $seurOrder->phone = $address->phone;
                $seurOrder->phone_mobile = $address->phone_mobile;
        */
        $seurOrder->codfee = 0;
//        $seurOrder->total_paid = $order->total_paid_real;

        $seurOrder->labeled = 0;
        $seurOrder->manifested = 0;

        $seurOrder->save();
    }


    public function printLabels($id_orders)
    {
        $print_labels = array();

        if(!isset($id_orders) || !is_array($id_orders))
            $id_orders = array();

        foreach ($id_orders as $id_seur_order) {
            $success = $this->createLabel($id_seur_order);

            if($success){
                $print_labels[] = $id_seur_order;
            }
        }

        return $print_labels;
    }

    public function createLabel($id_seur_order)
    {
        $seur_order = new SeurOrder($id_seur_order);

        $id_order = $seur_order->id_order;
		$order = new Order((int)$id_order);

		$date_calculate = strtotime('-14 day', strtotime(date('Y-m-d')));
        $date_display = date('Y-m-d H:m:i', $date_calculate);
		if (strtotime($order->date_add) < strtotime($date_display)) {
			echo 'Más de 14 días';
			return false;
		}

        $versionSpecialClass = '';
        if (!file_exists(_PS_MODULE_DIR_ . 'seur/img/logonew_32.png') && file_exists(_PS_MODULE_DIR_ . 'seur/img/logonew.png'))
            ImageManager::resize(_PS_MODULE_DIR_ . 'seur/img/logonew.png', _PS_MODULE_DIR_ . 'seur/img/logonew_32.png', 32, 32, 'png');

        SeurLib::displayWarningSeur();
        if ($this->module->isConfigured()) {
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

            $order_weigth = 0;
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

            if (in_array((int)$carrier->id_reference, $ids_seur_carriers)) {

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
                if (strcmp($order->module, 'seurcashondelivery') == 0) {
                    $label_data['reembolso'] = (float)$order_data['total_paid'];
                    $label_data['clave_portes'] = "R";
                }
                else{
                    $label_data['clave_portes'] = "F";
	            }

                $carrier_pos = SeurLib::getSeurCarrier('SEP');
                if ((int)$order->id_carrier == $carrier_pos['id']) {
                    $datospos = SeurLib::getOrderPos((int)$order->id_cart);
                    $this->context->smarty->assign(array('carrier_pos' => $carrier_pos));
                    if (!empty($datospos)) {
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
                    }
                }

                $label_file = SeurLabel::createLabels((int)$order->id, $label_data, $merchant_data, false);

                if ($label_file === false) {
                    SeurLib::showMessageError($this, 'Could not set printed value for this order');
                    return false;
                }

                SeurLib::showMessageOK($this, 'Label '.SeurLib::getOrderReference($order).' generated');
                return true;
            }
            else {
                SeurLib::showMessageError($this, 'Label '.SeurLib::getOrderReference($order).' don\'t generated: Carrier not Seur' );
                return false;
            }
        }
        SeurLib::showMessageError($this, 'Módulo Seur no configurado' );
        return false;
    }

    private function printLabel($id_seur_order, $type)
    {
        $seur_order = new Seurorder($id_seur_order);
        $id_order = $seur_order->id_order;
        $name = sprintf('%06d', (int)$id_order);
        $directory = _PS_MODULE_DIR_.'seur/files/deliveries_labels/';

        if ($type == 'txt')
        {
            if (file_exists($directory.$name.'.txt') && ($fp = Tools::file_get_contents($directory.$name.'.txt')))
            {
                ob_end_clean();
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename='.$name.'.txt');
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');

                echo $fp;
                exit;
            }
        }
        elseif ($type == 'pdf')
        {
            if (file_exists($directory.$name.'.pdf') && ($fp = Tools::file_get_contents($directory.$name.'.pdf')))
            {
                ob_end_clean();
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename='.$name.'.pdf');
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');

                echo $fp;
                exit;
            }
        }
        SeurLib::showMessageError($this, 'Document was already printed, but is missing in module directory' );
        return false;
    }
}
