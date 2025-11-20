<div class="panel">
    <div class="panel-heading">
        <i class="icon-search"></i> {l s='Pick-up locations search' mod='seur'}
    </div>
    <div class="panel-body">
        <div class="form-group">
            {l s='Search SEUR database for pick-up locations available for your business.' mod='seur'}
        </div>
        <form method="get" action="{$link->getAdminLink('AdminSeurPickupLocations')}">
            <input type="hidden" name="controller" value="AdminSeurPickupLocations">
            <input type="hidden" name="token" value="{Tools::getAdminTokenLite('AdminSeurPickupLocations')}">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{l s='Postal Code' mod='seur'}</label>
                        <input type="text" class="form-control" name="postal_code" value="{$postal_code|escape:'html':'UTF-8'}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{l s='City' mod='seur'}</label>
                        <input type="text" class="form-control" name="city" value="{$city|escape:'html':'UTF-8'}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{l s='Country' mod='seur'}</label>
                        <select class="form-control" name="country_id" required>
                            <option value="" {if !$country_id}selected{/if}>-- {l s='Select' mod='seur'} --</option>
                            {foreach from=$countries item=country}
                                <option value="{$country.iso_code}" {if $country_id && $country.iso_code == $country_id}selected{/if}>
                                    {$country.name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="icon-search"></i> {l s='Search' mod='seur'}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{if $search_performed}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-list"></i> {l s='Pick-up Locations' mod='seur'}
        </div>
        <div class="panel-body">
            {if $locations|count > 0}
                <div class="alert alert-info">
                    <i class="icon-info-circle"></i> {l s='Found' mod='seur'} {$locations|count} {l s='locations' mod='seur'}
                </div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>{l s='PUDO ID' mod='seur'}</th>
                        <th>{l s='Name' mod='seur'}</th>
                        <th>{l s='Address' mod='seur'}</th>
                        <th>{l s='Schedule' mod='seur'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$locations item=location}
                        <tr>
                            <td>{$location.pudoId|escape:'html':'UTF-8'}</td>
                            <td>{$location.name|escape:'html':'UTF-8'}</td>
                            <td>
                                <a target="_blank" rel="noopener"
                                   href="https://www.google.com/maps/search/?api=1&query={$location.latitude|string_format:"%+.6f"}%2C{$location.longitude|string_format:"%+.6f"}">
                                    {$location.address|escape:'html':'UTF-8'}<br/>
                                    {$location.postal_code|escape:'html':'UTF-8'} {$location.city|escape:'html':'UTF-8'}
                                </a>
                            </td>
                            <td>
                                {foreach from=$location.timetable item=schedule}
                                    {$schedule|escape:'html':'UTF-8'}<br/>
                                {/foreach}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {else}
                <div class="alert alert-warning">
                    <i class="icon-warning"></i> {l s='No pick-up locations found for the specified criteria' mod='seur'}
                </div>
            {/if}
        </div>
    </div>
{/if}
