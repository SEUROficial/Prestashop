<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_22($module) {

    $sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_services` (id_seur_services, id_seur_services_type, name) 
            VALUES (1, 1, 'S24')";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return $module;
}