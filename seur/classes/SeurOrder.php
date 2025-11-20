<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Maria Jose Santos <mariajose.santos@ebolution.com>
 * @copyright 2022 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

if (!defined('_PS_VERSION_'))
    exit;

class SeurOrder extends ObjectModel
{
    public $id_seur_order;
    public $id_order;
    public $id_seur_ccc;
    public $id_address_delivery;
    public $id_status;
    public $status_text;
    public $id_seur_carrier;
    public $product;
    public $service;
    public $numero_bultos;
    public $peso_bultos;
    public $ecb;
    public $labeled;
    public $manifested;
    public $date_labeled;
    public $codfee;
    public $cashondelivery;
    public $total_paid;
    public $firstname;
    public $lastname;
    public $id_country;
    public $id_state;
    public $address1;
    public $address2;
    public $postcode;
    public $city;
    public $dni;
    public $other;
    public $phone;
    public $phone_mobile;
    public $brexit;
    public $tariff;
    public $brexit_date;
    public $tariff_date;
    public $label_file;
    public $expeditionCode;
    public $ecbs;
    public $parcelNumbers;
    public $label_files;
    public $insured;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'seur2_order',
        'primary' => 'id_seur_order',
        'multilang' => false,
        'fields' => array(
            'id_order' =>               array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'id_seur_ccc' =>            array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'numero_bultos' =>          array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'peso_bultos' =>            array('type' => self::TYPE_FLOAT, 'required' => true, 'validate' => 'isFloat'),
            'ecb' =>                    array('type' => self::TYPE_STRING, 'size' => 68),
            'labeled' =>                array('type' => self::TYPE_BOOL, 'required' => true),
            'manifested' =>             array('type' => self::TYPE_BOOL, 'required' => true),
            'date_labeled' =>           array('type' => self::TYPE_DATE),
            'codfee' =>                 array('type' => self::TYPE_FLOAT, 'required' => true, 'validate' => 'isPrice'),
            'cashondelivery' =>         array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'id_address_delivery' =>    array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'id_status' =>              array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'status_text' =>            array('type' => self::TYPE_STRING),
            'id_seur_carrier' =>        array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'service' =>                array('type' => self::TYPE_STRING, 'required' => true, 'size' => 5),
            'product' =>                array('type' => self::TYPE_STRING, 'required' => true, 'size' => 5),
            'total_paid' =>             array('type' => self::TYPE_FLOAT, 'required' => true, 'validate' => 'isPrice'),
            'firstname' =>              array('type' => self::TYPE_STRING, 'required' => true, 'size' => 32),
            'lastname' =>               array('type' => self::TYPE_STRING, 'required' => true, 'size' => 32),
            'id_country' =>             array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'id_state' =>               array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'address1' =>               array('type' => self::TYPE_STRING, 'required' => true, 'size' => 128),
            'address2' =>               array('type' => self::TYPE_STRING, 'size' => 128),
            'postcode' =>               array('type' => self::TYPE_STRING, 'required' => true, 'size' => 12),
            'city' =>                   array('type' => self::TYPE_STRING, 'size' => 64),
            'dni' =>                    array('type' => self::TYPE_STRING, 'validate' => 'isDniLite', 'size' => 16),
            'other' =>                  array('type' => self::TYPE_STRING ),
            'phone' =>                  array('type' => self::TYPE_STRING, 'size' => 32),
            'phone_mobile' =>           array('type' => self::TYPE_STRING, 'size' => 32),
            'brexit' =>                array('type' => self::TYPE_BOOL),
            'tariff' =>                array('type' => self::TYPE_BOOL),
            'brexit_date' =>           array('type' => self::TYPE_DATE),
            'tariff_date' =>           array('type' => self::TYPE_DATE),
            'label_file' =>            array('type' => self::TYPE_STRING),
            'expeditionCode' =>        array('type' => self::TYPE_STRING, 'size' => 255),
            'ecbs' =>                  array('type' => self::TYPE_STRING, 'size' => 500),
            'parcelNumbers' =>         array('type' => self::TYPE_STRING, 'size' => 500),
            'label_files' =>           array('type' => self::TYPE_STRING, 'size' => 1000),
            'insured' =>               array('type' => self::TYPE_BOOL),
        ),
    );

    public static function getByOrder($id_order){

        $sql = "SELECT id_seur_order FROM `"._DB_PREFIX_."seur2_order` so WHERE id_order = ".(int)$id_order;

        $id_seur_order =  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if((int)$id_seur_order)
            $seurOrder = new SeurOrder((int)$id_seur_order);
        else
            $seurOrder = NULL;

        return($seurOrder);
    }

    public static function getUpdatableOrders(){

        $limit = 25;

        $sql = "SELECT DISTINCT so.id_order, id_seur_ccc 
                FROM `"._DB_PREFIX_."seur2_order` so 
                INNER JOIN `"._DB_PREFIX_."orders` o ON o.id_order=so.id_order
                LEFT JOIN `"._DB_PREFIX_."seur2_status` ss ON so.id_status=so.id_status 
                LEFT JOIN `"._DB_PREFIX_."seur2_query` sq ON sq.id_order=so.id_order 
                WHERE ecb != '' AND grupo !='ENTREGADO' 
                AND o.current_state != ".Configuration::get('PS_OS_CANCELED')."
                AND date_labeled > (DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                AND date_query < (DATE_SUB(NOW(), INTERVAL 8 HOUR))  
                AND sq.failed_attempts < 3
                ORDER BY so.id_order
                LIMIT " .$limit;
        $results =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ( ($results && (count($results) < $limit || empty($results))) || !$results){
            $sql = "SELECT DISTINCT so.id_order, id_seur_ccc 
                FROM `"._DB_PREFIX_."seur2_order` so 
                INNER JOIN `"._DB_PREFIX_."orders` o ON o.id_order=so.id_order
                LEFT JOIN `"._DB_PREFIX_."seur2_status` ss ON so.id_status=so.id_status 
                WHERE ecb != '' AND grupo !='ENTREGADO'
                AND o.current_state != ".Configuration::get('PS_OS_CANCELED')."
                AND date_labeled > (DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                AND so.id_order NOT IN (SELECT id_order FROM `"._DB_PREFIX_."seur2_query`)
                ORDER BY so.id_order
                LIMIT ".($limit - ($results ? count($results) : 0));
            $results2 =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            $results = array_merge($results, $results2 ?? array());
        }

        $orders = array();
        if ($results) {
            foreach ($results as $result) {
                $orders[] = ['id_order' => $result['id_order'], 'id_seur_ccc' => $result['id_seur_ccc']];
            }
        }
        return $orders;
    }


    public static function updateQueryDateSeur($id_order, $failed_attempt = false)
    {
        $sql = "SELECT date_query FROM `"._DB_PREFIX_."seur2_query` so WHERE id_order = ".(int)$id_order;
        $now = date("Y-m-d H:i:s");

        if(Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)){
            $query = "UPDATE `"._DB_PREFIX_."seur2_query` 
            SET `date_query`= '".$now."', `failed_attempts`= ". ($failed_attempt ? "failed_attempts + 1" : "0") .
            " WHERE id_order = ". (int)$id_order;
        }else{
            $query = "INSERT INTO `"._DB_PREFIX_."seur2_query` (`id_order`, `date_query`, `failed_attempts`) 
            VALUES (".$id_order.", '".$now."', " . ($failed_attempt ? "1" : "0") . ")";
        }
        Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($query);
    }

    public static function getStatusExpedition($type, $cod){

        $sql = "SELECT * FROM `"._DB_PREFIX_."seur2_status` ss WHERE cod_situ like '%".$type. str_pad($cod, 3, "0", STR_PAD_LEFT) ."'";

        $results =  Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return($results);

    }


    public static function getGroupStatusExpedition($id_status){

        $sql = "SELECT grupo FROM `"._DB_PREFIX_."seur2_status` ss WHERE id_status = '".$id_status."'";

        $result =  Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        return($result);

    }


    public function setCurrentStatus($id_status,$status_text)
    {
        $this->id_status = $id_status;
        $this->status_text = $status_text;
        $this->save();

        $group = self::getGroupStatusExpedition($id_status);

        switch ($group){
            case "EN TRÁNSITO":
                $id_status_order = (int)ConfigurationCore::get('SEUR2_STATUS_IN_TRANSIT');
                break;
            case "APORTAR SOLUCIÓN":
                $id_status_order = (int)ConfigurationCore::get('SEUR2_STATUS_CONTRIBUTE_SOLUTION');
                break;
            case "DEVOLUCIÓN EN CURSO":
                $id_status_order = (int)ConfigurationCore::get('SEUR2_STATUS_RETURN_IN_PROGRESS');
                break;
            case "DISPONIBLE PARA RECOGER EN TIENDA":
                $id_status_order = (int)ConfigurationCore::get('SEUR2_STATUS_AVAILABLE_IN_STORE');
                break;
            case "ENTREGADO":
                $id_status_order = (int)ConfigurationCore::get('SEUR2_STATUS_DELIVERED');
                break;
            case "INCIDENCIA":
                $id_status_order = (int)ConfigurationCore::get('SEUR2_STATUS_INCIDENCE');
                break;
            case "DOCUMENTACIÓN RECTIFICADA":
                $id_status_order = (int)ConfigurationCore::get('SEUR2_STATUS_IN_TRANSIT');
                break;

        }

        if($id_status_order) {
            $order = new Order((int)$this->id_order);

            if($order->current_state != (int)$id_status_order)
                $order->setCurrentState((int)$id_status_order);
        }
    }

    public static function getLabelFile($id_seur_order) {
        $sql = "SELECT label_files FROM `"._DB_PREFIX_."seur2_order` so WHERE id_seur_order = ".(int)$id_seur_order;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public static function addOrder($id_order, $id_seur_carrier, $recalculateShipping = true)
    {
        $db = Db::getInstance();
        $link = $db->getLink();

        if ($link instanceof \PDO) {
            $link->beginTransaction();
        } elseif ($link instanceof \mysqli) {
            $link->begin_transaction();
        }
        try {
            $order = new Order((int)$id_order);
            $order_payment = OrderPayment::getByOrderReference($order->reference);
            if (isset($order_payment[0])) {
                $order_payment = $order_payment[0];
            } else {
                $order_payment = null;
            }
            $address_delivery = new AddressCore($order->id_address_delivery);
            $cookie = Context::getContext()->cookie;
            $newcountry = new Country($address_delivery->id_country, (int)$cookie->id_lang);
            $seur_carrier = new SeurCarrier($id_seur_carrier);

            $carrier = Carrier::getCarrierByReference($seur_carrier->carrier_reference);
            $order->id_carrier = $carrier->id;
            $order->save();

            $id_order_carrier = $order->getIdOrderCarrier();
            $order_carrier = new OrderCarrier($id_order_carrier);
            $order_carrier->id_carrier = $carrier->id;

            if ($id_order_carrier == 0) {
                $order_carrier->id_order = (int)$id_order;
                $order_carrier->id_order_invoice = OrderInvoice::getInvoiceByNumber($order->invoice_number)->id;
                $order_carrier->add();
            } else {
                $order_carrier->update();
            }

            $seur_order = new SeurOrder();
            /* --- refreshShippingCost actualiza:-------
                order.total_shipping_tax_excl,
                order.total_shipping_tax_incl,
                order.total_shipping,
                order.total_paid_tax_excl,
                order.total_paid_tax_incl,
                order.total_paid,
                order_carrier.shipping_cost_tax_incl,
                order_carrier.shipping_cost_tax_excl
            */
            if ($recalculateShipping) {
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $seur_order->refreshShippingCost($order);
                }
                else {
                    $order->refreshShippingCost();
                }
            }

            $seur_order->id_order = (int)$id_order;
            $seur_order->id_seur_ccc = SeurLib::getCCC($newcountry->iso_code);
            $seur_order->id_seur_carrier = (int)$id_seur_carrier;
            $seur_order->numero_bultos = 1;
            $seur_order->peso_bultos = ($order->getTotalWeight()?$order->getTotalWeight():1);
            $seur_order->firstname = $address_delivery->firstname;
            $seur_order->lastname = $address_delivery->lastname;
            $seur_order->address1 = $address_delivery->address1;
            $seur_order->address2 = $address_delivery->address2;
            $seur_order->postcode = $address_delivery->postcode;
            $seur_order->phone = SeurLib::cleanPhone($address_delivery->phone);
            $seur_order->phone_mobile = SeurLib::cleanPhone($address_delivery->phone_mobile);
            $seur_order->city = $address_delivery->city;
            $seur_order->id_state = $address_delivery->id_state;
            $seur_order->id_country = $address_delivery->id_country;
            $seur_order->dni = $address_delivery->dni;
            $seur_order->other = $address_delivery->other;
            $seur_order->labeled = 0;
            $seur_order->manifested = 0;
            $seur_order->codfee = 0;
            $seur_order->id_address_delivery = $order->id_address_delivery;
            $seur_order->id_status = 0;
            $seur_order->service = $seur_carrier->service;
            $seur_order->product = $seur_carrier->product;
            $seur_order->total_paid = $order->total_paid_real;

            if (SeurLib::isCODPayment($order) && $order->module != SeurLib::CODPaymentModule) {
                //cambiar el método COD seleccionado al de seurcashondelivery
                $order->payment = SeurLib::CODPaymentName;
                $order->module = SeurLib::CODPaymentModule;

                $shipping_amount_tax_incl = $order->total_shipping_tax_incl; //ya actualizados al nuevo carrier (seur)
                $shipping_amount_tax_excl = $order->total_shipping_tax_excl; //ya actualizados al nuevo carrier (seur)

                //recalcular gastos por pago contrareembolso
                $cod_amount = SeurLib::calculateCODAmount($order);
                $order->total_paid_real = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
                $order->total_paid = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
                $order->total_paid_tax_excl = $order->total_products + $shipping_amount_tax_excl + $cod_amount;
                $order->total_paid_tax_incl = $order->total_products_wt + $shipping_amount_tax_incl + $cod_amount;
                $order->total_shipping = $shipping_amount_tax_incl + $cod_amount;

                // PS - Corregir valor total_shipping_tax_excl
                $cod_amount_tax_excl = $cod_amount;
                $percentage_apply = Configuration::get('SEUR2_SETTINGS_COD_FEE_PERCENT');
                if($percentage_apply){
                    $percentage_apply = str_replace(',','.',$percentage_apply);
                    $percentage_apply = $percentage_apply / 100;
                    // Calculamos el número final
                    $cod_amount_tax_excl = $cod_amount_tax_excl * (1 - $percentage_apply);
                }

                $order->total_shipping_tax_excl = $shipping_amount_tax_excl + $cod_amount_tax_excl;
                $order->total_shipping_tax_incl = $shipping_amount_tax_incl + $cod_amount;

                $seur_order->total_paid = $order->total_paid_real;
                $seur_order->cashondelivery = $order->total_paid_real;
                $seur_order->codfee = $cod_amount;

                //actualizar payment
                if (isset($order_payment)) {
                    $order_payment->amount = $order->total_paid;
                    $order_payment->payment_method = $order->payment;
                    $order_payment->id_currency = $order->id_currency;
                    $order_payment->update();
                }

                //actualizar costes envío transportista
                $order_carrier->shipping_cost_tax_excl = $shipping_amount_tax_excl + $cod_amount;
                $order_carrier->shipping_cost_tax_incl =  $shipping_amount_tax_incl + $cod_amount;
                $order_carrier->update();
            }

            $result = $seur_order->save();
            $order->save();

            //obtener datos de la factura y modificar
            if (isset($order_payment)) {
                $order_invoice = $order_payment->getOrderInvoice($id_order);
                if ($order_invoice) {
                    $order_invoice->total_discount_tax_excl = $order->total_discounts_tax_excl;
                    $order_invoice->total_discount_tax_incl = $order->total_discounts_tax_incl;
                    $order_invoice->total_paid_tax_excl = $order->total_paid_tax_excl;
                    $order_invoice->total_paid_tax_incl = $order->total_paid_tax_incl;
                    $order_invoice->total_products = $order->total_products;
                    $order_invoice->total_products_wt = $order->total_products_wt;
                    $order_invoice->total_shipping_tax_excl = $order->total_shipping_tax_excl;
                    $order_invoice->total_shipping_tax_incl = $order->total_shipping_tax_incl;
                    $order_invoice->total_wrapping = $order->total_wrapping;
                    $order_invoice->total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
                    $order_invoice->total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
                    $order_invoice->update();
                }
            }
            if ($result) {
                $link->commit();
                return self::getByOrder($id_order)->id_seur_order;
            }

        } catch (Exception $e) {
            SeurLib::log('ADD SEUR ORDER '.$id_order.' - '.$e->getMessage());
        }

        $link->rollback();
        return false;
    }

    /**
     * Re calculate shipping cost.
     *
     * @return object $order
     */
    public function refreshShippingCost($order)
    {
        if (empty($order->id)) {
            return false;
        }

        if (!Configuration::get('PS_ORDER_RECALCULATE_SHIPPING')) {
            return $order;
        }

        $fake_cart = new Cart((int) $order->id_cart);
        $new_cart = $fake_cart->duplicate();
        $new_cart = $new_cart['cart'];

        // assign order id_address_delivery to cart
        $new_cart->id_address_delivery = (int) $order->id_address_delivery;

        // assign id_carrier
        $new_cart->id_carrier = (int) $order->id_carrier;

        //remove all products : cart (maybe change in the meantime)
        foreach ($new_cart->getProducts() as $product) {
            $new_cart->deleteProduct((int) $product['id_product'], (int) $product['id_product_attribute']);
        }

        // add real order products
        foreach ($order->getProducts() as $product) {
            $new_cart->updateQty(
                $product['product_quantity'],
                (int) $product['product_id'],
                null,
                false,
                'up',
                0,
                null,
                true,
                true
            ); // - skipAvailabilityCheckOutOfStock
        }

        // get new shipping cost
        $base_total_shipping_tax_incl = (float) $new_cart->getPackageShippingCost((int) $new_cart->id_carrier, true, null);
        $base_total_shipping_tax_excl = (float) $new_cart->getPackageShippingCost((int) $new_cart->id_carrier, false, null);

        // calculate diff price, then apply new order totals
        $diff_shipping_tax_incl = $order->total_shipping_tax_incl - $base_total_shipping_tax_incl;
        $diff_shipping_tax_excl = $order->total_shipping_tax_excl - $base_total_shipping_tax_excl;

        $order->total_shipping_tax_excl -= $diff_shipping_tax_excl;
        $order->total_shipping_tax_incl -= $diff_shipping_tax_incl;
        $order->total_shipping = $order->total_shipping_tax_incl;
        $order->total_paid_tax_excl -= $diff_shipping_tax_excl;
        $order->total_paid_tax_incl -= $diff_shipping_tax_incl;
        $order->total_paid = $order->total_paid_tax_incl;
        $order->update();

        // save order_carrier prices, we'll save order right after this in update() method
        $orderCarrierId = (int) $order->getIdOrderCarrier();
        if ($orderCarrierId > 0) {
            $order_carrier = new OrderCarrier($orderCarrierId);
            $order_carrier->shipping_cost_tax_excl = $order->total_shipping_tax_excl;
            $order_carrier->shipping_cost_tax_incl = $order->total_shipping_tax_incl;
            $order_carrier->update();
        }

        // remove fake cart
        $new_cart->delete();

        return $order;
    }
}