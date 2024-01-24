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

{*
{if isset($email_warning_message)}
	{include file=$smarty.const._PS_MODULE_DIR_|escape:'htmlall':'UTF-8'|cat:'seur/views/templates/admin/warning_message.tpl' seur_warning_message=$email_warning_message|escape:'htmlall':'UTF-8'}
{/if}
*}
<div class="welcome_seur">
    <div class="col-xs-12 col-md-6">
        <div class="imgWelcome"><img src="{$img_path|escape:'htmlall':'UTF-8'}img_welcome.jpg"></div>
    </div>

    <div class="col-xs-12 col-md-6">
        <div style="height:100%">
        <div class="titleWelcome">{l s='Welcome Seur Module' mod='seur'}</div>
        <div class="logosWelcome row">
            <div class="col-md-6">
                <img src="{$img_path|escape:'htmlall':'UTF-8'}logo_seur.png" alt="{l s='Seur' mod='seur'}"
                     title="{l s='Seur' mod='seur'}" border="0"/>
            </div>
            <div class="col-md-6">
                <img src="{$img_path|escape:'htmlall':'UTF-8'}logo_prestashop.png" alt="{l s='Prestashop' mod='seur'}"
                     title="{l s='Prestashop' mod='seur'}" border="0"/>
            </div>
        </div>
        <div class="txtWelcome">
           {l s='Welcome text' mod='seur'}
        </div>
        <div class="LinksWelcome">
            {l s='Phone contact:' mod='seur'}
            <div class="phoneNumber">{l s='Phone number' mod='seur'}</div>
{*            <p><a href="http://www.seur.com/seur-esolutions.do"
                  target="_blank">{l s='http://www.seur.com/seur-esolutions.do' mod='seur'}</a></p>*}
            <br>
                <a href="{$module_path}&merchant=1"  class="button_seur">{l s='I am a customer' mod='seur'}</a>
        </div>
        </div>
</div>

    <script>
        $(document).ready(function(){
            $(".bootstrap.panel").hide();
        });
    </script>
