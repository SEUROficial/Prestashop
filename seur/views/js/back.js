/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

var seur =
    {
        setActiveCarrier: function (jsonData, args) {
            if (jsonData.result == 'OK') {
                var active = '';

                if (jsonData.response) {
                    active = jsonData.response;
                }

                if (active == 1) {
                    $('#activeCarrier_on').prop("checked", true);
                    $('#activeCarrier_off').prop("checked", false);
                }
                else {
                    $('#activeCarrier_off').prop("checked", true);
                    $('#activeCarrier_on').prop("checked", false);
                }
            }
        },

        ajaxCall: function (params, callback, callbackparams) {
            $('#success,#error').hide();
            $('#error,#success').html('');
            $.ajax(
                {
                    url: seur_ajaxcall_url,
                    data: params,
                    type: 'POST',
                    async: true,
                    dataType: 'json',
                    success: function (jsonData) {
                        callback(jsonData, callbackparams);
                    },
                    error: function (jqXHR, exception) {
                        $('.loading').removeClass('loading');
                        $('#error').html(error_response).slideDown();
                        $.scrollTo('#error', 1300, {offset: -150});
                    },
                    complete: function () {
                        $('.loading').removeClass('loading');
                    }

                });
        },
        langs: {},
        addLangs: function (id, fields) {
            seur.langs[id] = fields;
        },
        reloadTimer: null,
        reloadTimerIntervalSeconds: 30
    };


$(document).ready(function () {


    $(document).on('click','input[name="shippingAll"]',function(e) {
        $('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
    })

    $('.select-acciones-masivas').on('change', function () {
        if($(this).find('option:selected').val()=="manifest")
            $('#form-seur2_order').attr('target','_blank');
        else
            $('#form-seur2_order').attr('target','');
    });

    var fields = {};

    $('.adminseurcarrier #selectCarrier').change(function () {
        data = {};
        data['action'] = "setActiveCarrier";
        data['carrier_reference'] = $('#selectCarrier').val();
        dataCallBack = {};
        seur.ajaxCall(data, seur['setActiveCarrier'], dataCallBack);
    });

    $('.adminseurcarrier #selectCarrier').change();


    $("select[name^='select_status_']").on('change', function () {
        $(this).css("background", $(this).find('option:selected').attr("color"));
        $(this).css("color", "#FFFFFF");
    });

    $("#seur_carrier").on('change', function () {
        if($("#seur_carrier").val()!='') {
            $('#seur_confirm_carrier').prop("disabled",false);
        }
        else {
            $('#seur_confirm_carrier').prop("disabled",true);
        }

    });


    $('.adminorders #content').prepend($('#panel-move'));
    $('.adminorders #content').prepend($('.kpi-container'));

    $('#seur_new_order').on('click',function () {
        $("#seur_carrier").change();
        if ($("#seur_carrier").children('option').length == 1) {
            $("#form_order_new_shipping").submit();
        } else {
            $(".seur_opcion").hide();
            $(".seur_form").show();
        }
    });


    $("select[name^='select_status_']").css("background", $(this).find('option:selected').attr("color"));
    $("select[name^='select_status_']").css("color", "#FFFFFF");

    $("select[name^='select_status_']").each(function () {
        $(this).css('background', $(this).find('option:selected').attr('color'))
    });

    $("input[name=SEUR2_SETTINGS_COD]:radio").on('click',function () {
        var params = '';
        params = 'ajax=1&configure=seur&';

        if($(this).attr('id')=='cashDelivery_on'){
            params+= 'action=activateCashonDelivery';
            console.log($(this).attr('id'));
        }
        else{
            params+= 'action=deactivateCashonDelivery';
            console.log($(this).attr('id'));
        }

        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            async: false,
            url: currentIndex + '&token=' + token + '&' + 'rand=' + new Date().getTime(),
            data: params,
            success: function (data) {
            }
        });

    });


    $("#seur_tracking").on('click',function () {
        var params = '&action=updateShippings';
        $('html, body').css("cursor", "wait");
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            async: false,
            url: seur_ajaxcall_url + params,
            data: params,
            dataType: "json",
            error: function (data) {
                $('html, body').css("cursor", "default");
                console.log(data);
                alert("No se han podido actualizar los estados. Inténtelo más tarde");
                location.reload();
            },
            success: function (data) {
                var msg_error = '';
                $('html, body').css("cursor", "default");
                console.log(data);
                if (data.error) {
                    msg_error = data.error.replace(/([#])+/g, "\r\n");
                    msg_error = (data.error.length > 0 ? "\r\n\r\nErrores: " + msg_error + "\r\n\r\n" : "");
                }
                if (data.result == -1) {
                    alert("Ningún envío pendiente de actualizar");
                } else {
                    alert("Se han actualizado "+ data.result +" de "+ data.revisados +" envíos. "+ msg_error +"Si tiene mas pedidos pendientes de actualizar vuelva a pulsar");
                }
                location.reload();
            }
        });
    });

    seur.reloadTimer = setInterval(() => {
        if ($("#seur_tracking").length)
            location.reload();
        else
            clearInterval(seur.reloadTimer);
    }, seur.reloadTimerIntervalSeconds * 1000);

    $("#massive_change").on('change',function () {
       var valor = $("#massive_change").val();

       if(valor == "change_ccc")
            $(".cambio-masivo-ccc").show();
       else
           $(".cambio-masivo-ccc").hide();
    });
});

$(document).on('change',"input[name='type_service']", function(){
    var id = $("input[name='type_service']:checked").val();
   reloadServicesAndProducts(id);
});
$(document).on('change',"select[name='type_service']", function(){
    var id = $("select[name='type_service']").val();
    reloadServicesAndProducts(id);
});

$(document).ready(function(){
    $("input[name='type_service']:checked").trigger('change');
    $("select[name='type_service']").trigger('change');
})

function reloadServicesAndProducts(id){
    var currentIndex = 'index.php?controller=AdminSeurCarrier';
    seur_url_ajax = seur_url_basepath + "/modules/seur/ajax/";

    $.ajax({
        type: 'POST',
        headers: {"cache-control": "no-cache"},
        async: false,
        dataType: 'json',
        url: seur_url_ajax+'getServices.php',
        data : {
            ajax: 1,
            action: "services",
            id_service: id,
        },
        success: function (data) {

            // Limpiamos el select
            $("#selectService").find('option').remove();
            $(data.services).each(function(i, v){ // indice, valor
                var selected = "";
                if(v.id_seur_services == $("#service_prev").val()) {
                    selected = "selected";
                }

                $("#selectService").append('<option value="' + v.id_seur_services + '" '+selected+'>' + v.name + '</option>');
            })
        }
    });

    $.ajax({
        type: 'POST',
        headers: {"cache-control": "no-cache"},
        async: false,
        dataType: 'json',
        url: seur_url_ajax+'getProducts.php',
        data : {
            ajax: 1,
            action: "products",
            id_service: id,
        },
        success: function (data) {

            // Limpiamos el select
            $("#selectProduct").find('option').remove();
            $(data.products).each(function(i, v){ // indice, valor
                var selected = "";
                if(v.id_seur_product == $("#product_prev").val())
                    selected = "selected";

                $("#selectProduct").append('<option value="' + v.id_seur_product + '" '+selected+'>' + v.name + '</option>');
            })

        }
    });
}

$(document).on('click','.view_order',function() {
    $('.module_seur_edit').toggle();
});

$(document).on('click','.new_account_ccc',function() {

    $(".form_datos_cuenta input").val("");
    $(".form_datos_cuenta select").val(0);
    $("#id_seur_ccc").val("0");

});

$(document).on('change',"select[name='insured']", function() {
    var url = "/modules/seur/ajax/saveInsured.php";
    if (labeled==="0") {
        $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            async: false,
            url: url,
            data: {
                ajax: 1,
                action: "saveInsured",
                id_seur_order: id_seur_order,
                insured: $(this).val()
            },
            dataType: "json",
            error: function (data) {
                console.log(data);
            },
            success: function (data) {
            }
        });
    }
});