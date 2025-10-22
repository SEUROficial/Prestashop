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
    <div class="tab_select">{l s='Merchant' mod='seur'}</div>
    <div><a href="{$module_path}&settings=1">{l s='Setting' mod='seur'}</a></div>
</div>

{if isset($success) && strlen($success)}
    <div class="page_seur col-xs-12">
        <div>{$success}</div>
    </div>
{/if}
{if isset($errors) && $errors!=""}
    <div class="page_seur col-xs-12">
        <div class="title_seur seur_error">{l s='Please, before continue fix the next:' mod='seur'}</div>
        <div>{$errors}</div>
    </div>
{/if}

<form action="" method="POST">
    <div class="page_seur col-xs-12">
        <div class="title_seur">{l s='Identification data' mod='seur'}</div>

        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='NIF/CIF' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_MERCHANT_NIF_DNI" value="{$nif_cif}"></div>
                <div class="note_seur">{l s='Add company nif/cif' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='First Name' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_MERCHANT_FIRSTNAME" value="{$firstname}"></div>
                <div class="note_seur">{l s='Add contact first name' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Last name' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_MERCHANT_LASTNAME" value="{$lastname}"></div>
                <div class="note_seur">{l s='Add contact last name' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Company' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_MERCHANT_COMPANY" value="{$company}"></div>
                <div class="note_seur">{l s='Add company name' mod='seur'}</div>
            </div>
        </div>

        <div class="col_xs_12 row list_ccc">
            {foreach from=$lista_ccc item=ccc_item}
                <a {if $id_seur_ccc == $ccc_item['id_seur_ccc']} class="active" {/if} href="{$module_path}&ccc={$ccc_item['id_seur_ccc']}">{l s='Datos de cuenta ccc ' mod='serur'}{$ccc_item['ccc']}</a>
            {/foreach}
            <a class="new_account_ccc">{l s='Añadir Nueva Cuenta CCC' mod='seur'}</a>
            <input type="hidden" id="id_seur_ccc" name="id_seur_ccc" value="{$id_seur_ccc}" >
            <input type="hidden" id="id_shop" name="id_shop" value="{$id_shop}" >
        </div>

        <div class="box_seur col_xs_12 row form_datos_cuenta">
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='CIT' mod='seur'}</div>
                <div class="input_seur"><input name="cit" value="{$cit}"  maxlength="10"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='CCC' mod='seur'}</div>
                <div class="input_seur"><input name="ccc" value="{$ccc}" maxlength="5"></div>
                <div class="note_seur">{l s='Será proporcionado por SEUR (código numérico entre 1 y 5 dígitos)' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">*{l s='Franchise' mod='seur'}</div>
                <div class="input_seur"><input name="franchise" value="{$franchise}" maxlength="2"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">*{l s='Nombre Personalizado' mod='seur'}</div>
                <div class="input_seur"><input name="nombre_personalizado" value="{$nombre_personalizado}" ></div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>

            <hr class="col-xs-12">
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Phone' mod='seur'}</div>
                <div class="input_seur"><input name="phone" value="{$phone}"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Email' mod='seur'}</div>
                <div class="input_seur"><input name="email" value="{$email}"></div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>
            <hr class="col-xs-12">

            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Type of road' mod='seur'}</div>
                <div class="input_seur"><select name="street_type">
                        <option value="">---</option>

                        <option value="AUT" id="AUT" {if ($street_type=="AUT")}selected="selected"{/if}>AUTOVIA</option>
                        <option value="AVD" id="AVD" {if ($street_type=="AVD")}selected="selected"{/if}>AVENIDA</option>
                        <option value="CL" id="CL"   {if ($street_type=="CL")}selected="selected"{/if}>CALLE</option>
                        <option value="CRT" id="CRT" {if ($street_type=="CRT")}selected="selected"{/if}>CARRETERA</option>
                        <option value="CTO" id="CTO" {if ($street_type=="CTO")}selected="selected"{/if}>CENTRO COMERCIAL</option>
                        <option value="EDF" id="EDF" {if ($street_type=="EDF")}selected="selected"{/if}>EDIFICIO</option>
                        <option value="ENS" id="ENS" {if ($street_type=="ENS")}selected="selected"{/if}>ENSANCHE</option>
                        <option value="GTA" id="GTA" {if ($street_type=="GTA")}selected="selected"{/if}>GLORIETA</option>
                        <option value="GRV" id="GRV" {if ($street_type=="GRV")}selected="selected"{/if}>GRAN VIA</option>
                        <option value="PSO" id="PSO" {if ($street_type=="PSO")}selected="selected"{/if}>PASEO</option>
                        <option value="PZA" id="PZA" {if ($street_type=="PZA")}selected="selected"{/if}>PLAZA</option>
                        <option value="POL" id="POL" {if ($street_type=="POL")}selected="selected"{/if}>POLIGONO INDUSTRIAL</option>
                        <option value="RAM" id="RAM" {if ($street_type=="RAM")}selected="selected"{/if}>RAMBLA</option>
                        <option value="RDA" id="RDA" {if ($street_type=="RDA")}selected="selected"{/if}>RONDA</option>
                        <option value="ROT" id="ROT" {if ($street_type=="ROT")}selected="selected"{/if}>ROTONDA</option>
                        <option value="TRV" id="TRV" {if ($street_type=="TRV")}selected="selected"{/if}>TRAVESIA</option>
                        <option value="URB" id="URB" {if ($street_type=="URB")}selected="selected"{/if}>URBANIZACION</option>
                    </select>
                    </div>
                <div class="note_seur">{l s='Add type of road' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Road name' mod='seur'}</div>
                <div class="input_seur"><input name="street_name" value="{$street_name}"></div>
                <div class="note_seur">{l s='Add road name' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">*{l s='Postal code' mod='seur'}</div>
                <div class="input_seur"><input name="post_code" value="{$post_code}"></div>
                <div class="note_seur">{l s='Add postal code' mod='seur'}</div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='City' mod='seur'}</div>
                <div class="input_seur"><input name="town" value="{$town}"></div>
                <div class="note_seur">{l s='Add city' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='State' mod='seur'}</div>
                <div class="input_seur"><input name="state" value="{$state}"></div>
                <div class="note_seur">{l s='Add state' mod='seur'}</div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Country' mod='seur'}</div>
                <div class="input_seur"><select name="country">
                        <option value=""></option>
                        <option value="ES" {if ($country=="ES")}selected="selected"{/if}>ESPAÑA</option>
                        <option value="PT" {if ($country=="PT")}selected="selected"{/if}>PORTUGAL</option>
                    </select>
                </div>
                <div class="note_seur">{l s="Add country" mod='seur'}</div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3 row">
                <div class="col-xs-6 col-lg-3">
                    <div class="label_seur">*{l s='Number' mod='seur'}</div>
                    <div class="input_seur"><input name="street_number" value="{$street_number}"></div>
                </div>
                <div class="col-xs-6 col-lg-3">
                    <div class="label_seur">{l s='Stair' mod='seur'}</div>
                    <div class="input_seur"><input name="staircase" value="{$staircase}"></div>
                </div>
                <div class="col-xs-6 col-lg-3">
                    <div class="label_seur">{l s='Floor' mod='seur'}</div>
                    <div class="input_seur"><input name="floor" value="{$floor}"></div>
                </div>
                <div class="col-xs-6 col-lg-3">
                    <div class="label_seur">{l s='Door' mod='seur'}</div>
                    <div class="input_seur"><input name="door" value="{$door}"></div>
                </div>
            </div>
            <div class="col-xs-6 col-lg-3">
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>
        </div>

        <div class="title_seur col-xs-12">{l s='API Connection data' mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='client ID' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_API_CLIENT_ID" value="{$api_client_id}"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='client secret' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_API_CLIENT_SECRET" value="{$api_client_secret}"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='username' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_API_USERNAME" value="{$api_username}"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='password' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_API_PASSWORD" type='password' value="{$api_password}"></div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>
        </div>

        <div class="title_seur col-xs-12">{l s='Empresa registrada como Importadora o Exportadora' mod='seur'}</div>
        <div class="box_seur col_xs_12 row">
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='rEORI' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_R_EORI" value="{$rEORI}"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='dEORI' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_D_EORI" value="{$dEORI}"></div>
            </div>
            <div class="col-xs-12 col-md-6 col-lg-3">
                <div class="label_seur">* {l s='Taric' mod='seur'}</div>
                <div class="input_seur"><input name="SEUR2_TARIC" value="{$taric}"></div>
            </div>
            <div class="xs-hidden md-hidden col-lg-12 sin-padding">
            </div>
        </div>

        <div class='clearfix'>
            <button type="submit" name="submitMerchantSeur" class="btn btn-default submitSeur"><i
                        class="icon-save"></i> {l s='Save' mod='seur'}</button>
        </div>

        <script>
            $(document).ready(function(){
                $(".bootstrap.panel").hide();
            });
        </script>
    </div>
</form>


