{*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*}
	<script type="text/javascript">
		{literal}
		var seur_ajaxcall_url = '{/literal}{$link->getAdminLink('AdminSeurShipping', true)|escape:'quotes':'UTF-8'}{literal}&ajax=1';
		var id_seur_order = '{/literal}{$id_seur_order}{literal}';
		var labeled = '{/literal}{$labeled}{literal}';
		var seur_url_basepath = '{/literal}{$seur_url_basepath}{literal}';
		$('#shipping_table td:last-child').html('<a href="#seur"><button class="btn btn-default btn-disk pull-right" type="button" value="1"><i class="icon-eye"></i> {/literal}{l s='Ver desglose' mod='seur'}{literal}</button></a>');
		{/literal}
	</script>
	<div id="panel-move">
		<a name="seur" ></a>
		<form action="{$url_edit_order}" method="post">
		<div class="panel panel-seur-order card">
			<div>
			<div class="module_seur_logo"></div>
			<div class="module_seur_cost">
				<label>{l s='Shipping cost to customer' mod='seur'}</label>
				<span>{convertPrice price=$gastos_envio}</span>
				 |
				<label>{l s='Shipping cost to merchant' mod='seur'}</label>
				<span>Pendiente</span>
			</div>
			</div>
			<div>
				<div class="module_seur_datas">
					<div class="title_seur">{l s='Order Seur' mod='seur'}</div>
					<div>
						<label>{l s='Tracking shipping' mod='seur'}: {$tracking}</label>
						<span><a href="{$url_tracking}" target="_blank" class="enlace_seur">Enlace</a></span>
					</div>
					<div>
						<label>{l s='Status' mod='seur'}:</label>
						<span>{$estado}</span>
					</div>

				</div>
				<div class="module_seur_num_paq">
					<div>
						<label>{l s='# pack' mod='seur'}</label>
						<input type="text" name="num_bultos" value="{$num_bultos}" {if $labeled || $classic}readonly{/if}>
					</div>
				</div>
				<div class="module_seur_weight">
					<div>
						<label>{l s='Weight' mod='seur'} Kgr.</label>
						<input type="text" name="peso" value="{$peso}" {if $labeled}readonly{/if}>
					</div>
				</div>
				<div class="module_seur_weight">
					<div>
						<label>{l s='CCC' mod='seur'}</label>
						<select name="id_seur_ccc" >
                            {foreach from=$list_ccc item=ccc_item}
								<option {if $id_seur_ccc == $ccc_item['id_seur_ccc']} selected {/if} value="{$ccc_item['id_seur_ccc']}">{$ccc_item['nombre_personalizado']}</option>
                            {/foreach}
						</select>
					</div>
				</div>
				<div class="module_seur_weight">
					<div>
						<label>{l s='Envío asegurado' mod='seur'}</label>
						<select name="insured" id="insured" {if $labeled} disabled="disabled" {/if}>
							<option {if $insured == 0 } selected {/if} value="0">{l s='No' mod='seur'}</option>
							<option {if $insured == 1 } selected {/if} value="1">{l s='Si' mod='seur'}</option>
						</select>
					</div>
				</div>
				{*<div class="module_seur_weight">
					<div>
						<label>{l s='GEOLABEL' mod='seur'}</label>
						<select name="id_seur_ccc" >
							<option {if $geolabel == 0 } selected {/if} value="0">{l s='No' mod='seur'}</option>
							<option {if $geolabel == 1 } selected {/if} value="1">{l s='Si' mod='seur'}</option>
						</select>
					</div>
				</div>*}
				<div class="module_seur_buttons">
					{if !$labeled}
						<a href="javascript:void(0);" class="view_order">{l s='Edit' mod='seur'}</a>
						<button type="submit" class="save_order">{l s='Save' mod='seur'}</button>
					{/if}
					<a class="print_label" href="{$print_label}" >{l s='Print label' mod='seur'}</a>
					{if $send_to_digital_docu}
						<a class="send_dd" href="{$send_digital_docu}" target="_self">{l s='Send to Digital Docu' mod='seur'}</a>
					{/if}
				</div>
			</div>
			<div>
				<div class="module_seur_edit">
					<div class="title_seur">{l s='Editar envío' mod='seur'}</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Fistname' mod='seur'}</label>
						<input type="text" name="firstname" value="{$firstname}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Lastname' mod='seur'}</label>
						<input type="text" name="lastname" value="{$lastname}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Phone' mod='seur'}</label>
						<input type="text" name="phone" value="{$phone}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Phone Mobile' mod='seur'}</label>
						<input type="text" name="phone_mobile" value="{$phone_mobile}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='DNI/NIF' mod='seur'}</label>
						<input type="text" name="dni" value="{$dni}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Comment' mod='seur'}</label>
						<input type="text" name="other" value="{$other}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Address1' mod='seur'}</label>
						<input type="text" name="address1" value="{$address1}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Address2' mod='seur'}</label>
						<input type="text" name="address2" value="{$address2}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Postal Code' mod='seur'}</label>
						<input type="text" name="postcode" value="{$postcode}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='City' mod='seur'}</label>
						<input type="text" name="city" value="{$city}">
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Country' mod='seur'}</label>
						<select name="id_country">
							{foreach from=$countries item=country}
								<option value="{$country.id_country}" {if $country.id_country==$id_country}selected{/if}>{$country.name}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='State' mod='seur'}</label>
						<select name="id_state">
                            {foreach from=$states item=state}
								<option value="{$state.id_state}" {if $state.id_state==$id_state}selected{/if}>{$state.name}</option>
                            {/foreach}
						</select>
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Service Type' mod='seur'}</label>
						<select name="type_service" id="type_service">
							{foreach from=$services_types item=service_type}
								<option value="{$service_type.id_seur_services_type}"
										{if $shipping_type==$service_type.id_seur_services_type }selected{/if}>
									{$service_type.name}
								</option>
							{/foreach}
						</select>
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Product Code' mod='seur'}</label>
						<input type="hidden" id="product_prev" value="{$product_code}">
						<select name="product" id="selectProduct">
							{foreach from=$products key="key" item="item"}
								<option value="{$key}" {if $product_code==$key }selected{/if}>{$item}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-xs-12 col-sm-6">
						<label>{l s='Service Code' mod='seur'}</label>
						<input type="hidden" id="service_prev" value="{$service_code}">
						<select name="service" id="selectService">
							{foreach from=$services key="key" item="item"}
								<option value="{$key}" {if $service_code==$key }selected{/if}>{$item}</option>
							{/foreach}
						</select>
					</div>
					<div style="clear:both"></div>
				</div>
			</div>
		</form>
		</div>
	</div>
