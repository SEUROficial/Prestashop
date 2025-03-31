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

{if $posEnabled}

<input type="hidden" name="pos_selected" id="pos_selected" value="false" />
<input type="hidden" name="id_cart_seur" id="id_cart_seur" value="{$cookie->id_cart|intval}" />
<input type="hidden" name="id_seur_pos" id="id_seur_pos" value="{$id_seur_pos}" />
<input type="hidden" name="ps_version" id="ps_version" value="{$ps_version|escape:'htmlall':'UTF-8'}" />
<input type="hidden" name="id_seur_RESTO" id="id_seur_RESTO" value="{$seur_resto|escape:'htmlall':'UTF-8'}" />
<input type="hidden" name="seur_map_reload_config" id="seur_map_reload_config" value="{$seur_map_reload_config|escape:'htmlall':'UTF-8'}" />
<input type="hidden" name="seurGoogleApiKey" id="seurGoogleApiKey" value="{$seurGoogleApiKey}">
{if isset($id_address)}
	<input type="hidden" name="id_address_delivery" id="id_address_delivery" value="{$id_address|intval}" />
{/if}

<div id="seurPudoContainer">
	<div id="listedPoint" style="display:none">{l s='Select a pick up point.' mod='seur'}
		<div id="listPoints">
			<h2>{l s='Select a Pick up' mod='seur'}</h2>
		</div>
	</div>
	<div id="noSelectedPointInfo" style="display:none">{l s='Select a pick up point before proceeding with your order.' mod='seur'}<div class="arrow"></div></div>
	<div id="collectionPointInfo">
		<h2>{l s='Pick up point selected' mod='seur'}</h2>
		<div class="title">{l s='Company' mod='seur'}:</div><div class="text" id="post_codeCompany">{l s='Company' mod='seur'}</div>
		<div class="title">{l s='Address' mod='seur'}:</div><div class="text" id="post_codeAddress">{l s='Address' mod='seur'}</div>
		<div class="title">{l s='City' mod='seur'}:</div><div class="text" id="post_codeCity">{l s='City' mod='seur'}</div>
		<div class="title">{l s='Postal Code' mod='seur'}:</div><div class="text" id="post_codePostalCode">{l s='Postal Code' mod='seur'}</div>
		<div class="title">{l s='Phone' mod='seur'}:</div><div class="text" id="post_codePhone">{l s='Phone' mod='seur'}&nbsp;</div>
		<div class="title">{l s='Timetable' mod='seur'}:</div><div class="text" id="post_codeTimetable">{l s='Timetable' mod='seur'}</div>
		<div class="arrow"></div>
	</div>
</div>
{/if}
