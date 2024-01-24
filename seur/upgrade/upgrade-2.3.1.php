<?php
/**
 * Created by PhpStorm.
 * Date: 2021-06-14
 */

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_3_1($module) {
    $sql =  "ALTER TABLE `"._DB_PREFIX_."seur2_ccc` ADD COLUMN id_shop int(11) NULL DEFAULT 1;";
    Db::getInstance()->execute($sql);
    return $module;
}