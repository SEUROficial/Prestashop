/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @copyright 2007-2015 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


var bodyid;
var ps_version_seur;
var displayCarriers = false;
var id_seur_pos;

var id_address_delivery;
var collectionPointInfo;
var noSelectedPointInfo;
var listPoints;

var carrierTable;
var carrierTableInput;
var carrierTableInputContainer;

var currentCarrierId;
var map;
var gMaps;

var id_seur_RESTO_array;

$(document).ready(function()
{
	$('input[type="radio"][name="id_carrier"]').on('change', function () {
		initSeurCarriers();
	});

	$('.delivery-option input[type="radio"]').on('change', function (event ) {
		initSeurCarriers();
	});

	$('#cgv').on('change', function()
	{
		check_reembolsoSeur();
	});
	$('#recyclable').on('change', function()
	{
		check_reembolsoSeur();
	});
	$('#gift').on('change', function()
	{
		check_reembolsoSeur();
	});
	$('#id_address_delivery').on('change', function()
	{
		check_reembolsoSeur();
	});
});

function initSeurCarriers()
{
	displayCarriers = false;
	assignGlobalVariables();

	if (displayCarriers)
	{
		initSeurMaps();
	}
	else
	{
		if(ps_version_seur != 'ps7'){
			$('div.seurMapContainer').remove();
			$('#noSelectedPointInfo').remove();
			$('#collectionPointInfo').remove();
			$('#listPoints').remove();
		}
		else{
			$('div.seurMapContainer').fadeOut();
			$('#noSelectedPointInfo').fadeOut();
			$('#collectionPointInfo').fadeOut();
			$('#listPoints').fadeOut();

		}
	}
}

function assignGlobalVariables()
{
	bodyid = $('body').attr('id');
	ps_version_seur = $('#ps_version').val();

	if(ps_version_seur == null)
	{
		ps_version_seur = 'ps5';
	}

	id_seur_pos = $('#id_seur_pos').val();

	if ( $('#id_address_delivery').length ) {
		id_address_delivery = $('#id_address_delivery');
	}
	if ( $('#opc_id_address_delivery').length ) {
		id_address_delivery = $('#opc_id_address_delivery');
	}
	if ( typeof AppOPC !== typeof undefined && $('#delivery_id').length ) {
		id_address_delivery = $('#delivery_id');
	}
	collectionPointInfo = $('#collectionPointInfo');
	noSelectedPointInfo = $('#noSelectedPointInfo');
	listPoints = $('#listPoints');

	carrierTable = (ps_version_seur == 'ps4' ? $('#carrierTable') : $('#carrier_area'));
	carrierTableInput = (ps_version_seur == 'ps4' ? $('input[name="id_carrier"]') : $('.delivery_option_radio'));
	carrierTableInputContainer = (ps_version_seur == 'ps4' ? '#carrierTable' : '#carrier_area .delivery_options');

	$('#pos_selected').val('false');

	if(ps_version_seur == 'ps4')
	{
		if($('#carrierTable').length != 0 && seurCarrierDisplayed(id_seur_pos))
		{
			displayCarriers = true;
		}
	}
	else if(ps_version_seur == 'ps5')
	{
		if($('.delivery_options').length != 0 && seurCarrierDisplayed(id_seur_pos))
		{
			displayCarriers = true;
		}
	}
	else
	{
		if($('.delivery-options').length != 0 && seurCarrierDisplayed(id_seur_pos))
		{
			displayCarriers = true;
		}
	}

	map = $('<div />').attr('id', 'seurMap').attr('init', 'false');

	if ($('#id_seur_RESTO').length > 0)
	{
		id_seur_RESTO = $('#id_seur_RESTO');
		id_seur_RESTO_array = id_seur_RESTO.val().split(',');
	}
}

function check_reembolsoSeur()
{
	currentCarrierId = $('input[type="radio"]:checked', $(carrierTableInputContainer)).val();
	if (typeof currentCarrierId !== 'undefined')
		currentCarrierId = currentCarrierId.replace(',', '');
	if(currentCarrierId == id_seur_pos){
		if(map.attr('init') == 'false'){ map.removeClass('showmap').attr('init', 'true').css('position', 'absolute'); }
		if(!map.hasClass('showmap')){ map.addClass('showmap').css('position', 'relative'); }
		$('#reembolsoSEUR').hide();
	}
	else if(id_seur_RESTO_array.indexOf(""+currentCarrierId) > -1)
	{
		$('#reembolsoSEUR').show();
		setTimeout(function(){ $('#reembolsoSEUR').show(); }, 500);
		setTimeout(function(){ $('#reembolsoSEUR').show(); }, 1000);
		setTimeout(function(){ $('#reembolsoSEUR').show(); }, 3000);
	}
	else
	{
		$('#reembolsoSEUR').hide();
	}
}

function getQuerystring(key, default_)
{
	if(default_==null){ default_=""; }
	key = key.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regex = new RegExp("[\\?&]"+key+"=([^&#]*)");
	var qs = regex.exec(window.location.href);
	if(qs == null){ return default_; }else{ return qs[1]; }
}

function seurCarrierDisplayed(id_seur_pos)
{
	var displayed = false;

	if (ps_version_seur == 'ps4')
	{
		$('#carrierTable input[type="radio"]').each(function()
		{
			if(Number($(this).val().replace(/[^0-9]+/g, '')) == Number(id_seur_pos))
			{
				displayed = true;
				return;
			}
		});
	}
	else if (ps_version_seur == 'ps5') {
		$('.delivery_options input[type="radio"]').each(function () {
			if (Number($(this).val().replace(/[^0-9]+/g, '')) == Number(id_seur_pos)) {
				displayed = true;
				return;
			}
		});
	}
	else{
		$('.delivery-options input[type="radio"]').each(function () {
			if (Number($(this).val().replace(/[^0-9]+/g, '')) == Number(id_seur_pos)) {
				displayed = true;
				return;
			}
		});

	}

	return displayed;
}

function initSeurMaps()
{
	if(displayCarriers){
		$('span', map).css({ 'line-height' : '64px', 'font-size' : '50px' });

		map = $('<div />').addClass('seurMapContainer').html(map);

		if(ps_version_seur == 'ps4')
		{
			map.insertAfter($('#carrierTable'));
		}
		else if(ps_version_seur == 'ps5')
		{
			var pNavTmp = $("#carrier_area div.delivery_options_address:first");
			map.insertAfter(pNavTmp);
		}
		else if(ps_version_seur == 'ps7')
		{
			var pNavTmp = $(".delivery-options");
			map.insertAfter(pNavTmp);
		}

		if(ps_version_seur != 'ps7') {
			noSelectedPointInfo.fadeOut();
			collectionPointInfo.fadeOut();
			noSelectedPointInfo.insertAfter(map);
			collectionPointInfo.insertAfter(map);
		}
		else{
			noSelectedPointInfo.insertAfter(map);
			noSelectedPointInfo.fadeOut();
			collectionPointInfo.insertAfter(map);
			collectionPointInfo.fadeOut();

		}

		gMapOptions = {
			zoom: 13,
			center: new google.maps.LatLng(0,0),
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			noClear: true,
			disableDefaultUI: true,
			panControl:true,
			zoomControl:true,
			mapTypeControl:true,
			scaleControl:true,
			streetViewControl:true,
			overviewMapControl:true,
			rotateControl:true,
			keyboardShortcuts: false,
			disableDoubleClickZoom: false,
			draggable: true,
			scrollwheel: true,
			draggableCursor: 'move',
			draggingCursor: 'move',
			mapTypeControl: true,
			navigationControl: true,
			streetViewControl: true,
			navigationControlOptions: {
				position: google.maps.ControlPosition.TOP_RIGHT,
				style: google.maps.NavigationControlStyle.ANDROID
			},
			scaleControl: false,
			scaleControlOptions: {
				position: google.maps.ControlPosition.BOTTOM_LEFT,
				style: google.maps.ScaleControlStyle.SMALL
			}
		};


		currentMarker = null;


		if (gMaps == undefined) {
			gMaps = new google.maps.Map(document.getElementById('seurMap'), gMapOptions);
		}

		userMarker = new google.maps.Marker({
			position: null,
			map: gMaps,
			title: 'Direcci\u00f3n pr\u00f3xima a usted',
			icon: baseDir + 'modules/seur/views/img/user.png',
			cursor: 'default',
			draggable: false
		});

		// if one step checkout and ps5
		if((bodyid == 'order-opc') && ps_version_seur == 'ps5')
		{
			carrier_value = $('input[type="radio"].delivery_option_radio').attr('name');

			str = carrier_value;
			cad_string = str.substring(str.indexOf('[') + 1,str.indexOf(']'));
			// set value of onchange
			$('.delivery_option_radio').each(function(){
				carrier_value = $(this).attr('value');
				$(this).on('change', null, function(){
					updateOneStepCloser();
				});
			});
			// add reload the page
			$('#id_address_delivery').attr('onchange','updateAddressesDisplay(); updateAddressSelectionOneStep();');
		}

		// if ps7
		if(ps_version_seur == 'ps7')
		{
			carrier_value = $('.delivery-options input[type="radio"]').attr('name');

			str = carrier_value;
			cad_string = str.substring(str.indexOf('[') + 1,str.indexOf(']'));
			// set value of onchange
			$('.delivery_option').each(function(){
				carrier_value = $(this).attr('value');
				$(this).on('change', null, function(){
					updateOneStepCloser();
				});
			});
			// add reload the page
			$('#id_address_delivery').attr('onchange','updateAddressesDisplay(); updateAddressSelectionOneStep();');
		}

		id_carrier = "";
		var id_seur_pos = $('#id_seur_pos').val();

		first_time_id = id_seur_pos;

		if(map.attr('init') == 'false' )
		{ map.attr('init','true').css('position','absolute'); }

		if ((ps_version_seur != 'ps7' && $('input[type="radio"]').is(':checked')) || (ps_version_seur == 'ps7' && $('.delivery-options input[type="radio"]').is(':checked')))
		{
			if(ps_version_seur != 'ps7')
			{
				currentCarrierId = $('input[type="radio"]:checked', $(carrierTableInputContainer)).val();
			}
			else
			{
				currentCarrierId = $('.delivery-options input[type="radio"]:checked').val();
			}

			currentCarrierId = currentCarrierId.replace(",", "");

			if(currentCarrierId == id_seur_pos )
			{
				printMap();

			}
			else
			{
				if(ps_version_seur != 'ps7'){
					$('div.seurMapContainer').remove();
					$('#noSelectedPointInfo').remove();
					$('#collectionPointInfo').remove();
					$('input[name=processCarrier]').removeAttr('disabled');
					$('button[name=processCarrier]').removeAttr('disabled');
				}
				else{
					$('div.seurMapContainer').fadeOut();
					$('#noSelectedPointInfo').fadeOut();
					$('#collectionPointInfo').fadeOut();
					$('input[name=processCarrier]').removeAttr('disabled');
					$('button[name=processCarrier]').removeAttr('disabled');

				}
			}
		}
	}
};

function saveCollectorPoint(id_cart, post_codeData )
{
	var chosen_address_delivery = id_address_delivery.val();

	if (!(chosen_address_delivery in seur_token_))
		var current_token = null;
	else
		var current_token = seur_token_[chosen_address_delivery];

	$.ajax({
		url: baseDir+'modules/seur/ajax/getPickupPointsAjax.php',
		type: 'GET',
		data: {
			savepos : true,
			id_cart : encodeURIComponent(id_cart),
			id_seur_pos : encodeURIComponent(post_codeData.codCentro),
			company : encodeURIComponent(post_codeData.company),
			address : encodeURIComponent(post_codeData.address),
			city : encodeURIComponent(post_codeData.city),
			post_code : encodeURIComponent(post_codeData.post_code),
			phone : encodeURIComponent(post_codeData.phone),
			timetable : encodeURIComponent(post_codeData.timetable),
			chosen_address_delivery : chosen_address_delivery,
			token : encodeURIComponent(current_token)
		},
		dataType: 'json',
		async: false,
		success: function(data)
		{
			$('#pos_selected').val("true");
			$('#opc_payment_methods').show();
			$('input[name="processCarrier"]').removeAttr('disabled');
			$('button[name="processCarrier"]').removeAttr('disabled');
		},
		error: function(xhr, ajaxOptions, thrownError)
		{
			$('#pos_selected').val('false');
		}
	});
}

function updateOneStepCloser()
{
	var recyclablePackage = 0;
	var cgvChecked = 0;
	var gift = 0;
	var giftMessage = '';
	var delivery_option_radio = $('.delivery_option_radio');
	var delivery_option_params = '&';
	$.each(delivery_option_radio, function(i)
	{
		if($(this).prop('checked')) delivery_option_params += $(delivery_option_radio[i]).attr('name') + '=' + encodeURIComponent($(delivery_option_radio[i]).val()) + '&';
	});
	if(delivery_option_params == '&') delivery_option_params = '&delivery_option=&';
	if($('input#recyclable:checked').length) recyclablePackage = 1;
	if($('input#gift:checked').length)
	{
		gift = 1;
		giftMessage = encodeURIComponent($('#gift_message').val());
	}
	if($('input#cgv:checked').length) cgvChecked = 1;
	$('#opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeOut('slow');
	$.ajax({
		type: 'POST',
		headers: { "cache-control": "no-cache" },
		url: orderOpcUrl + '?rand=' + new Date().getTime(),
		async: true,
		cache: false,
		dataType : "json",
		data: 'ajax=true&method=updateCarrierAndGetPayments' + delivery_option_params +'checked='+encodeURIComponent(cgvChecked)+
			'&recyclable=' + encodeURIComponent(recyclablePackage) + '&gift=' + encodeURIComponent(gift) +
			'&gift_message=' + encodeURIComponent(giftMessage) + '&token=' + encodeURIComponent(static_token),
		success: function(jsonData){
			if (jsonData.hasError)
			{
				var errors = '';
				for(var error in jsonData.errors){
					//IE6 bug fix
					if(error !== 'indexOf') errors += jsonData.errors[error] + "\n";
				}
				alert(errors);
			}
			else
			{
				updateCartSummary(jsonData.summary);
				updatePaymentMethods(jsonData);
				updateHookShoppingCart(jsonData.summary.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.summary.HOOK_SHOPPING_CART_EXTRA);
				$('#opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeOut('slow');
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			if(textStatus !== 'abort') alert("TECHNICAL ERROR: unable to save carrier \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus); // @TODO make translatable text
			$('#opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeOut('slow');
		}
	});
}

function updateAddressSelectionOneStep()
{
	var idAddress_delivery = ($('#opc_id_address_delivery').length == 1 ? $('#opc_id_address_delivery').val() : $('#id_address_delivery').val());
	var idAddress_invoice = ($('#opc_id_address_invoice').length == 1 ? $('#opc_id_address_invoice').val() : ($('#addressesAreEquals:checked').length == 1 ? idAddress_delivery : ($('#id_address_invoice').length == 1 ? $('#id_address_invoice').val() : idAddress_delivery)));
	$('#opc_account-overlay').fadeIn('slow');
	$('#opc_delivery_methods-overlay').fadeIn('slow');
	$('#opc_payment_methods-overlay').fadeIn('slow');
	$.ajax({
		type: 'POST',
		headers: { "cache-control": "no-cache" },
		url: orderOpcUrl + '?rand=' + new Date().getTime(),
		async: true,
		cache: false,
		dataType : "json",
		data: 'ajax=true&method=updateAddressesSelected&id_address_delivery=' + encodeURIComponent(idAddress_delivery) +
			'&id_address_invoice=' + encodeURIComponent(idAddress_invoice) + '&token=' + encodeURIComponent(static_token),
		success: function(jsonData)
		{
			if(jsonData.hasError)
			{
				var errors = '';
				for(var error in jsonData.errors){
					//IE6 bug fix
					if(error !== 'indexOf') errors += jsonData.errors[error] + "\n";
				}
				alert(errors);
			}
			else
			{
				// Update all product keys with the new address id
				$('#cart_summary .address_'+deliveryAddress).each(function(){
					$(this).removeClass('address_'+deliveryAddress).addClass('address_'+idAddress_delivery);
					$(this).attr('id', $(this).attr('id').replace(/_\d+$/, '_'+idAddress_delivery));
					if($(this).find('.cart_unit span').length > 0 && $(this).find('.cart_unit span').attr('id').length > 0){
						$(this).find('.cart_unit span').attr('id', $(this).find('.cart_unit span').attr('id').replace(/_\d+$/, '_'+idAddress_delivery));
					}
					if($(this).find('.cart_total span').length > 0 && $(this).find('.cart_total span').attr('id').length > 0){
						$(this).find('.cart_total span').attr('id', $(this).find('.cart_total span').attr('id').replace(/_\d+$/, '_'+idAddress_delivery));
					}
					if($(this).find('.cart_quantity_input').length > 0 && $(this).find('.cart_quantity_input').attr('name').length > 0){
						var name = $(this).find('.cart_quantity_input').attr('name')+'_hidden';
						$(this).find('.cart_quantity_input').attr('name', $(this).find('.cart_quantity_input').attr('name').replace(/_\d+$/, '_'+idAddress_delivery));
						if($(this).find('[name='+name+']').length > 0) $(this).find('[name='+name+']').attr('name', name.replace(/_\d+_hidden$/, '_'+idAddress_delivery+'_hidden'));
					}
					if($(this).find('.cart_quantity_delete').length > 0 && $(this).find('.cart_quantity_delete').attr('id').length > 0){
						$(this).find('.cart_quantity_delete').attr('id', $(this).find('.cart_quantity_delete').attr('id').replace(/_\d+$/, '_'+idAddress_delivery)).attr('href', $(this).find('.cart_quantity_delete').attr('href').replace(/id_address_delivery=\d+&/, 'id_address_delivery='+idAddress_delivery+'&'));
					}
					if($(this).find('.cart_quantity_down').length > 0 && $(this).find('.cart_quantity_down').attr('id').length > 0){
						$(this).find('.cart_quantity_down').attr('id', $(this).find('.cart_quantity_down').attr('id').replace(/_\d+$/, '_'+idAddress_delivery)).attr('href', $(this).find('.cart_quantity_down').attr('href').replace(/id_address_delivery=\d+&/, 'id_address_delivery='+idAddress_delivery+'&'));
					}
					if($(this).find('.cart_quantity_up').length > 0 && $(this).find('.cart_quantity_up').attr('id').length > 0){
						$(this).find('.cart_quantity_up').attr('id', $(this).find('.cart_quantity_up').attr('id').replace(/_\d+$/, '_'+idAddress_delivery)).attr('href', $(this).find('.cart_quantity_up').attr('href').replace(/id_address_delivery=\d+&/, 'id_address_delivery='+idAddress_delivery+'&'));
					}
				});
				// Update global var deliveryAddress
				deliveryAddress = idAddress_delivery;
				if(window.ajaxCart !== undefined)
				{
					$('#cart_block_list dd, #cart_block_list dt').each(function()
					{
						if(typeof($(this).attr('id')) != 'undefined') $(this).attr('id', $(this).attr('id').replace(/_\d+$/, '_' + idAddress_delivery));
					});
				}
				updateCarrierListOneStep(jsonData.carrier_data);
				updatePaymentMethods(jsonData);
				updateCartSummary(jsonData.summary);
				updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
				if($('#gift-price').length == 1) $('#gift-price').html(jsonData.gift_price);
				$('#opc_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeOut('slow');
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			if(textStatus !== 'abort') alert("TECHNICAL ERROR: unable to save adresses \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus); // @TODO make translatable fields
			$('#opc_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeOut('slow');
		}
	});

}

function updateUserMapPosition()
{
	usrAddress = getUserAddress(id_address_delivery.val() );
	geocoder = new google.maps.Geocoder();
	geocoder.geocode({ 'address': usrAddress}, function(result, status)
	{
		if (status == google.maps.GeocoderStatus.OK)
		{
			gMaps.setCenter(result[0].geometry.location);
			userMarker.setPosition(result[0].geometry.location );
		}
		else alert('updateUserMapPosition id_address: '+id_address_delivery.val()+' Error address in update the map: ' + status); // @TODO make translatable text
	});
}

function updateCarrierListOneStep(json)
{
	var html = json.carrier_block;
	// @todo  check with theme 1.4
	$('#carrier_area').replaceWith(html);
	bindInputs();
	/* update hooks for carrier module */
	$('#HOOK_BEFORECARRIER').html(json.HOOK_BEFORECARRIER);
	if(bodyid == 'order-opc' && ps_version_seur == 'ps5' && $('.delivery_option_radio').length > 0)
	{
		carrier_value = $('.delivery_option_radio').attr('name');
		str = carrier_value;
		cad_string = str.substring(str.indexOf('[') + 1,str.indexOf(']'));
		// set value of onchange
		$('.delivery_option_radio').each(function()
		{
			carrier_value = $(this).attr('value');
			$(this).on('change', null, function()
			{
				updateOneStepCloser();
			});
		});
	}
	if(map.attr('init') == 'false')
	{
		map.removeClass('showmap').attr('init','true').css('position','absolute');
	}
	if($('input[type="radio"]').is(':checked'))
	{
		currentCarrierId = $('input[type="radio"]:checked', $(carrierTableInputContainer)).val();
		currentCarrierId = currentCarrierId.replace(",", "");
		if(currentCarrierId == id_seur_pos )
		{
			(!map.hasClass("showmap") ? map.addClass('showmap').css('position','relative') : "" );
			if($('#pos_selected').val() == "false")
			{
				$('input[name="processCarrier"]').attr("disabled","disabled");
				$('button[name="processCarrier"]').attr("disabled","disabled");
				$('#opc_payment_methods').hide();
				noSelectedPointInfo.fadeIn();
			}
			if($('#pos_selected').val() == "true")
			{
				$('input[name="processCarrier"]').removeAttr("disabled");
				$('button[name="processCarrier"]').removeAttr("disabled");
				noSelectedPointInfo.fadeOut();
				$('#opc_payment_methods').show();
			}
			($('#reembolsoSEUR').is(":visible") ? $('#reembolsoSEUR').fadeOut() : "" );
		}
		else
		{
			map.removeClass('showmap').css('position','absolute');
			if(ps_version_seur != 'ps7') {
				noSelectedPointInfo.remove();
				collectionPointInfo.remove();
			}
			else{
				noSelectedPointInfo.fadeOut();
				collectionPointInfo.fadeOut();
			}
			$('#opc_payment_methods').show();
		}
	}
}

function getUserAddress(idAddress)
{
	if (!(idAddress in seur_token_))
		var current_token = null;
	else
		var current_token = seur_token_[idAddress];

	address = "";
	$.ajax({
		url: baseDir+'modules/seur/ajax/getPickupPointsAjax.php',
		type: 'GET',
		data: {
			usr_id_address : encodeURIComponent(idAddress),
			token : encodeURIComponent(current_token)
		},
		dataType: 'html',
		async: false,
		success: function(addr)
		{
			address = addr;
		},
		error: function(xhr, ajaxOptions, thrownError)
		{

		}
	});
	return address;
}

// Returns a new map with the customer address
function printMap()
{
	usrAddress = getUserAddress(id_address_delivery.val());
	points = getSeurCollectionPoints();

	geocoder = new google.maps.Geocoder();
	geocoder.geocode({ 'address': usrAddress}, function(result, status)
	{
		if(status == google.maps.GeocoderStatus.OK )
		{
			gMaps.setCenter(result[0].geometry.location );
			userMarker.setPosition(result[0].geometry.location );
			$('.seurMapContainer').css({'position' : 'relative', 'left' : 'inherit'});
			printCollectorPoints(points);

			check_reembolsoSeur();

			listPoints.appendTo('.seurMapContainer');
			listPoints.fadeIn();
			$('#seurPudoContainer').appendTo('.seurMapContainer');

			(!map.hasClass("showmap") ? map.addClass('showmap').css('position','relative') : "" );

			if ($('#pos_selected').val() == "false"){
				$('input[name="processCarrier"]').attr("disabled","disabled");
				$('button[name="processCarrier"]').attr("disabled","disabled");
				$('#opc_payment_methods').hide();
				noSelectedPointInfo.fadeIn();
			}
			if ($('#pos_selected').val() == "true")
			{
				$('input[name="processCarrier"]').removeAttr("disabled");
				$('button[name="processCarrier"]').removeAttr("disabled");
				noSelectedPointInfo.fadeOut();
				$('#opc_payment_methods').show();
			}
			($('#reembolsoSEUR').is(":visible") ? $('#reembolsoSEUR').fadeOut() : "" );
		}
		else
		{
			map.removeClass('showmap').css('position','absolute');

			if(ps_version_seur != 'ps7') {
				noSelectedPointInfo.fadeOut();
				collectionPointInfo.fadeOut();
				$('#opc_payment_methods').show();
				alert(no_pickup_points_error_text);
				$('div.seurMapContainer').fadeOut();
				$('#noSelectedPointInfo').fadeOut();
				$('#collectionPointInfo').fadeOut();
			}
			else{
				noSelectedPointInfo.remove();
				collectionPointInfo.remove();
				$('#opc_payment_methods').show();
				alert(no_pickup_points_error_text);
				$('div.seurMapContainer').remove();
				$('#noSelectedPointInfo').remove();
				$('#collectionPointInfo').remove();
			}
			$('input[name=processCarrier]').removeAttr('disabled');
			$('button[name=processCarrier]').removeAttr('disabled');
			printPointsList(points);
		}
	});
}

function getSeurCollectionPoints()
{
	if (!(id_address_delivery.val() in seur_token_))
		var current_token = null;
	else
		var current_token = seur_token_[id_address_delivery.val()];

	points = false;

	$.ajax({
		url: baseDir+'modules/seur/ajax/getPickupPointsAjax.php',
		type: 'GET',
		data: {
			id_address_delivery : encodeURIComponent(id_address_delivery.val()),
			token : encodeURIComponent(current_token)
		},
		dataType: 'json',
		async: false,
		success: function(data)
		{
			points = data;
		},
		error: function(xhr, ajaxOptions, thrownError){ map.html(thrownError); }
	});
	return points;
}

function printPointsList(collectorPoints) {
	$('#seurPudoContainer').html('');
	$.each(collectorPoints, function (key, post_code) {
		var html = $("<div><input type='radio' name='pickupPoint' id='pickupPoint' value='"+post_code.codCentro+"' required='required'> <span class='tittle'>" + post_code.company + "</span> - <span class='direccion'>" + post_code.address + "</span></div>").on('click', function () {
			currentPoint = PointClick(post_code)
		});
		$('#seurPudoContainer').append(html);
	});
	$('#seurMap').hide();
	$('.seurMapContainer').css({'position': 'relative', 'left': 'inherit', 'height': 'auto'});
	$('#listPoints').css({'position': 'relative', 'width': '100%', 'height': 'auto'});
	$('#seurPudoContainer').css({'display' : 'block', 'padding': '0 0 15px 0'});
}

$('#pickupPoint').on('click', function () {
	currentPoint = PointClick(currentPoint, post_code)
});

function printCollectorPoints(collectorPoints) {
	google.maps.Map.prototype.markers = new Array(); // Array the points of sale
	google.maps.Marker.prototype.post_codeData = new Object();//Array the data of points of sale
	google.maps.Marker.prototype.popup = new Object();// Array the data of points of sale popup
	google.maps.Marker.prototype.savepost_codeData = function(data) {
		this.post_codeData = data;
	};
	google.maps.Marker.prototype.savePopup = function(popup) {
		this.popup = popup;
	};
	google.maps.Map.prototype.addMarker = function(marker) {
		this.markers[this.markers.length] = marker;
	};

	clearMarkers();

	var markers = [];
	$.each(collectorPoints, function (key, post_code) {
		latlng = new google.maps.LatLng(
			parseFloat(post_code.position.lat),
			parseFloat(post_code.position.lng)
		);
		marker = new google.maps.Marker({
			position: new google.maps.LatLng(post_code.position.lat, post_code.position.lng),
			map: gMaps,
			title: 'Seleccionar ' + post_code.company,
			icon: baseDir + 'modules/seur/views/img/puntoRecogida.png',
			cursor: 'default',
			draggable: false
		});
		popup = new google.maps.InfoWindow({
			content: "<h4>" + post_code.company + "</h4><p>" + post_code.address + "</p>"
		});
		gMaps.addMarker(marker);
		marker.savepost_codeData(post_code);
		marker.savePopup(popup);

		markers[key] = marker;
		var html = $("<div><span class='tittle'>" + post_code.company + "</span><p>" + post_code.address + "</p></div>").on('click', function () {
			currentMarker = markerClick(currentMarker, markers[key])
		});

		$('#listPoints').append(html);

		google.maps.event.addListener(marker, 'click', function () {
			currentMarker = markerClick(currentMarker, this);
		});
	});
}

function markerClick(currentMarker, marker){

	if(currentMarker != null ){
		currentMarker.setIcon(baseDir+'modules/seur/views/img/puntoRecogida.png');
		currentMarker.popup.close();
	}
	marker.setIcon(baseDir+'modules/seur/views/img/puntoRecogidaSel.png');
	marker.popup.open(gMaps, marker);
	$('#id_seur_pos', collectionPointInfo).val(marker.post_codeData.codCentro );
	$('#post_codeCompany', collectionPointInfo).html(marker.post_codeData.company );
	$('#post_codeAddress', collectionPointInfo).html(marker.post_codeData.address );
	$('#post_codeCity', collectionPointInfo).html(marker.post_codeData.city );
	$('#post_codePostalCode', collectionPointInfo).html(marker.post_codeData.post_code );
	$('#post_codeTimetable', collectionPointInfo).html(marker.post_codeData.timetable );
	$('#post_codePhone', collectionPointInfo).html(marker.post_codeData.phone );
	if(!collectionPointInfo.is(":visible"))	{
		collectionPointInfo.fadeIn();
		noSelectedPointInfo.fadeOut();
	}
	currentMarker = marker;
	saveCollectorPoint($('#id_cart_seur').val(), marker.post_codeData);
	return currentMarker;
}

function PointClick(post_codeData){
	$("input[name=pickupPoint][value=" + post_codeData.codCentro + "]").attr('checked', 'checked');
	$('#id_seur_pos', collectionPointInfo).val(post_codeData.codCentro);
	$('#post_codeCompany', collectionPointInfo).html(post_codeData.company);
	$('#post_codeAddress', collectionPointInfo).html(post_codeData.address);
	$('#post_codeCity', collectionPointInfo).html(post_codeData.city);
	$('#post_codePostalCode', collectionPointInfo).html(post_codeData.post_code);
	$('#post_codeTimetable', collectionPointInfo).html(post_codeData.timetable);
	$('#post_codePhone', collectionPointInfo).html((post_codeData.phone==''?'-':post_codeData.phone));
	if(!collectionPointInfo.is(":visible")) {
		collectionPointInfo.fadeIn();
		noSelectedPointInfo.fadeOut();
	}
	saveCollectorPoint($('#id_cart_seur').val(), post_codeData);
	return true;
}

function clearMarkers(){
	for(var i=0; i < gMaps.markers.length; i++){
		gMaps.markers[i].setMap(null);
	}
	gMaps.markers = [];
}