<?php
require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

if (version_compare(_PS_VERSION_, '1.5', '<'))
    require_once(_PS_MODULE_DIR_.'seur/backward_compatibility/backward.php');

if (class_exists('SeurOrder') == false)
    include_once(_PS_MODULE_DIR_.'seur/classes/SeurOrder.php');

$context = Context::getContext();
$id_seur_order = (int)Tools::getValue('id_seur_order', 0);
$insured = (int)Tools::getValue('insured',0);
$seur_order = new SeurOrder($id_seur_order);
$seur_order->insured = $insured;
$seur_order->save();
echo json_encode(array(
    'success' => true
));
die();
