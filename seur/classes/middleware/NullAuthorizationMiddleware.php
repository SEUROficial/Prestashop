<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Carlos Cid <carlos.cid@ebolution.com>
 * @copyright 2023 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

namespace Seur\Prestashop\Middleware;

require_once(_PS_MODULE_DIR_.'seur/interfaces/AuthorizationMiddleware.php');

use Seur\Prestashop\Interfaces\AuthorizationMiddleware;

/**
 * Authorizes everything, so use it when authorization is not required.
 */
class NullAuthorizationMiddleware implements AuthorizationMiddleware
{
    public function isAuthorized(): bool
    {
        return true;
    }
}
