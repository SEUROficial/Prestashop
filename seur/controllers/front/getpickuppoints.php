<?php
if (!defined('_PS_VERSION_')) { exit; }

class SeurGetpickuppointsModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function displayAjax()
    {
        header_remove('X-Powered-By'); // higiene

        // Incluye la librería del módulo si hace falta
        if (!class_exists('SeurLib')) {
            $lib = _PS_MODULE_DIR_.'seur/classes/SeurLib.php';
            if (file_exists($lib)) {
                require_once $lib;
            }
        }

        // según los parámetros
        try {
            if (Tools::getValue('id_address_delivery')) {
                return $this->handleListByAddress();
            }

            if (Tools::getValue('usr_id_address')) {
                return $this->handleUserAddressString();
            }

            if (Tools::getValue('savepos') && Tools::getValue('id_seur_pos')) {
                return $this->handleSaveSelection();
            }

            // Sin parámetros válidos → JSON vacío
            header('Content-Type: application/json; charset=utf-8');
            echo '{}';
            exit;

        } catch (PrestaShopException $e) {
            if (method_exists($e, 'displayMessage')) {
                $e->displayMessage();
            }
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Unexpected error on getpickuppoints controller';
            exit;
        }
    }

    /** Lista puntos pickup cercanos a la dirección de entrega (JSON) */
    protected function handleListByAddress()
    {
        $context = $this->context;
        $cookie  = $context->cookie;

        $idAddr  = (int) Tools::getValue('id_address_delivery');
        $address = new Address($idAddr, (int)$cookie->id_lang);
        $country = new Country((int)$address->id_country, (int)$cookie->id_lang);

        $data = [
            'postalCode'  => str_pad((string)$address->postcode, 4, '0', STR_PAD_LEFT),
            'countryCode' => (string)$country->iso_code,
            'cityName'    => (string)$address->city,
        ];

        // Token y llamada al WS
        if (!class_exists('SeurLib') || !method_exists('SeurLib', 'getToken')) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'SEUR lib not available';
            exit;
        }

        $token = SeurLib::getToken();
        if (!$token) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Unable to generate SEUR token';
            exit;
        }

        $urlws   = Configuration::get('SEUR2_URLWS_PICKUPS');
        $headers = [
            'Accept: */*',
            'Content-Type: application/json',
            'Authorization: Bearer '.$token,
        ];

        $response = SeurLib::sendCurl($urlws, $headers, $data, 'GET');

        // Manejo de errores
        if (isset($response->errors) || !isset($response->data)) {
            if (method_exists('SeurLib', 'showMessageError')) {
                SeurLib::showMessageError(null, 'GET PICKUPS Error: '.$response->errors[0]->detail, true);
            }
            header('Content-Type: application/json; charset=utf-8');
            echo '[]';
            exit;
        }

        $centros = [];
        foreach ($response->data as $centro) {
            $streetNumber = property_exists($centro, 'streetNumber') ? ', '.(string)$centro->streetNumber : '';
            $centros[] = [
                'company'   => (string)$centro->name,
                'address'   => (string)$centro->address.$streetNumber,
                'address2'  => 'PudoId: '.(string)$centro->pudoId,
                'codCentro' => (string)$centro->pudoId,
                'city'      => (string)$centro->cityName,
                'post_code' => (string)$centro->postalCode,
                'phone'     => '',
                'gMapDir'   => (string)$centro->address.$streetNumber.', '.(string)$centro->cityName,
                'position'  => ['lat' => (float)$centro->latitude, 'lng' => (float)$centro->longitude],
                'timetable' => method_exists('SeurLib', 'getTimeTable') ? (string)SeurLib::getTimeTable($centro->openingTime) : '',
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($centros, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Devuelve el string de dirección del usuario para Google Maps (texto plano) */
    protected function handleUserAddressString()
    {
        $cookie = $this->context->cookie;
        $usr    = new Address((int)Tools::getValue('usr_id_address'), (int)$cookie->id_lang);

        $gMapUsrDir = $usr->address1.' '.$usr->postcode.','.$usr->city.','.$usr->country;

        //header('Content-Type: text/plain; charset=utf-8');
        //echo $gMapUsrDir;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['result' => $gMapUsrDir], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Guarda o actualiza el punto elegido para el carrito actual (JSON) */
    protected function handleSaveSelection()
    {
        require_once _PS_MODULE_DIR_.SEUR_MODULE_NAME.'/classes/SeurOrderPos.php';
        $id_cart = (int)$this->context->cart->id;
        $seurOrderPos = SeurOrderPos::getByCartId((int)$this->context->cart->id);
        $seurOrderPos->id_cart = $id_cart;
        $seurOrderPos->id_seur_pos = substr(pSQL(Tools::getValue('id_seur_pos')), 0, 50);
        $seurOrderPos->company = substr(pSQL(urldecode((string)Tools::getValue('company'))), 0, 50);
        $seurOrderPos->address = substr(pSQL(urldecode((string)Tools::getValue('address'))), 0, 100);
        $seurOrderPos->city = substr(pSQL(urldecode((string)Tools::getValue('city'))), 0, 15);
        $seurOrderPos->postal_code = substr(pSQL(urldecode((string)Tools::getValue('post_code'))), 0, 12);
        $seurOrderPos->timetable = substr(pSQL(urldecode((string)Tools::getValue('timetable'))), 0, 50);
        $seurOrderPos->phone = substr(pSQL(urldecode((string)Tools::getValue('phone'))), 0, 20);
        $seurOrderPos->save();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['result' => (bool)$result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}