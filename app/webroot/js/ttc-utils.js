function getNewDateUsingTimezone(){
    date = new Date.parse(Date.now().toString('dd/MM/yyyy')+ " "+$('.ttc-program-time').text().trim().substr(-8,5));
    localTime = date.getTime(); // in milliseconds
    newDate = new Date(localTime);
    return newDate;
}

function getCountryCodes(country){
    //alert(country+" , "+countries[country]);
    return countries[country];
    
}

var countries ={
    "Afghanistan":93,
    "Albania":355,
    "Algeria":213,
    "American Samoa":1684,
    "Andorra":376,
    "Angola":244,
    "Anguilla":1264, 
    "Antarctica":672,
    "Antigua and Barbuda":1268, 
    "Argentina":54,
    "Armenia":374,
    "Aruba":297,
    "Australia":61,
    "Austria":43,
    "Azerbaijan":994,
    "Bahamas":1242, 
    "Bahrain":973,
    "Bangladesh":880,
    "Barbados":1246, 
    "Belarus":375,
    "Belgium":32,
    "Belize":501,
    "Benin":229,
    "Bermuda":1441, 
    "Bhutan":975,
    "Bolivia":591,
    "Bosnia and Herzegovina":387,
    "Botswana":267,
    "Brazil":55,
    "British Virgin Islands":1284, 
    "Brunei":673,
    "Bulgaria":359,
    "Burkina Faso":226,
    "Burma (Myanmar)":95,
    "Burundi":257,
    "Cambodia":855,
    "Cameroon":237,
    "Canada":1,
    "Cape Verde":238,
    "Cayman Islands":1345, 
    "Central African Republic":236,
    "Chad":235,
    "Chile":56,
    "China":86,
    "Christmas Island":61,
    "Cocos (Keeling) Islands":61,
    "Colombia":57,
    "Comoros":269,
    "Cook Islands":682,
    "Costa Rica":506,
    "Croatia":385,
    "Cuba":53,
    "Cyprus":357,
    "Czech Republic":420,
    "Democratic Republic of the Congo":243,
    "Denmark":45,
    "Djibouti":253,
    "Dominica":1767, 
    "Dominican Republic":1809, 
    "Ecuador":593,
    "Egypt":20,
    "El Salvador":503,
    "Equatorial Guinea":240,
    "Eritrea":291,
    "Estonia":372,
    "Ethiopia":251,
    "Falkland Islands":500,
    "Faroe Islands":298,
    "Fiji":679,
    "Finland":358,
    "France":33,
    "French Polynesia":689,
    "Gabon":241,
    "Gambia":220,
    "Gaza Strip":970,
    "Georgia":995,
    "Germany":49,
    "Ghana":233,
    "Gibraltar":350,
    "Greece":30,
    "Greenland":299,
    "Grenada":1473, 
    "Guam":1671, 
    "Guatemala":502,
    "Guinea":224,
    "Guinea-Bissau":245,
    "Guyana":592,
    "Haiti":509,
    "Holy See (Vatican City)":39,
    "Honduras":504,
    "Hong Kong":852,
    "Hungary":36,
    "Iceland":354,
    "India":91,
    "Indonesia":62,
    "Iran":98,
    "Iraq":964,
    "Ireland":353,
    "Isle of Man":44,
    "Israel":972,
    "Italy":39,
    "Ivory Coast":225,
    "Jamaica":1876, 
    "Japan":81,
    "Jordan":962,
    "Kazakhstan":7,
    "Kenya":254,
    "Kiribati":686,
    "Kosovo":381,
    "Kuwait":965,
    "Kyrgyzstan":996,
    "Laos":856,
    "Latvia":371,
    "Lebanon":961,
    "Lesotho":266,
    "Liberia":231,
    "Libya":218,
    "Liechtenstein":423,
    "Lithuania":370,
    "Luxembourg":352,
    "Macau":853,
    "Macedonia":389,
    "Madagascar":261,
    "Malawi":265,
    "Malaysia":60,
    "Maldives":960,
    "Mali":223,
    "Malta":356,
    "Marshall Islands":692,
    "Mauritania":222,
    "Mauritius":230,
    "Mayotte":262,
    "Mexico":52,
    "Micronesia":691,
    "Moldova":373,
    "Monaco":377,
    "Mongolia":976,
    "Montenegro":382,
    "Montserrat":1664, 
    "Morocco":212,
    "Mozambique":258,
    "Namibia":264,
    "Nauru":674,
    "Nepal":977,
    "Netherlands":31,
    "Netherlands Antilles":599,
    "New Caledonia":687,
    "New Zealand":64,
    "Nicaragua":505,
    "Niger":227,
    "Nigeria":234,
    "Niue":683,
    "Norfolk Island":672,
    "North Korea":850,
    "Northern Mariana Islands":1670, 
    "Norway":47,
    "Oman":968,
    "Pakistan":92,
    "Palau":680,
    "Panama":507,
    "Papua New Guinea":675,
    "Paraguay":595,
    "Peru":51,
    "Philippines":63,
    "Pitcairn Islands":870,
    "Poland":48,
    "Portugal":351,
    "Puerto Rico":1,
    "Qatar":974,
    "Republic of the Congo":242,
    "Romania":40,
    "Russia":7,
    "Rwanda":250,
    "Saint Barthelemy":590,
    "Saint Helena":290,
    "Saint Kitts and Nevis":1869, 
    "Saint Lucia":1758, 
    "Saint Martin":1599, 
    "Saint Pierre and Miquelon":508,                   
    "Saint Vincent and the Grenadines":1784, 
    "Samoa":685,
    "San Marino":378,
    "Sao Tome and Principe":239,
    "Saudi Arabia":966,
    "Senegal":221,
    "Serbia":381,
    "Seychelles":248,
    "Sierra Leone":232,
    "Singapore":65,
    "Slovakia":421,
    "Slovenia":386,
    "Solomon Islands":677,
    "Somalia":252,
    "South Africa":27,
    "South Korea":82,
    "Spain":34,
    "Sri Lanka":94,
    "Sudan":249,
    "Suriname":597,
    "Swaziland":268,
    "Sweden":46,
    "Switzerland":41,
    "Syria":963,
    "Taiwan":886,
    "Tajikistan":992,
    "Tanzania":255,
    "Thailand":66,
    "Timor-Leste":670,
    "Togo":228,
    "Tokelau":690,
    "Tonga":676,
    "Trinidad and Tobago":1868, 
    "Tunisia":216,
    "Turkey":90,
    "Turkmenistan":993,
    "Turks and Caicos Islands":1649, 
    "Tuvalu":688,
    "Uganda":256,
    "Ukraine":380,
    "United Arab Emirates":971,
    "United Kingdom":44,
    "United States" :1,
    "Uruguay":598,
    "US Virgin Islands":1340, 
    "Uzbekistan":998,
    "Vanuatu":678,
    "Venezuela":58,
    "Vietnam":84,
    "Wallis and Futuna":681,
    "West Bank":970,
    "Yemen":967,
    "Zambia":260,
    "Zimbabwe":263
    };

function addContentFormHelp(baseUrl) {
    if (!baseUrl)
        baseUrl="../.."
    $.each($("[name*='content']").prev(":not(:has(img)) :not(div)"),
            function (key, elt){
                    $("<img class='ttc-help' src='/img/question-mark-icon-32.png'/>").appendTo($(elt)).click(function(){requestHelp(this, baseUrl, 'content')});
            });
    $.each($("[name*='template']").prev(":not(:has(img)) :not(div)"),
            function (key, elt){
                    $("<img class='ttc-help' src='/img/question-mark-icon-32.png'/>").appendTo($(elt)).click(function(){requestHelp(this, baseUrl, 'template')});
            });
    $.each($("[name*='keyword']").prev("label").not(":has(img)"),
            function (key, elt){
                    $("<img class='ttc-help' src='/img/question-mark-icon-32.png'/>").appendTo($(elt)).click(function(){requestHelp(this, baseUrl, 'keyword')});
            });
}

function requestHelp(elt, baseUrl, topic) {
    if ($($(elt).parent().next()).attr('class') == 'ttc-help-box') {
        $(elt).parent().next().remove();
        return;
    }
    $("<div class='ttc-help-box'><img src='/img/ajax-loader.gif' /></div>").insertAfter($(elt).parent()).load('/documentation', 
        'topic='+topic);
}


function vusionAjaxError(jqXHR, textStatus, errorThrown){
    if (textStatus == 'timeout') {
         $('#flashMessage').show().text('Poor network connection (request timed out)');
         return;
    }
    $('#flashMessage').show().text('Error: action failed.');
}


function pullBackendNotifications(url) {
    $.ajax({ 
        url: url, 
        success: function(data){
            $('#flashMessage').hide();
            if (data['logs']) {
                $("#notifications").empty();
                for (var x = 0; x < data['logs'].length; x++) {
                    data['logs'][x] = data['logs'][x].replace(data['logs'][x].substr(1,19),"<span style='font-weight:bold'>"+data['logs'][x].substr(1,19)+"</span>");	
                    $("#notifications").append(data['logs'][x]+"<br \>");
                }
            }
        },
        timeout: 500,
        error: vusionAjaxError,
    });
}

function pullSimulatorUpdate(url){
	$.ajax({
        url: url,
        success: function(data){
            $('#flashMessage').hide();
            if (data['message']) {
                    var message = $.parseJSON(data['message']);
                    $("#simulator-output").append("<div>> "+Date.now().toString('yy/MM/dd HH:mm')+" from "+message['from_addr']+" to "+message['to_addr']+" '"+message['content']+"'</div>")
            }
        },
        timeout: 1000,
        error: vusionAjaxError
        });
}

function logMessageSent(){
    var log = "> "+Date.now().toString('yy/MM/dd HH:mm')+" from "+$('[name="participant-phone"]').val()+" '"+$('[name="message"]').val()+"'";
    $('[name="participant-phone"]').val('')
    $('[name="message"]').val('')
    $('#simulator-output').append("<div>"+log+"</div>");
}

function updateClock(){
	originalTimeString = $('.ttc-program-time').text();
	currentTime = $('.ttc-program-time').text().trim().substr(-8,8);
	currentTimeArray = currentTime.split(':');
	currentHours = Number(currentTimeArray[0]);
	currentMinutes = Number(currentTimeArray[1]);
	currentSeconds = Number(currentTimeArray[2]);
	
	currentSeconds = currentSeconds + 1;
	if (currentSeconds > 59) {
		currentMinutes += 1;
		if (currentMinutes > 59) {
			currentHours += 1;
			if (currentHours > 23) {
				currentHours = 0;
			}
			currentMinutes = 0;
		}
		currentSeconds = 0;
	}
	
	// Pad the hours, minutes and seconds with leading zeros, if required
	currentHours = ( currentHours < 10 ? "0" : "" ) + currentHours;
	currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
	currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;
	
	currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds;
	newTimeString = originalTimeString.replace(currentTime, currentTimeString);
	$('.ttc-program-time').text(newTimeString);
}
