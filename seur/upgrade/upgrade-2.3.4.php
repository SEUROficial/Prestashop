<?php
/**
 * Created by PhpStorm.
 * Date: 2021-06-14
 */

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_3_4($module) {
    $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN label_file varchar(255) NULL;";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    SeurLib::updateFieldLabelFile();

    return $module;
}