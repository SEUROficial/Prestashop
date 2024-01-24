<?php
require_once(_PS_MODULE_DIR_.'seur/classes/ProductType.php');

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_4($module) {
    $pt = new ProductType;
    $pt->install();
    return $module;
}