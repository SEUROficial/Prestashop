{*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*}
<script type="text/javascript">
    {literal}
    var seur_ajaxcall_url = '{/literal}{$link->getAdminLink('AdminModules', true)|escape:'quotes':'UTF-8'}{literal}&configure=seur&module_name=seur&ajax=1';
    var urlmodule = '</literal>{$urlmodule}<literal>'
    {/literal}
</script>


<div id="panel-move">
    <div class="panel panel-seur card">
        <div class='module_seur_logo'></div>
        <div class='seur_opcion'>
            <div class="seur_texto">
                <span class="header">{l s='¿Quieres enviar este pedido con Seur?' mod='seur'}</span><br>
                <span>{l s='Pulsa el botón para convertir este envío con SEUR' mod='seur'}</span><br>
            </div>
            <div class="seur_texto seur_boton">
                <button class="btn btn-default btn-disk" id="seur_new_order"
                        name="seur_shipping_order_edit" type="button" value="1">
                    {l s='Send by SEUR' mod='seur'}
                </button>
            </div>
        </div>

        {if count($carriers)}
        <div class='seur_form'>
            <form action="{$urlmodule}" method="post" id="form_order_new_shipping">
            <div class="seur_texto seur_select">
                <label>{l s='Selecciona un transportista' mod='seur'}</label>
                <select name="seur_carrier" id="seur_carrier" >
                    <!--<option value="">{l s='Selecciona un transportista' mod='seur'}</option>-->
                    {foreach from=$carriers item=carrier}
                        <option value="{$carrier.id_seur_carrier}">{$carrier.name}</option>
                    {/foreach}
                </select>
            </div>
            <div class="seur_texto seur_boton">
                <button type="submit" class="btn btn-default btn-disk" id="seur_confirm_carrier"
                        name="seur_shipping_order_edit" type="button" value="1">
                    {l s='Confirm' mod='seur'}
                </button>
            </div>
            </form>
            {else}
                <div class="seur_texto">
                    <span class="header">{l s='No ha dado de alta transportistas de Seur' mod='seur'}</span><br>
                    <span>{l s='Acceda a la configuración de Transportistas SEUR para darlos de alta' mod='seur'}</span><br>
                </div>
        </div>
        {/if}
    </div>
    <div style='margin: 20px;'></div>

</div>
