<?php
if (!defined('_PS_VERSION_')) { exit; }

class SeurSaveinsuredModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function displayAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $id_seur_order = (int)Tools::getValue('id_seur_order', 0);
        $insured       = (int)Tools::getValue('insured', 0);

        if ($id_seur_order <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing or invalid id_seur_order']);
            exit;
        }
        if ($insured !== 0 && $insured !== 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid insured']);
            exit;
        }

        // Cargar clase si no está autoloaded
        if (!class_exists('SeurOrder')) {
            $file = _PS_MODULE_DIR_.'seur/classes/SeurOrder.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
        if (!class_exists('SeurOrder')) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'SeurOrder class not found']);
            exit;
        }

        $seur_order = new SeurOrder($id_seur_order);
        if (!Validate::isLoadedObject($seur_order)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SeurOrder not found']);
            exit;
        }

        $seur_order->insured = $insured;
        $ok = (bool)$seur_order->save();

        echo json_encode(['success' => $ok], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}