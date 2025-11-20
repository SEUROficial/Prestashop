<?php
require_once(_PS_MODULE_DIR_.'seur/scripts/ScriptHandler.php');
require_once(_PS_MODULE_DIR_.'seur/classes/factories/UpdateShipmentsStatusHandlerFactory.php');
require_once(_PS_MODULE_DIR_.'seur/classes/middleware/TokenAuthorizationMiddleware.php');

use Seur\Prestashop\Commands\UpdateShipmentsStatus;
use Seur\Prestashop\Factories\UpdateShipmentsStatusHandlerFactory;
use Seur\Prestashop\Middleware\TokenAuthorizationMiddleware;
use Seur\Prestashop\Scripts\ScriptHandler;

if (!defined('_PS_VERSION_')) { exit; }

class SeurUpdateshipmentsModuleFrontController extends ModuleFrontController
{
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
            $cmd = new UpdateShipmentsStatus();
            $out = $cmd->handle(); // ['result','revisados','error']
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

    // Se ejecuta cuando NO hay ?ajax=1 → llamada script handler (con log)
    public function initContent()
    {
        // Si por cualquier motivo llegara aquí en una petición AJAX, salimos.
        if (Tools::getValue('ajax')) {
            return;
        }

        // Ejecutar el handler de actualización de envíos (con logs)
        $auth_middleware = new TokenAuthorizationMiddleware('secret', Configuration::get('SEUR2_API_CLIENT_SECRET'));
        $script_instance = new ScriptHandler(new UpdateShipmentsStatusHandlerFactory, $auth_middleware);
        $script_instance->__invoke();
        die;
    }
}
