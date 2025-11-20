<?php
/**
* @category SeurTransporte
* @package SeurTransporte/seur
* @author <desarrollo@ebolution.com>
* @copyright 2025 Seur Transporte
* @license https://seur.com/ Proprietary
*/
namespace Seur\Prestashop;

require_once(_PS_MODULE_DIR_.'seur/scripts/ScriptHandler.php');
require_once(_PS_MODULE_DIR_.'seur/classes/factories/UpdateShipmentsStatusHandlerFactory.php');
require_once(_PS_MODULE_DIR_.'seur/classes/middleware/NullAuthorizationMiddleware.php');

use Configuration;
use Exception;

use PrestaShop\PrestaShop\Adapter\Entity\Context;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\Module;
use PrestaShop\PrestaShop\Adapter\Entity\PrestaShopLogger;
use Seur\Prestashop\Factories\UpdateShipmentsStatusHandlerFactory;
use Seur\Prestashop\Middleware\NullAuthorizationMiddleware;
use Seur\Prestashop\Scripts\ScriptHandler;


if (!defined('_PS_VERSION_'))
exit;

if (!defined('SEUR_MODULE_NAME'))
define('SEUR_MODULE_NAME', 'seur');

class SeurCron{

    private const SEUR_CRON_TABLE = 'seur2_cron_tasks';
    public const SEUR_UPDATE_SHIPMENTS_CRON_NAME = 'update_shipments_status';

    protected $module;

    public function __construct()
    {
        $this->module = Module::getInstanceByName(SEUR_MODULE_NAME);
    }

    public static function getNextRunTime($name)
    {
        $sql = 'SELECT `next_run_at` FROM `'._DB_PREFIX_.self::SEUR_CRON_TABLE.'`
             WHERE `name`="'.pSQL($name).'" AND `active`=1';
        $date = Db::getInstance()->getValue($sql);
        // convertir timestamp a dd/mm/yyyy hh:mm:ss
        return $date ? date('d/m/Y H:i:s', (int)$date) : null;
    }
    public static function getLastRunTime($name)
    {
        $sql = 'SELECT `last_run_at` FROM `'._DB_PREFIX_.self::SEUR_CRON_TABLE.'`
             WHERE `name`="'.pSQL($name).'" AND `active`=1';
        $date = Db::getInstance()->getValue($sql);
        // convertir timestamp a dd/mm/yyyy hh:mm:ss
        return $date ? date('d/m/Y H:i:s', (int)$date) : null;
    }
    public static function getTaskByName($name)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.self::SEUR_CRON_TABLE.'`
             WHERE `name`="'.pSQL($name).'"';
        return Db::getInstance()->getRow($sql);
    }

    public function hasDueTasks($now)
    {
        $sql = 'SELECT name FROM `'._DB_PREFIX_.self::SEUR_CRON_TABLE.'`
            WHERE `active`=1 AND `next_run_at` <= '.(int)$now;
        return (bool) Db::getInstance()->getValue($sql);
    }

    public function pingCronAsync()
    {
        $link = Context::getContext()->link->getModuleLink(
            SEUR_MODULE_NAME,
            'cron',
            ['k' => Configuration::get('SEUR2_CRON_KEY')],
            true
        );

        // Intento con curl si existe
        if (function_exists('curl_init')) {
            $ch = curl_init($link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);        // no esperamos
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);
            @curl_exec($ch);
            @curl_close($ch);
            return;
        }
    }

    public function runDueTasks()
    {
        $now = time();

        // Bloqueo por tarea para evitar colisiones si tienes varias instancias
        $tasks = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.self::SEUR_CRON_TABLE.'` WHERE `active`=1 AND `next_run_at` <= '.(int)$now
        );

        foreach ($tasks as $t) {
            $ok = false;
            try {
                $ok = self::executeTaskHandler($t['handler']);
            } catch (Exception $e) {
                PrestaShopLogger::addLog('[SEUR] Task '.$t['name'].' error: '.$e->getMessage(), 3);
            }

            $last = (int)$now;
            $next = $last + (int)$t['interval_seconds'];
            Db::getInstance()->update(
                self::SEUR_CRON_TABLE,
                [
                    'last_run_at' => (int)$last,
                    'next_run_at' => (int)$next
                ],
                '`id_task`='.(int)$t['id_task']
            );

            if (!$ok) {
                // opcional: política de reintentos, logs, etc.
                PrestaShopLogger::addLog('[SEUR] Task '.$t['name'].' Not returns OK', 3);
            }
        }
    }

    protected function executeTaskHandler($handler)
    {
        // 1) handler como "Clase@metodo"
        if (strpos($handler, '@') !== false) {
            list($class, $method) = explode('@', $handler, 2);
            if (!class_exists($class)) {
                require_once _PS_MODULE_DIR_.SEUR_MODULE_NAME.'/classes/'.$class.'.php';
            }
            if (class_exists($class) && method_exists($class, $method)) {
                $obj = new $class();
                $obj->module = $this->module;
                $obj->context = Context::getContext();
                $obj->$method();
                return true;
            }
            return false;
        }

        // 2) handler como método del propio módulo
        if (method_exists($this, $handler)) {
            $this->$handler();
            return true;
        }

        return false;
    }

    protected function reprogramTask($name, $handler, $interval, $nextRun, $active = true)
    {
        $exists = (int) Db::getInstance()->getValue(
            'SELECT id_task FROM `'._DB_PREFIX_.self::SEUR_CRON_TABLE.'` WHERE `name`="'.pSQL($name).'"'
        );
        $data = [
            'name' => pSQL($name),
            'handler' => pSQL($handler),
            'interval_seconds' => (int)$interval,
            'next_run_at' => (int)$nextRun,
            'active' => $active ? 1 : 0,
        ];

        if ($exists) {
            Db::getInstance()->update(self::SEUR_CRON_TABLE, $data, 'id_task='.(int)$exists);
        } else {
            Db::getInstance()->insert(self::SEUR_CRON_TABLE, $data);
        }
    }

    public  function reprogramShipmentUpdateCron($active, $interval)
    {
        $module = $this->module::getInstanceByName(SEUR_MODULE_NAME);
        if ($module && $module->active) {
            $nextRun = time() + 60; // en 1 minuto

            $this->reprogramTask(
                self::SEUR_UPDATE_SHIPMENTS_CRON_NAME,
                'updateShipments',
                $interval,
                $nextRun,
                $active
            );
        }
    }

    protected function updateShipments()
    {
        // Ejecutar el handler de actualización de envíos (con logs)
        $auth_middleware = new NullAuthorizationMiddleware();
        $script_instance = new ScriptHandler(new UpdateShipmentsStatusHandlerFactory, $auth_middleware);
        $script_instance->__invoke();
    }

}