<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Carlos Cid <carlos.cid@ebolution.com>
 * @copyright 2023 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

namespace Seur\Prestashop\Scripts;

use Seur\Prestashop\Interfaces\CommandHandlerFactory;
use Seur\Prestashop\Interfaces\AuthorizationMiddleware;
use Logger;

require_once(_PS_MODULE_DIR_.'seur/interfaces/CommandHandlerFactory.php');
require_once(_PS_MODULE_DIR_.'seur/interfaces/AuthorizationMiddleware.php');

/**
 * Generic class to handle scripts (eg. a cron job).
 *
 * The object factory injects the real script handler.
 * The authorization middleware manage if the request is authorized.
 */
class ScriptHandler
{
    /**
     * @var CommandHandlerFactory
     */
    private $factory;
    /**
     * @var AuthorizationMiddleware
     */
    private $middleware;

    public function __construct(
        CommandHandlerFactory $factory,
        AuthorizationMiddleware $middleware
    ) {
        $this->factory = $factory;
        $this->middleware = $middleware;
    }

    public function __invoke(): void
    {
        try {
            if ($this->authorized()) {
                $this->dispatch();
            }
        } catch (\Exception $ex) {
            $message = "Unexpected error while processing command: " . $ex->getMessage() . ". Command execution aborted.";
            Logger::addLog($message, 3); // 3 - Error
        }
    }

    private function authorized(): bool
    {
        return $this->middleware->isAuthorized();
    }

    private function dispatch(): void
    {
        $handler = $this->factory->create();
        $handler->handle();
    }
}
