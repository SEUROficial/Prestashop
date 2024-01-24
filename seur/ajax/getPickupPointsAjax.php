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

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

if (version_compare(_PS_VERSION_, '1.5', '<'))
	require_once(_PS_MODULE_DIR_.'seur/backward_compatibility/backward.php');

if (class_exists('SeurLib') == false)
	include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

$context = Context::getContext();


ini_set('default_charset', 'UTF-8');

if (Tools::getValue('id_address_delivery'))
{
	//if (Tools::getToken(SEUR_MODULE_NAME.Tools::getValue('id_address_delivery')) != Tools::getValue('token'))
	//	exit;

	try	{
        $urlws = Configuration::get('SEUR2_URLWS_PICKUPS');
        $cookie = $context->cookie;
        $address_delivery = new Address((int)Tools::getValue('id_address_delivery'), (int)$cookie->id_lang);
        $newcountry = new Country($address_delivery->id_country, (int)$cookie->id_lang);

        $data = [
            "postalCode" => str_pad($address_delivery->postcode, 4, '0', STR_PAD_LEFT),
            "countryCode" => $newcountry->iso_code,
            "cityName" => $address_delivery->city
        ];

        $token = SeurLib::getToken();
        if (!$token)
            return false;

        $headers[] = "Accept: */*";
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer ".$token;

        $response = SeurLib::sendCurl($urlws, $headers, $data, "GET");

        if (isset($response->errors) || !(isset($response->data))) {
            SeurLib::showMessageError(null, 'GET PICKUPS Error: '.$response->errors[0]->detail, true);
            return false;
        }
        foreach ($response->data as $centro) {
            $centros[] = array(
                'company' => (string)$centro->name,
                'address' => (string)$centro->address.', '.(string)$centro->streetNumber,
                'address2' => 'PudoId: '.(string)$centro->pudoId,
                'codCentro' => (string)$centro->pudoId,
                'city' => (string)$centro->cityName,
                'post_code' => (string)$centro->postalCode,
                'phone' => '',
                'gMapDir' => $centro->address.', '.$centro->streetNumber.', '.$centro->cityName,
                'position' => array('lat' => (float)$centro->latitude, 'lng' => (float)$centro->longitude),
                'timetable' => (string)SeurLib::getTimeTable($centro->openingTime)
            );
        }
        echo json_encode($centros);
	}
	catch (PrestaShopException $e)
	{
        $e->displayMessage();
		return false;
	}
}

if (Tools::getValue('usr_id_address'))
{
	//if (Tools::getToken(SEUR_MODULE_NAME.Tools::getValue('usr_id_address')) != Tools::getValue('token'))
	//	exit;

	$usrAddress = new Address((int)Tools::getValue('usr_id_address'), (int)$cookie->id_lang );
	$gMapUsrDir = $usrAddress->address1.' '.$usrAddress->postcode.','.$usrAddress->city.','.$usrAddress->country;
	echo $gMapUsrDir;
}

if (Tools::getValue('savepos') && Tools::getValue('id_seur_pos'))
{
	//if (Tools::getToken(SEUR_MODULE_NAME.Tools::getValue('chosen_address_delivery')) != Tools::getValue('token'))
	//	exit;

	$id_cart = (int)$context->cart->id;
	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `id_cart`
		FROM `'._DB_PREFIX_.'seur2_order_pos` 
		WHERE `id_cart` = "'.(int)$id_cart.'"
	');
	
	if ($result !== false)
	{
		echo '{"result":"'.Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
			UPDATE `'._DB_PREFIX_.'seur2_order_pos` 
			SET 
				`id_seur_pos` = "'.Tools::getValue('id_seur_pos').'", 
				`company` = "'.pSQL(urldecode(Tools::getValue('company'))).'", 
				`address` = "'.pSQL(urldecode(Tools::getValue('address'))).'", 
				`city` = "'.pSQL(urldecode(Tools::getValue('city'))).'", 
				`postal_code` = "'.pSQL(urldecode(Tools::getValue('post_code'))).'", 
				`timetable` = "'.pSQL(urldecode(Tools::getValue('timetable'))).'", 
				`phone` = "'.pSQL(urldecode(Tools::getValue('phone'))).'"
			WHERE `id_cart` = "'.(int)$id_cart.'"
		').'"}';
	}
	else
	{
		echo '{"result":"'.Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
			INSERT INTO `'._DB_PREFIX_.'seur2_order_pos`
				(`id_cart`, `id_seur_pos`, `company`, `address`, `city`, `postal_code`, `timetable`, `phone`) 
			VALUES
				(
					"'.(int)$id_cart.'",
					"'.Tools::getValue('id_seur_pos').'",
					"'.pSQL(urldecode(Tools::getValue('company'))).'",
					"'.pSQL(urldecode(Tools::getValue('address'))).'",
					"'.pSQL(urldecode(Tools::getValue('city'))).'",
					"'.pSQL(urldecode(Tools::getValue('post_code'))).'",
					"'.pSQL(urldecode(Tools::getValue('timetable'))).'",
					"'.pSQL(urldecode(Tools::getValue('phone'))).'"
				)
		').'"}';
	}
}
