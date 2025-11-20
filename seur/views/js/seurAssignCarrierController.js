$(document).ready(function () {
    var numCheck =  $(".orders").find("input[type=checkbox]").length;
    if(numCheck==0) {
        $(".orders tbody tr").each(function(){
            var id = $(this).find("td").first().html().trim();
            if (id=='') {
                id = $(this).find("td:nth-of-type(2)").first().html().trim();
            }
            if($(this).find("td").length>1) {
                $(this).prepend("<td class='row-selector text-center'><input type='checkbox' name='orderBox[]' value='" + id + "' class='noborder'></td>");
            }
        });

        $(".orders thead tr").prepend("<th class='center fixed-width-xs'><input type='checkbox' name='shippingAll' value='0' class='noborder'></th>");
    }

    $("#exec-bulk-assign-carrier").on('click', function(){
        var create_label = $("#create_label_after_assign").is(":checked") ? 1 : 0;
        $("#form-orders").append("<input type='hidden' name='massive_create_label' value='"+create_label+"'>");
        $("#form-orders").append("<input type='hidden' name='massive_action' value='"+$("#massive_change").val()+"'>");
        $("#form-orders").submit();
    });

    $("#massive_change").on('change',function () {
        var valor = $("#massive_change").val();
        // si valor contiene "assign_" mostrar el select de carriers
        if (valor.indexOf("assign_") !== -1)
            $(".create_label_after_assign").show();
        else
            $(".create_label_after_assign").hide();
    });
});