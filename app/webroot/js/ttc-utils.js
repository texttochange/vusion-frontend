jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ? 
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

function getNewDateUsingTimezone(){
    return moment($("#local-date-time").text(), "DD/MM/YYYY HH:mm:ss").toDate(); 
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
    $.each($("[name*='content']").prev(":not(:has(img)):not(div):not(span)"),
            function (key, elt){
                    $("<img class='ttc-help' src='/img/help-icon-16.png'/>").appendTo($(elt)).click(function(){requestHelp(this, baseUrl, 'content')});
            });
    $.each($("[name*='[template]']").prev(":not(:has(img)):not(div)"),
            function (key, elt){ 
                    $("<img class='ttc-help' src='/img/help-icon-16.png'/>").appendTo($(elt)).click(function(){requestHelp(this, baseUrl, 'template')});
            });
    $.each($("[name*='\.keyword']").prev("label").not(":has(img)"),
            function (key, elt){
                    $("<img class='ttc-help' src='/img/help-icon-16.png'/>").appendTo($(elt)).click(function(){requestHelp(this, baseUrl, 'keyword')});
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
    if (this.userAction) {
        $('#flashMessage').show().text(localized_errors['vusion_ajax_action_failed']+this.userAction).attr('class', 'message failure');
    }
    if (textStatus == 'timeout') {
        $('#connectionState').show().text(localized_errors['vusion_ajax_timeout_error']);
    }
    if (textStatus == 'error') {
        $('#connectionState').show().text(localized_errors['vusion_ajax_connection_error']);
    }
}

function saveAjaxError(jqXHR, textStatus, errorThrown){
    if (this.userAction) {
        if (textStatus == 'timeout') {
            message = localized_errors['vusion_ajax_action_failed_due'];
            message = message.replace(/\{0\}/g, this.userAction);
            message = message.replace(/\{1\}/g, localized_errors["timeout"]);
        } else {
            message = localized_errors['vusion_ajax_action_failed']+this.userAction;
            message = message.replace(/\{0\}/g, this.userAction)
        }
        $('#flashMessage').show().text(message).attr('class', 'message failure');
    }
    if (textStatus == 'timeout') {
        $('#connectionState').show().text(localized_errors['vusion_ajax_timeout_error']);
    }
    reactivateSaveButtons();
}

function disableSaveButtons(){
    $("#button-save").unbind("click");
    $('input[type="submit"]').attr("disabled", true);	
}

function reactivateSaveButtons(){
    $('input[type="submit"]').removeAttr("disabled");
    $("#button-save").bind("click", function(){
        disableSaveButtons();        
        $("#dynamic-generic-program-form").submit();
    });
}


function pullBackendNotifications(url) {
    $.ajax({ 
        url: url, 
        success: function(data){
            $('#connectionState').hide();
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
            $('#connectionState').hide();
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
	var newTime = moment($("#local-date-time").text(), "DD/MM/YYYY HH:mm:ss").add('seconds',1).format("DD/MM/YYYY HH:mm:ss");
	$("#local-date-time").text(newTime);	
}

function createFilter(minimize, selectedStackOperator, stackRules){

    if(typeof(minimize)==='undefined') minimize = false;    
    if(typeof(selectedStackOperator)==='undefined') selectedStackOperator = 'all';
    if(typeof(stackRules)==='undefined') stackRules = {};

    if ($('#filter-stack').length != 0) {
        return;
    }

   var stack = document.createElement("div");
   $(stack).attr('id', 'filter-stack').attr('class', 'ttc-filter-stack');
   $('#advanced_filter_form').prepend(stack);

   //The operator between the stack rules
   var stackOperatorPrefix = document.createElement("p");
   $(stackOperatorPrefix).html(localized_actions['filter_operator_prefix']);

   var stackOperatorSuffix = document.createElement("p");
   $(stackOperatorSuffix).html(localized_actions['filter_operator_suffix']);

   var stackOperatorSelect = document.createElement("select");
   $(stackOperatorSelect).attr('name', 'filter_operator');

   $.each(window.app.filterParameterOptions['operator'], function(val, text) {
        var option = new Option(text, val);
        if (val==selectedStackOperator) {
            $(option).attr('selected', true);
        }
		$(stackOperatorSelect).append(option);
   });

   var stackOperator = document.createElement("div");
   $(stackOperator).attr('id', 'filter-stack-operator').attr('class', 'ttc-filter-stack-operator');
   $(stackOperator).append(stackOperatorPrefix).append(stackOperatorSelect).append(stackOperatorSuffix);
   $('#advanced_filter_form').prepend(stackOperator);

   var title = document.createElement("div");
   $(title).attr('class','ttc-filter-title').html(localized_actions['filter']);
   $('#advanced_filter_form').prepend(title);

   var minimizeButton = document.createElement("img");
   $(minimizeButton).attr('class','ttc-add-icon').attr('src', '/img/minimize-icon-16.png').on('click', minimizeFilter);
   $('#advanced_filter_form').prepend(minimizeButton);

   var deleteButton = document.createElement("img");
   $(deleteButton).attr('class','ttc-add-icon').attr('src', '/img/delete-icon-16.png').on('click', removeFilter);
   $('#advanced_filter_form').prepend(deleteButton);

   var count = 1;
   $.each(stackRules, function(i, rule) {
       addStackFilter();
       $("select[name='filter_param["+count+"][1]']").val(rule[1]).children("option[value="+rule[1]+"]").click();
       if (typeof(rule[2]) === 'undefined') return true;
       $("[name='filter_param["+count+"][2]']").val(rule[2]).click();
 	   if (typeof(rule[3]) === 'undefined') return true;
 	   // If the selected element is not loaded, add it to the drop down
 	   if ($("[name='filter_param["+count+"][3]']select").size() > 0 && $("[name='filter_param["+count+"][3]'] option").size() == 1) {
 	       $("[name='filter_param["+count+"][3]']").prepend(new Option(rule[3],rule[3]))
 	   } 
	   $("[name='filter_param["+count+"][3]']").val(rule[3]);
       count++;	   
    });
    
   if (minimize) {
       $(minimizeButton).click();
   }    
    
}

function minimizeFilter() {
    $(this).parent().children(":not(img):not([class='ttc-filter-title'])").slideUp('fast');
    $(this).attr('src','/img/expand-icon-16.png').attr('class', 'ttc-add-icon').off().on('click', expandFilter);
}

function expandFilter() {
    $(this).parent().children().each(function(){ 
        if ($(this).attr('type')=='text')
            $(this).show();      //workaround for webkit bug that doesnt display sometimes the text input element       
        $(this).slideDown('fast')});
    $(this).attr('src','/img/minimize-icon-16.png').attr('class', 'ttc-add-icon').off().on('click', minimizeFilter);    
}

function removeFilter() {
    $(this).parent().hide().children(':not(.submit)').remove();
    if (window.location.search != "")
        window.location.replace("index")
}

function addStackFilter(){

	var count = $('.ttc-stack-filter').length + 1;
	var stackFilter = document.createElement("div");
	$(stackFilter).attr('class','ttc-stack-filter').attr('name','stack-filter['+count+']').appendTo('#filter-stack');
	
	var addButton = document.createElement("img");
	$(addButton).attr('class','ttc-add-icon').attr('src', '/img/add-icon-16.png').on('click', addStackFilter);
	$(stackFilter).append(addButton);
	
	var deleteButton = document.createElement("img");
	$(deleteButton).attr('class','ttc-add-icon').attr('src', '/img/remove-icon-16.png').on('click', removeStackFilter);
	$(stackFilter).append(deleteButton);
	
	// retrieve the contents of the array stored by javascript in window.app
	var fieldOptions = window.app.filterFieldOptions;
	// add dropdown for fields
	var filterFieldDropDown = document.createElement("select");
	$(filterFieldDropDown).attr('name','filter_param['+count+'][1]');
	$(filterFieldDropDown).append(new Option("", ""))
	    .on('click', function(event){supplyOperatorOptions(this);});
	$.each(fieldOptions, function(value, details) {
	        $(filterFieldDropDown).append(new Option(details['label'], value));
	});
	$(stackFilter).append(filterFieldDropDown);
	
}

function removeStackFilter(){
	$(this).parent().remove();
	if($(".ttc-stack-filter").length == 0){
	    $('#advanced_filter_form').hide();
	    $('#quick_filter_form').show();
	    if (window.search != "")
	        window.location.replace("index")
	}
}

function hasNoStackFilter(){
	if($(".ttc-stack-filter").length == 0){
		addStackFilter();
	}
}

function supplyOperatorOptions(elt) {
    $(elt).nextAll('input,select').remove();
    var field = $(elt).val();
    if (field == "")
        return;
    var operators = window.app.filterFieldOptions[field]['operators'];

    var operatorDropDownName = $(elt).attr('name').replace(new RegExp("\\[1\\]$","gm"), "");
 
    var operatorDropDown = document.createElement("select");
	$(operatorDropDown).attr('name', operatorDropDownName + '[2]');
	$(operatorDropDown).on('click', function(){ supplyParameterOptions(this) });
	$.each(operators, function(operator, details) {
	        $(operatorDropDown).append(new Option(localize_label(operator), operator));
	});
	$(elt).after(operatorDropDown);
	supplyParameterOptions(operatorDropDown);
}


function supplyParameterOptions(operatorElt) {

    $(operatorElt).nextAll('input,select').remove();

    var name = $(operatorElt).attr('name').replace(new RegExp("\\[2\\]$","gm"), "");
    var field = $('[name="'+name+'[1]"]').val()
    var operator = $(operatorElt).val();
    var operatorType = window.app.filterFieldOptions[field]['operators'][operator]['parameter-type'];

    switch (operatorType) 
    {
    case "none":
        break;
    case "date":
	    $(operatorElt).after("<input name='"+name+"[3]'></input>");
	    $("[name='"+name+"[3]']").datepicker();
        break;
    case "text":
        $(operatorElt).after("<input name='"+name+"[3]'></input>");
	    break;
	case "dialogue":
	    $(operatorElt).after("<select name='"+name+"[3]'></select>");
	    var options = window.app.filterParameterOptions[operatorType];
        $.each(options, function(key, value){
                $("[name='"+name+"[3]']").append(new Option(value['name'], key));      
        })
        break;
    case "interaction":
        $(operatorElt).after("<select name='"+name+"[3]'></select>");
	    var options = window.app.filterParameterOptions['dialogue'];
        $.each(options, function(dialogueId, details){
                $.each(details['interactions'], function(interactionId, content) { 
                        $("[name='"+name+"[3]']").append(new Option(details['name']+" - "+content, interactionId));
                });      
        })
        break;
	default:
	    $(operatorElt).after("<select name='"+name+"[3]' data='"+operatorType+"'></select>");
	    var options = window.app.filterParameterOptions[operatorType];
        $.each(options, function(key, value){
                $("[name='"+name+"[3]']").append(new Option(value, key));      
        })
    }
}

function generateDropdown(event) {
    item = $("[name='"+event.data.filterPrefix+"[2]']");
    if ($(item).val() != "") {
        if ($("[name='"+event.data.filterPrefix+"[3]']").length > 0) {
            $("[name='"+event.data.filterPrefix+"[3]']").empty();
        } else {
            $(item).after("<select name='"+event.data.filterPrefix+"[3]'></select>");
        }
        if (window.app.dialogueConditionOptions[$(item).val()]['interactions'] == null) {
            $("[name='"+event.data.filterPrefix+"[3]']").remove();
            return;
        }
        $.each(window.app.dialogueConditionOptions[$(item).val()]['interactions'], function(id, content){
                $("[name='"+event.data.filterPrefix+"[3]']").append(new Option(content, id));      
        })
    }
}


function S4() {
   return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
}
function guid() {
   return (S4()+S4()+S4());
}

//Necessary for IE browser
if (!Array.indexOf) {
  Array.prototype.indexOf = function (obj, start) {
    for (var i = (start || 0); i < this.length; i++) {
      if (this[i] == obj) {
        return i;
      }
    }
    return -1;
  }
}


function generateExportDialogue(obj) {
    var url = $(obj).attr("url") + ".json" + window.location.search;
    var dialog = $('<div id="export-dialogue" style="display:none" >'+localized_messages['generating_file']+'</div>').appendTo('body');
        dialog.dialog({ 
                title: localized_actions['export'],
                close: function(event, ui) {
                    dialog.remove();
                },
                modal: true
        });
        $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                success : function(response) {
                    if (response["status"]=="fail") {
                        $("#export-dialogue").html("fail: "+ response["message"]);
                        return;
                    }
                    $("#export-dialogue").html(localized_messages['download_should_start']);
                    setTimeout(function() {
                            window.location.replace("download?file="+response["file"])
                    }, 1000);
                }
        });
} 

function generateMassTagDialogue(obj){
	var url = $(obj).attr("url") + window.location.search;
	
	var dialog = $('<div id="masstag-dialogue" style="display:none">'+
		'<form name="formTag" action=\'javascript:submitMassTag()\' url="'+url+'" method="get" onsubmit="return alphanumeric()">'+
		'<input type="text" name="tag" id="masstag-tags">'+
		'<div id="masstag-error-message" class="masstag-error" style="display:none"/>'+
		'<input type="submit" value="Tag" id="clicky">'+
		'</form>'+
		'</div>').appendTo('body');
	dialog.dialog({
			title: localized_actions['mass_tag'], 
			close:function(event, ui){
			dialog.remove(); 
			},
			model: true
	});
}

function submitMassTag(){	
	var tag = $('[name*="tag"]').val();	
	var url = $('#masstag-dialogue').find('form').attr('url');
	if(url.contains('?')){
		window.location= url+"&tag="+tag;
	}else{
		window.location= url+"?tag="+tag;
	}		
}
          
function alphanumeric() {
	var tagRegex = new RegExp('^[a-zA-Z0-9]+(,(\\s)?[a-zA-Z0-9]+)*$','i');	
	var tag = $('#masstag-tags').val();
	if(tag.match(tagRegex)){  				
		return true;  
	}  
	else{
		$('#masstag-error-message').text('Your MassTag has special caharacters.These are not allowed').show();
		//localization of the error message
		return false;  
	}  
}

function addPredefinedContent() {
	var predefinedMessages = window.app["predefinedMessageOptions"];
	var messageId = $("#predefined-message option:selected").val();
	$.each(predefinedMessages, function (i, predefinedMessage) {
	    if (messageId == predefinedMessage.id) {
	        if ($("#unattached-content").val() != "") {                    
		    var test = confirm(localized_errors['warning_unattached_message']);
		    if (test == false) {
		        $("#predefined-message option:eq(0)").prop("selected", true);
		        return;
		    }
		}
		$("#unattached-content").val(predefinedMessage.content);
	    }
	});
}

function loadParameterOptions(parameter, url) {
    $.ajax({
        url: url,
        success: function(data){
            $('#connectionState').hide();
            if (data['results']) {
                var options = {};
                for (var i = 0; i < data['results'].length; i++) {
                    options[data['results'][i]] = data['results'][i];
                }
                window.app.filterParameterOptions[parameter] = options;
                $("select[data='"+parameter+"']").each( function(index, select) {
                    currentVal = $(select).val();
                    $(select).empty();
                    $.each(options, function(key, value){
                            $(select).append(new Option(value, key));      
                    })
                    $(select).val(curentVal);
                });

            }
        },
        timeout: 0,
        error: vusionAjaxError
    });
}
