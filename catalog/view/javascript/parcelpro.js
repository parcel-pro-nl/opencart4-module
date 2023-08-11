function popup_close() {
    $('#modal').hide();
}

function popup_submit(data) {
    AddressIsParcelshop(data);
}

function AddressIsParcelshop(data) {
    if (data) {
        $("input[name=pp_firstname]").val(data.LocationType);
        $("input[name=pp_lastname]").val(data.Name);
        $("input[name=pp_company]").val(data.Id);
        $("input[name=pp_address_1]").val(data.Street);
        $("input[name=pp_address_2]").val(data.Housenumber + data.HousenumberAdditional);
        $("input[name=pp_postcode]").val(data.Postalcode);
        $("input[name=pp_city]").val(data.City);
        //match = /^(.*)\-(\d+)$/.exec(data.LocationTypeId);
        //$("input[name=country_id]").val('NL');
        // console.log(window['_QuickCheckoutData']);
        // var _od = window['_QuickCheckoutData'].order_data;
        // _od['pp_gebruiker_id'] = $('input[name=pp_gebruiker_id]').val();
        // _od['pp_firstname'] = $('input[name=pp_firstname]').val();
        // _od['pp_lastname'] = $('input[name=pp_lastname]').val();
        // _od['pp_company'] = $('input[name=pp_company]').val();
        // _od['pp_address_1'] = $('input[name=pp_address_1]').val();
        // _od['pp_address_2'] = $('input[name=pp_address_2]').val();
        // _od['pp_postcode'] = $('input[name=pp_postcode]').val();
        // _od['pp_city'] = $('input[name=pp_city]').val();
    }
    var firstname = $("#shipping_method\\:firstname").val();
    var lastname = $("#shipping_method\\:lastname").val();

    if (firstname == "DHL ParcelShop") {
        var label = $('label[for="s_method_parcelpro_dhl_dfyparcelshop"]');
        var price = $('span', label);
        var priceHtml = $('<div>').append(price.clone()).html();
        $(label).html(firstname + " " + lastname + " <strong>" + priceHtml + "<strong>");

        return true;
    }
    if (firstname == "PostNL Pakketpunt") {
        var label = $('label[for="s_method_parcelpro_postnl_pakjegemak"]');
        var price = $('span', label);
        var priceHtml = $('<div>').append(price.clone()).html();
        $(label).html(firstname + " " + lastname + " <strong>" + priceHtml + "<strong>");
        return true;
    }
    return false;
}

function ParcelProPickupPointCard() {
    const first_name = $("input[name=pp_firstname]").val();
    const last_name = $("input[name=pp_lastname]").val();
    const company = $("input[name=pp_company]").val();
    const address1 = $("input[name=pp_address_1]").val();
    const address2 = $("input[name=pp_address_2]").val();
    const postcode = $("input[name=pp_postcode]").val();
    const city = $("input[name=pp_city]").val();


    const title = $("#parcel-pro-pickup-point .card-title");
    const body = $("#parcel-pro-pickup-point .card-text");

    if (company == '') {
        title.text('No pick-up location selected');
        body.html('');
    } else {
        title.text(first_name + ' ' + last_name);
        body.html(address1 + ' ' + address2 + '<br>' + postcode + ' ' + city)
    }
}

function ParcelProKiezerUrl() {
    var url = "https://login.parcelpro.nl/plugin/afhaalpunt/parcelpro-kiezer.html";
    var postcode = $("input[name=shipping_postcode]").val() ? $("input[name=shipping_postcode]").val() : $("input[name=shipping_postcode]").val();
    var country = $("select[name=shipping_country_id]").val() ? $("select[name=shipping_country_id]").val() : $("select[name=shipping_country_id]").val();

    url += "?";
    url += "id=" + $("input[name=pp_gebruiker_id]").val();
    url += "&postcode=" + postcode;
    url += "&country=" + getCountryCode(parseInt(country));
    url += "&origin=" + window.location.protocol + "//" + window.location.hostname;
    
    return url;
}

function ParcelProInitIframe(value, init = false) {
    let showCard = false;
    if (value === 'parcel_pro.shipping_parcel_pro_type_id_3533') {
        console.log(value, init);

        if(!init){
            $('#modal').show();
            $('#afhaalpunt_frame').attr('src', ParcelProKiezerUrl() + '&carrier=PostNL');

        }

        showCard = true;
    }

    if (value === 'parcel_pro.shipping_parcel_pro_type_id_dfyparcelshop') {
        if(!init) {
            $('#modal').show();
            $('#afhaalpunt_frame').attr('src', ParcelProKiezerUrl() + '&carrier=DHL');
        }

        showCard = true;
    }

    if (showCard && init) {
        ParcelProPickupPointCard();
        $('#parcel-pro-pickup-point').show();
    }else if(showCard){
        $('#parcel-pro-pickup-point').show();
    } else {
        $('#parcel-pro-pickup-point').hide();
    }
}

function getCountryCode(countryId){
    switch(countryId){
        case 14:
            return 'AT'
        case 21:
            return 'BE';
        case 33:
            return 'BG';
        case 53:
            return 'HR';
        case 55:
            return 'CY';
        case 56:
            return 'CZ';
        case 57:
            return 'DK';
        case 67:
            return 'EE';
        case 72:
            return 'FI';
        case 74:
            return 'FR';
        case 81:
            return 'DE';
        case 84:
            return 'GR';
        case 97:
            return 'HU';
        case 103:
            return 'LV';
        case 117:
            return 'LT';
        case 123:
            return 'LT';
        case 124:
            return 'LU';
        case 132:
            return 'MT';
        case 150:
            return 'NL';
        case 170:
            return 'PL';
        case 171:
            return 'PT';
        case 175:
            return 'RO';
        case 189:
            return 'ES';
        case 190:
            return 'SK';
        case 195:
            return 'SI';
        case 203:
            return 'SE';
        default:
            return '';
    }
}

window.addEventListener("message", function (event) {
    if (event.origin === "https://login.parcelpro.nl") {
        var msg = event.data;

        console.log(msg);

        if (msg == "closewindow") {
            ParcelProPickupPointCard();
            popup_close();
        } else {
            AddressIsParcelshop(msg);
            ParcelProPickupPointCard();
            popup_close();
			const data = $('#pp-parcelshop-form').serialize() + '&shipping_method=' + $('#input-shipping-code').val();

			chain.attach(function () {
				return $.ajax({
					url: 'index.php?route=extension/parcelpro/shipping/parcelpro|savePickupPoint',
					type: 'post',
					data: data,
					dataType: 'json',
					contentType: 'application/x-www-form-urlencoded',
					success: function (json) {
						if (json['redirect']) {
							location = json['redirect'];
						}

						if (json['error']) {
							$('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> ' + json['error'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
						}

						if (json['success']) {
							$('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-circle-check"></i> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');

							$('#button-payment-method').trigger('click');
						}
					},
					error: function (xhr, ajaxOptions, thrownError) {
						console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
        }
    } else {
        console.log(event.origin + "!== https://login.parcelpro.nl");
    }
}, false);

$(document).ready(function () {
    const value = $("input#input-shipping-code").val().slice(0, -2).toLowerCase();
    let currentValue = value;
    ParcelProInitIframe(value, true);

    setInterval(function () {
        if (currentValue != $("input#input-shipping-code").val().slice(0, -2).toLowerCase()) {
            $("input#input-shipping-code").trigger('change');
            currentValue = $("input#input-shipping-code").val().slice(0, -2).toLowerCase();
        }
    }, 500);

    $("input#input-shipping-code").on('change', function () {
        const value = $(this).val().slice(0, -2).toLowerCase();

        $('input[name=pp_company]').val('');

        ParcelProInitIframe(value);
    });

    $("#parcel-pro-change-pickupt-point").on('click', function (e) {
        e.preventDefault();
        const value = $("input#input-shipping-code").val().slice(0, -2).toLowerCase();

        ParcelProInitIframe(value);
    })
});
