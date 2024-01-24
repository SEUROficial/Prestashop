{if $print_labels && $print_labels|@count}
    {addJsDef print_labels=$print_labels}
{else}
    {addJsDef print_labels=array()}
{/if}
<script type="text/javascript">
    var seur_url = '{$url_controller_shipping}&action=print_labels&id_orders='+print_labels.join('_');

    print_new_tab(seur_url);

    function print_new_tab(url) {
        var win = window.open(url, '_blank');
        if(typeof win == 'undefined')
            alert("Desactive el bloqueo de ventanas emergentes");
        else
            win.focus();
    }
</script>