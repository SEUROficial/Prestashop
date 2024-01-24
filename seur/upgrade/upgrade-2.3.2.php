<?php
/**
 * Created by PhpStorm.
 * Date: 2021-06-14
 */

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_3_2($module) {

    $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN brexit tinyint(1) NULL DEFAULT 0;";
    $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN tariff tinyint(1) NULL DEFAULT 0;";
    $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN brexit_date timestamp NULL;";
    $sql[] = "ALTER TABLE `"._DB_PREFIX_."seur2_order` ADD COLUMN tariff_date timestamp NULL;";

    $sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_products` (id_seur_product, id_seur_services_type, name) 
            VALUES (116, 3, 'MULTIPARCEL');";

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'seur2_european_countries` (
  `id` int(11) NOT NULL,
  `iso_code` varchar(5) NOT NULL,
  `country` varchar(100) NOT NULL
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'seur2_european_countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iso_code` (`iso_code`)';

    $sql[] = "INSERT INTO `"._DB_PREFIX_."seur2_european_countries` (`id`, `iso_code`, `country`) VALUES
(1, 'AT', 'Austria'),
(2, 'BE', 'Bélgica'),
(3, 'BG', 'Bulgaria'),
(4, 'CY', 'Chipre'),
(5, 'CZ', 'República Checa'),
(6, 'DE', 'Alemania'),
(7, 'DK', 'Dinamarca'),
(8, 'EE', 'Estonia'),
(9, 'ES', 'España'),
(10, 'FI', 'Finlandia'),
(11, 'FR', 'Francia'),
(12, 'GR', 'Grecia'),
(13, 'HR', 'Croacia'),
(14, 'HU', 'Hungría'),
(15, 'IE', 'Irlanda'),
(16, 'IT', 'Italia'),
(17, 'LT', 'Lituania'),
(18, 'LU', 'Luxemburgo'),
(19, 'LV', 'Letonia'),
(20, 'MT', 'Malta'),
(21, 'NL', 'Países Bajos'),
(22, 'PL', 'Polonia'),
(23, 'PT', 'Portugal'),
(24, 'RO', 'Rumanía'),
(25, 'SE', 'Suecia'),
(26, 'SI', 'Eslovenia'),
(27, 'SK', 'Eslovaquia')";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return $module;
}