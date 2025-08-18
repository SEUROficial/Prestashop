let bodyid;
let ps_version_seur;
let id_seur_pos;
let seurGoogleApiKey;
let validGoogleApiKey;
let seurInitialized = false;

let id_address_delivery_seur;
let collectionPointInfo;
let noSelectedPointInfo;
let seurPudoContainer;
let listPoints;

let carrierTable;
let carrierTableInput;
let carrierTableInputContainer;

let currentCarrierId;
let map;
let gMaps;
let userMarker;
let seurMarkers = [];
let selectedMarker;
let infoWindow = new google.maps.InfoWindow({
	disableAutoPan: true
});

let id_seur_RESTO_array;

$(document).ready(function()
{
	const body = $('body');
	const delivery_id = $('#delivery_id');
	body.on('change',
		'input[type="radio"][name="id_carrier"], ' +
		'.delivery-option input[type="radio"], ' +
		'.delivery-options input[type="radio"]', function () {
		initSeurCarriers();
	});

	// One Page Checkout Prestashop by PresTeamShop
	if (delivery_id.length && delivery_id.val() === '') {
		body.on('change',
			'#delivery_id_country, ' +
			'#delivery_postcode, ' +
			'#delivery_city, ' +
			'#delivery_id_state', function () {
				initSeurCarriers();
			});
	}

	body.on('change', '#cgv, #recyclable, #gift, #id_address_delivery', function () {
		check_reembolsoSeur();
	});
});

async function initSeurCarriers() {
	if (!seurInitialized) {
		seurAssignGlobalVariables();
	}
	if (seurInitialized) {
		validGoogleApiKey = await isGoogleApiKeyValid(seurGoogleApiKey);
		usrAddress = getUserAddress(id_address_delivery_seur.val());
		points = getSeurCollectionPoints();

		cleanSeurMaps();
		getCurrentCarrierId();
		if (getDisplaySeurCarriers() && currentCarrierIsSeurPickup()) {
			initContainers();
			if (validGoogleApiKey) {
				initSeurMaps();
			} else {
				initSeurPointList();
			}
			if (!localStorage.getItem('seur_pickupPoint')) {
				noSelectedPointInfo.show();
			}
		}
	}
}

async function seurAssignGlobalVariables() {
	bodyid = $('body').attr('id');
	ps_version_seur = $('#ps_version').val() ?? 'ps5';

	id_seur_pos = $('#id_seur_pos').val();
	seurGoogleApiKey = $('#seurGoogleApiKey').val();

	if ($('#id_address_delivery').length) {
		id_address_delivery_seur = $('#id_address_delivery');
	}
	if ($('#opc_id_address_delivery').length) {
		id_address_delivery_seur = $('#opc_id_address_delivery');
	}
	if (typeof AppOPC !== typeof undefined && $('#delivery_id').length && $('#delivery_id').val() !== '') {
		id_address_delivery_seur = $('#delivery_id');
	}
	if (typeof id_address_delivery_seur === 'undefined') {
		return;
	}
	collectionPointInfo = $('#collectionPointInfo');
	noSelectedPointInfo = $('#noSelectedPointInfo');
	seurPudoContainer = $('#seurPudoContainer');
	listPoints = $('#listPoints');

	carrierTable = (ps_version_seur == 'ps4' ? $('#carrierTable') : $('#carrier_area'));
	carrierTableInput = (ps_version_seur == 'ps4' ? $('input[name="id_carrier"]') : $('.delivery_option_radio'));
	carrierTableInputContainer = (ps_version_seur == 'ps4' ? '#carrierTable' : '#carrier_area .delivery_options');

	$('#pos_selected').val('false');

	if ($('#id_seur_RESTO').length > 0) {
		id_seur_RESTO = $('#id_seur_RESTO');
		id_seur_RESTO_array = id_seur_RESTO.val().split(',');
	}
	seurInitialized = true;
}

function check_reembolsoSeur()
{
	if (currentCarrierIsSeurPickup()) {
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

async function isGoogleApiKeyValid(apiKey) {
	if (!apiKey) {
		return false;
	}

	const url = 'https://maps.googleapis.com/maps/api/geocode/json?address=c/Jacinto%201,Cadiz,Spain&key=' + apiKey;
	try {
		const data = await $.getJSON(url); // Espera la respuesta
		if (data.error_message) {
			console.error("Error en la petición:", data.error_message);
			return false;
		}
		return true;
	} catch (error) {
		console.error("Error en la petición:", error);
		return false;
	}
}

function seurCarrierDisplayed(id_seur_pos)
{
	var displayed = false;

	var selector = '.delivery-options input[type=\"radio\"]';
	if (ps_version_seur == 'ps4') { selector = '#carrierTable input[type=\"radio\"]'; }
	if (ps_version_seur == 'ps5') { selector = '.delivery_options input[type=\"radio\"]'; }
	if (!id_seur_pos) { return displayed; }

	id_seur_pos_array = id_seur_pos.split(',');
	id_seur_pos_array.forEach(function(id_seur_pos){
		$(selector).each(function () {
			if (Number($(this).val().replace(/[^0-9]+/g, '')) == Number(id_seur_pos)) {
				displayed = true;
			}
		});
	});

	return displayed;
}

function initSeurPointList() {
	setButtonProcessCarrier('disabled');
	printPointsList(points);
	seurPudoContainer.show();
}

function initContainers() {
	map = $('<div />').addClass('seurMapContainer').html(map);
	$('span', map).css({ 'line-height' : '64px', 'font-size' : '50px' });

	carrierExtraContentDiv = selectedCarrierDiv();
	map.appendTo(carrierExtraContentDiv);
	noSelectedPointInfo.insertAfter(map);
	collectionPointInfo.insertAfter(map);
	seurPudoContainer.insertAfter(map);
}

function initContainers() {
	map = $('<div />').attr('id', 'seurMap').attr('init', 'false');
	map = $('<div />').addClass('seurMapContainer').html(map);
	$('span', map).css({ 'line-height' : '64px', 'font-size' : '50px' });

	carrierExtraContentDiv = selectedCarrierDiv();
	map.appendTo(carrierExtraContentDiv);
	noSelectedPointInfo.insertAfter(map);
	collectionPointInfo.insertAfter(map);
	seurPudoContainer.insertAfter(map);
}

async function initSeurMaps()
{
	// Cargar las librerías necesarias
	const {Map} = await google.maps.importLibrary("maps");
	const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");

	gMaps = new google.maps.Map(document.getElementById('seurMap'), {
		zoom: 13,
		center: {lat: 0, lng: 0},
		mapId: 'ROADMAP',
		disableDefaultUI: true,
		panControl: true,
		zoomControl: true,
		mapTypeControl: true,
		scaleControl: true,
		streetViewControl: true,
		rotateControl: true,
		keyboardShortcuts: false,
		disableDoubleClickZoom: false,
		draggable: true,
		scrollwheel: true
	});

	let userMarkerElement = document.createElement('div');
	userMarkerElement.className = 'custom-marker';
	userMarkerElement.innerHTML = '<img src="' + baseDir + 'modules/seur/views/img/user.png">';

	userMarker = new google.maps.marker.AdvancedMarkerElement({
		position: {lat: 0, lng: 0},
		map: gMaps,
		title: 'Dirección próxima a usted',
		gmpClickable: true,
		content: userMarkerElement,
	});

	// if one step checkout and ps5
	if ((bodyid == 'order-opc') && ps_version_seur == 'ps5') {
		carrier_value = $('input[type="radio"].delivery_option_radio').attr('name');
		delivery_option_selector = '.delivery_option_radio';
	}

	if (ps_version_seur == 'ps7') {
		carrier_value = $('.delivery-options input[type="radio"]').attr('name');
		delivery_option_selector = '.delivery_option';
	}

	str = carrier_value;
	cad_string = str.substring(str.indexOf('[') + 1, str.indexOf(']'));
	// set value of onchange
	$(delivery_option_selector).each(function () {
		carrier_value = $(this).attr('value');
		$(this).on('change', null, function () {
			updateOneStepCloser();
		});
	});
	// add reload the page
	$('#id_address_delivery').attr('onchange', 'updateAddressesDisplay(); updateAddressSelectionOneStep();');

	setButtonProcessCarrier('disabled');
	noSelectedPointInfo.fadeIn();
	printMap();
};

function saveCollectorPoint(id_cart, postCodeData )
{
	var chosen_address_delivery = id_address_delivery_seur.val();
	localStorage.setItem('seur_pickupPoint', postCodeData.codCentro);
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
			id_seur_pos : encodeURIComponent(postCodeData.codCentro),
			company : encodeURIComponent(postCodeData.company),
			address : encodeURIComponent(postCodeData.address),
			city : encodeURIComponent(postCodeData.city),
			post_code : encodeURIComponent(postCodeData.post_code),
			phone : encodeURIComponent(postCodeData.phone),
			timetable : encodeURIComponent(postCodeData.timetable),
			chosen_address_delivery : chosen_address_delivery,
			token : encodeURIComponent(current_token)
		},
		dataType: 'json',
		async: false,
		success: function(data)
		{
			$('#pos_selected').val("true");
			setButtonProcessCarrier('enabled');
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
	usrAddress = getUserAddress(id_address_delivery_seur.val() );
	geocoder = new google.maps.Geocoder();
	geocoder.geocode({ 'address': usrAddress}, function(result, status)
	{
		if (status == google.maps.GeocoderStatus.OK)
		{
			gMaps.setCenter(result[0].geometry.location);
			userMarker.position = result[0].geometry.location;
		}
		else alert('updateUserMapPosition id_address: '+id_address_delivery_seur.val()+' Error address in update the map: ' + status); // @TODO make translatable text
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
	if(map.attr('init') == 'false') {
		map.removeClass('showmap').attr('init','true').css('position','absolute');
	}
	if($('input[type="radio"]').is(':checked'))
	{
		if (currentCarrierIsSeurPickup()) {
			(!map.hasClass("showmap") ? map.addClass('showmap').css('position','relative') : "" );
		} else{
			map.removeClass('showmap').css('position','absolute');
		}

		setButtonProcessCarrier($('#pos_selected').val() == "false" ? 'disabled' : 'enabled');

		($('#reembolsoSEUR').is(":visible") ? $('#reembolsoSEUR').fadeOut() : "" );
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
function printMap() {
	try {
		if (map.attr('init') == 'false') {
			map.attr('init', 'true').css('position', 'absolute');
		}
		map.removeClass('showmap').css('position', 'absolute');

		geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address': usrAddress}, function (result, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				gMaps.setCenter(result[0].geometry.location);
				userMarker.position = result[0].geometry.location;
				$('.seurMapContainer').css({'position': 'relative', 'left': 'inherit', 'height': ''});
				$('#seurMap').css({'position': '', 'left': ''});
				seurPudoContainer.hide();
				printCollectorPoints(points);

				check_reembolsoSeur();

				setButtonProcessCarrier($('#pos_selected').val() == "false" ? 'disabled' : 'enabled');

				($('#reembolsoSEUR').is(":visible") ? $('#reembolsoSEUR').fadeOut() : "");
			} else {
				console.error("Geocoder falló debido a: " + status);
				seurPudoContainer.show();
			}
		});

	} catch (error) {
		console.error("Se produjo un error: " + error.message);
		seurPudoContainer.show();
	}
}

function getSeurCollectionPoints()
{
	if (!(id_address_delivery_seur.val() in seur_token_))
		var current_token = null;
	else
		var current_token = seur_token_[id_address_delivery_seur.val()];

	points = false;

	$.ajax({
		url: baseDir+'modules/seur/ajax/getPickupPointsAjax.php',
		type: 'GET',
		data: {
			id_address_delivery : encodeURIComponent(id_address_delivery_seur.val()),
			token : encodeURIComponent(current_token)
		},
		dataType: 'json',
		async: false,
		success: function(data)
		{
			points = data;
		},
		error: function(xhr, ajaxOptions, thrownError){ console.error(thrownError); if (map) map.html(thrownError); }
	});
	return points;
}

function printPointsList(collectorPoints) {
	seurPudoContainer.html('');
	$.each(collectorPoints, function (key, postCodeData) {
		var isChecked = "";
		if (postCodeData.codCentro === localStorage.getItem('seur_pickupPoint')) {
			isChecked = "checked='checked'";
			currentPoint = PointClick(postCodeData);
		}
		var html = $("<div><input type='radio' name='pickupPoint' id='pickupPoint' value='" + postCodeData.codCentro + "' required='required' " + isChecked + "> <span class='tittle'>" + postCodeData.company + "</span> - <span class='direccion'>" + postCodeData.address + "</span></div>").on('click', function () {
			currentPoint = PointClick(postCodeData);
		});
		seurPudoContainer.append(html);
	});
	listPoints.css({'position': 'relative', 'width': '100%', 'height': 'auto', 'display': 'block'});
	seurPudoContainer.css({'display': 'block', 'padding': '0 0 15px 0'});
}

$('#pickupPoint').on('click', function () {
	currentPoint = PointClick(currentPoint, postCodeData)
});

function printCollectorPoints(collectorPoints) {
	clearMarkers();
	listPoints = $('<div />').attr('id', 'listPoints');

	collectorPoints.forEach((postCodeData , index) => {
		let latlng = { lat: parseFloat(postCodeData.position.lat), lng: parseFloat(postCodeData.position.lng) };

		// Crear contenedor del marcador
		const iconContainer = document.createElement("div");

		// Crear imágenes para el marcador
		const iconSelected = document.createElement("img");
		iconSelected.src = baseDir + 'modules/seur/views/img/puntoRecogidaSel.png';
		iconSelected.className = "marker-icon";
		iconSelected.style.display = "none";

		const iconDefault = document.createElement("img");
		iconDefault.src = baseDir + 'modules/seur/views/img/puntoRecogida.png';
		iconDefault.className = "marker-icon";
		iconDefault.style.display = "block";

		// Añadir ambas imágenes al contenedor
		iconContainer.appendChild(iconSelected);
		iconContainer.appendChild(iconDefault);

		let marker = new google.maps.marker.AdvancedMarkerElement({
			position: latlng,
			map: gMaps,
			title: `Seleccionar ${postCodeData.company}`,
			gmpClickable: true,
			content: iconContainer,
		});

		seurMarkers.push({ marker, iconSelected, iconDefault, postCodeData });

		marker.addListener('gmp-click', () => {
			selectMarker(index);
		});

		let html = `<div class='list-item' data-index="${index}">
						<span class='title'>${postCodeData.company}</span>
						<p>${postCodeData.address}</p>
					</div>`;

		listPoints.append(html);

		if (postCodeData.codCentro === localStorage.getItem('seur_pickupPoint')) {
			selectMarker(index);
		}
	});

	listPoints.appendTo('.seurMapContainer');
	listPoints.fadeIn();

	document.querySelectorAll(".list-item").forEach(item => {
		item.addEventListener("click", function () {
			const index = parseInt(this.getAttribute("data-index"));
			selectMarker(index);
		});
	});
}
function selectMarker(index) {
	infoWindow.close();
	if (selectedMarker) {
		selectedMarker.iconSelected.style.display = "none";
		selectedMarker.iconDefault.style.display = "block";
	}

	selectedMarker = seurMarkers[index];
	selectedMarker.iconSelected.style.display = "block";
	selectedMarker.iconDefault.style.display = "none";

	gMaps.setCenter(selectedMarker.marker.position);
	markerClick(selectedMarker.marker, selectedMarker.postCodeData);

	// Obtener datos del punto seleccionado
	const { company, address, schedule, phone } = selectedMarker.postCodeData;

	// Contenido del InfoWindow
	const contentString = `
        <div style="max-width: 250px;">
            <p><strong>${company}</strong>
            <br>${address}</p>
        </div>
    `;

	// Mostrar InfoWindow en la posición del marcador
	infoWindow.setContent(contentString);
	infoWindow.open(gMaps, selectedMarker.marker);
}

function markerClick(marker, postCodeData) {
	updatePointInfo(postCodeData);
	gMaps.setCenter(marker.position);
}

function PointClick(postCodeData){
	updatePointInfo(postCodeData);
	$("input[name=pickupPoint][value=" + postCodeData.codCentro + "]").attr('checked', 'checked');
	noSelectedPointInfo.hide();
	return true;
}

function updatePointInfo(postCodeData){
	localStorage.setItem('seur_pickupPoint', postCodeData.codCentro);
	$('#post_codeId', collectionPointInfo).val(postCodeData.codCentro );
	$('#post_codeCompany', collectionPointInfo).html(postCodeData.company);
	$('#post_codeAddress', collectionPointInfo).html(postCodeData.address);
	$('#post_codeCity', collectionPointInfo).html(postCodeData.city);
	$('#post_codePostalCode', collectionPointInfo).html(postCodeData.post_code);
	$('#post_codeTimetable', collectionPointInfo).html(postCodeData.timetable);
	$('#post_codePhone', collectionPointInfo).html((postCodeData.phone==''?'-':postCodeData.phone));

	collectionPointInfo.fadeIn();
	noSelectedPointInfo.fadeOut();

	saveCollectorPoint($('#id_cart_seur').val(), postCodeData);
}

function clearMarkers() {
	seurMarkers.forEach(marker => marker.map = null); // Elimina cada marcador del mapa
	seurMarkers = []; // Vaciar el array
}

function cleanSeurMaps()
{
	$('div.seurMapContainer').remove();
	seurPudoContainer.hide()
	noSelectedPointInfo.hide();
	collectionPointInfo.hide();
	listPoints.remove();
}

function setButtonProcessCarrier(state)
{
	if(state == 'disabled')
	{
		$('input[name="processCarrier"]').attr("disabled","disabled");
		$('button[name="processCarrier"]').attr("disabled","disabled");
		$('button[name="confirmDeliveryOption"]').attr("disabled","disabled");
		$('#opc_payment_methods').hide();
	}
	else
	{
		$('input[name="processCarrier"]').removeAttr("disabled");
		$('button[name="processCarrier"]').removeAttr("disabled");
		$('button[name="confirmDeliveryOption"]').removeAttr("disabled");
		$('#opc_payment_methods').show();
	}
}
function getCurrentCarrierId() {
	if (ps_version_seur != 'ps7') {
		currentCarrierId = $('input[type="radio"]:checked', $(carrierTableInputContainer)).val();
	} else {
		currentCarrierId = $('.delivery-options input[type="radio"]:checked').val();
	}
	currentCarrierId = currentCarrierId.replace(",", "");
}

function currentCarrierIsSeurPickup() {
	var id_seur_pos_array = $('#id_seur_pos').val().split(',');
	if (id_seur_pos_array.indexOf(""+currentCarrierId) > -1) {
		return true;
	}
	return false;
}

function selectedCarrierDiv() {
	// Localizar el elemento seleccionado
	const deliveryOptionInput = $(`#delivery_option_${currentCarrierId}`);
	let selectedDeliveryOption;

	// Caso 1: Buscar el ancestro directo con las clases específicas
	selectedDeliveryOption = deliveryOptionInput.closest('.row.delivery-option.js-delivery-option');

	// Caso 2: Estructura estándar de PrestaShop
	if (!selectedDeliveryOption.length) {
		selectedDeliveryOption = deliveryOptionInput.closest('.row.delivery-option');
	}

	// Caso 3: Otra alternativa (One Page Checkout Prestashop by PresTeamShop)
	if (!selectedDeliveryOption.length) {
		selectedDeliveryOption = deliveryOptionInput.closest(`.delivery-option.delivery_option_${currentCarrierId}`);
	}

	// Caso 4: Si no se encuentra, buscar una estructura alternativa
	if (!selectedDeliveryOption.length) {
		selectedDeliveryOption = deliveryOptionInput.closest('.delivery-options-items');
	}

	// Verificar que exista
	if (selectedDeliveryOption.length) {
		// Buscar el contenedor donde se deben mover los elementos
		let carrierExtraContent = selectedDeliveryOption.next('.row.carrier-extra-content.js-carrier-extra-content');

		// Caso alternativo: siguiente sin js-
		if (!carrierExtraContent.length) {
			carrierExtraContent = selectedDeliveryOption.next('.row.carrier-extra-content');
		}

		// Caso alternativo: Buscar dentro de la nueva estructura
		if (!carrierExtraContent.length) {
			carrierExtraContent = selectedDeliveryOption.find('.carrier-extra-content-new');
		}

		// Caso alternativo: One Page Checkout Prestashop by PresTeamShop
		if (!carrierExtraContent.length) {
			carrierExtraContent = selectedDeliveryOption.find('.row.carrier-extra-content.js-carrier-extra-content');
		}

		// Caso alternativo: Si no se encuentra, buscar una estructura alternativa
		if (!carrierExtraContent.length) {
			carrierExtraContent = selectedDeliveryOption.find('.carrier-extra-content');
		}

		// Si se encuentra el contenedor, mover los elementos dentro de él
		if (carrierExtraContent.length) {
			return carrierExtraContent;
		}
	}

	return null;
}

function getDisplaySeurCarriers() {
	const isSeurCarrierDisplayed = seurCarrierDisplayed(id_seur_pos);
	if (!isSeurCarrierDisplayed) return false;

	const carrierConditions = {
		ps4: () => $('#carrierTable').length !== 0,
		ps5: () => $('.delivery_options').length !== 0,
		default: () => $('.delivery-options').length !== 0
	};
	return (carrierConditions[ps_version_seur] || carrierConditions.default)();
}
