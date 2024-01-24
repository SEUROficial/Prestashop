<div style="text-align:center; clear:both">
    <div class="acciones-masivas">
            <span class="texto-acciones-masivas">{l s='Massive acctions' mod='seur'}
                <select class="select-acciones-masivas" name="massive_change" id="massive_change">
                    <option value=""></option>
                    <option value="print_labels">{l s='Print Labels' mod='seur'}</option>
                    <option value="manifest">{l s='Generate Manifest' mod='seur'}</option>
                    <!--option value="change_ccc">{*l s='Cambiar CCC' mod='seur'*}</option>-->
                </select>
                <!--
                <span style="display: none;" class="texto-acciones-masivas cambio-masivo-ccc">{*l s='Cuenta CCC' mod='seur'*}
                    <select class="select-acciones-masivas" name="select_change_ccc" id="select_change_ccc">
                        {* foreach from=$list_ccc item=ccc_item *}
                            <option {* if $id_seur_ccc == $ccc_item['id_seur_ccc']* } selected {* /if *} value="{* $ccc_item['id_seur_ccc']*}">{*$ccc_item['nombre_personalizado']*}</option>
                        {* /foreach*}
                    </select>
                </span>
                -->
                <button class="boton-masivas btn btn-default btn-disk" id="exec-acciones-masivas">{l s='Execute massives' mod='seur'}</button>
            </span>
        <br/>
    </div>
</div>