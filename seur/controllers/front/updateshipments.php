<?php
if (!defined('_PS_VERSION_')) { exit; }

class SeurUpdateshipmentsModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    private function respond($payload, $code = 200, $ctype = 'application/json')
    {
        http_response_code($code);
        header('Content-Type: '.$ctype.'; charset=utf-8');
        echo is_string($payload)
            ? $payload
            : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function runUpdate()
    {
        $token = (string) Tools::getValue('secret');
        $valid = (string) Configuration::get('SEUR2_API_CLIENT_SECRET');
        if (!$valid || !hash_equals($valid, $token)) {
            $this->respond(['result'=>0,'revisados'=>0,'error'=>'#Forbidden'], 403);
        }

        // Autoload composer si existe
        $autoload = _PS_MODULE_DIR_.'seur/vendor/autoload.php';
        if (file_exists($autoload)) { require_once $autoload; }

        try {
            $cmd = new \Seur\Prestashop\Commands\UpdateShipmentsStatus($this->context);
            $out = (array) $cmd->handle(); // ['result','revisados','error']
            $this->respond($out);
        } catch (\Throwable $e) {
            $this->respond(['result'=>0,'revisados'=>0,'error'=>'#'.$e->getMessage()], 500);
        }
    }

    // Se ejecuta cuando llamas con ?ajax=1
    public function displayAjax()
    {
        $this->runUpdate();
    }

    // Se ejecuta cuando NO hay ?ajax=1 → evitamos Smarty y devolvemos JSON igual
    public function initContent()
    {
        $this->runUpdate();
    }
}
