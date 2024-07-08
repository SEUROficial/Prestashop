<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_10($module) {

    $sql = [];
    $query = "SELECT count(*) as existe FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_name = '"._DB_PREFIX_."seur2_order'
             AND column_name = 'insured'";
    $existe = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    if (!$existe) {
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN insured tinyint(1) NULL DEFAULT 0;";
    }

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return $module;
}