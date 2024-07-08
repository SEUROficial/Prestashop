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

        $now = date("Y-m-d H:i:s");

        $sql = "SELECT DISTINCT so.id_order, id_seur_ccc 
                FROM `"._DB_PREFIX_."seur2_order` so 
                INNER JOIN `"._DB_PREFIX_."orders` o ON o.id_order=so.id_order
                LEFT JOIN `"._DB_PREFIX_."seur2_status` ss ON so.id_status=so.id_status 
                LEFT JOIN `"._DB_PREFIX_."seur2_query` sq ON sq.id_order=so.id_order 
                WHERE ecb != '' AND grupo !='ENTREGADO' 
                AND o.current_state != ".Configuration::get('PS_OS_CANCELED')."
                AND date_labeled > (DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                AND date_query < (DATE_SUB(NOW(), INTERVAL 8 HOUR)) 
                ORDER BY so.id_order
                LIMIT 25";

        $results =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ( ($results && (count($results) == 0 || empty($results))) || !$results){
            $sql = "SELECT DISTINCT so.id_order, id_seur_ccc 
                FROM `"._DB_PREFIX_."seur2_order` so 
                INNER JOIN `"._DB_PREFIX_."orders` o ON o.id_order=so.id_order
                LEFT JOIN `"._DB_PREFIX_."seur2_status` ss ON so.id_status=so.id_status 
                WHERE ecb != '' AND grupo !='ENTREGADO'
                AND o.current_state != ".Configuration::get('PS_OS_CANCELED')."
                AND date_labeled > (DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                AND so.id_order NOT IN (SELECT id_order FROM `"._DB_PREFIX_."seur2_query`)
                ORDER BY so.id_order
                LIMIT 25";
            $results =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        }

        $orders = array();
        if ($results) {
            foreach ($results as $result) {
                $orders[] = ['id_order' => $result['id_order'], 'id_seur_ccc' => $result['id_seur_ccc']];
            }
        }
        return $orders;
    }


    public static function updateQueryDateSeur($id_order){

        $sql = "SELECT date_query FROM `"._DB_PREFIX_."seur2_query` so WHERE id_order = ".(int)$id_order;

        $now = date("Y-m-d H:i:s");

        if(Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)){
            $query= 'UPDATE `'._DB_PREFIX_.'seur2_query` SET `date_query`= "'.$now.'" WHERE id_order = '.(int)$id_order;
        }else{
            $query= 'INSERT INTO `'._DB_PREFIX_.'seur2_query`(`id_order`, `date_query`) VALUES ('.$id_order.',"'.$now.'")';
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

    public static function getLabelFile($id_order) {
        $sql = "SELECT label_files FROM `"._DB_PREFIX_."seur2_order` so WHERE id_seur_order = ".(int)$id_order;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
}