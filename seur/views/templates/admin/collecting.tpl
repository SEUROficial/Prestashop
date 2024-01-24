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

    <div class="page_seur col-xs-12">
        <div class="box_seur col-xs-12">
            {if $pickupFixed==2}
                <div class="col-xs-12 col-md-12">
                    {l s='Texto de recogida Fija' mod='seur'}
                </div>
            {else}
                <div class="col-xs-12 col-md-6 ccc_border">
                    <div class="col-xs-12 col-md-12 ccc_modo">{l s='NORMAL' mod='seur'}</div>
                    {foreach name='ccc' key='ccc' item='pickup' from=$pickupSolicited}
                    <div class="col-xs-12 col-md-{(12/(count($pickupSolicited)))}">
                        <div class="box_pickup textoCentrado">
                        <form action="" method="POST">
                            <input type="hidden" name="id_seur_ccc" value="{$pickup.id_seur_ccc}">
                            <input type="hidden" name="pickup_frio" value="0">
                            {if count($pickupSolicited)>1}<span class="ccc">{l s='CCC:' mod='seur'} {$ccc}</span>{/if}
                        {if $pickup.date}
                            <img src="{$img_path|escape:'htmlall':'UTF-8'}ico_check.png">
                            <span class="text">{l s='Planned collection' mod='seur'}: {$pickup.num_pickup} </span>
                            <input type="hidden" name="id_pickup" value="{$pickup.id_seur_pickup}">
                            <span class="date">{$pickup.date|date_format:"%d/%m/%Y"}</span>
                            <button type="submit" class='boton cancel' name="cancel_pickup">{l s='Cancel' mod='seur'}</button>
                        {else}
                            <img src="{$img_path|escape:'htmlall':'UTF-8'}ico_cross.png">
                            <span class="text">{l s='Unplanned collection' mod='seur'}</span>
                            <span class="w_date">{l s='If you wish you can request a pickup' mod='seur'}</span>
                            <button type="submit" class='boton create' name="request_pickup">{l s='Request pickup'  mod='seur'}</button>
                        {/if}
                        </form>
                        </div>
                    </div>
                    {/foreach}
                </div>
                <div class="col-xs-12 col-md-6 ccc_border">
                    <div class="col-xs-12 col-md-12 ccc_modo">{l s='FRIO' mod='seur'}</div>
                    {foreach name='ccc' key='ccc' item='pickup' from=$pickupSolicitedFrio}
                    <div class="col-xs-12 col-md-{(12/(count($pickupSolicitedFrio)))}">
                        <div class="box_pickup textoCentrado" >
                            <form action="" method="POST">
                                <input type="hidden" name="id_seur_ccc" value="{$pickup.id_seur_ccc}">
                                <input type="hidden" name="pickup_frio" value="1">
                                {if count($pickupSolicitedFrio)>1}<span class="ccc">{l s='CCC:' mod='seur'} {$ccc}</span>{/if}
                                {if $pickup.date}
                                    <img src="{$img_path|escape:'htmlall':'UTF-8'}ico_check.png">
                                    <span class="text">{l s='Planned collection' mod='seur'}: {$pickup.num_pickup} </span>
                                    <input type="hidden" name="id_pickup" value="{$pickup.id_seur_pickup}">
                                    <span class="date">{$pickup.date|date_format:"%d/%m/%Y"}</span>
                                    <button type="submit" class='boton cancel' name="cancel_pickup">{l s='Cancel' mod='seur'}</button>
                                {else}
                                    <img src="{$img_path|escape:'htmlall':'UTF-8'}ico_cross.png">
                                    <span class="text">{l s='Unplanned collection' mod='seur'}</span>
                                    <span class="w_date">{l s='If you wish you can request a pickup' mod='seur'}</span>
                                    <button type="submit" class='boton create' name="request_pickup">{l s='Request pickup'  mod='seur'}</button>
                                {/if}
                            </form>
                        </div>
                    </div>
                    {/foreach}
                </div>
            {/if}
            <div class="col-xs-12 col-md-12">
                <div class="imgWelcome"><img src="{$img_path|escape:'htmlall':'UTF-8'}img_gest_recogidas.jpg"></div>
            </div>
        </div>
    </div>
