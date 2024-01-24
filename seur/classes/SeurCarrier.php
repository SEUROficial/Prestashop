<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @version  Release: 0.4.4
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
    exit;

class SeurCarrier extends ObjectModel
{
    public $id_seur_carrier;
    public $carrier_reference;
    public $id_seur_ccc;
    public $shipping_type;
    public $service;
    public $product;
    public $free_shipping;
    public $free_shipping_weight;
    public $free_shipping_price;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'seur2_carrier',
        'primary' => 'id_seur_carrier',
        'multilang' => false,
        'fields' => array(
            'carrier_reference' =>      array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'id_seur_ccc' =>            array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt'),
            'shipping_type' =>          array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isInt', 'size' => 2),
            'service' =>                array('type' => self::TYPE_STRING, 'required' => true, 'size' => 5),
            'product' =>                array('type' => self::TYPE_STRING, 'required' => true, 'size' => 5),
            'free_shipping' =>          array('type' => self::TYPE_BOOL, 'required' => true, 'validate' => 'isBool'),
            'free_shipping_weight' =>   array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'free_shipping_price' =>    array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
        ),
    );


    public static function getSeurCarriers($excludePickUp = false, $international = false)
    {
        $sql = "SELECT * FROM `"._DB_PREFIX_."seur2_carrier` sc
                LEFT JOIN `"._DB_PREFIX_."carrier` c ON (c.`id_reference` = sc.`carrier_reference` 
                AND c.deleted = 0 
                AND c.active = 1)";
        $sql .= " WHERE shipping_type ". ($international?"=":"!=") ." 3";

        if($excludePickUp) {
            $sql .= " AND shipping_type != 2";
        }

        $carriers =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return($carriers);
    }

    public static function getSeurCarriersIds($excludePickUp = false)
    {

        $result = self::getSeurCarriers($excludePickUp);

        $list = array();

        foreach($result as $carrier)
        {
            $list[] = $carrier['carrier_reference'];
        }
        return $list;
    }

    public static function getSeurCarrierByIdCarrier($id_carrier)
    {

        $sql = "SELECT id_seur_carrier FROM `"._DB_PREFIX_."seur2_carrier` sc
                LEFT JOIN `"._DB_PREFIX_."carrier` c ON (c.`id_reference` = sc.`carrier_reference` AND c.deleted=0 AND c.active=1)
                WHERE id_carrier = ".(int)$id_carrier;

        $carrier =  (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        return($carrier);
    }

    public static function isPosActive($id = NULL)
    {
        $where = "";
        if ($id != NULL)
        {
            $where = " AND carrier_reference = ".(int)$id;
        }

        $sql = "SELECT COUNT(*) as activo FROM `"._DB_PREFIX_."seur2_carrier` sc
                LEFT JOIN `"._DB_PREFIX_."carrier` c ON (c.`id_reference` = sc.`carrier_reference` AND c.deleted=0 AND c.active=1)
                WHERE shipping_type = 2 ". $where;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

}