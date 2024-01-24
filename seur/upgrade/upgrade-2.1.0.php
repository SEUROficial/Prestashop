<?php

/**

 * Created by PhpStorm.

 * User: desar06

 * Date: 11/10/2018

 * Time: 11:38

 */



if (!defined('_PS_VERSION_'))

    exit;



const TYPE_NATIONAL = 1;

const TYPE_PICKUP = 2;

const TYPE_INTERNATIONAL = 3;



function upgrade_module_2_1_0($module)

{



    $sql = "ALTER TABLE `"._DB_PREFIX_."seur2_ccc` ADD COLUMN geolabel tinyint(1) DEFAULT 0;";

    Db::getInstance()->execute($sql);



    $sql = "ALTER TABLE `"._DB_PREFIX_."seur2_ccc` ADD COLUMN nombre_personalizado varchar(255) DEFAULT 0;";

    Db::getInstance()->execute($sql);



    if(!Configuration::get('SEUR2_URLWS_ET_GEOLABEL'))

        Configuration::updateValue('SEUR2_URLWS_ET_GEOLABEL', 'https://api.seur.com/geolabel/swagger-ui.html#/add-shipment-controller');



    return $module;

}