<?php
/*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*/

require_once(_PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . 'seur' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SeurOrder.php');

class AdminSeurCollectingController extends ModuleAdminController
{
    public function __construct()
    {
        $module = Module::getInstanceByName('seur');;

        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->addJQuery();
            $this->addJS($module->getPath() . 'views/js/seurController.js');
        }

        $this->bootstrap = true;
        $this->name = 'AdminSeurCollecting';
        $this->table = 'seur2_order';
        $this->className = 'SeurOrder';
        $this->lang = false;
        $this->module = $module;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->show_messages = array();

        AdminController::__construct();

        $this->context->smarty->assign('controlador', 'AdminSeurCollecting');

        if (Tools::isSubmit('request_pickup')) {
            $id_seur_ccc = Tools::getValue('id_seur_ccc');
            $pickup_frio = Tools::getValue('pickup_frio');

            if ($this->requestPickup($id_seur_ccc, $pickup_frio)) {
                $texto = "Solicitud de recogida realizada con éxito";
                SeurLib::showMessageOK($this, $texto);
            }
            else {
                $texto = "No se ha podido realizar la solicitud de recogida";
                SeurLib::showMessageError($this, $texto);
            }
        }

        if(Tools::isSubmit('cancel_pickup')){
            $id_pickup = Tools::getValue('id_pickup');
            if ($this->cancelPickup($id_pickup)) {
                $texto = "Solicitud de cancelación de recogida realizada con éxito";
                SeurLib::showMessageOK($this, $texto);
            }
            else {
                $texto = "No se ha podido realizar la cancelación de recogida";
                SeurLib::showMessageError($this, $texto);
            }
        }
    }

    public function renderList()
    {
        if (count($this->module->errorConfigure())) {
            Tools::redirectAdmin('index.php?controller=adminmodules&configure=seur&token=' . Tools::getAdminTokenLite('AdminModules') . '&module_name=seur&settings=1');
            die();
        }

        $this->context->smarty->assign(
            array(
                'url_module' => $this->context->link->getAdminLink('AdminModules', true) . "&configure=seur&module_name=seur",
                'url_controller_shipping' => $this->context->link->getAdminLink('AdminSeurShipping', true),
                'url_controller_collecting' => $this->context->link->getAdminLink('AdminSeurCollecting', true),
                'url_controller_tracking' => $this->context->link->getAdminLink('AdminSeurTracking', true),
                'url_controller_returns' => $this->context->link->getAdminLink('AdminSeurReturns', true),
                'img_path' => $this->module->getPath() . 'views/img/',
                'module_path' => 'index.php?controller=AdminModules&configure=' . $this->module->name . '&token=' . Tools::getAdminToken("AdminModules" . (int)(Tab::getIdFromClassName("AdminModules")) . (int)$this->context->cookie->id_employee),
                'seur_url_basepath' => seurLib::getBaseLink(),
            ));

        $this->context->smarty->assign(
            array('tabSelect' => "collecting",
                  'show_messages' => $this->show_messages
            )
        );

        $smarty = $this->context->smarty;
        $html = "";
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/header.tpl');;
        $html .= $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/tabs.tpl');;
        $html .= $this->renderCollecting();
        return $html;
    }

    public function renderCollecting()
    {
        $pickupFixed = Configuration::get('SEUR2_SETTINGS_PICKUP');
        $pickupSolicited = array();
        $pickupSolicitedFrio = array();

        if($pickupFixed==1) {
            $seur_cccs = SeurCCC::getListCCC();

            foreach ($seur_cccs as $seur_ccc) {
                $pickupSolicited[$seur_ccc['ccc']] = SeurPickup::getLastPickup($seur_ccc['id_seur_ccc'], 0);
                $pickupSolicitedFrio[$seur_ccc['ccc']] = SeurPickup::getLastPickup($seur_ccc['id_seur_ccc'], 1);
            }
        }

        $this->context->smarty->assign(
            array('pickupFixed' => $pickupFixed,
                'pickupSolicited' => $pickupSolicited,
                'pickupSolicitedFrio' => $pickupSolicitedFrio)
        );

        $smarty = $this->context->smarty;
        $html = $smarty->fetch(_PS_MODULE_DIR_ . 'seur/views/templates/admin/collecting.tpl');;

        return $html;
    }

    private function requestPickup($id_ccc, $pickup_frio)
    {
        $pickup = new SeurPickup();
        return $pickup->createPickup($id_ccc, $pickup_frio);
    }

    private function cancelPickup($id_pickup)
    {
        $pickup = new SeurPickup();
        return $pickup->cancelPickup($id_pickup);
    }
}
