<div style="text-align:center; clear:both">
    <div class="acciones-masivas">
            <div class="texto-acciones-masivas">{l s='Massive acctions' mod='seur'}
                <select class="select-acciones-masivas" name="massive_change" id="massive_change">
                    <option value=""></option>
                    {foreach from=$seur_active_carriers item=carrier}
                        <option value="assign_carrier_{$carrier.id_carrier}">{l s='Assign to' mod='seur'} {$carrier.name}</option>
                    {/foreach}
                </select>
                <div style="display: none;" class="texto-acciones-masivas create_label_after_assign">
                    <label for="create_label_after_assign">{l s='Create labels after assignation' mod='seur'}</label>
                    <input type="checkbox" id="create_label_after_assign" name="create_label_after_assign" />
                </div>
            </div>

            <div class="boton-acciones-masivas">
                <button class="boton-masivas btn btn-default btn-disk" id="exec-bulk-assign-carrier">{l s='Execute massives' mod='seur'}</button>
            </div>
        <br/>
    </div>
</div>