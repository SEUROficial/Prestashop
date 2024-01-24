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

class SeurExpedition
{
    public static function getExpeditions($expedition_data = null)
    {
        try
        {
            $urlws = Configuration::get('SEUR2_URLWS_E');
            //$urlws = 'https://servicios.apipre.seur.io/pic/v1/tracking-services/simplified';  //QUITAR!!!!!!

            $token = SeurLib::getToken();
            if (!$token)
                return false;

            $headers[] = "Accept: */*";
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer ".$token;

            if (isset($expedition_data['id_seur_ccc'])) {
                $id_seur_ccc = $expedition_data['id_seur_ccc'];
            }
            $merchant_data = SeurLib::getMerchantData($id_seur_ccc);

            $data = [
                'ref' => $expedition_data['reference'],
                'refType' => 'REFERENCE',
                'idNumber' => $expedition_data['idNumber'],
                'accountNumber' => $merchant_data['ccc'],
                'businessUnit' => $merchant_data['franchise']
            ];

            $response = SeurLib::sendCurl($urlws, $headers, $data, "GET");

            if (isset($response->errors) || !(isset($response->data))) {
                SeurLib::showMessageError(null, 'TRACKING Error: '.isset($response->errors[0]->detail)?? '', true);
                return false;
            }
            SeurLib::showMessageOK(null, "TRACKING Status OK");
            return $response;
        }
        catch (PrestaShopException $e)
        {
            SeurLib::log('GET TRACKING - ' . $e->getMessage());
            return false;
        }
    }
}
