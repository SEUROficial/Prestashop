<?php

namespace Seur\Prestashop\Commands;

require_once(_PS_MODULE_DIR_.'seur/interfaces/CommandHandler.php');
if (!class_exists('SeurLib'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');
if (!class_exists('SeurOrder'))
    require_once(_PS_MODULE_DIR_.'seur/classes/SeurOrder.php');

use Carrier;
use Country;
use Customer;
use Db;
use Message;
use PrestaShop\PrestaShop\Adapter\Entity\Validate;
use Seur\Prestashop\Interfaces\CommandHandler;
use SeurLabel;
use SeurLib;
use SeurOrder;
use Order;
use SeurTown;
use Tools;

class AutoCreateLabel implements CommandHandler
{

    private $id_order;

    /**
     * @param int $int
     */
    public function __construct($id_order)
    {
        $this->id_order = $id_order;
    }

    public function handle()
    {
        $order = new Order((int)$this->id_order);
        $id_seur_order = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT id_seur_order FROM `'._DB_PREFIX_.'seur2_order` WHERE `id_order` = '. $this->id_order
        );

        $seur_order = new SeurOrder($id_seur_order);
        $id_order = $seur_order->id_order;

        $seur_order->labeled = false;
        $seur_carriers = SeurLib::getSeurCarriers(false);

        $ids_seur_carriers = array();
        foreach ($seur_carriers as $value) {
            $ids_seur_carriers[] = (int)$value['carrier_reference'];
        }

        if (!Validate::isLoadedObject($order))
            return false;

        $delivery_price = $order_weigth = 0;
        $products = $order->getProductsDetail();

        foreach ($products as $product) {
            $order_weigth += (float)$product['product_weight'] * (float)$product['product_quantity'];
        }

        $order_weigth = ($order_weigth < 1.0 ? 1.0 : (float)$order_weigth);

        $customer = new Customer((int)$order->id_customer);

        $iso_country = Country::getIsoById((int)$seur_order->id_country);

        $post_code = $seur_order->postcode;
        if ($iso_country === 'PT') {
            $post_code = explode(' ', $seur_order->postcode);
            $post_code = $post_code[0];
        }
        if ($iso_country === 'IE') {
            $post_code = '1';
        }

        $carrier = new Carrier((int)$order->id_carrier);

        if (in_array((int)$carrier->id_reference, $ids_seur_carriers))
        {
            $order_data = SeurLib::getSeurOrder((int)$order->id);
            $order_weigth = ((float)$order_weigth != $order_data['peso_bultos'] ? (float)$order_data['peso_bultos'] : (float)$order_weigth);
            $order_weigth = ($order_weigth < 1.0 ? 1.0 : (float)$order_weigth);

            $name = $seur_order->firstname . ' ' . $seur_order->lastname;
            $direccion = $seur_order->address1 . ' ' . $seur_order->address2;
            $newcountry = new Country((int)$seur_order->id_country, (int)$customer->id_lang);

            $id_seur_ccc = $seur_order->id_seur_ccc;
            $merchant_data = SeurLib::getMerchantData((int)$id_seur_ccc);

            $iso_merchant = $merchant_data['country'];

            $id_employee = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
                SELECT id_employee FROM `'._DB_PREFIX_.'employee` WHERE `id_profile` = 1'
            );

            $rate_data = array(
                'town' => $seur_order->city,
                'peso' => (float)$order_weigth,
                'post_code' => $post_code,
                'bultos' => $order_data['numero_bultos'],
                'ccc' => $merchant_data['ccc'],
                'franchise' => $merchant_data['franchise'],
                'iso' => $newcountry->iso_code,
                'iso_merchant' => $iso_merchant,
                'id_employee' => $id_employee['id_employee'] ?? 1,
                'product' => $seur_order->product,
                'service' => $seur_order->service
            );

            $order_messages_str = '';
            $info_adicional_str = $seur_order->other;
            $order_messages = Message::getMessagesByOrderId((int)$id_order);

            if (is_array($order_messages)) {
                foreach ($order_messages as $order_messag_tmp)
                    $order_messages_str .= "\n" . $order_messag_tmp['message'];

                if (substr_count($order_messages_str, "\n") > 5)
                    $order_messages_str = str_replace(array("\r", "\n"), ' | ', $order_messages_str);

                if (Tools::strlen($order_messages_str) > 250)
                    $order_messages_str = Tools::substr($order_messages_str, 0, 247) . '...';

                $order_messages_str = trim($order_messages_str);
            }
            if (!empty($order_messages_str)) {
                $info_adicional_str = $order_messages_str;
            }
            $info_adicional_str = html_entity_decode($info_adicional_str);

            $label_data = array(
                'pedido' => SeurLib::getOrderReference($order),
                'total_bultos' => $order_data['numero_bultos'],
                'total_kilos' => (float)$order_weigth,
                'direccion_consignatario' => $direccion,
                'consignee_town' => $seur_order->city,
                'codPostal_consignatario' => $post_code,
                'telefono_consignatario' => SeurLib::cleanPhone(!empty($seur_order->phone) ? $seur_order->phone : $seur_order->phone_mobile),
                'movil' => SeurLib::cleanPhone(!empty($seur_order->phone_mobile) ? $seur_order->phone_mobile : $seur_order->phone),
                'name' => $name,
                'companyia' => (!empty($seur_order->company) ? $seur_order->company : ''),
                'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
                'dni' => $seur_order->dni,
                'info_adicional' => $info_adicional_str,
                'country' => $newcountry->name,
                'iso' => $newcountry->iso_code,
                'iso_merchant' => $iso_merchant,
                'id_employee' => $id_employee['id_employee'] ?? 1,
            );

            if (strcmp($order->module, 'seurcashondelivery') == 0) {
                $rate_data['reembolso'] = (float)$order->total_paid;
                $label_data['reembolso'] = (float)$order->total_paid;
                $label_data['clave_reembolso'] = "F";
                $label_data['valor_reembolso'] = (float)$order->total_paid;
            }
            else{
                $label_data['clave_reembolso'] = "";
                $label_data['valor_reembolso'] = "0";
            }

            /* COMPROBAMOS SI ES UN TRANSPORTISTA DE RECOGIDA EN PUNTO DE VENTA Y REESCRIBIMOS*/
            $servicio = $seur_order->service;
            $producto = $seur_order->product;

            $datospos = SeurLib::getOrderPos((int)$order->id_cart);

            if (!empty($datospos) && $datospos && SeurLib::isPickup($servicio, $producto))
            {
                $label_data = array(
                    'pedido' => SeurLib::getOrderReference($order),
                    'total_bultos' => $label_data['total_bultos'],
                    'total_kilos' => (float)$label_data['total_kilos'],
                    'direccion_consignatario' => $direccion,
                    'consignee_town' => $datospos['city'],
                    'codPostal_consignatario' => $datospos['postal_code'],
                    'telefono_consignatario' => SeurLib::cleanPhone(!empty($seur_order->phone) ? $seur_order->phone : $seur_order->phone_mobile),
                    'movil' => SeurLib::cleanPhone(!empty($seur_order->phone_mobile) ? $seur_order->phone_mobile : $seur_order->phone),
                    'name' => $name,
                    'companyia' => $datospos['company'],
                    'email_consignatario' => Validate::isLoadedObject($customer) ? $customer->email : '',
                    'dni' => $seur_order->dni,
                    'info_adicional' => $info_adicional_str,
                    'country' => $newcountry->name,
                    'iso' => $newcountry->iso_code,
                    'cod_centro' => $datospos['id_seur_pos'],
                    'iso_merchant' => $iso_merchant
                );
                $rate_data['cod_centro'] = $datospos['id_seur_pos'];
            }

            if ($order->hasInvoice()){
                if (!SeurLib::isEuropeanShipping($seur_order->id_seur_order)) {
                    SeurLib::invoiceBrexit((int)$seur_order->id_seur_order);
                    SeurLib::invoiceTariff((int)$seur_order->id_seur_order);
                }
            }

            $is_international = SeurLib::isInternationalShipping($iso_country);
            $is_geolabel = SeurLib::isGeoLabel($id_seur_ccc);

            $label_file = SeurLabel::createLabels((int)$order->id, $label_data, $merchant_data, $is_geolabel, $is_international, true);

            if ($label_file === false) {
                echo "<br>Could not set printed value for this order '. $order->reference<br>";
                return false;
            }

            return true;

        }
        else
        {
            echo "<br>Label ".SeurLib::getOrderReference($order)." don\'t generated: Carrier not Seur<br>";
            return false;
        }
    }
}
