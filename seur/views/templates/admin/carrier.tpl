{include file="./header.tpl"}

<form action="" method="POST">
	<div class="page_seur col-xs-12">
		<input type="hidden" value="{$id_seur_carrier}" name="id_seur_carrier">
		<input type="hidden" value="{$id_seur_ccc}" name="id_seur_ccc">
		<input type="hidden" id="service_prev" value="{$service}">
		<input type="hidden" id="product_prev" value="{$product}">
		<div class="title_seur col-xs-12">{l s='Seur carrier' mod='seur'}</div>
		<div class="box_seur col_xs_12 col-md-12 col-lg-8">
			<div class="col-xs-12 col-md-12 col-lg-4">
				<div class="label_seur uppercase">{l s='Carrier' mod='seur'}</div>
                {if count($carriers)}
				<div class="input_seur">
					<select name="carrier" id="selectCarrier">
						{foreach from=$carriers item="carrier"}
							<option value="{$carrier.id_reference}"
                                    {if $carrier.id_reference==$carrier_reference }selected{/if}>{$carrier.name}</option>
						{/foreach}
					</select>
				</div>
				<div class="note_seur">{l s='Select carrier' mod='seur'}</div>
                {else}
                    {l s='No existen trasportistas adicionales. Es necesrio que añada nuevos transportistas accediendo a' mod='seur'}
					<a href="{$url_carrier}">{l s='gestión de transportistas' mod='seur'}</a>
                {/if}
				<div class="separador"></div>
				<div class="label_seur uppercase">{l s='Shippping type' mod='seur'}</div>
				<div class="">
					<input type="radio" name="type_service" value="1"
                           {if $shipping_type==1 }checked{/if}> {l s='SEUR National' mod='seur'}
					<br>
					<input type="radio" name="type_service" value="2"
                           {if $shipping_type==2 }checked{/if}> {l s='SEUR Puntos Pick Up' mod='seur'}
					<br>
					<input type="radio" name="type_service" value="3"
                           {if $shipping_type==3 }checked{/if}> {l s='SEUR International' mod='seur'}
				</div>
			</div>
			<div class="col-xs-12 col-md-12 col-lg-4">
				<div class="label_seur uppercase">{l s='Service' mod='seur'}</div>
				<div class="input_seur">
					<select name="service" id="selectService">
                        {foreach from=$services key="key" item="item"}
							<option value="{$key}"
									{if $service==$key }selected{/if}>{$item}</option>
                        {/foreach}
					</select>
				</div>
				<div class="note_seur">{l s='Select service' mod='seur'}</div>
				<div class="separador"></div>
				<div class="label_seur uppercase">{l s='Product' mod='seur'}</div>
				<div class="input_seur">
					<select name="product" id="selectProduct">
                        {foreach from=$products key="key" item="item"}
							<option value="{$key}"
                                    {if $product==$key }selected{/if}>{$item}</option>
                        {/foreach}
					</select>
				</div>
				<div class="note_seur">{l s='Select product' mod='seur'}</div>
			</div>
			<div class="col-xs-12 col-md-12 col-lg-4">
				<div class="label_seur uppercase">{l s='Free shipping' mod='seur'}</div>
				<div class="input_seur">
					<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="freeShipping" id="freeShipping_on" value="1" {if $free_shipping}checked="checked"{/if}>
							<label for="freeShipping_on" class="radioCheck">{l s='Yes' mod='seur'}</label>
							<input type="radio" name="freeShipping" id="freeShipping_off" value="0" {if !$free_shipping}checked="checked"{/if}>
							<label for="freeShipping_off" class="radioCheck">{l s='No' mod='seur'}</label>
							<a class="slide-button btn"></a>
					</span>
				</div>
				<br>
				<div class="row">
					<div class="col-xs-6">
						<div class="label_seur">{l s='By weight' mod='seur'}</div>
						<div class="input_seur"><input name="free_shipping_weight" value="{$free_shipping_weight}" class="col-xs-6"><div class="col-xs-6">Kg.</div></div>
					</div>
					<div class="col-xs-6">
						<div class="label_seur">{l s='By price' mod='seur'}</div>
						<div class="input_seur"><input name="free_shipping_price" value="{$free_shipping_price}" class="col-xs-6"><div class="col-xs-6">&euro;</div></div>
					</div>
				</div>
				<div class="separador"></div>
				<div class="label_seur uppercase">{l s='Active' mod='seur'}</div>
				<div class="input_seur">
					<span class="switch prestashop-switch fixed-width-lg">
							<input type="radio" name="activeCarrier" id="activeCarrier_on" value="1"  {if $active}checked="checked"{/if}>
							<label for="activeCarrier_on" class="radioCheck">{l s='Yes' mod='seur'}</label>
							<input type="radio" name="activeCarrier" id="activeCarrier_off" value="0"  {if $active}checked="checked"{/if}>
							<label for="activeCarrier_off" class="radioCheck">{l s='No' mod='seur'}</label>
							<a class="slide-button btn"></a>
					</span>
				</div>
				<div class="note_seur">{l s='Active/Disable carrier' mod='seur'}</div>
			</div>
		</div>
		<div class="xs_hidden md-hidden col-lg-4">
			<img src="{$img_path|escape:'htmlall':'UTF-8'}config_carrier.jpg" style="margin-left:15px; width:100%">
		</div>
	</div>
	<div class="page_seur">
        {if count($carriers)}
		<div class="pull-left" style="margin-right:15px;">
			<button type="submit" name="submitCarrierSeur" class="btn btn-default submitSeur"><i class="icon-save"></i> {l s='Save' mod='seur'}</button>
		</div>
        {/if}
		<div>
			<a href='{$url_list}' class="btn btn-default submitSeur"><i class="icon-close"></i> {l s='Cancel' mod='seur'}</a>
		</div>
	</div>
</form>


