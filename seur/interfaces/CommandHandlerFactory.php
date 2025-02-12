<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Carlos Cid <carlos.cid@ebolution.com>
 * @copyright 2023 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

namespace Seur\Prestashop\Interfaces;

require_once(_PS_MODULE_DIR_.'seur/interfaces/CommandHandler.php');

interface CommandHandlerFactory
{
    public function create(): CommandHandler;
    public function getLogFile(): string;
}
