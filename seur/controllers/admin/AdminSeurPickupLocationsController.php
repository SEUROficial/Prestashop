<?php
/**
 * @category SeurTransporte
 * @package SeurTransporte/seur
 * @author Ebolution TEAM <desarrollo@ebolution.com>
 * @copyright 2022 Seur Transporte
 * @license https://seur.com/ Proprietary
 */

require_once(_PS_MODULE_DIR_ . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PDFMerger.php');
require_once(_PS_MODULE_DIR_ . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SeurOrder.php');
if (!class_exists('SeurCashOnDelivery')) include_once(_PS_MODULE_DIR_.'seurcashondelivery/seurcashondelivery.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSeurPickupLocationsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();

        $postal_code = Tools::getValue('postal_code');
        $city = Tools::getValue('city');
        $country_id = Tools::getValue('country_id');

        $locations = [];
        $search_performed = false;

        if ($postal_code && $city && $country_id) {
            $locations = $this->searchLocations($postal_code, $city, $country_id);
            $search_performed = is_array($locations);
        }

        $countries = $this->getCountries();

        $this->context->smarty->assign([
            'countries' => $countries,
            'locations' => $locations,
            'search_performed' => $search_performed,
            'postal_code' => $postal_code,
            'city' => $city,
            'country_id' => $country_id,

            'url_module' => $this->context->link->getAdminLink('AdminModules', true) . "&configure=seur&module_name=seur",
            'seur_url_basepath' => seurLib::getBaseLink(),
            'img_path' => $this->module->getPath() . 'views/img/',
            'url_controller_shipping' => $this->context->link->getAdminLink('AdminSeurShipping', true),
            'url_controller_collecting' => $this->context->link->getAdminLink('AdminSeurCollecting', true),
            'url_controller_tracking' => $this->context->link->getAdminLink('AdminSeurTracking', true),
            'url_controller_returns' => $this->context->link->getAdminLink('AdminSeurReturns', true),
            'url_controller_bulk_assign_carrier' => $this->context->link->getAdminLink('AdminSeurBulkAssignCarrier', true),
            'url_controller_pickup_locations' => $this->context->link->getAdminLink('AdminSeurPickupLocations', true),
            'module_path' => 'index.php?controller=AdminModules&configure=' . $this->module->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Seur::findTabIdByClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
            'bulk_assign_enabled' => Configuration::get('SEUR2_BULK_ASSIGN_CARRIER'),
            'tabSelect' => 'pickup-locations',
        ]);
        $this->context->smarty->assign(
            array(
            ));

        $content  = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/header.tpl');;
        $content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/tabs.tpl');;
        $content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'seur/views/templates/admin/pickup_locations.tpl'
        );

        $this->context->smarty->assign('content', $content);
    }

    private function getCountries(): array
    {
        $merged = [];
        $default_countries = [
            ['iso_code' => 'ES', 'name' => 'España'],
            ['iso_code' => 'PT', 'name' => 'Portugal'],
            ['iso_code' => 'AD', 'name' => 'Andorra'],
        ];
        $all_countries = SeurLib::getCountries();

        foreach (array_merge($default_countries, $all_countries) as $item) {
            if ( !isset($merged[$item['iso_code']]) ) {
                $merged[$item['iso_code']] = $item;
            }
        }

        return array_values($merged);
    }


    protected function searchLocations($postal_code, $city, $country_id)
    {
        $data = [
            'postalCode'  => $postal_code,
            'countryCode' => $country_id,
            'cityName'    => $city,
        ];

        // Token y llamada al WS
        if (!class_exists('SeurLib') || !method_exists('SeurLib', 'getToken')) {
            $this->errors[] = 'SEUR lib not available';
            return false;
        }

        $token = SeurLib::getToken();
        if (!$token) {
            $this->errors[] = 'Unable to generate SEUR token';
            return false;
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
            $this->errors[] = "Unexpected error occurred while fetching pickup locations.";
            return false;
        }

        $centros = [];
        foreach ($response->data as $centro) {
            $streetNumber = property_exists($centro, 'streetNumber') ? ', '.(string)$centro->streetNumber : '';
            $streetType = property_exists($centro, 'streetType') ? (string)$centro->streetType.' ' : '';

            $timetable = [];
            if (isset($centro->openingTime->weekDays)) {
                foreach ($centro->openingTime->weekDays as $day) {
                    $timetable[] = $this->module->l(ucfirst($day->day)) .  ' ' . $day->openingHours;
                }
            }
            $centros[] = [
                'name'      => (string)$centro->name,
                'address'   => $streetType . $centro->address . $streetNumber,
                'pudoId'    => (string)$centro->pudoId,
                'city'      => (string)$centro->cityName,
                'post_code' => (string)$centro->postalCode,
                'latitude'  => (float)$centro->latitude,
                'longitude' => (float)$centro->longitude,
                'timetable' => $timetable,
            ];
        }

        return $centros;
    }
}
