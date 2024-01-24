<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_3_5($module) {

    $sql = [];
    $query = "SELECT count(*) as existe FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_name = '"._DB_PREFIX_."seur2_order'
             AND column_name = 'brexit'";
    $existe = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    if (!$existe) {
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN brexit tinyint(1) NULL DEFAULT 0;";
    }

    $query = "SELECT count(*) as existe FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_name = '"._DB_PREFIX_."seur2_order'
             AND column_name = 'tariff'";
    $existe = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    if (!$existe) {
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN tariff tinyint(1) NULL DEFAULT 0;";
    }

    $query = "SELECT count(*) as existe FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_name = '"._DB_PREFIX_."seur2_order'
             AND column_name = 'brexit_date'";
    $existe = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    if (!$existe) {
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN brexit_date timestamp NULL;";
    }

    $query = "SELECT count(*) as existe FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_name = '"._DB_PREFIX_."seur2_order'
             AND column_name = 'tariff_date'";
    $existe = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    if (!$existe) {
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN tariff_date timestamp NULL;";
    }

    $query = "SELECT count(*) as existe FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_name = '"._DB_PREFIX_."seur2_order'
             AND column_name = 'label_file'";
    $existe = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    if (!$existe) {
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN label_file varchar(255) NULL;";
    }

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return $module;
}