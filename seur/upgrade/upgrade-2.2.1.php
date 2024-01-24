<?php
/**
 * Created by PhpStorm.
 * Date: 2021-06-14
 */

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_2_1($module) {
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_query` (
        `id_order` int(11) NOT NULL,
        `date_query` datetime,
    PRIMARY KEY (`id_order`,`date_query`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    Db::getInstance()->execute($sql);
    return $module;
}