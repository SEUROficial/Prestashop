<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_8($module) {
    Configuration::updateValue('SEUR2_AUTO_CREATE_LABELS', 0);
    Configuration::updateValue('SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE', '');
    Configuration::updateValue('SEUR2_AUTO_CALCULATE_PACKAGES', 0);
    return $module;
}