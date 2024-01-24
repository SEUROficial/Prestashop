<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Carlos Cid <carlos.cid@ebolution.com>
 * @copyright 2023 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

namespace Seur\Prestashop\Factories;

require_once(_PS_MODULE_DIR_.'seur/interfaces/CommandHandlerFactory.php');
require_once(_PS_MODULE_DIR_.'seur/classes/commands/UpdateShipmentsStatus.php');

use Seur\Prestashop\Commands\UpdateShipmentsStatus;
use Seur\Prestashop\Interfaces\CommandHandler;
use Seur\Prestashop\Interfaces\CommandHandlerFactory;

class UpdateShipmentsStatusHandlerFactory implements CommandHandlerFactory
{
    public function create(): CommandHandler
    {
        return new UpdateShipmentsStatus();
    }
}
