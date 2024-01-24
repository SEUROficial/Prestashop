{*
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
 *}

{include file="./header.tpl"}


{if isset($email_warning_message)}
    {include file=$smarty.const._PS_MODULE_DIR_|escape:'htmlall':'UTF-8'|cat:'seur/views/templates/admin/warning_message.tpl' seur_warning_message=$email_warning_message|escape:'htmlall':'UTF-8'}
{/if}


<div class="header_setting">
    <div><a href="{$module_path}&merchant=1">{l s='Merchant' mod='seur'}</a></div>
    <div class="tab_select">{l s='Setting' mod='seur'}</div>
</div>


<form action="" method="POST">
    <div class="page_seur col-xs-12">

        <div class="title_seur col-xs-12">{l s="Cash on delivery" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="label_seur">{l s='Active Cash on Delivery' mod='seur'}</div>
                <div class="input_seur">
                <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="SEUR2_SETTINGS_COD" id="cashDelivery_on" value="1"
                               {if $cashDelivery}checked="checked"{/if}>
                        <label for="cashDelivery_on" class="radioCheck">{l s="Yes" mod="seur"}</label>
                        <input type="radio" name="SEUR2_SETTINGS_COD" id="cashDelivery_off" value="0"
                               {if !$cashDelivery}checked="checked"{/if}>
                        <label for="cashDelivery_off" class="radioCheck">{l s="No" mod="seur"}</label>
                        <a class="slide-button btn"></a>
                </span>
                </div>
            </div>
            <div class="col-xs-12 col-md-12 col-lg-6 row">
                <div class="col-xs-12 col-md-6 col-lg-3">
                    <div class="label_seur">{l s='Fee percent' mod='seur'}</div>
                    <div class="input_seur_mini"><input name="SEUR2_SETTINGS_COD_FEE_PERCENT" value="{$cod_fee_percent}"> %</div>
                </div>
                <div class="col-xs-12 col-md-6 col-lg-3">
                    <div class="label_seur">{l s='Min fee' mod='seur'}</div>
                    <div class="input_seur_mini"><input name="SEUR2_SETTINGS_COD_FEE_MIN" value="{$cod_fee_min}"> &euro;</div>
                </div>
                <div class="col-xs-12 col-md-6 col-lg-3">
                    <div class="label_seur">{l s='Min total available' mod='seur'}</div>
                    <div class="input_seur_mini"><input name="SEUR2_SETTINGS_COD_MIN" value="{$cod_min}"> &euro;</div>
                </div>
                <div class="col-xs-12 col-md-6 col-lg-3">
                    <div class="label_seur">{l s='Max total available' mod='seur'}</div>
                    <div class="input_seur_mini"><input name="SEUR2_SETTINGS_COD_MAX" value="{$cod_max}"> &euro;</div>
                </div>
                <div class="col-xs-12 col-md-12 col-lg-12">
                    <br>
                    <div class="note_seur">{l s="Introduzca un procentaje para cargar a los clientes como recargo y / o un importe mínimo en caso de no llegar al importe del porcentaje." mod='seur'}</div>
                </div>

            </div>
        </div>

        <div class="title_seur col-xs-12">{l s="Notifications and Alerts" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="subtitle_seur col-xs-12">{l s="Notifications" mod='seur'}</div>
            <div class="col-xs-12 col-md-6 col-lg-6">
                <div class="radio_seur">
                    <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="SEUR2_SETTINGS_NOTIFICATION" id="notification_on" value="1"
                                   {if $notification}checked="checked"{/if}>
                            <label for="notification_on" class="radioCheck">{l s="Yes" mod="seur"}</label>
                            <input type="radio" name="SEUR2_SETTINGS_NOTIFICATION" id="notification_off" value="0"
                                   {if !$notification}checked="checked"{/if}>
                            <label for="notification_off" class="radioCheck">{l s="No" mod="seur"}</label>
                            <a class="slide-button btn"></a>
                    </span>
                </div>
                <div class="radio_seur">
                    <input type="radio" name="SEUR2_SETTINGS_NOTIFICATION_TYPE" value="1"
                           {if $notification_type==1 }checked{/if}> {l s='Email' mod='seur'}
                    <input type="radio" name="SEUR2_SETTINGS_NOTIFICATION_TYPE" value="2"
                           {if $notification_type==2 }checked{/if}> {l s='SMS' mod='seur'}
                    <input type="radio" name="SEUR2_SETTINGS_NOTIFICATION_TYPE" value="3"
                           {if $notification_type==3 }checked{/if}> {l s='Email & SMS' mod='seur'}
                </div>
                <div class="col-xs-12">
                    <div class="note_seur">{l s="Seur le notificará cuando se realice el envío. Para usar esta funcionalidad debe contratarla previamente." mod='seur'}</div>
                </div>
            </div>

            <div class="subtitle_seur col-xs-12">{l s="Alerts" mod='seur'}</div>
            <div class="col-xs-12 col-md-6 col-lg-6">
                <div class="radio_seur">
                    <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="SEUR2_SETTINGS_ALERT" id="alerts_on" value="1"
                                   {if $alerts}checked="checked"{/if}>
                            <label for="alerts_on" class="radioCheck">{l s="Yes" mod="seur"}</label>
                            <input type="radio" name="SEUR2_SETTINGS_ALERT" id="alerts_off" value="0"
                                   {if !$alerts}checked="checked"{/if}>
                            <label for="alerts_off" class="radioCheck">{l s="No" mod="seur"}</label>
                            <a class="slide-button btn"></a>
                    </span>
                </div>
                <div class="radio_seur">
                    <input type="radio" name="SEUR2_SETTINGS_ALERT_TYPE" value="1"
                           {if $alerts_type==1 }checked{/if}> {l s='Email' mod='seur'}
                    <input type="radio" name="SEUR2_SETTINGS_ALERT_TYPE" value="2"
                           {if $alerts_type==2 }checked{/if}> {l s='SMS' mod='seur'}
                    <input type="radio" name="SEUR2_SETTINGS_ALERT_TYPE" value="3"
                           {if $alerts_type==3 }checked{/if}> {l s='Email & SMS' mod='seur'}
                </div>
                <div class="col-xs-12">
                    <div class="note_seur">{l s="Seur le informará cuando el paquete esté enviado. Para usar esta funcionalidad debe contratarla previamente." mod='seur'}</div>
                </div>
            </div>
        </div>

        <div class="title_seur col-xs-12">{l s="Print" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="radio_seur">
                    <input type="radio" name="SEUR2_SETTINGS_PRINT_TYPE" value="1"
                           {if $print_type==1 }checked{/if}> {l s='Pdf' mod='seur'}
                    <input type="radio" name="SEUR2_SETTINGS_PRINT_TYPE" value="2"
                           {if $print_type==2 }checked{/if}> {l s='Etiqueta' mod='seur'}
                    <input type="radio" name="SEUR2_SETTINGS_PRINT_TYPE" value="3"
                           {if $print_type==3 }checked{/if}> {l s='A4_3' mod='seur'}
                </div>
                <div class="col-xs-12">
                    <div class="note_seur">{l s="Seleccione Pdf para impresora normal. La impresora térmica debe proveerla SEUR." mod='seur'}</div>
                </div>
            </div>
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="radio_seur">
                    <input type="radio" name="SEUR2_SETTINGS_LABEL_REFERENCE_TYPE" value="1"
                           {if $label_reference_type==1 }checked{/if}> {l s='Referencia pedido' mod='seur'}
                    <input type="radio" name="SEUR2_SETTINGS_LABEL_REFERENCE_TYPE" value="2"
                           {if $label_reference_type==2 }checked{/if}> {l s='Id pedido' mod='seur'}
                </div>
                <div class="col-xs-12">
                    <div class="note_seur">{l s="Identificador a mostrar en la etiqueta" mod='seur'}</div>
                </div>
            </div>
        </div>

        <div class="title_seur col-xs-12">{l s="Collection" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="radio_seur col-xs-3 col-md-2 col-lg-1">
                <input type="radio" name="SEUR2_SETTINGS_PICKUP" value="1"
                       {if $collection_type==1 }checked{/if}> {l s='Automatic' mod='seur'}
            </div>
            <div class="col-xs-9 col-md-10 col-lg-11">
                <div class="radio_seur note_seur">{l s="La recogida automática es generada automáticamente con el primer pedido del día." mod='seur'}</div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding"></div>
            <div class="radio_seur col-xs-3 col-md-2 col-lg-1">
                <input type="radio" name="SEUR2_SETTINGS_PICKUP" value="2"
                       {if $collection_type==2 }checked{/if}> {l s='Fix' mod='seur'}
            </div>
            <div class="col-xs-9 col-md-10 col-lg-11">
                <div class="radio_seur note_seur">{l s="La recogida fija debe estar contratada con SEUR para pasar cada día a recoger." mod='seur'}</div>
            </div>
        </div>


        <div class="title_seur col-xs-12">{l s="Google Api key" mod='seur'}</div>

        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">{l s="Google Api key" mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_GOOGLE_API_KEY" value="{$google_key}"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">&nbsp;</div>
                <div class="note_seur">{l s="Lorem ipsum" mod='seur'} <!--a href="#">{l s="Click here" mod='seur'}</a-->
                </div>
            </div>
        </div>

        <div class="title_seur col-xs-12">{l s="Automatic labels creation" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
          <div class="col-xs-6 col-md-6 col-lg-6">
            <div class="label_seur">{l s='Active' mod='seur'}</div>
            <div class="input_seur">
                    <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="SEUR2_AUTO_CREATE_LABELS" id="generationLabel_on" value="1"
                                   {if $auto_create_labels}checked="checked"{/if}>
                            <label for="generationLabel_on" class="radioCheck">{l s="Yes" mod="seur"}</label>
                            <input type="radio" name="SEUR2_AUTO_CREATE_LABELS" id="generationLabel_off" value="0"
                                   {if !$auto_create_labels}checked="checked"{/if}>
                            <label for="generationLabel_off" class="radioCheck">{l s="No" mod="seur"}</label>
                            <a class="slide-button btn"></a>
                    </span>
            </div>
          </div>
          <div class="col-xs-6 col-md-6 col-lg-6">
            <div class="label_seur">{l s='Payments Methods' mod='seur'}</div>
            <div class="note_seur">{l s="Seleccionar los métodos de pago que estarán activos para la creación automática de etiquetas." mod='seur'}</div>
            <div class="note_seur">{l s="Pulsar Control o Shift para seleccionar más de 1." mod='seur'}</div>
              <div class="input_seur">
                <select name="SEUR2_AUTO_CREATE_LABELS_PAYMENTS_METHODS_AVAILABLE[]" id="payment_methods" multiple>
                    {foreach $payments_methods as $payment}
                        {assign var="selected" value=""}
                        {if in_array($payment['name'], $auto_create_labels_payments_methods_available)}
                            {assign var="selected" value="selected"}
                        {/if}
                        <option value="{$payment['name']}" {$selected}>{$payment['displayName']}</option>
                    {/foreach}
                </select>
            </div>
          </div>
        </div>

        <div class="title_seur col-xs-12">{l s="Automatic packages creation" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
          <div class="col-xs-12 col-md-12 col-lg-12">
            <div class="label_seur">{l s='Active' mod='seur'}</div>
            <div class="input_seur">
                      <span class="switch prestashop-switch fixed-width-lg">
                              <input type="radio" name="SEUR2_AUTO_CALCULATE_PACKAGES" id="generationPackages_on" value="1"
                                     {if $auto_calculate_packages}checked="checked"{/if}>
                              <label for="generationPackages_on" class="radioCheck">{l s="Yes" mod="seur"}</label>
                              <input type="radio" name="SEUR2_AUTO_CALCULATE_PACKAGES" id="generationPackages_off" value="0"
                                     {if !$auto_calculate_packages}checked="checked"{/if}>
                              <label for="generationPackages_off" class="radioCheck">{l s="No" mod="seur"}</label>
                              <a class="slide-button btn"></a>
                      </span>
            </div>
          </div>
        </div>

        <div class="title_seur col-xs-12">{l s="Capture orders from other carriers" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="label_seur">{l s='Active capture orders' mod='seur'}</div>
                <div class="input_seur">
                <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="SEUR2_CAPTURE_ORDER" id="captureOrder_on" value="1"
                               {if $capture_order}checked="checked"{/if}>
                        <label for="captureOrder_on" class="radioCheck">{l s="Yes" mod="seur"}</label>
                        <input type="radio" name="SEUR2_CAPTURE_ORDER" id="captureOrder_off" value="0"
                               {if !$capture_order}checked="checked"{/if}>
                        <label for="captureOrder_off" class="radioCheck">{l s="No" mod="seur"}</label>
                        <a class="slide-button btn"></a>
                </span>
                </div>
            </div>
        </div>


        <div class="title_seur col-xs-12">{l s="Status Seur" mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="label_seur">{l s='Set orders as sended' mod='seur'}</div>
                <div class="input_seur">
                <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="SEUR2_SENDED_ORDER" id="sendedOrder_on" value="1"
                               {if $sended_order}checked="checked"{/if}>
                        <label for="sendedOrder_on" class="radioCheck">{l s="Yes" mod="seur"}</label>
                        <input type="radio" name="SEUR2_SENDED_ORDER" id="sendedOrder_off" value="0"
                               {if !$sended_order}checked="checked"{/if}>
                        <label for="sendedOrder_off" class="radioCheck">{l s="No" mod="seur"}</label>
                        <a class="slide-button btn"></a>
                </span>
                </div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">{l s='In transit' mod='seur'}</div>
                <div class="input_seur">{include file='./select_status_ps.tpl' name_select='SEUR2_STATUS_IN_TRANSIT' status_ps=$status_ps value=$status_in_transit}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">{l s='Return in progress' mod='seur'}</div>
                <div class="input_seur">{include file='./select_status_ps.tpl' name_select='SEUR2_STATUS_RETURN_IN_PROGRESS' status_ps=$status_ps value=$status_return_in_progress}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">{l s='Available to pick up in store' mod='seur'}</div>
                <div class="input_seur">{include file='./select_status_ps.tpl' name_select='SEUR2_STATUS_AVAILABLE_IN_STORE' status_ps=$status_ps value=$status_available_in_store}</div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding"></div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">{l s='Contribute solution' mod='seur'}</div>
                <div class="input_seur">{include file='./select_status_ps.tpl' name_select='SEUR2_STATUS_CONTRIBUTE_SOLUTION' status_ps=$status_ps value=$status_contribute_solution}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">{l s='Delivered' mod='seur'}</div>
                <div class="input_seur">{include file='./select_status_ps.tpl' name_select='SEUR2_STATUS_DELIVERED' status_ps=$status_ps value=$status_delivered}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">{l s='Incidence' mod='seur'}</div>
                <div class="input_seur">{include file='./select_status_ps.tpl' name_select='SEUR2_STATUS_INCIDENCE' status_ps=$status_ps value=$status_incidence}</div>
            </div>
        </div>
        <div class="cron-alerts">
            <div class="alert alert-info">
                {l s='You can set up a scheduled task to refresh the status of your shipments using the following URL:' mod='seur'}
                <br><br>
                <strong>{$module_url}/modules/seur/scripts/UpdateShipments.php?secret={$module_secret}</strong>
            </div>
            <div class="alert alert-info">
                {l s='Alternatively, if you have access to the system console, you can create a CRON job like:' mod='seur'}
                <br><br>
                <strong><code>* * * * * cd {$module_folder}/scripts; /usr/bin/php UpdateShipments.php</code></strong>
            </div>
        </div>
    <div class='clearfix'>
        <button type="submit" name="submitSettingsSeur" class="btn btn-default submitSeur"><i class="icon-save"></i> {l s="Save" mod="seur"}</button>
    </div>
        <script>
            $(document).ready(function(){
                $(".bootstrap.panel").hide();
            });
        </script>

    </div>
</form>
