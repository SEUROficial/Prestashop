{*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*}
<select name='select_status_{$name_select}' id='select_status_{$name_select}'>
    <option value='0' style="background-color:#FFFFFF" color="#FFFFFF"></option>
    {foreach from=$status_ps item="status"}
        <option style="background-color: {$status['color']};color: white;padding:2px;margin-bottom:2px" color="{$status['color']}" value="{$status['id_order_state']}" {if $value==$status['id_order_state']}selected{/if}>{$status['name']}</option>
    {/foreach}
</select>