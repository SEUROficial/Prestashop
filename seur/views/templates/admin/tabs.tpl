{if isset($show_messages) && (count($show_messages))}
    <div class="clearfix"></div>
    <div class="page_seur">
        <div class="bootstrap" id="messages">
            {foreach from=$show_messages item=message}
                {$message}
            {/foreach}
        </div>
    </div>
{/if}
<div class="clearfix"></div>
<div class="page_seur col-xs-2 col-sm-2 pull-right">
{if $controlador=='AdminSeurTracking'}
    <div class="seur_boton_actualiza pull-right">
        <button class="btn btn-default btn-disk pull-right" id="seur_tracking" name="seur_tracking" type="button" value="1">
            Actualizar estados<span class="boton_logo"></span>
        </button>
    </div>
{/if}
</div>
<div class="tabsSeur page_seur col-xs-12 col-sm-10">
    <div class="shipSeur col-xs-12 col-md-3 {if $tabSelect == "shipping"}tabSelected{/if}"><a href="{$url_controller_shipping}"><img src="{$img_path|escape:'htmlall':'UTF-8'}ico_gest_envios.png"><span>{l s='Shipping Manage' mod='seur'}</span></a></div>
    <div class="collectSeur col-xs-12 col-md-3  {if $tabSelect == "collecting"}tabSelected{/if}"><a href="{$url_controller_collecting}"><img src="{$img_path|escape:'htmlall':'UTF-8'}ico_gest_recogidas.png"><span>{l s='Collect Manage' mod='seur'}</span></a></div>
    <div class="trackSeur col-xs-12 col-md-3  {if $tabSelect == "tracking"}tabSelected{/if}"><a href="{$url_controller_tracking}"><img src="{$img_path|escape:'htmlall':'UTF-8'}ico_seg_envios.png"><span>{l s='Tracking packages' mod='seur'}</span></a></div>
<!--    <div class="returnSeur col-xs-12 col-md-3  {*if $tabSelect == "returning"*}tabSelected{*/if*}"><a href="{*$url_controller_returns*}"><img src="{*$img_path|escape:'htmlall':'UTF-8'*}ico_devoluciones.png"><span>{*l s='Returns' mod='seur'*}</span></a></div>-->
</div>
<div class="clearfix"></div>