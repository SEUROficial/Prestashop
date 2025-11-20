<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Desarrollo <desarrollo@ebolution.com>
 * @copyright 2025 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

if (!defined('_PS_VERSION_'))
    exit;

class SeurOrderPos extends ObjectModel
{
    public $id_cart;
    public $id_seur_pos;
    public $company;
    public $address;
    public $city;
    public $postal_code;
    public $timetable;
    public $phone;

    public $id_seur_order;
    public $id_order;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'seur2_order_pos',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            'id_cart'     => array('type' => self::TYPE_INT, 'required' => true),
            'id_seur_pos' => array('type' => self::TYPE_STRING, 'size' => 50, 'required' => true),
            'company'     => array('type' => self::TYPE_STRING, 'size' => 50),
            'address'     => array('type' => self::TYPE_STRING, 'size' => 100),
            'city'        => array('type' => self::TYPE_STRING, 'size' => 15),
            'postal_code' => array('type' => self::TYPE_STRING, 'size' => 12),
            'timetable'   => array('type' => self::TYPE_STRING, 'size' => 50),
            'phone'       => array('type' => self::TYPE_STRING, 'size' => 20),
        ),
    );

    public static function getByCartId($id_cart)
    {
        $orderPos = new SeurOrderPos();
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'seur2_order_pos`
            WHERE `id_cart` = '.(int)$id_cart;

        $data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if ($data) {
            $orderPos->hydrate($data);
        }
        return $orderPos;
    }
}