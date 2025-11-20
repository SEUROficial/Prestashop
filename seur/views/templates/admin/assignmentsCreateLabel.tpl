<form id="form_assignments_create_labels" method="post" action="{$url_controller_shipping}&action=print_labels">
    <input type="hidden" id="id_seur_orders" name="id_seur_orders" value="{$id_seur_orders}" />
</form>
<script>
    $(document).ready(function() {
        $('#form_assignments_create_labels').submit();
    });
</script>