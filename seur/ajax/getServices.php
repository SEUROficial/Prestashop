<?php
require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

if (version_compare(_PS_VERSION_, '1.5', '<'))
    require_once(_PS_MODULE_DIR_.'seur/backward_compatibility/backward.php');

if (class_exists('SeurLib') == false)
    include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

$context = Context::getContext();

$id_service = Tools::getValue('id_service');

$sql = "SELECT id_seur_services, name FROM `" . _DB_PREFIX_ . "seur2_services` WHERE id_seur_services_type =" . (int)$id_service;
$services = Db::getInstance()->executeS($sql);

echo json_encode(array(
    'success' => true,
    'services' => $services
));
die();
