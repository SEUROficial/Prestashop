<?php
/**
 * Created by PhpStorm.
 * Date: 2021-06-14
 */

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_3_0($module) {
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_query` (
        `id_order` int(11) NOT NULL,
        `date_query` datetime,
    PRIMARY KEY (`id_order`,`date_query`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
    Db::getInstance()->execute($sql);

    $sql =  "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD COLUMN id_seur_ccc int(10) NOT NULL;";
    $sql .= "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD COLUMN pudoId tinyint(1) DEFAULT NULL;";
    $sql .= "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD COLUMN frio tinyint(1) DEFAULT 0;";
    $sql .= "ALTER TABLE `"._DB_PREFIX_."seur2_pickup` ADD UNIQUE INDEX `UNIQUE_SEUR2_PICKUP` (`id_seur_ccc` ASC, `date` ASC, `frio` ASC);";
    Db::getInstance()->execute($sql);

    Configuration::updateValue('SEUR2_URLWS_ADD_SHIP',    'https://api.seur.com/geolabel/api/shipment/addShipment');
    Configuration::updateValue('SEUR2_URLWS_SHIP_LABEL',  'https://api.seur.com/geolabel/api/shipment/getLabel');

    Configuration::updateValue('SEUR2_URLWS_TOKEN',        'https://servicios.api.seur.io/pic_token');
    Configuration::updateValue('SEUR2_URLWS_BREXIT_INV',   'https://servicios.api.seur.io/pic/v1/brexit/invoices');
    Configuration::updateValue('SEUR2_URLWS_BREXIT_TARIF', 'https://servicios.api.seur.io/pic/v1/brexit/tariff-item');
    Configuration::updateValue('SEUR2_URLWS_PICKUP',       'https://servicios.api.seur.io/pic/v1/collections');
    Configuration::updateValue('SEUR2_URLWS_PICKUP_CANCEL','https://servicios.api.seur.io/pic/v1/collections/cancel');
    Configuration::updateValue('SEUR2_URLWS_PICKUPS',      'https://servicios.api.seur.io/pic/v1/pickups');

    Configuration::updateValue('SEUR2_API_CLIENT_ID', '');
    Configuration::updateValue('SEUR2_API_CLIENT_SECRET', '');
    Configuration::updateValue('SEUR2_API_USERNAME', '');
    Configuration::updateValue('SEUR2_API_PASSWORD', '');

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

    return $module;
}