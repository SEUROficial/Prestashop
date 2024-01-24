<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Carlos Cid <carlos.cid@ebolution.com>
 * @copyright 2023 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

namespace Seur\Prestashop\Scripts;

/**
 * Actualizar el estado de los pedidos vía CRON.
 *
 * Forma de invocarlo:
 *
 * https://<shop-url>/modules/seur/scripts/UpdateShipments.php?secret=<seur_merchant_client_secret>
 * cd <path-to-prestashop>/modules/seur/scripts; php UpdateShipments.php
 */
require_once('bootstrap.php');

require_once(_PS_MODULE_DIR_.'seur/scripts/ScriptHandler.php');
require_once(_PS_MODULE_DIR_.'seur/classes/factories/UpdateShipmentsStatusHandlerFactory.php');
require_once(_PS_MODULE_DIR_.'seur/classes/middleware/TokenAuthorizationMiddleware.php');
require_once(_PS_MODULE_DIR_.'seur/classes/middleware/NullAuthorizationMiddleware.php');

use Seur\Prestashop\Middleware\NullAuthorizationMiddleware;
use Seur\Prestashop\Middleware\TokenAuthorizationMiddleware;
use Seur\Prestashop\Factories\UpdateShipmentsStatusHandlerFactory;

$auth_middleware = php_sapi_name() === 'cli' ?
    new NullAuthorizationMiddleware() :
    new TokenAuthorizationMiddleware('secret', \Configuration::get('SEUR2_API_CLIENT_SECRET'));
$script_instance = new ScriptHandler(new UpdateShipmentsStatusHandlerFactory, $auth_middleware);
$script_instance->__invoke();
die;
