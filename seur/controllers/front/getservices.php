<?php
if (!defined('_PS_VERSION_')) { exit; }

class SeurGetservicesModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function displayAjax()
    {
        $id_service = (int) Tools::getValue('id_service');

        $q = new DbQuery();
        $q->select('id_seur_services, name')
            ->from('seur2_services')
            ->where('id_seur_services_type = '.$id_service);

        $services = Db::getInstance()->executeS($q);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success'  => true,
            'services' => $services ?: [],
        ]);
        exit;
    }
}