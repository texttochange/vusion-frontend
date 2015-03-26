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


function addContentFormHelp(baseUrl) {
    if (!baseUrl)
        baseUrl="../.."
    addFormHelp(baseUrl, 'content', $("[name*='.content']").prev(":not(:has(img)):not(div):not(span)"));
    addFormHelp(baseUrl, 'forward-content', $("[name*='.forward-content']").prev(":not(:has(img)):not(div):not(span)"));
    addFormHelp(baseUrl, 'forward-to', $("[name*='.forward-to']").prev(":not(:has(img)):not(div):not(span)"));
    addFormHelp(baseUrl, 'set-forward-message-condition', $("[name*='set-forward-message-condition']").next(":not(:has(img))"));
    addFormHelp(baseUrl, 'template', $("[name*='[template]']").prev(":not(:has(img)):not(div)"));
    addFormHelp(baseUrl, 'keyword', $("[name*='\.keyword']").prev("label").not(":has(img)"));
    addFormHelp(baseUrl, 'forward-url', $("[name*='\.forward-url']").prev("label").not(":has(img)"));
    addFormHelp(baseUrl, 'proportional-labelling', $("[value='proportional-labelling']:checked").parent().next().children('legend').not(":has(img)"));
    addFormHelp(baseUrl, 'proportional-tagging', $("[value='proportional-tagging']:checked").parent().next().children('legend').not(":has(img)"));
    addFormHelp(baseUrl, 'content', $("[name*='.invite-content']").prev(":not(:has(img)):not(div):not(span)"));
    addFormHelp(baseUrl, 'feedback-inviter', $("[name*='.feedback-inviter']").prev(":not(:has(img)):not(div):not(span)"));
    addFormHelp(baseUrl, 'keep-tags', $("[name*='.keep-tags']").prev(":not(:has(img)):not(div):not(span)"));
    addFormHelp(baseUrl, 'keep-labels', $("[name*='.keep-labels']").prev(":not(:has(img)):not(div):not(span)"));
}   


function addFormHelp(baseUrl, name, selector) {
    $.each(selector,
        function (key, elt){
            $("<img class='ttc-help' src='/img/help-icon-16.png'/>").appendTo($(elt)).click(function(){requestHelp(this, baseUrl, name)});
        });
}


function requestHelp(elt, baseUrl, topic) {
    if ($($(elt).parent().next()).attr('class') == 'ttc-help-box') {
        $(elt).parent().next().remove();
        return;
    }
    $("<div class='ttc-help-box'><img src='/img/ajax-loader.gif' /></div>").insertAfter($(elt).parent())
    $.ajax({
        url: '/documentation.json', 
        type: 'GET',
        data: 'topic='+topic,
        dataType: 'json',
        success: function(response) {
            if (response['status'] == 'fail') {
                $(".ttc-help-box").html(response['message']);
            } else {
                $(".ttc-help-box").html(response['documentation']);
            }
        }
    }); 
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
            message = localized_errors['vusion_ajax_action_failed']+" "+this.userAction;
            message = message.replace(/\{0\}/g, this.userAction)
        }
        $('#flashMessage').show().text(message).attr('class', 'message failure');
    }
    if (textStatus == 'timeout') {
        $('#connectionState').show().text(localized_errors['vusion_ajax_timeout_error']);
    }
    reactivateSaveButtons();
}


function formSubmit() {
    disableSaveButtons();
    $("#dynamic-generic-program-form").submit();
}

function disableSaveButtons() {
    $("#dynamic-generic-program-form").attr("disabled", "disabled");	
}

function reactivateSaveButtons() {
    $("#dynamic-generic-program-form").removeAttr("disabled");
}

function isFormSubmit(element) {
    return $("#dynamic-generic-program-form").attr("disabled") != "disabled";
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
            var option = new Option(localize_label(text), val);
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
    
    $.each(stackRules, function(i, rule) {
            index = i + 1;
            addStackFilter();
            $("select[name='filter_param["+index+"][1]']").val(rule[1]).children("option[value="+rule[1]+"]").change();
            if (typeof(rule[2]) === 'undefined') return true;
            $("[name='filter_param["+index+"][2]']").val(rule[2]).change();
            if (typeof(rule[3]) === 'undefined') return true;
            // If the selected element is not loaded, add it to the drop down
            if ($("[name='filter_param["+index+"][3]']select").size() > 0 && $("[name='filter_param["+index+"][3]'] option").size() == 1) {
                $("[name='filter_param["+index+"][3]']").prepend(new Option(rule[3],rule[3]))
            } 
            $("[name='filter_param["+index+"][3]']").val(rule[3]);
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
        window.location.replace(window.location.href.split("?")[0])
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
    $(filterFieldDropDown).on('change', function(event) {
        supplyOperatorOptions(this);
    });
    $.each(fieldOptions, function(value, details) {
            var option = new Option(details['label'], value);
            $(filterFieldDropDown).append(option);
    });
     
    $(stackFilter).append(filterFieldDropDown);
    $(filterFieldDropDown).change();
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
    $(operatorDropDown).on('change', function(){ 
        supplyParameterOptions(this); 
    });
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
        $("[name='"+name+"[3]']").datepicker({
                timeFormat: "hh:mm",
                timeOnly: false,
                dateFormat:"dd/mm/yy"
        });
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
        if (options = []) {
            $("[name='"+name+"[3]']").append(new Option("", ""));
        }
        break;
    case "interaction":
        $(operatorElt).after("<select name='"+name+"[3]'></select>");
        var options = window.app.filterParameterOptions['dialogue'];
        $.each(options, function(dialogueId, details){
                $.each(details['interactions'], function(interactionId, content) { 
                        $("[name='"+name+"[3]']").append(new Option(details['name']+" - "+content, interactionId));
                });      
        })
        if (options = []) {
            $("[name='"+name+"[3]']").append(new Option("", ""));
        }
        break;
    default:
        $(operatorElt).after("<select name='"+name+"[3]' data='"+operatorType+"'></select>");
        var options = window.app.filterParameterOptions[operatorType];
        $.each(options, function(key, value){
                $("[name='"+name+"[3]']").append(new Option(value, key));      
        })
        if (options = []) {
            $("[name='"+name+"[3]']").append(new Option("", ""));
        }
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
    if(url.indexOf('?') != -1){
        window.location= url+"&tag="+tag;
    }else{
        window.location= url+"?tag="+tag;
    }        
}


function generateMassUntagDialogue(obj){
    var url = $(obj).attr("url") + window.location.search;
    
    var dialog = $('<div id="massuntag-dialogue" style="display:none">'+
        '<form name="formUntag" action=\'javascript:submitMassUntag()\' url="'+url+'" method="get" onsubmit="return alphanumeric()">'+
        '<input type="text" name="untag" id="masstag-tags">'+
        '<div id="masstag-error-message" class="masstag-error" style="display:none"/>'+
        '<input type="submit" value="Untag" id="clicky">'+
        '</form>'+
        '</div>').appendTo('body');
        dialog.dialog({
                title: localized_actions['mass_untag'], 
                close:function(event, ui){
                    dialog.remove(); 
                },
                model: true
        });
}


function submitMassUntag() {    
    var tag = $('[name*="untag"]').val();    
    var url = $('#massuntag-dialogue').find('form').attr('url');
    var untagConfirm = confirm("Do you want to delete this tag?");
    if (untagConfirm == true)
    {
        if(url.indexOf('?') != -1){
            window.location= url+"&tag="+tag;
        }else{
            window.location= url+"?tag="+tag;
        }
    }    
}


function alphanumeric() {
    var tagRegex = new RegExp('^[a-zA-Z0-9]+((\\s)[a-zA-Z0-9]+)*$');    
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


function loadFilterParameterOptions(parameter, url) {
    //Dirty: passing of the rule require rencoding the & in the url parameters 
    url = url.replace(/&amp;/g, "&");
    $.ajax({
            url: url,
            contentType: 'application/json; charset=utf-8',
            success: function(data){
                $('#connectionState').hide();
                if (data['data']) {
                    var results = data['data'];
                    var options = {};
                    for (var i = 0; i < results.length; i++) {
                        options[results[i]] = results[i];
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


function loadPaginationCount(url) {
    $('#data-control-nav .next a').addClass('disabled');
    //Dirty: passing of the rule require rencoding the & in the url parameters 
    url = url.replace(/&amp;/g, "&");
    $.ajax({
        url: url,
        success: function(data){
            if (data['status'] == 'ok') {
                $('#data-control-nav .next a').removeClass('disabled');
                $('.ttc-page-count').attr('title', data['pagination-count']);
                $('#paging-count').text(data['rounded-count']);
                var paging_end = parseInt($('#paging-end').text());
                var paging_count = parseInt(data['pagination-count']);
                if (paging_end >= paging_count) {
                    $('#data-control-nav .next').addClass('disabled').empty().text('>');
                }
            }
        },
        timeout: 45000,  //45 sec
        error: function(jqXHR, textStatus, errorthrown) {
            $('#data-control-nav .next a').removeClass('disabled');
            $('#paging-count').text(localized_labels['many']);
        }
    });
}


function loadProgramStats(){
    var  programs = window.app.programs;
    var program = {};
    for(var i = 0; i< programs.length; i++){
        program = programs[i];
        var programUrl = program['Program']['url'];
        $.ajax({
                type: "GET",
                contentType: 'application/json; charset=utf-8',
                url: "/"+programUrl+"/programAjax/getStats.json",
                success: function(data){
                    renderStats(data['program-url'], data['program-stats'])
                },
                timeout: 360000,  // 6 minutes
                error: function(jqXHR, textStatus, errorThrown){
                    var url = this.url;
                    renderStatsError(url)
                }
        });
    }
}


function renderStats(programUrl, stats) {
    if(window.app.isProgramSpecific) {
        $("#programstats").empty().append(generateHtmlProgramStatsInside(stats));
    }
    $("#"+programUrl+" .ttc-program-stats").empty().append(generateHtmlProgramStats(stats));
}


function renderStatsError(url) {
    if(window.app.isProgramSpecific) {
        $("#programstats").empty().append(generateHtmlProgramStatsInside())
    }
    $("#"+getParameterByName(url, 'programUrl')+" .ttc-program-stats").empty().append(generateHtmlProgramStats())
}


function generateHtmlProgramStats(programStats) {
    if(programStats == null){
        programStats = {
            'active-participant-count': {'exact': 'N/A'},
            'participant-count' : {'exact': 'N/A'},
            'all-received-messages-count': {'exact': 'N/A'},
            'current-month-received-messages-count' : {'exact': 'N/A'},
            'all-sent-messages-count' : {'exact': 'N/A'},
            'current-month-sent-messages-count' : {'exact': 'N/A'},
            'total-current-month-messages-count' : {'exact': 'N/A'},
            'history-count' : {'exact': 'N/A'},
            'today-schedule-count' : {'exact': 'N/A'},
            'schedule-count' : {'exact': 'N/A'},
            'object-type' : {'exact': 'program-stats'},
            'model-version': {'exact': '1'}};
    }
    
    var myTemplate ='<div>'+
    '<span class="stat" '+ ((programStats['active-participant-count']['exact'] != 'N/A' || programStats['participant-count']['exact'] != 'N/A') ? 'title="'+localize_label("Optin/Total participant(s)")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +'>'+
    ((programStats['active-participant-count']['exact'] != 'N/A' || programStats['participant-count']['exact'] != 'N/A') ? 'ACTIVE_PARTICIPANT/TOTAL_PARTICIPANT' : 'N/A')+                    
    '</span> '+localize_label('participant(s)')+
    '</div>'+
    '<div>'+
    '<span class="stat" '+ ((programStats['history-count']['exact'] != 'N/A' || programStats['total-current-month-messages-count']['exact'] != 'N/A') ? 'title="'+localize_label("Total(total current month) message(s)")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +'>'+
    'TOTAL_HISTORY(TOTAL_CURRENT_MONTH_MESSAGES)'+
    '</span> '+localize_label('total message(s)')+
    '</div>'+
    '<div>'+
    '<span class="stat" '+ ((programStats['all-received-messages-count']['exact'] != 'N/A' || programStats['current-month-received-messages-count']['exact'] != 'N/A') ? 'title="'+localize_label("Total(current month) received")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +'>'+
    'ALL_RECEIVED_MESSAGES(CURRENT_MONTH_RECEIVED_MESSAGES)'+
    '</span> '+localize_label('received')+' -'+
    '<span class="stat" '+ ((programStats['all-sent-messages-count']['exact'] != 'N/A' || programStats['current-month-sent-messages-count']['exact'] != 'N/A') ? 'title="'+localize_label("Total(current month) sent")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +'>'+
    'ALL_SENT_MESSAGES(CURRENT_MONTH_SENT_MESSAGES)'+
    '</span> '+localize_label('sent message(s)')+
    '</div>'+
    '<div>'+
    '<span class="stat" '+ ((programStats['schedule-count']['exact'] != 'N/A' || programStats['today-schedule-count']['exact'] != 'N/A') ? 'title="'+localize_label("Total(today) schedule(s)")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +'>'+
    'SCHEDULE(TODAY_SCHEDULE)'+
    '</span> '+localize_label('schedule(s)')+
    '</div>'
    
    myTemplate = myTemplate.replace('ACTIVE_PARTICIPANT', programStats['active-participant-count']['exact']);
    myTemplate = myTemplate.replace('TOTAL_PARTICIPANT', programStats['participant-count']['exact']);
    myTemplate = myTemplate.replace('TOTAL_HISTORY', programStats['history-count']['exact']);
    myTemplate = myTemplate.replace('TOTAL_CURRENT_MONTH_MESSAGES', programStats['total-current-month-messages-count']['exact']);
    myTemplate = myTemplate.replace('ALL_RECEIVED_MESSAGES', programStats['all-received-messages-count']['exact']);
    myTemplate = myTemplate.replace('CURRENT_MONTH_RECEIVED_MESSAGES', programStats['current-month-received-messages-count']['exact']);
    myTemplate = myTemplate.replace('ALL_SENT_MESSAGES', programStats['all-sent-messages-count']['exact']);
    myTemplate = myTemplate.replace('CURRENT_MONTH_SENT_MESSAGES', programStats['current-month-sent-messages-count']['exact']);
    myTemplate = myTemplate.replace('SCHEDULE', programStats['schedule-count']['exact']);
    myTemplate = myTemplate.replace('TODAY_SCHEDULE', programStats['today-schedule-count']['exact']);
    return myTemplate;
}


function generateHtmlProgramStatsInside(programStats) {
    if(programStats == null){
        programStats = {
            'active-participant-count': 'N/A',
            'participant-count' : 'N/A',
            'all-received-messages-count': 'N/A',
            'current-month-received-messages-count' : 'N/A',
            'all-sent-messages-count' : 'N/A',
            'current-month-sent-messages-count' : 'N/A',
            'total-current-month-messages-count' : 'N/A',
            'history-count' : 'N/A',
            'today-schedule-count' : 'N/A',
            'schedule-count' : 'N/A',
            'object-type' : 'program-stats',
        'model-version': '1'};
    }
    
    var myTemplate ='<table class="stat">'+
    '<tr '+
    ((programStats['active-participant-count'] != 'N/A' || programStats['participant-count'] != 'N/A') ? 'title="'+localize_label('Participant(s) Optin/Total')+'"' : 'title="'+localize_label("Stats Not Available")+'"') +' >'+
    '<td><img  src="/img/participant-icon-14.png"></td><td>'+
    ((programStats['active-participant-count'] != 'N/A' || programStats['participant-count'] != 'N/A') ? 'ACTIVE_PARTICIPANT/TOTAL_PARTICIPANT' : 'N/A')+
    ' '+localize_label('participant(s)')+'</td></tr>'+
    '<tr '+
    ((programStats['history-count'] != 'N/A' || programStats['total-current-month-messages-count'] != 'N/A') ? 'title="'+localize_label("Message(s)-Total(Current-Month)")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +' >'+
    '<td><img src="/img/message-icon-14.png"> </td><td>'+
    ((programStats['active-participant-count'] != 'N/A' || programStats['participant-count'] != 'N/A') ? 'TOTAL_HISTORY(TOTAL_CURRENT_MONTH_MESSAGES)' : 'N/A')+
    ' '+localize_label('message(s)')+'</td></tr>'+
    '<tr  '+
    ((programStats['all-received-messages-count'] != 'N/A' || programStats['current-month-received-messages-count'] != 'N/A') ? 'title="'+localize_label("Received Total(Current Month)")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +' >'+
    '<td><img src="/img/incoming-icon-14.png"></td><td>'+
    ((programStats['active-participant-count'] != 'N/A' || programStats['participant-count'] != 'N/A') ? 'ALL_RECEIVED_MESSAGES(CURRENT_MONTH_RECEIVED_MESSAGES)' : 'N/A')+
    ' '+localize_label('received')+'</td></tr>'+
    '<tr  '+
    ((programStats['all-sent-messages-count'] != 'N/A' || programStats['current-month-sent-messages-count'] != 'N/A') ? 'title="'+localize_label("Sent Total(Current Month)")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +' >'+
    '<td><img src="/img/outgoing-icon-14.png"></td><td>'+
    ((programStats['active-participant-count'] != 'N/A' || programStats['participant-count'] != 'N/A') ? 'ALL_SENT_MESSAGES(CURRENT_MONTH_SENT_MESSAGES)' : 'N/A')+
    ' '+localize_label('sent')+'</td></tr>'+
    '<tr  '+
    ((programStats['schedule-count'] != 'N/A' || programStats['today-schedule-count'] != 'N/A') ? 'title="'+localize_label("Schedule Total(Today)")+'"' : 'title="'+localize_label("Stats Not Available")+'"') +' >'+
    '<td><img  src="/img/schedule-icon-14.png"></td><td> '+
    ((programStats['active-participant-count'] != 'N/A' || programStats['participant-count'] != 'N/A') ? 'SCHEDULE(TODAY_SCHEDULE)' : 'N/A')+
    ' '+localize_label('scheduled')+'</td></tr>'+
    '</table>'
    
    myTemplate = myTemplate.replace('ACTIVE_PARTICIPANT', programStats['active-participant-count']['rounded']);
    myTemplate = myTemplate.replace('TOTAL_PARTICIPANT', programStats['participant-count']['rounded']);
    myTemplate = myTemplate.replace('TOTAL_HISTORY', programStats['history-count']['rounded']);
    myTemplate = myTemplate.replace('TOTAL_CURRENT_MONTH_MESSAGES', programStats['total-current-month-messages-count']['rounded']);
    myTemplate = myTemplate.replace('ALL_RECEIVED_MESSAGES', programStats['all-received-messages-count']['rounded']);
    myTemplate = myTemplate.replace('CURRENT_MONTH_RECEIVED_MESSAGES', programStats['current-month-received-messages-count']['rounded']);
    myTemplate = myTemplate.replace('ALL_SENT_MESSAGES', programStats['all-sent-messages-count']['rounded']);
    myTemplate = myTemplate.replace('CURRENT_MONTH_SENT_MESSAGES', programStats['current-month-sent-messages-count']['rounded']);
    myTemplate = myTemplate.replace('SCHEDULE', programStats['schedule-count']['rounded']);
    myTemplate = myTemplate.replace('TODAY_SCHEDULE', programStats['today-schedule-count']['rounded']);
    return myTemplate;
}


function getParameterByName(url, name){
    name = name.replace (/[\[]/, "\\\[").replace (/[\]]/, "\\\]");
    var regex = new RegExp("[\\?&]" + name +"=([^&#]*)"),
    results = regex.exec(url);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, ""));
}


function clickProgramBox(url,event) {
    if (event.ctrlKey) {
        window.open("/"+url);
    } else {
        window.location.pathname = "/"+url;
    }
}

function popupBrowser(obj) {
    var url = $(obj).attr("url") + window.location.search;
    var newPopupWindow = window.open(url, 'reportissue', 'titlebar=no, toolbar=no, resizable=no, height=610, width=600');
    if (window.focus) {
        newPopupWindow.focus();
    }
    return false;
}

function popupBrowserClose() {
        window.close();
}

function popupNewBrowserTab(obj) {
    var url = $(obj).attr("url") + window.location.search;
    var newPopupWindow = window.open(url, '_blank');
    newPopupWindow.focus();
}

function disableSubmit() {
    $('#close-report').attr('style', 'visibility:hidden');
    $('#submit-report').attr('style', 'visibility:hidden');
    $('#sending-email').append(localized_messages['sending_report']);
}

function disableSend() {
    $('#send-invite').attr('style', 'visibility:hidden');
    $('#sending-email').append(localized_messages['sending_invite']);
}
