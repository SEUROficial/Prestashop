<?php
/*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*/

require_once(_PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SeurOrder.php');

class AdminSeurTrackingController extends ModuleAdminController
{

    public function __construct()
    {
        $module = Module::getInstanceByName('seur');

        $this->bootstrap = true;
        $this->name = 'AdminSeurTracking';
        $this->table = 'seur2_order';
        $this->identifier = "id_seur_order";
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

        $this->fields_list = array(
            'id_seur_order' => array(
                'title' => $this->module->l('ID seur order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'class' => 'hidden',
            ),
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
                'filter_key' => 'o!reference'
            ),
            'order_date' => array(
                'title' => $this->module->l('Date Order'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'o!date_add'
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
            ),
            'id_situacion' => array(
                'title' => $this->module->l('Shipping Status'),
                'class' => 'hidden',
            ),
            'status_text' => array(
                'title' => $this->module->l('Shipping Status'),
                'align' => 'left',
                'orderby' => true,
                'search' => false,
                'badge_success' => true,
                'badge_warning' => true,
                'badge_danger' => true
            ),
        );

        $this->_join .= ' 			
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON a.id_order = o.id_order
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` c ON c.id_country = a.id_country AND c.id_lang=' . (int)Context::getContext()->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'state` st ON st.id_state = a.id_state
            LEFT JOIN `' . _DB_PREFIX_ . 'seur2_ccc` sc ON sc.id_seur_ccc = a.id_seur_ccc';

        $this->_select .= 'a.firstname,a.lastname, o.date_add, o.reference, c.name as country, a.status_text , st.name as state, a.id_status as id_situacion';

        $this->_where = "AND a.id_status != 0";


        $this->context->smarty->assign('controlador', 'AdminSeurTracking');

        AdminController::__construct();


    }

    public function renderList(){

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
                'url_controller_bulk_assign_carrier' => $this->context->link->getAdminLink('AdminSeurBulkAssignCarrier', true),
                'url_controller_pickup_locations' => $this->context->link->getAdminLink('AdminSeurPickupLocations', true),
                'img_path' => $this->module->getPath() . 'views/img/',
                'module_path' => 'index.php?controller=AdminModules&configure=' . $this->module->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Seur::findTabIdByClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
                'seur_url_basepath' => seurLib::getBaseLink(),
                'bulk_assign_enabled' => Configuration::get('SEUR2_BULK_ASSIGN_CARRIER'),
                'tabSelect' => 'tracking',
            ));


        $smarty = $this->context->smarty;
        $html = "";

        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/header.tpl');;
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/tabs.tpl');;
        $html .= parent::renderList();

        return $html;
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $id_lang_shop);

        if ($this->_list) {
            foreach ($this->_list as &$row) {

                $id_status =  $row['id_situacion'];

                $grupo = SeurOrder::getGroupStatusExpedition($id_status);

                switch ($grupo) {
                    case "ENTREGADO":
                        $row['badge_success'] = 1;
                        break;
                    case "APORTAR SOLUCIÓN":
                    case "INCIDENCIA":
                        $row['badge_danger'] = 1;
                        break;
                    case "EN TRÁNSITO":
                    case "DISPONIBLE PARA RECOGER EN TIENDA":
                        $row['badge_warning'] = 1;
                        break;
                }
            }
        }
    }

    public function renderView(){
        $seurOrder = new SeurOrder(Tools::getValue('id_seur_order'));
        $url = Context::getContext()->link->getAdminLink(
            'AdminOrders',
            Tools::getAdminTokenLite('AdminOrders'),
            ['action' => 'vieworder', 'orderId' => $seurOrder->id_order]
        );
        Tools::redirectAdmin($url .'&vieworder&id_order='.$seurOrder->id_order);
    }
}
