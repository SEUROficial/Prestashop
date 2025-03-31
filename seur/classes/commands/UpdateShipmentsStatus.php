<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Carlos Cid <carlos.cid@ebolution.com>
 * @copyright 2023 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

namespace Seur\Prestashop\Commands;

require_once(_PS_MODULE_DIR_.'seur/interfaces/CommandHandler.php');
if (!class_exists('SeurLib'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');
if (!class_exists('SeurOrder'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurOrder.php');
require_once(_PS_MODULE_DIR_.'seur/classes/Expedition.php');

use Seur\Prestashop\Interfaces\CommandHandler;
use SeurOrder;
use SeurExpedition;
use SeurLib;
use Exception;
use Order;
use Configuration;

class UpdateShipmentsStatus implements CommandHandler
{
    /**
     * @var string
     */
    private $error_messages;
    /**
     * @var array
     */
    private $orders_processed;
    /**
     * @var array
     */
    private $shipments_status;
    const LOG_FILE = 'updateShipments';

    public function __construct()
    {
        $this->error_messages = '';
        $this->orders_processed = [];
        $this->shipments_status = [];
    }

    public function handle()
    {
        $shipments = SeurOrder::getUpdatableOrders();
        if (count($shipments) == 0) {
            return [
                'result' => -1
            ];
        }

        foreach($shipments as $shipment)
        {
            $this->processSingleShipment($shipment);
        }

        return [
            'error' => $this->error_messages,
            'result' => count($this->orders_processed),
            'revisados' => count($shipments),
            'envios' => $this->shipments_status
        ];
    }

    private function processSingleShipment(array $shipment)
    {
        $failed = false;

        /* Consultar estado */
        $response = $this->getShipmentStatus($shipment);
        if (empty($response->data)) {
            $this->error_messages .= ' # '.$shipment['id_order'].' - no response data';
            $failed = true;
        }

        $shipment_status = $this->parseShipmentStatusResponse($response);
        if ( false === $shipment_status ) {
            $this->error_messages .= ' # '.$shipment['id_order'].' - no 4 matches';
            $failed = true;
        }

        $expedition_status = SeurOrder::getStatusExpedition($shipment_status['tipo_situ'], (int)$shipment_status['cod_situ']);
        if (!isset($expedition_status['id_status'])) {
            //echo "Error al actualizar estado pedido ".$shipment['id_order']."<br/>";
            $this->error_messages .= ' # '.$shipment['id_order'].' - id_status vacío. tipo_situ: '.$shipment_status['tipo_situ']. ' - cod_situ: '.$shipment_status['cod_situ'] ;
            $failed = true;
        }

        $this->updateShipmentStatus($shipment, $shipment_status, $expedition_status, $failed);
        if ($failed) {
            return;
        }
    }

    private function getShipmentStatus(array $shipment)
    {
        $order = new Order($shipment['id_order']);

        $response = SeurExpedition::getExpeditions(array(
            'reference' => SeurLib::getOrderReference($order),
            'idNumber' => Configuration::get('SEUR2_MERCHANT_NIF_DNI'),
            'id_seur_ccc' => $shipment['id_seur_ccc']
        ));

        return $response;
    }

    private function parseShipmentStatusResponse(\stdClass $state)
    {
        $expedicion = $state->data[0];
        $cod_situacion = $expedicion->eventCode;
        preg_match('/(.)(.)([0-9]*)/', $cod_situacion, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) != 4) {
            return false;
        }

        return [
            'expedicion' => $expedicion,
            'cod_situacion' => $cod_situacion,
            'tipo_situ' => $matches[2][0],
            'cod_situ' => $matches[3][0]
        ];
    }

    private function updateShipmentStatus(
        array $shipment,
        array $shipment_status,
        array $expedition_status,
        bool $failed
    ) {
        try {
            if ($failed) {
                SeurOrder::updateQueryDateSeur($shipment['id_order'], true);
                return;
            }
            $this->initContext();
            $this->performUpdateShipmentStatus($shipment, $shipment_status, $expedition_status);
            $this->trackOperation($shipment, $shipment_status, $expedition_status);
        } catch (Exception $e) {
            //echo "Error al actualizar estado pedido ".$id_order."<br/>";
            $this->error_messages .= ' # '.$shipment['id_order'].' - '. $e->getMessage();
        }
    }

    private function performUpdateShipmentStatus(
        array $shipment,
        array $shipment_status,
        array $expedition_status
    ) {
        //echo '-'.$shipment['id_order'].'-'.$shipment_status['tipo_situ'].'-'.$shipment_status['cod_situ'].'-'.$expedition_status['id_status'].'-'.$shipment_status['expedicion']->description.'-';die;
        $order = SeurOrder::getByOrder($shipment['id_order']);
        $order->setCurrentStatus($expedition_status['id_status'], $shipment_status['expedicion']->description);

        SeurOrder::updateQueryDateSeur($shipment['id_order']);
    }

    private function trackOperation(
        array $shipment,
        array $shipment_status,
        array $expedition_status
    ) {
        $this->orders_processed[] = $shipment;
        $this->shipments_status[$shipment['id_order']] = [
            'tipo_situ' => $shipment_status['tipo_situ'],
            'cod_situ' => $shipment_status['cod_situ'],
            'status' => $expedition_status['id_status'],
            'desc' => $shipment_status['expedicion']->description
        ];
    }

    private function initContext()
    {
        require_once _PS_ROOT_DIR_ . '/config/config.inc.php';
        require_once _PS_ROOT_DIR_ . '/init.php';

        $context = \Context::getContext();

        if ($context->employee === null) {
            //get first admin employee
            $result = \Db::getInstance()->executeS('SELECT id_employee FROM '._DB_PREFIX_.'employee WHERE active = 1 AND id_profile = 1 ORDER BY id_employee ASC LIMIT 1');
            $employeeId = $result && isset($result[0]) ? $result[0]['id_employee'] : 1;
            $context->employee = New \Employee($employeeId);
        }
    }
}
