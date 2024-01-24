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
if (!class_exists('SeurLib')) include(_PS_MODULE_DIR_ . 'seur/classes/SeurLib.php');
if (!class_exists('PrinterType')) include(_PS_MODULE_DIR_ . 'seur/classes/PrinterType.php');

class SeurLabel
{
    const SHIPMENT_COMMENT_LENGTH = 50;
    const SHIPMENT_STREETNAME_LENGTH = 70;

    public static function createLabels($id_order, $label_data, $merchant_data, $is_geolabel, $is_international, $auto_create_label = false)
    {
        try {
            $seur_order = SeurOrder::getByOrder($id_order);
            if (!$seur_order->expeditionCode) {

                $preparedData = SeurLabel::prepareDataShipment($id_order, $label_data, $merchant_data);
                if (!$preparedData) return false;

                $response = SeurLabel::addShipment($preparedData, $auto_create_label);
                if (!$response) return false;
            } else {
                $response['data']['shipmentCode'] = $seur_order->expeditionCode;
                $response['data']['ecbs'] = explode(',', $seur_order->ecbs);
                $response['data']['parcelNumbers'] = explode(',', $seur_order->parcelNumbers);
                $response = json_decode(json_encode($response));
            }
            $preparedData['total_packages'] = $seur_order->numero_bultos;
            $preparedData['total_weight'] = $seur_order->peso_bultos;

            $is_pdf = SeurLib::isPdf();
            $result = SeurLabel::getLabel($response, $is_pdf, $label_data, $id_order, $auto_create_label);
            if (!$result) {
                return false;
            }

            $ecbs = $result['ecbs'];
            $parcelNumbers = $result['parcelNumbers'];
            $label_files = $result['label_files'];
            $expeditionCode = $result['expeditionCode'];
            $trackingNumber = (SeurLib::isInternationalShipping($label_data['iso'])?$parcelNumbers[0]:$ecbs[0]);

            SeurLib::setSeurOrder($id_order, $ecbs[0], (float)$preparedData['total_packages'], (float)$preparedData['total_weight'], 1, $label_files[0], $auto_create_label);
            SeurLib::setSeurOrderExpeditionCode($id_order, $expeditionCode, $ecbs, $parcelNumbers, $label_files);
            SeurLib::setOrderShippingNumber($id_order, $trackingNumber);

            SeurPickup::createPickupIfAuto($merchant_data, $id_order);

        } catch (PrestaShopException $e) {
            if(!$auto_create_label)
                SeurLib::showMessageError(null, "Se ha producido una excepción de Prestashop: ".$e->getMessage(), true );
            return false;
        } catch (SoapFault $e) {
            if(!$auto_create_label)
                SeurLib::showMessageError(null, "Se ha producido una excepción de Soap: ".$e->getMessage(), true );
            return false;
        }
        return $label_files[0];
    }

    public static function prepareData($id_order, $label_data)
    {
        $preparedData = [];

        if (!Validate::isFileName($label_data['pedido'])) {
             return false;
        }

        $seur_order = SeurOrder::getByOrder($id_order);
        $servicio = $seur_order->service;
        $producto = $seur_order->product;
        $name = $seur_order->firstname." ".$seur_order->lastname;
        $seur_carrier = new SeurCarrier((int)$seur_order->id_seur_carrier);
        $mercancia = false;

        if (SeurLib::isInternationalShipping($label_data['iso'])) {
            $mercancia = true;
        }

        $claveReembolso = '';
        $valorReembolso = '';

        if (isset($label_data['reembolso']) && ( !SeurLib::isInternationalShipping($label_data['iso']))) {
            $claveReembolso = 'F';
            $valorReembolso = (float)$label_data['reembolso'];
        }

        if (isset($label_data['cod_centro'])) {
            $producto = 48;
            $servicio = 1;
            if (( SeurLib::isInternationalShipping($label_data['iso']))) {
                $servicio = 77;
            }
        }

        $total_weight = $label_data['total_kilos'];
        $total_packages = $label_data['total_bultos'];

        if($total_weight == 0) $total_weight = 1;
        if($total_packages == 0 || $servicio==77) $total_packages = 1;

        $pesoBulto = $total_weight / $total_packages;
        if ($pesoBulto < 1) { //1kg
            $pesoBulto = 1;
            $total_weight = $total_packages;
        }

        $preparedData['name'] = $name;
        $preparedData['notification'] = (Configuration::get('SEUR2_SETTINGS_NOTIFICATION')==1 ? Configuration::get('SEUR2_SETTINGS_NOTIFICATION_TYPE'):0);
        $preparedData['advice_checkbox'] = Configuration::get('SEUR2_SETTINGS_ALERT');
        $preparedData['distribution_checkbox'] = Configuration::get('SEUR2_SETTINGS_ALERT_TYPE');
        $preparedData['servicio'] = $servicio;
        $preparedData['producto'] = $producto;
        $preparedData['mercancia'] = $mercancia;
        $preparedData['claveReembolso'] = $claveReembolso;
        $preparedData['valorReembolso'] = $valorReembolso;
        $preparedData['total_weight'] = $total_weight;
        $preparedData['total_packages'] = $total_packages;
        $preparedData['pesoBulto'] = $pesoBulto;

        return $preparedData;
    }

    public static function prepareDataShipment($id_order, $label_data, $merchant_data)
    {
        $merchant_data = SeurCCC::correctCCC($id_order, $label_data, $merchant_data);
        $preparedData = SeurLabel::prepareData($id_order, $label_data);
        if (!$preparedData) {
            return false;
        }

        $parcels = [];
        for ($i = 1; $i <= (float)$preparedData['total_packages']; $i++) {
            $parcels[] = [
                    "weight" => $preparedData['pesoBulto'],
                    "width" => 1,
                    "height" => 1,
                    "length" => 1,
                    "parcelReference" => "BULTO_".$i
                ];
        }

        $data = [
            "serviceCode" => $preparedData['servicio'],
            "productCode" => $preparedData['producto'],
            "charges" => "P",
            "ecommerceName" => "PrestaShop",
            "security" => false,
            //"pod" => "S",
            "dConsig" => false,
            "did" => false,
            "dSat" =>  false,
            "change" => false,
            /*"aduOutKey" =>  "P",
            "aduInKey" =>  "P",
            "customsGoodsType" => "C",
            "agdReference" => "",*/
            "reference" => $label_data['pedido'],
            "receiver" => [
                "name" => $preparedData['name'],
                "idNumber" => $label_data['dni'],
                "phone" => ($label_data['telefono_consignatario']??($label_data['movil']??'')),
                "email" => $label_data['email_consignatario'],
                "contactName" => $preparedData['name'],
                "address" => [
                    "streetName" => substr($label_data['direccion_consignatario'], 0, self::SHIPMENT_STREETNAME_LENGTH),
                    "postalCode" => $label_data['codPostal_consignatario'],
                    "country" => $label_data['iso'],
                    "city" => ($label_data['consignee_town']??'')
                ]
            ],
            "sender" => [
                "name" =>  Configuration::get('SEUR2_MERCHANT_COMPANY'),
                "idNumber" => Configuration::get('SEUR2_MERCHANT_NIF_DNI'),
                "phone" => $merchant_data['phone'],
                "accountNumber" => $merchant_data['ccc'].'-'.$merchant_data['franchise'],
                "email" => $merchant_data['email'],
                "contactName" => Configuration::get('SEUR2_MERCHANT_COMPANY'),
                "address" => [
                    "streetName" => substr($merchant_data['street_type'].' '.$merchant_data['street_name'].' '.$merchant_data['street_number'], 0, self::SHIPMENT_STREETNAME_LENGTH),
                    "postalCode" => $merchant_data['post_code'],
                    "country" => $merchant_data['country'],
                    "cityName" => $merchant_data['town']
                ]
            ],
            //"comments" => $label_data['info_adicional']. (Configuration::get('SEUR2_PRODS_REFS_IN_COMMENTS')? SeurLabel::getProductsRefs($id_order) : ""),
            "parcels" => $parcels
        ];

        $comments = [];
        if (($preparedData['servicio'] == 15) && ($preparedData['producto'] == 114)) {
            // Envío nacional 48h, producto 'Fresh'
            $comments[] = 'ENTREGA: ' . SeurLib::getDeliveryDate();
            $comments[] = 'TIPO: ' . SeurLib::getShipmentType($id_order);
        } else {
            $comments[] = $label_data['info_adicional']. (Configuration::get('SEUR2_PRODS_REFS_IN_COMMENTS')? SeurLabel::getProductsRefs($id_order) : "");
        }
        $data['comments'] = substr(implode('; ', $comments), 0, self::SHIPMENT_COMMENT_LENGTH);

        if (isset($label_data['cod_centro'])) {
            $data["receiver"]["address"]["pickupCentreCode"] = $label_data['cod_centro'];
        }

        $order = new Order($id_order);
        $seur_order = SeurOrder::getByOrder($id_order);
        if (SeurLib::isInternationalShipping($label_data['iso']) &&
            !SeurLib::isEuropeanShipping($seur_order->id_seur_order)) {
            $data['taric'] = Configuration::get('SEUR2_TARIC');
            $data['declaredValue'] = [
                "currencyCode" => "EUR",
                "amount" => $order->total_paid
            ];
        }

        if (SeurLib::isCODPayment($order)) {
            $data['codValue'] = [
                "currencyCode" => "EUR",
                "amount" => $preparedData['valorReembolso'],
                "codFee" => "D"
            ];
        }

        return $data;
    }

    private static function getProductsRefs($id_order) {
        $refs = ' ';
        $order = new Order($id_order);
        foreach ($order->getProducts() as $product) {
            $refs .= ($refs==''?'':'-') . $product['product_reference'];
        }
        return $refs;
    }

    public static function getLabel($response, $is_pdf, $label_data, $id_order, $auto_create_label = false)
    {
        try
        {
            $urlws = Configuration::get('SEUR2_URLWS_LABELS');

            $token = SeurLib::getToken();
            if (!$token)
                return false;

            $headers[] = "Accept: */*";
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer ".$token;

            $type = new PrinterType();
            $types = $type->getOptions();
            $printerType = $types[Configuration::get('SEUR2_SETTINGS_PRINT_TYPE')];
            $data = [
                'code' => $response->data->shipmentCode,
                'type' => $printerType,
                'entity' => 'EXPEDITIONS'
            ];
            if ($printerType == $types[PrinterType::PRINTER_TYPE_A4_3]) {
                $data['templateType'] = PrinterType::TEMPLATE_TYPE_A4_3;
            }

            $responseLabel = SeurLib::sendCurl($urlws, $headers, $data, "GET");

            if (!$auto_create_label && isset($responseLabel->errors)) {
                SeurLib::showMessageError(null, 'getLabel Error: '.$responseLabel->errors[0]->detail, true);
                return false;
            }

            $cont = 1;
            $label_files = [];
            foreach ($responseLabel->data as $data) {
                if ($is_pdf) {
                    $content = base64_decode($data->pdf);
                } else {
                    $content = $data->label;
                }

                if (is_writable(_PS_MODULE_DIR_ . 'seur/files/deliveries_labels/')) {
                    $label_file = $label_data['pedido'] . ($cont==1 ? '' : '_'.$cont) . ($is_pdf ? '.pdf' : '.txt');
                    file_put_contents(_PS_MODULE_DIR_ . 'seur/files/deliveries_labels/' . $label_file, $content);
                    SeurLib::setAsPrinted($id_order, $label_file);
                    $label_files[] = $label_file;
                    $cont++;
                }
            }
            $expeditionCode = $response->data->shipmentCode;
            $ecbs = $response->data->ecbs;
            $parcelNumbers = $response->data->parcelNumbers;

            if(!$auto_create_label)
                SeurLib::showMessageOK(null, "getLabel OK");

            return [
                'ecbs' => $ecbs,
                'parcelNumbers' => $parcelNumbers,
                'expeditionCode' => $expeditionCode,
                'label_files' => $label_files
            ];
        }
        catch (PrestaShopException $e)
        {
            SeurLib::log('getLabel Exception - ' . $e->getMessage());
            return false;
        }
    }

    public static function addShipment($preparedData, $auto_create_label = false)
    {
        try
        {
            $urlws = Configuration::get('SEUR2_URLWS_ET');

            $token = SeurLib::getToken();
            if (!$token)
                return false;

            $headers[] = "Accept: */*";
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer ".$token;

            $response = SeurLib::sendCurl($urlws, $headers, $preparedData, "POST");

            if (!$auto_create_label && isset($response->errors)) {
                SeurLib::showMessageError(null, 'addShipment Error: '.$response->errors[0]->detail, true);
                return false;
            }

            if (!$auto_create_label && isset($response->error)) {
                SeurLib::showMessageError(null, 'addShipment Error: '.$response->error, true);
                return false;
            }

            if(!$auto_create_label)
                SeurLib::showMessageOK(null, "addShipment Created OK");
            return $response;
        }
        catch (PrestaShopException $e)
        {
            SeurLib::log('ADD SHIPMENTS - ' . $e->getMessage());
            return false;
        }
    }
}
