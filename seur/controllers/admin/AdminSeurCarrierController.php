<?php
/*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*/


require_once(_PS_MODULE_DIR_.DIRECTORY_SEPARATOR.'seur'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'SeurCarrier.php');

class AdminSeurCarrierController extends ModuleAdminController
{

    const TYPE_NATIONAL = 1;
    const TYPE_PICKUP = 2;
    const TYPE_INTERNATIONAL = 3;

    public function __construct()
    {

        $module = Module::getInstanceByName('seur');;

        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->addJQuery();
            $this->addJS($module->getPath() . 'views/js/back.js');
        }

        $this->bootstrap = true;
        $this->table = 'seur2_carrier';
        $this->className = 'SeurCarrier';
        $this->lang = false;
        $this->module = $module;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();


        $sql_carrier = "SELECT c.`name` as name
                            FROM `" . _DB_PREFIX_ . "carrier` AS c
                            WHERE active=1 AND deleted=0
                            ORDER BY c.`name`";

        $select_carriers = Db::getInstance()->ExecuteS($sql_carrier);

        $list_carrier = array();
        foreach ($select_carriers as $carrier) {
            $list_carrier[$carrier['name']] = $carrier['name'];
        }


        $sql_ccc = "SELECT DISTINCT sc.`ccc` as name
                            FROM `" . _DB_PREFIX_ . "seur2_ccc` AS sc
                            ORDER BY sc.`ccc`";

        $select_ccc = Db::getInstance()->ExecuteS($sql_ccc);

        $list_cccs = array();
        foreach ($select_ccc as $ccc) {
            $list_cccs[$ccc['name']] = $ccc['name'];
        }

//        $this->_select = '
//		a.id_currency,
//		a.id_order AS id_pdf,
//		login AS `login`,
//		osl.`name` AS `osname`,
//		CONCAT(login, " (",address.`firstname`," ",address.`lastname`,")") AS `customer`,
//		os.`color`,
//		IF((SELECT so.id_order FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer AND so.id_order < a.id_order LIMIT 1) > 0, 0, 1) as new,
//		country_lang.name as cname,
//		IF(a.valid, 1, 0) badge_success,
//		a.etiquetado,
//		a.servido,
//        ca.name as carrier_name,
//        a.observaciones_etiqueta as msg';

        $this->_select = 'id_seur_carrier as id_seur2_carrier, ca.id_carrier, ca.name, st.name';

        $this->_defaultOrderBy = 'id_seur_carrier';


        $this->_join = '
		LEFT JOIN `' . _DB_PREFIX_ . 'carrier` ca ON ca.`id_reference` = a.`carrier_reference` AND ca.deleted=0
		LEFT JOIN `' . _DB_PREFIX_ . 'seur2_services_type` st ON st.`id_seur_services_type` = a.`shipping_type` AND ca.deleted=0
		LEFT JOIN `' . _DB_PREFIX_ . 'seur2_services` ft ON ft.`id_seur_services` = a.`service` AND ca.deleted=0
		LEFT JOIN `' . _DB_PREFIX_ . 'seur2_products` pt ON pt.`id_seur_product` = a.`product` AND ca.deleted=0
		';

        $this->_group = ' GROUP BY id_carrier';


        $this->_use_found_rows = true;

        $this->fieldImageSettings = array(
            'name' => 'logo',
            'dir' => 's'
        );

        $this->fields_list = array(
            'image' => array(
                'title' => $this->module->l('Logo'),
                'align' => 'center',
                'image' => 's',
                'image_id' => 'id_carrier',
                'orderby' => false,
                'search' => false
            ),
            'name_carrier' => array(
                'title' => $this->module->l('Name'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'ca!name'
            ),
            'shipping_type' => array(
                'title' => $this->module->l('Shipping type'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'st!name'
            ),
            'service' => array(
                'title' => $this->module->l('Service'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'ft!name'
            ),
            'product' => array(
                'title' => $this->module->l('Product'),
                'align' => 'left',
                'orderby' => true,
                'search' => true,
                'filter_key' => 'pt!name'
            ),
            'free_shipping' => array(
                'title' => $this->module->l('Free shipping'),
                'align' => 'left',
                'active' => 'free',
                'orderby' => true,
                'search' => true,
            ),
            'active' => array(
                'title' => $this->module->l('Active'),
                'align' => 'left',
                'active' => 'active',
                'orderby' => true,
                'search' => true,
            ),
        );


        AdminController::__construct();

        $this->postProccess();

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
                'url_controller' => $this->context->link->getAdminLink('AdminSeurShipping', true),
                'img_path' => $this->module->getPath() . 'views/img/',
                'module_path' => 'index.php?controller=AdminModules&configure=' . $this->module->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Tab::getIdFromClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
                'seur_url_basepath' => seurLib::getBaseLink(),
            ));

        $smarty = $this->context->smarty;
        $html = "";
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/header.tpl');
        $html .= parent::renderList();
        return $html;
    }

    public function renderForm()
    {

        $this->context->smarty->assign(
            array(
                'url_module' => $this->context->link->getAdminLink('AdminModules', true) . "&configure=seur&module_name=seur",
                'url_list' => $this->context->link->getAdminLink('AdminSeurCarrier', true),
                'url_carrier' => $this->context->link->getAdminLink('AdminCarriers', true),
                'img_path' => $this->module->path . 'views/img/',
                'module_path' => 'index.php?controller=AdminModules&configure=' . $this->module->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Tab::getIdFromClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
                'seur_url_basepath' => seurLib::getBaseLink(),
            ));


        $carrier = new SeurCarrier((int)Tools::getValue('id_seur2_carrier'));

        $id_seur_carrier = $carrier->id_seur_carrier;
        $carrier_reference = $carrier->carrier_reference;
        $id_seur_ccc = $carrier->id_seur_ccc;
        $shipping_type = $carrier->shipping_type != 0 ? $carrier->shipping_type : 1;
        $service = $carrier->service;
        $product = $carrier->product;
        $free_shipping = $carrier->free_shipping;
        $free_shipping_weight = $carrier->free_shipping_weight;
        $free_shipping_price = $carrier->free_shipping_price;
        $active = 1;


        $sql = "SELECT id_reference, name FROM `" . _DB_PREFIX_ . "carrier` 
                WHERE deleted=0 
                AND id_reference NOT IN (
                    SELECT carrier_reference FROM `" . _DB_PREFIX_ . "seur2_carrier` WHERE id_seur_carrier!=" . (int)$id_seur_carrier . ")";
        $carriers = Db::getInstance()->executeS($sql);

        $sql = "SELECT id_seur_services, name FROM `" . _DB_PREFIX_ . "seur2_services` WHERE id_seur_services_type =" . (int)$shipping_type;
        $serviceTypes = Db::getInstance()->getRow($sql);

        $services = array();
        $products = array();


        $this->context->smarty->assign(array(
            'id_seur_carrier' => $id_seur_carrier,
            'id_seur_ccc' => $id_seur_ccc,
            'carrier_reference' => $carrier_reference,
            'carriers' => $carriers,
            'shipping_type' => $shipping_type,
            'services' => $services,
            'service' => $service,
            'products' => $products,
            'product' => $product,
            'free_shipping' => $free_shipping,
            'free_shipping_weight' => $free_shipping_weight,
            'free_shipping_price' => $free_shipping_price,
            'active' => $active,
        ));


        /*
        $sql ="
            UPDATE `"._DB_PREFIX_."lgpampling_product` pp
            LEFT JOIN (SELECT o.id_product, @curRow := @curRow + 1 AS orden_final
                        FROM `ps_lgpampling_product` o
                    JOIN (SELECT @curRow := 0) r
                    LEFT JOIN `"._DB_PREFIX_."product` p ON p.id_product = o.id_product
                    WHERE p.id_product = o.id_product AND active=1
                    ORDER BY orden) o ON pp.id_product = o.id_product
            set
                pp.orden = o.orden_final";

        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);


        $sql = "SELECT p.id_product, id_tipo, l.name, l.link_rewrite, pp.orden, i.id_image FROM `"._DB_PREFIX_."product` p
                LEFT JOIN `"._DB_PREFIX_."product_lang` l ON (p.`id_product` = l.`id_product` AND l.id_lang=".(int)$this->context->language->id.")
                LEFT JOIN `"._DB_PREFIX_."product_shop` s ON (p.`id_product` = s.`id_product`)
                LEFT JOIN `"._DB_PREFIX_."lgpampling_product` pp ON (p.`id_product` = pp.`id_product`)
                LEFT JOIN `"._DB_PREFIX_."image` i ON (p.`id_product` = i.`id_product` AND i.cover=1)
                WHERE s.active=1 AND s.visibility != 'none'
                ORDER BY pp.orden DESC";

        $products =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $this->context->smarty->assign('products', $products);
*/
//        parent::initContent();

        return $this->context->smarty->fetch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'carrier.tpl');

    }

    public function postProccess()
    {
        if (((bool)Tools::isSubmit('submitCarrierSeur')) == true) {

            $id_seur_carrier = (int)Tools::getValue('id_seur_carrier');
            $seurCarrier = new SeurCarrier($id_seur_carrier);
            $seurCarrier->id_seur_ccc = (int)Tools::getValue('id_seur_ccc');
            $seurCarrier->carrier_reference = (int)Tools::getValue('carrier');
            $seurCarrier->shipping_type = (int)Tools::getValue('type_service');
            $seurCarrier->service = (int)Tools::getValue('service');
            $seurCarrier->product = (int)Tools::getValue('product');
            $seurCarrier->free_shipping = (int)Tools::getValue('freeShipping');
            $seurCarrier->free_shipping_weight = (float)Tools::getValue('free_shipping_weight');
            $seurCarrier->free_shipping_price = (float)Tools::getValue('free_shipping_price');

            $seurCarrier->save();

            $carrier_ref = (int)Tools::getValue('carrier');
            $carrier = Carrier::getCarrierByReference($carrier_ref);
            $carrier->external_module_name = $this->module->name;
            $carrier->shipping_external = 1;
            $carrier->is_module = 1;
            $carrier->need_range = 1;

            $carrier->save();

            switch ($seurCarrier->shipping_type) {

                case self::TYPE_PICKUP:
                    $img = '/views/img/icono_pickup.jpg';
                    break;
                case self::TYPE_INTERNATIONAL:
                    $img = '/views/img/icono_internacional.jpg';
                    break;
                default:
                    $img = '/views/img/icono_nacional.jpg';
                    break;
            }

            @copy(dirname(dirname(__DIR__)) . $img, _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
            @copy(dirname(dirname(__DIR__)) . $img, _PS_TMP_IMG_DIR_ . '/carrier_mini_' . (int)$carrier->id . '_1.jpg');
            @copy(dirname(dirname(__DIR__)) . $img, _PS_TMP_IMG_DIR_ . '/seur2_carrier_mini_' . (int)$carrier->id . '_1.jpg');

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSeurCarrier'));
        }
    }

}
