<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_5_25($module) {

    Configuration::updateValue('SEUR2_CRON_KEY', Tools::passwdGen(32));

    $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."seur2_cron_tasks` (
      `id_task` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(191) NOT NULL,
      `handler` VARCHAR(191) NOT NULL,
      `interval_seconds` INT UNSIGNED NOT NULL DEFAULT 3600,
      `next_run_at` INT UNSIGNED NOT NULL,
      `last_run_at` INT UNSIGNED NULL,
      `active` TINYINT(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (`id_task`),
      UNIQUE KEY `uniq_name` (`name`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8";

    // Install new tabs
    $module->createAdminTab();

    $sql[] = 'DELETE t1 FROM `'._DB_PREFIX_.'seur2_order_pos` t1
          INNER JOIN `'._DB_PREFIX_.'seur2_order_pos` t2 
          WHERE t1.id_cart = t2.id_cart 
          AND t1.id_seur_pos < t2.id_seur_pos';

    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'seur2_order_pos` 
          DROP PRIMARY KEY, 
          ADD COLUMN `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
          MODIFY COLUMN `company` varchar(50) NULL,
          MODIFY COLUMN `address` varchar(100) NULL,
          MODIFY COLUMN `city` varchar(15) NULL,
          MODIFY COLUMN `postal_code` varchar(12) NULL,
          MODIFY COLUMN `timetable` varchar(50) NULL,
          MODIFY COLUMN `phone` varchar(20) NULL';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return $module;
}