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

class SeurCCC extends ObjectModel
{
    public $id_seur_ccc;
    public $cit;
    public $ccc;
    public $nombre_personalizado;
    public $franchise;
    public $street_type;
    public $street_name;
    public $street_number;
    public $staircase;
    public $floor;
    public $door;
    public $post_code;
    public $town;
    public $state;
    public $country;
    public $phone;
    public $email;
    public $ws_user;
    public $ws_password;
    public $e_devoluciones;
    public $url_devoluciones;
    public $is_default;
    public $id_shop;

    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'seur2_ccc',
        'primary' => 'id_seur_ccc',
        'multilang' => false,
        'fields' => array(
            'cit' =>                array('type' => self::TYPE_STRING, 'size' => 10),
            'ccc' =>                array('type' => self::TYPE_STRING, 'size' => 5),
            'nombre_personalizado' =>          array('type' => self::TYPE_STRING, 'size' => 255),
            'franchise' =>          array('type' => self::TYPE_STRING, 'size' => 5),
            'street_type' =>        array('type' => self::TYPE_STRING, 'size' => 5),
            'street_name' =>        array('type' => self::TYPE_STRING, 'size' => 60),
            'street_number' =>      array('type' => self::TYPE_STRING, 'size' => 10),
            'staircase' =>          array('type' => self::TYPE_STRING, 'size' => 10),
            'floor' =>              array('type' => self::TYPE_STRING, 'size' => 10),
            'door' =>               array('type' => self::TYPE_STRING, 'size' => 10),
            'post_code' =>          array('type' => self::TYPE_STRING, 'size' => 10),
            'town' =>               array('type' => self::TYPE_STRING, 'size' => 50),
            'state' =>              array('type' => self::TYPE_STRING, 'size' => 50),
            'country' =>            array('type' => self::TYPE_STRING, 'size' => 15),
            'phone' =>              array('type' => self::TYPE_STRING, 'size' => 10),
            'email' =>              array('type' => self::TYPE_STRING, 'size' => 50),
            'e_devoluciones' =>     array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'url_devoluciones' =>   array('type' => self::TYPE_STRING, 'size' => 255),
            'is_default' =>         array('type' => self::TYPE_BOOL),
            'id_shop' =>            array('type' => self::TYPE_INT),
        ),
    );

    public static function getCCCDefault(){
        $sql_shop = '';
        if (Context::getContext()->shop) {
            $sql_shop = ' AND id_shop = ' .Context::getContext()->shop->id;
        }

        $sql = "SELECT id_seur_ccc 
                FROM `"._DB_PREFIX_."seur2_ccc` 
                WHERE is_default = 1";
        $id_ccc = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql . $sql_shop);

        if($id_ccc==0) {
            $sql = "SELECT min(id_seur_ccc) as id_seur_ccc 
                    FROM `"._DB_PREFIX_."seur2_ccc`
                    WHERE 1=1";
            $id_ccc = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql . $sql_shop);
        }

        return $id_ccc;
    }

    public static function getListCCC()
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'seur2_ccc`';
        if (Context::getContext()->shop) {
            $sql .= ' WHERE id_shop = ' .Context::getContext()->shop->id;
        }
        return DB::getInstance()->ExecuteS($sql);
    }

    public static function correctCCC($id_order, $label_data, $merchant_data) {
        if (($merchant_data['ccc']==12642 || $merchant_data['ccc']==12641)) {
            if ($label_data['iso'] == 'ES' || $label_data['iso'] == 'PT' || $label_data['iso'] == 'AD') {
                $servicio = 31;
                $producto = 2;
                $ccc = 12641;
            } else {
                $servicio = 77;
                $producto = 104;
                $ccc = 12642;
            }
            $merchant_data = SeurCCC::getMerchantDataByCCC($ccc);
            $seur_order = SeurOrder::getByOrder($id_order);
            $seur_order->service = $servicio;
            $seur_order->product = $producto;
            $seur_order->id_seur_ccc = $ccc;
            $seur_order->save();
        }
        return $merchant_data;
    }

    public static function getMerchantDataByCCC($ccc)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur2_ccc`
			WHERE `ccc` = '.(int)$ccc
        );
    }

    public static function getShops() {
       return ShopCore::getShops();
    }

    public static function getMerchantDataByIdCCC($id_seur_ccc)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur2_ccc`
			WHERE `id_seur_ccc` = '.(int)$id_seur_ccc
        );
    }

}