<?php

use Seur\Prestashop\SeurCron;
use PrestaShopLogger;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

class SeurCronModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_header = false;
    public $display_footer = false;

    public function initContent()
    {
        parent::initContent();

        // Seguridad básica
        $key = Tools::getValue('k');
        if ($key !== Configuration::get('SEUR2_CRON_KEY')) {
            header('HTTP/1.1 403 Forbidden');
            die('Forbidden');
        }

        // Evita solapamientos con un lock (DB GET_LOCK)
        $locked = $this->acquireLock('seur_cron_lock', 0);
        if (!$locked) {
            die('BUSY'); // ya hay una ejecución
        }

        try {
            $module = Module::getInstanceByName('seur');
            $seurCron = new SeurCron($module);
            $seurCron->runDueTasks(); // ejecuta tareas vencidas
            //echo 'OK';
        } catch (Exception $e) {
            PrestaShopLogger::addLog('[SEUR] CRON ERROR: '.$e->getMessage(), 3);
            header('HTTP/1.1 500 Internal Server Error');
            //echo 'ERROR';
        } finally {
            $this->releaseLock('seur_cron_lock');
        }
        die;
    }

    protected function acquireLock($name, $timeout = 0)
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT GET_LOCK("'.pSQL($name).'", '.(int)$timeout.')'
        );
    }
    protected function releaseLock($name)
    {
        Db::getInstance()->getValue('SELECT RELEASE_LOCK("'.pSQL($name).'")');
    }
}
