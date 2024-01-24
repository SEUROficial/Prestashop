<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_4_0($module) {
    Configuration::updateValue('SEUR2_PRODS_REFS_IN_COMMENTS', 0);
    return $module;
}