{if $print_labels && $print_labels|@count}
    {addJsDef print_labels=$print_labels}
{else}
    {addJsDef print_labels=array()}
{/if}
<script type="text/javascript">
    var seur_url = '{$url_controller_shipping}&action=print_label&id_order=';

    print_labels.forEach(print_new_tab);

    function print_new_tab(item, index) {
        var win = window.open(seur_url+item, '_blank');
        if(typeof win == 'undefined')
            alert("Desactive el bloqueo de ventanas emergentes");
        else
            win.focus();
    }
</script>