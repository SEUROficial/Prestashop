<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_19($module) {

    Configuration::updateValue('SEUR2_URLWS_UPDATE_SHIPMENTS_ADD_PARCELS', 'https://servicios.api.seur.io/pic/v1/shipments/addpack');

    $sql = [];
    $query = "SELECT count(*) as existe FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_name = '"._DB_PREFIX_."seur2_query'
             AND column_name = 'failed_attempts'";
    $existe = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    if (!$existe) {
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_query` ADD COLUMN failed_attempts INT DEFAULT 0;";
    }

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return $module;
}