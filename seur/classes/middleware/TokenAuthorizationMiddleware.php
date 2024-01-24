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
use Tools;

/**
 * Validates that a token on the request contains a specific string.
 *
 * Aborts request execution with a 401 response code if not authorized.
 */
class TokenAuthorizationMiddleware implements AuthorizationMiddleware
{
    /**
     * @var String
     */
    private $token_name;
    /**
     * @var String
     */
    private $expected_value;

    public function __construct(
        String $token_name,
        String $expected_value
    ) {
        $this->token_name = $token_name;
        $this->expected_value = $expected_value;
    }

    public function isAuthorized(): bool
    {
        $secret = Tools::getValue($this->token_name);
        if ( empty($secret) ) {
            http_response_code(401);
            die;
        }

        if ( $secret !== $this->expected_value ) {
            http_response_code(401);
            die;
        }

        return true;
    }
}
