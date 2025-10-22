<?php
if (!defined('_PS_VERSION_')) { exit; }

class SeurGetproductsModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function displayAjax()
    {
        $id_service = (int) Tools::getValue('id_service');

        $q = new DbQuery();
        $q->select('id_seur_product, name')
            ->from('seur2_products')
            ->where('id_seur_services_type = '.$id_service);

        $products = Db::getInstance()->executeS($q);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array(
            'success' => true,
            'products' => $products ?: [],
        ));
        die();
    }
}