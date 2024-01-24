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

class SeurManifest
{
    public static function createManifest($id_orders)
    {
        $merchants  = array();

        foreach($id_orders as $id_order)
        {
            $seurOrder = new SeurOrder((int)$id_order);
            $order = new Order((int)$seurOrder->id_order);
            $order_manifest = array();

            $order_manifest['id'] =  $seurOrder->id;
            $order_manifest['reference'] =  SeurLib::getOrderReference($order);
            $order_manifest['consig_name'] =  $seurOrder->firstname." ".$seurOrder->lastname;
            $order_manifest['consig_address'] =  $seurOrder->address1." ".$seurOrder->address2;
            $order_manifest['consig_postalcode'] =  $seurOrder->postcode;
            $order_manifest['consig_phone'] =  $seurOrder->phone_mobile;
            $order_manifest['bultos'] =  $seurOrder->numero_bultos;
            $order_manifest['producto'] =  $seurOrder->product;
            $order_manifest['ecb'] =   explode(" - ", $seurOrder->ecb);
            $order_manifest['servicio'] =  $seurOrder->service;
            $order_manifest['bultos'] =  $seurOrder->numero_bultos;
            $order_manifest['peso'] =  $seurOrder->peso_bultos;

            $set_cod_value = false;
            $order_manifest['cashondelivery'] =  0;
            if ($order->module == "seurcashondelivery") {
                //añadimos total del pago en el campo reembolso
                $order_manifest['cashondelivery'] = $seurOrder->total_paid;
                $set_cod_value = true;
            }
            // para solucionar la incidencia con la sobreescritura de esta campo por el valor del contrareembolso
            if ($order->module != "seurcashondelivery" && $seurOrder->cashondelivery > 0) {
                $set_cod_value = true;
            }
            // seteamos el valor si es necesario
            if ($set_cod_value) {
                Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute(
                    'UPDATE `'._DB_PREFIX_.'seur2_order` 
					SET `cashondelivery` = '.$order_manifest['cashondelivery'].' 
					WHERE id_order = '.$order->id);
            }

            $order_manifest['otros'] = $seurOrder->other;
            $state = new State($seurOrder->id_state);
            $order_manifest['state'] = $state->name;
            $country = new Country($seurOrder->id_country, Context::getContext()->language->id);
            $order_manifest['country'] = $country->name;

            $merchants[$seurOrder->id_seur_ccc][] = $order_manifest;

            $seurOrder->manifested = 1;
            if (!$seurOrder->codfee) { $seurOrder->codfee = 0; }
            if (!$seurOrder->total_paid) { $seurOrder->total_paid = 0; }
            $seurOrder->save();
        }

        foreach ($merchants as $key => $orders)
        {
            $company = Configuration::get('SEUR2_MERCHANT_COMPANY');
            $cif = Configuration::get('SEUR2_MERCHANT_NIF_DNI');
            $merchant = new SeurCCC($key);

            $smarty = Context::getcontext()->smarty;
            $smarty->assign("ccc", $merchant->ccc);
            $smarty->assign("company", $company);
            $smarty->assign("cif", $cif);
            $smarty->assign("franchise", $merchant->franchise);
            $smarty->assign("street_type", $merchant->street_type);
            $smarty->assign("street_name", $merchant->street_name);
            $smarty->assign("street_number", $merchant->street_number);
            $smarty->assign("state", $merchant->state);
            $smarty->assign("postalcode", $merchant->post_code);
            $smarty->assign("city", $merchant->town);
            $smarty->assign("date", date('d/m/Y'));
            $smarty->assign("hour", date('H:i'));
            $smarty->assign("orders", $orders);

            $manifest_header = $smarty->fetch(_PS_MODULE_DIR_."seur/views/templates/admin/manifest-header.tpl");
            $manifest_content = $smarty->fetch(_PS_MODULE_DIR_."seur/views/templates/admin/manifest-content.tpl");

            ob_end_clean();

            $pdf = new PDFGenerator();
            $pdf->SetHeaderMargin(10);
            $pdf->SetPrintHeader(true);
            $pdf->setFont($pdf->font, '', 5, '', false);
            $pdf->setHeaderFont(array($pdf->font, '', 7, '', false));
            $pdf->setFooterFont(array($pdf->font, '', 8, '', false));
            $pdf->SetMargins(15, 30, 15);
            $pdf->createHeader($manifest_header);
            $pdf->AddPage('P', 'A4');
            $pdf->writeHTML($manifest_content, false, false, false, false, 'P');
            $pdf->Output("Manifiesto_".$merchant->ccc."_".date('YmdHis').".pdf", 'I');
        }

    }

    public function processBulkGeneraETQNacional(){
        $id_orders = Tools::getValue('orderBox');

        ob_end_clean();
        $pdf = new PDFGenerator();
        $pdf->SetPrintHeader(false);
        $pdf->SetFontSize(12);
        $pdf->SetMargins(15, 50, 15);

        $this->context->cookie->__set('id_orders',json_encode($id_orders));
        $this->context->cookie->update();

        foreach($id_orders as $id_order)
        {
            $order = new Order((int)$id_order);
            $address = new Address((int)$order->id_address_delivery);
            $customer = new Customer((int)$order->id_customer);
            $country = new Country($address->id_country, Context::getContext()->language->id);
            $state = new State($address->id_state, Context::getContext()->language->i);

            $order->etiquetado = 1;
            $order->save();

            Context::getcontext()->smarty->assign("address", $address);
            Context::getcontext()->smarty->assign("customer", $customer);
            Context::getcontext()->smarty->assign("state", $state);
            Context::getcontext()->smarty->assign("country", $country);

            $carrier = new Carrier($order->id_carrier);
            $address_template = Context::getContext()->smarty->fetch(_PS_MODULE_DIR_."seur/views/templates/admin/manifest.tpl");

            $pdf->AddPage('P', 'A4');
            $pdf->writeHTML($address_template, false, false, false, false, 'P');
        }
        $pdf->Output("Manifiesto'.$this->tipo.date('YmdHis').'.pdf", 'I');
    }
}
