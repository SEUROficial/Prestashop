<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_6($module) {

    $sql[] = "UPDATE `"._DB_PREFIX_."seur2_status` SET grupo = 'ENTREGADO' WHERE cod_situ = 'LL873'";
    $sql[] = "UPDATE `"._DB_PREFIX_."seur2_status` SET grupo = 'ENTREGADO' WHERE cod_situ = 'LL010'";
    $sql[] = "UPDATE `"._DB_PREFIX_."seur2_status` SET grupo = 'ENTREGADO' WHERE cod_situ = 'LL030'";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return $module;
}