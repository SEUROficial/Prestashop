$(document).ready(function () {
    var numCheck =  $(".seur2_order").find("input[type=checkbox]").length;
    if(numCheck==0) {
        $(".seur2_order tbody tr").each(function(){
            var id = $(this).find("td").first().html().trim();
            if (id=='') {
                id = $(this).find("td:nth-of-type(2)").first().html().trim();
            }
            if($(this).find("td").length>1) {
                $(this).prepend("<td class='row-selector text-center'><input type='checkbox' name='shippingBox[]' value='" + id + "' class='noborder'></td>");
            }
        });

        $(".seur2_order thead tr").prepend("<th class='center fixed-width-xs'><input type='checkbox' name='shippingAll' value='0' class='noborder'></th>");
    }
    $("#exec-acciones-masivas").on('click', function(){
        $("#form-seur2_order").append("<input type='hidden' name='massive_change_ccc' value='"+$("#select_change_ccc").val()+"'>");
        $("#form-seur2_order").append("<input type='hidden' name='massive_action' value='"+$("#massive_change").val()+"'>");
        $("#form-seur2_order").submit();
    });
});