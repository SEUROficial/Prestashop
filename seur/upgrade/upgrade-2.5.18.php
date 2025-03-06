<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_18($module) {

    Configuration::updateValue('SEUR2_URLWS_SHIPMENT_UPDATE','https://servicios.api.seur.io/pic/v1/shipments/update');

    return $module;
}