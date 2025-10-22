<?php
/**
 * 2007-2014 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
    exit;

const TYPE_NATIONAL = 1;
const TYPE_PICKUP = 2;
const TYPE_INTERNATIONAL = 3;

function upgrade_module_2_0_0($module)
{

    // SE SIGUEN LOS PASOS DE INSTALACION


    if (!$module->registerHook('adminOrder')
        || !$module->registerHook('orderDetail')
        || !$module->registerHook('extraCarrier')
        || !$module->registerHook('updateCarrier')
        || !$module->registerHook('displayOrderConfirmation')
        || !$module->registerHook('header')
        || !$module->registerHook('backOfficeHeader')
        || !$module->registerHook('actionValidateOrder')
    ){

    }

    if (version_compare(_PS_VERSION_, '1.5.4', '<')) {
        if (!$module->registerHook('orderDetailDisplayed')){
        }
    } else {
        if (!$module->registerHook('displayOrderDetail')) {
        }
    }

    if (!$module->createAdminTab()) {
    }

    $module->createOrderStates();

    $dir_destino = _PS_MODULE_DIR_ . 'seurcashondelivery';
    $module->recursiveDeleteOnDisk($dir_destino);

    $dir_origen = _PS_MODULE_DIR_ . $module->name . '/install/1.5/seurcashondelivery';
    $module->copyDirectory($dir_origen, $dir_destino);


    if (!$module->createDatabases()) {
    }

    // SE CAPTURAN LOS DATOS DE LA VERSIÓN ANTERIOR PARA TRASLADARLOS A LA CONFIGURACION DE LA NUEVA

    $sql = "SELECT * FROM `"._DB_PREFIX_."seur_configuration`";
    $configuration = Db::getInstance()->getRow($sql);

    $seurInternacional = $configuration['international_orders'];
    $seurPos = $configuration['pos'];
    $seurPrintType = $configuration['print_type'];
    $seurTarifa = $configuration['tarifa'];
    $seurPuntosVenta = $configuration['pickup']+1;


    $sql = "SELECT * FROM `"._DB_PREFIX_."seur_merchant`";
    $merchant = Db::getInstance()->getRow($sql);

    $merchantUser = $merchant['user'];
    $merchantPass = $merchant['pass'];
    $merchantCit = $merchant['cit'];
    $merchantCcc = $merchant['ccc'];
    $merchantNifDni = $merchant['nif_dni'];
    $merchantFistName = $merchant['name'];
    $merchantLastName = $merchant['first_name'];
    $merchantFranchise = $merchant['franchise'];
    $merchantCompanyName = $merchant['company_name'];
    $merchantStreetType = $merchant['street_type'];
    $merchantStreetName = $merchant['street_name'];
    $merchantStreetNumber = $merchant['street_number'];
    $merchantStaircase = $merchant['staircase'];
    $merchantFloor = $merchant['floor'];
    $merchantDoor = $merchant['door'];
    $merchantPostCode = $merchant['post_code'];
    $merchantTown = $merchant['town'];
    $merchantState = $merchant['state'];
    $merchantCountry = $merchant['country'];
    $merchantPhone = $merchant['phone'];
    $merchantFax = $merchant['fax'];
    $merchantEmail = $merchant['email'];


    $seurCod = $configuration['seur_cod'];
    $seurCodPercent = Configuration::get('SEUR_REMCAR_CARGO');
    $seurCodMin = Configuration::get('SEUR_REMCAR_CARGO_MIN');
    $seurCodType = Configuration::get('SEUR_REMCAR_TIPO_CARGO');

    $seurNacionalService = Configuration::get('SEUR_NACIONAL_SERVICE');
    $seurNacionalProduct = Configuration::get('SEUR_NACIONAL_PRODUCT');
    $seurInternacionalService = Configuration::get('SEUR_INTERNACIONAL_SERVICE');
    $seurInternacionalProduct = Configuration::get('SEUR_INTERNACIONAL_PRODUCT');
    $seurWsUsername = Configuration::get('SEUR_WS_USERNAME');
    $seurWsPassword = Configuration::get('SEUR_WS_PASSWORD');
    $gratisPeso = (float)Configuration::get('SEUR_FREE_WEIGTH');
    $gratisImporte = (float)Configuration::get('SEUR_FREE_PRICE');


    // INCORPORAMOS LOS DATOS DE CONFIGURACION

    Configuration::updateValue("SEUR2_MERCHANT_NIF_DNI", $merchantNifDni);
    Configuration::updateValue("SEUR2_MERCHANT_FIRSTNAME", $merchantFistName);
    Configuration::updateValue("SEUR2_MERCHANT_LASTNAME", $merchantLastName);
    Configuration::updateValue("SEUR2_MERCHANT_COMPANY", $merchantCompanyName);
    Configuration::updateValue("SEUR2_MERCHANT_CLICKCOLLECT", 0);


    Configuration::updateValue("SEUR2_SETTINGS_COD", $seurCod);
    Configuration::updateValue("SEUR2_SETTINGS_COD_FEE_PERCENT", $seurCodPercent);
    Configuration::updateValue("SEUR2_SETTINGS_COD_FEE_MIN", $seurCodMin);
    Configuration::updateValue("SEUR2_SETTINGS_COD_MIN", 0);
    Configuration::updateValue("SEUR2_SETTINGS_COD_MAX", 0);
    Configuration::updateValue("SEUR2_SETTINGS_PRINT_TYPE", $seurPrintType);
    Configuration::updateValue("SEUR2_SETTINGS_PICKUP", $seurPuntosVenta);
    Configuration::updateValue("SEUR2_GOOGLE_API_KEY", "");
    Configuration::updateValue("SEUR2_CAPTURE_ORDER", 1);



    // INCORPORAMOS LOS DATOS DEL MERCHANT

    $sql = "REPLACE INTO `"._DB_PREFIX_."seur2_ccc` (id_seur_ccc,cit,ccc,franchise,street_type,street_name,street_number,staircase,floor,door,post_code,town,state,country,phone,email,e_devoluciones,url_devoluciones,is_default) VALUES (
    1,'".$merchantCit."','".$merchantCcc."','".$merchantFranchise."','".$merchantStreetType."','".$merchantStreetName."','".$merchantStreetNumber."','".$merchantStaircase."','".$merchantFloor."','".$merchantDoor."','".$merchantPostCode."','".$merchantTown."','".$merchantState."','".$merchantCountry."','".$merchantPhone."','".$merchantEmail."',0,'',1)";
    Db::getInstance()->execute($sql);


    // SE TRANSFORMAN LOS TRANSPORTISTAS EXISTENTES

    $sql = "SELECT * FROM `"._DB_PREFIX_."seur_history` WHERE `active` = 1";
    $carriers = Db::getInstance()->executeS($sql);

    foreach($carriers as $carrierOld)
    {
        switch ($carrierOld['type']){
            case 'SEP': // Punto de venta
                $idCarrierPuntoVenta = $carrierOld['id_seur_carrier'];
                $carrier = new Carrier($idCarrierPuntoVenta);
                $seurCarrier = new SeurCarrier();
                $seurCarrier->carrier_reference = $carrier->id_reference;
                $seurCarrier->service = '1';
                $seurCarrier->product = '48';
                $seurCarrier->shipping_type = 2;
                break;
            case 'SCN': // Canarias
                $idCarrierCanarias = $carrierOld['id_seur_carrier'];
                $carrier = new Carrier($idCarrierCanarias);
                $seurCarrier = new SeurCarrier();
                $seurCarrier->carrier_reference = $carrier->id_reference;
                $seurCarrier->service = 13;
                $seurCarrier->product = 2;
                $seurCarrier->shipping_type = 1;
                break;
            case 'SCE': // Canarias Express 48-72
                $idCarrierCanariasExpress = $carrierOld['id_seur_carrier'];
                $carrier = new Carrier($idCarrierCanariasExpress);
                $carrier->name = "Canarias Aéreo";
                $seurCarrier = new SeurCarrier();
                $seurCarrier->carrier_reference = $carrier->id_reference;
                $seurCarrier->service = 31;
                $seurCarrier->product = 2;
                $seurCarrier->shipping_type = 1;
                break;
            default: // Seur nacional
                $idCarrierNacional = $carrierOld['id_seur_carrier'];
                $carrier = new Carrier($idCarrierNacional);
                $seurCarrier = new SeurCarrier();
                $seurCarrier->carrier_reference = $carrier->id_reference;
                $seurCarrier->service = $seurNacionalService;
                $seurCarrier->product = $seurNacionalProduct;
                $seurCarrier->shipping_type = 1;
                break;
        }

        $seurCarrier->id_seur_ccc = 1;

        if($gratisPeso || $gratisImporte){
            $gratis = 1;
        }
        else{
            $gratis = 0;
        }

        $seurCarrier->free_shipping = $gratis;
        $seurCarrier->free_shipping_weight = $gratisPeso;
        $seurCarrier->free_shipping_price = $gratisImporte;

        $seurCarrier->save();

        $carrier->external_module_name = $module->name;
        $carrier->shipping_external = 1;
        $carrier->is_module = 1;
        $carrier->need_range = 1;

        $carrier->save();

        switch ($seurCarrier->shipping_type) {

            case TYPE_PICKUP:
                $img = '/views/img/icono_pickup.jpg';
                break;
            case TYPE_INTERNATIONAL:
                $img = '/views/img/icono_internacional.jpg';
                break;
            default:
                $img = '/views/img/icono_nacional.jpg';
                break;
        }

        @copy(dirname(dirname(__DIR__)) . $img, _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
        @copy(dirname(dirname(__DIR__)) . $img, _PS_TMP_IMG_DIR_ . '/carrier_mini_' . (int)$carrier->id . '_1.jpg');
        @copy(dirname(dirname(__DIR__)) . $img, _PS_TMP_IMG_DIR_ . '/seur2_carrier_mini_' . (int)$carrier->id . '_1.jpg');
    }


    // SE EXTRAEN LOS DATOS DE PEDIDOS DE SEUR PARA INCORPORARLOS AL NUEVO SISTEMA


    $sql = "REPLACE INTO `"._DB_PREFIX_."seur2_order` (id_seur_order,id_seur_ccc,id_order,id_address_delivery,id_status,status_text,id_seur_carrier,service,product,numero_bultos,peso_bultos,ecb,labeled,manifested,date_labeled,codfee,cashondelivery,total_paid,firstname,lastname,id_country,id_state,address1,address2,postcode,city,dni,other,phone,phone_mobile) 
        (
            SELECT o.id_order,1,o.id_order,o.id_address_delivery,0,'',sc.id_seur_carrier,sc.service,sc.product, os.numero_bultos, os.peso_bultos,'',os.printed_label OR os.printed_pdf,o.date_add < CURDATE(),o.date_add,os.codfee,0,os.total_paid,a.firstname,a.lastname,a.id_country,a.id_state,a.address1,a.address2,a.postcode, a.city, a.dni, a.other, a.phone, a.phone_mobile  
            FROM `"._DB_PREFIX_."seur_order` os 
            LEFT JOIN `"._DB_PREFIX_."orders` o ON os.id_order = o.id_order
            LEFT JOIN `"._DB_PREFIX_."carrier` c ON o.id_carrier = c.id_carrier
            LEFT JOIN `"._DB_PREFIX_."seur2_carrier` sc ON sc.carrier_reference = c.id_reference
            LEFT JOIN `"._DB_PREFIX_."address` a ON a.id_address = o.id_address_delivery
        )";
    Db::getInstance()->execute($sql);

    $sql = "REPLACE INTO `"._DB_PREFIX_."seur2_order` (id_seur_order,id_seur_ccc,id_order,id_address_delivery,id_status,status_text,id_seur_carrier,service,product,numero_bultos,peso_bultos,ecb,labeled,manifested,date_labeled,codfee,cashondelivery,total_paid,firstname,lastname,id_country,id_state,address1,address2,postcode,city,dni,other,phone,phone_mobile) 
        (
            SELECT o.id_order,1,o.id_order,o.id_address_delivery,0,'',sc.id_seur_carrier,sc.service,sc.product, os.numero_bultos, os.peso_bultos,'',os.printed_label OR os.printed_pdf,o.date_add < CURDATE(),o.date_add,os.codfee,0,os.total_paid,a.firstname,a.lastname,a.id_country,a.id_state,op.address,'',op.postal_code, op.city, a.dni, a.other, a.phone, a.phone_mobile  
            FROM `"._DB_PREFIX_."seur_order` os 
            INNER JOIN `"._DB_PREFIX_."orders` o ON os.id_order = o.id_order
            INNER JOIN `"._DB_PREFIX_."seur_order_pos` op ON o.id_cart = op.id_cart
            LEFT JOIN `"._DB_PREFIX_."carrier` c ON o.id_carrier = c.id_carrier
            LEFT JOIN `"._DB_PREFIX_."seur2_carrier` sc ON sc.carrier_reference = c.id_reference
            LEFT JOIN `"._DB_PREFIX_."address` a ON a.id_address = o.id_address_delivery
        )";
    Db::getInstance()->execute($sql);

    $sql = "REPLACE INTO `"._DB_PREFIX_."seur2_order_pos` SELECT * FROM `"._DB_PREFIX_."seur_order_pos`";
    Db::getInstance()->execute($sql);

    $sql = "REPLACE INTO `"._DB_PREFIX_."seur2_pickup` SELECT 1, localizer,num_pickup,tasacion,date FROM `"._DB_PREFIX_."seur_pickup` ORDER BY id_seur_pickup DESC LIMIT 0,1";
    Db::getInstance()->execute($sql);

    Db::getInstance()->execute($sql);

    /*
        // SE ELIMINAN DATOS DE CONFIGURACIÓN OBSOLETOS Y TABLAS OBSOLETAS

        $sql = "DROP TABLE `"._DB_PREFIX_."seur_configuration`";
        Db::getInstance()->execute($sql);

        $sql = "DROP TABLE `"._DB_PREFIX_."seur_history`";
        Db::getInstance()->execute($sql);

        $sql = "DROP TABLE `"._DB_PREFIX_."seur_merchant`";
        Db::getInstance()->execute($sql);

        $sql = "DROP TABLE `"._DB_PREFIX_."seur_order`";
        Db::getInstance()->execute($sql);

        $sql = "DROP TABLE `"._DB_PREFIX_."seur_order_pos`";
        Db::getInstance()->execute($sql);

        $sql = "DROP TABLE `"._DB_PREFIX_."seur_pickup`";
        Db::getInstance()->execute($sql);



        Configuration::deleteByName('CONF_SEURCASHONDELIVERY_FIXED');
        Configuration::deleteByName('CONF_SEURCASHONDELIVERY_VAR');
        Configuration::deleteByName('CONF_SEURCASHONDELIVERY_FIXED_FOREIGN');
        Configuration::deleteByName('CONF_SEURCASHONDELIVERY_VAR_FOREIGN');
        Configuration::deleteByName('SEUR_CONFIGURATION_OK');
        Configuration::deleteByName('SEUR_Configured');
        Configuration::deleteByName('SEUR_URLWS_SP');
        Configuration::deleteByName('SEUR_URLWS_R');
        Configuration::deleteByName('SEUR_URLWS_E');
        Configuration::deleteByName('SEUR_URLWS_A');
        Configuration::deleteByName('SEUR_URLWS_ET');
        Configuration::deleteByName('SEUR_URLWS_M');
        Configuration::deleteByName('SEUR_PRINTER_NAME');
        Configuration::deleteByName('SEUR_REMCAR_CARGO');
        Configuration::deleteByName('SEUR_REMCAR_CARGO_MIN');
        Configuration::deleteByName('SEUR_NACIONAL_SERVICE');
        Configuration::deleteByName('SEUR_NACIONAL_PRODUCT');
        Configuration::deleteByName('SEUR_INTERNACIONAL_SERVICE');
        Configuration::deleteByName('SEUR_INTERNACIONAL_PRODUCT');
        Configuration::deleteByName('SEUR_WS_USERNAME');
        Configuration::deleteByName('SEUR_WS_PASSWORD');
        Configuration::deleteByName('SEUR_REMCAR_TIPO_CARGO');
        Configuration::deleteByName('SEUR_FREE_WEIGTH');
        Configuration::deleteByName('SEUR_FREE_PRICE');

    */

    return $module;
}

?>