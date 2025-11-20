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
<div class="box seur_table_block">
	<table class="detail_step_by_step table table-bordered">
		<tbody>
			<tr>
				<td><img src="{$logo|escape:'htmlall':'UTF-8'}" alt="{l s='SEUR' mod='seur'}" border="0" /></td>
				<td>{l s='Reference' mod='seur'}: <b>{$reference|escape:'htmlall':'UTF-8'}</b></td>
				<td>
					{l s='Estate' mod='seur'}: <b>{$seur_order_state|escape:'htmlall':'UTF-8'}</b><br>
                    {if $url_tracking!=''}<b><a href="{$url_tracking}" target="_blank">{l s='Track your order' mod='seur'}</a></b>{/if}
				</td>
			</tr>
		</tbody>
	</table>
</div>

{if $show_returns_site_link}
<div class="seur_returns_site_link" style="justify-content:flex-end; margin: 0 0 16px 0; display: none;">
	<a href="{$returns_site_url}" target="_blank" rel="noopener" style="background-color: white; padding: 2px; border-radius: 4px; border: 2px solid #005baa; display: inline-block;">
		<img src="{$returns_site_link_img}" alt="{l s='SEUR Returns' mod='seur'}" />
	</a>
</div>
	<script type="text/javascript">
		(function() {
			function ready(fn){
				if (document.readyState!=='loading'){
					fn();
				} else {
					document.addEventListener('DOMContentLoaded', fn);
				}
			}

			function inject() {
				try {
					var header = document.querySelector('.page-header, header.page-header, h1.h1, h1');
					if (!header) return;
					var linkDiv = document.querySelector('.seur_returns_site_link');
					if (!linkDiv) return;
					header.parentNode.insertBefore(linkDiv, header);
					linkDiv.style.display = 'flex';
				} catch(e) { /* silencioso */ }
			}
			ready(inject);
		})();
	</script>
{/if}