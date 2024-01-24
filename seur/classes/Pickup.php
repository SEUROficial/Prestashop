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

class SeurPickup
{
    public static function createPickup($id_seur_ccc=1, $pickup_frio = null, $id_order=null)
    {
        if ((int)date('H') < '14')
        {
            try
            {
                $urlws = Configuration::get('SEUR2_URLWS_PICKUP');

                $merchant_data = SeurLib::getMerchantData($id_seur_ccc);

                $frio = $pickup_frio;
                if (!isset($frio) || $frio == null) {
                    $frio = SeurLib::isPickupFrio($id_seur_ccc);
                }

                $int = false;
                $noeur = false;

                /* TODO: ¿Como pasar el $id_order cuando es una petición de pickup manual y no se tiene este valor?
                $seur_order = SeurLib::getSeurOrder($id_order);
                $country_iso_code =  Country::getIsoById((int)$seur_order->id_country);
                $int = SeurLib::isInternationalShipping($country_iso_code);
                $noeur = !SeurLib::isEuropeanShipping($seur_order['id_seur_order']);
                */

                $service = 'SEUR2_PICKUP_SERVICE' . ($int?'_INT':'') . ($noeur?'_NOEUR':'') . ($frio?'_FRIO':'');
                $product = 'SEUR2_PICKUP_PRODUCT' . ($int?'_INT':'') . ($noeur?'_NOEUR':'') . ($frio?'_FRIO':'');
                $serviceCode = Configuration::get($service);
                $productCode = Configuration::get($product);

                $ref = SeurLib::generateRef();
                $customer = Configuration::get('SEUR2_MERCHANT_COMPANY');
                $idNumber = Configuration::get('SEUR2_MERCHANT_NIF_DNI');
                $collectionDate = date("Y-m-d");
                $data = [
                    'serviceCode' => $serviceCode,
                    'productCode' => $productCode,
                    "taric" => 1,
                    "incoTerms" => 1,
                    "collectionDate" => $collectionDate,
                    "customsGoodsCode" => "C",
                    "driverLocation" => true,
                    'ref' => $ref,
                    "label" => false,
                    "payer" => "ORD",
                    'customer' => [
                        'name' => $customer,
                        'idNumber' => $idNumber,
                        "accountNumber" => $merchant_data['ccc'].'-'.$merchant_data['franchise'],
                        "phone" => $merchant_data['phone'],
                        "email" => $merchant_data['email']
                    ],
                    'sender' => [
                        'name' => $customer,
                        'address' => [
                            'streetName' => $merchant_data['street_name'],
                            'cityName' => $merchant_data['town'],
                            'postalCode' => $merchant_data['post_code'],
                            'country' => $merchant_data['country'],
                        ],
                    ],
                    "restrictions" => [
                        [
                            "scheduleEveningTimeSlotFrom" => "16:00:00",
                            "scheduleEveningTimeSlotTo" => "19:00:00"
                        ]
                    ]
                ];

                $token = SeurLib::getToken();
                if (!$token)
                    return false;

                $headers[] = "Accept: */*";
                $headers[] = "Content-Type: application/json";
                $headers[] = "Authorization: Bearer ".$token;

                $response = SeurLib::sendCurl($urlws, $headers, $data, "POST");

                if (isset($response->errors) || !(isset($response->data))) {
                    SeurLib::showMessageError(null, 'CREATE PICKUP Error: '.$response->errors[0]->detail, true);
                    return false;
                }
                if (!self::insertPickup($id_seur_ccc, $frio, $response->data->collectionRef)) {
                    SeurLib::showMessageError(null, 'CREATE PICKUP: No se ha podido añadir la recogida a BD. Ref: '.$response->data->collectionRef, true);
                    return false;
                }
                SeurLib::showMessageOK(null, "Se ha solicitado la recogida a SEUR");
                return true;
            }
            catch (PrestaShopException $e) {
                SeurLib::log('CREATE PICKUP - ' . $e->getMessage());
                return false;
            }
        }
        else {
            $message = 'Pickups after 2pm cannot be arranged via module, contact us by phone to arrange it manually.';
            SeurLib::showMessageWarning(null, $message);
            return false;
        }
    }

	private static function insertPickup($id_seur_ccc, $pickup_frio, $numPickup)
	{
		return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'seur2_pickup`
				(`id_seur_ccc`, `num_pickup`, `date`, `frio`)
			VALUES
				('.(int)$id_seur_ccc.', "'.pSQL($numPickup).'", now(), '.($pickup_frio?1:0).')
		');
	}

	public static function getLastPickup($id_seur_ccc, $frio=0)
	{
	    $pickup_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT p.*, c.id_seur_ccc
			FROM `'._DB_PREFIX_.'seur2_ccc` c
			LEFT JOIN `'._DB_PREFIX_.'seur2_pickup` p ON c.id_seur_ccc = p.id_seur_ccc
                AND SUBSTR(date,1,10) = "'.date('Y-m-d').'"
                AND ifnull(p.frio,0) = '.$frio.'
                WHERE c.id_seur_ccc = '.(int)$id_seur_ccc);
		return $pickup_data;
	}

    public static function getLastPickupFrio($id_seur_ccc, $frio=1)
    {
        return self::getLastPickup($id_seur_ccc, $frio);
    }

    function getPickup($id_pickup) {
        $pickup_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur2_pickup`   
			WHERE id_seur_pickup = '.(int)$id_pickup) ;

        return $pickup_data;
    }

    public static function cancelPickup($id_pickup)
    {
        try
        {
            $pickup = self::getPickup($id_pickup);

            $urlws = Configuration::get('SEUR2_URLWS_PICKUP_CANCEL');

            $data = [
                'codes' => [$pickup['num_pickup']]
            ];

            $token = SeurLib::getToken();
            if (!$token)
                return false;

            $headers[] = "Accept: */*";
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer ".$token;

            $response = SeurLib::sendCurl($urlws, $headers, $data, "POST");

            if (isset($response->error)) {
                SeurLib::showMessageError(null, 'CANCEL PICKUP Error: '.$response->errors[0]->detail, true);
                return false;
            }
            if (!self::deletePickup($id_pickup)) {
                SeurLib::log('CANCEL PICKUP '. $id_pickup . ' - ' .'No se ha podido eliminar la recogida de BD.');
                return false;
            }
            return true;
        }
        catch (PrestaShopException $e)
        {
            SeurLib::log('CANCEL PICKUP '. $id_pickup . ' - ' . $e->getMessage());
            return false;
        }
    }

    private static function deletePickup($id_pickup)
    {
        return Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'seur2_pickup`
			WHERE id_seur_pickup = '.(int)$id_pickup);
    }

    public static function createPickupIfAuto($merchant_data, $id_order) {
	    $make_pickup = true;
        $auto = (Configuration::get('SEUR2_SETTINGS_PICKUP') == 1); //Automatico

        // Pickup yet generated?
        $pickup_data = SeurPickup::getLastPickup($merchant_data['id_seur_ccc']);
        if (!empty($pickup_data)) {
            $datepickup = explode(' ', $pickup_data['date']);
            $datepickup = $datepickup[0];
            if (strtotime(date('Y-m-d')) == strtotime($datepickup))
                $make_pickup = false;
        }

        if ($make_pickup && $auto) {
            return SeurPickup::createPickup($merchant_data['id_seur_ccc'], null, $id_order);
        }
    }

}
