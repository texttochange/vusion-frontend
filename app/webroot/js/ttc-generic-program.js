var program = {"script": [ 
        "dialogues",
        ],
    "name" : "text",
    "partner": "text",
    "shortcode" : "text",
    "participants":["add-participant"],
    "add-group":"button",
    "add-participant":"button",
    "participant":["phone","name"],
    "phone":"text",
    "dialogues": ["add-dialogue"],
    "add-dialogue":"button",                    
    "Dialogue": ["name",  "checkbox-set-prioritized", "auto-enrollment", "interactions","dialogue-id", "activated"],
    "dialogue-id": "hidden",
    //"auto-enrollment": "select",
    "auto-enrollment": ["radio-type-auto-enrollment"],
    "radio-type-auto-enrollment": "radiobuttons",
    "type-auto-enrollment": {"none":"none", "all":"all"},
    //"auto-enrollment-options": [{"value":"none", "html":"None"}, {"value": "all", "html": "All participants"}],    
    "checkbox-set-prioritized": "checkboxes",
    "set-prioritized": {"prioritized": "prioritized"},
    "interactions":["add-interaction"],
    "interaction":["radio-type-schedule", "radio-type-interaction","interaction-id", "activated"],
    "interaction-id":"hidden",
    "activated":"hidden",
    "add-interaction":"button",
    "announcement": ["content"],
    "question-answer": ["content","keyword", "checkbox-set-use-template", "radio-type-question", "checkbox-set-max-unmatching-answers", "radio-type-unmatching-feedback","checkbox-set-reminder"],
    "radio-type-unmatching-feedback" : "radiobuttons",
    "type-unmatching-feedback": {
        "no-unmatching-feedback": "no-unmatching-feedback",
        "program-unmatching-feedback":"program-unmatching-feedback",
        "interaction-unmatching-feedback":"interaction-unmatching-feedback"},
    "interaction-unmatching-feedback": ["unmatching-feedback-content"],
    "unmatching-feedback-content": "textarea",
    "checkbox-set-use-template": "checkboxes",
    "set-use-template": {"use-template": "use-template"},
    "checkbox-set-max-unmatching-answers": "checkboxes",
    "set-max-unmatching-answers": {"max-unmatching-answers": "max-unmatching-answers"},
    "max-unmatching-answers": ["max-unmatching-answer-number", "max-unmatching-answer-actions"],
    "max-unmatching-answer-number": "text",
    "max-unmatching-answer-actions": ["add-max-unmatching-answer-action"],
    "add-max-unmatching-answer-action": "button",
    "max-unmatching-answer-action": ["radio-type-action"],
    "radio-type-question": "radiobuttons", 
    "type-question":{"closed-question":"closed-question","open-question":"open-question"},
    "closed-question": ["label-for-participant-profiling", "checkbox-set-answer-accept-no-space", "answers"],
    "checkbox-set-answer-accept-no-space": "checkboxes",
    "set-answer-accept-no-space": {"answer-accept-no-space": "answer-accept-no-space"},
    "label-for-participant-profiling": "text",
    "open-question": ["answer-label", "feedbacks"],
    "answer-label": "text",
    "requests-responses":["add-request-response"],
    "add-request-response":"button",
    "request-response":["content","responses","actions"],
    "radio-type-routing":"radiobuttons",
    "type-routing":{"keyword-routing": "Keyword routing","phone-routing":"Phone number routing (all incomming message from participant will be root to this program)"},    
    "keyword-routing":["keyword"],
    "answers":["add-answer"],
    "add-answer": "button",
    "answer": ["choice","feedbacks", "answer-actions"],
    "feedbacks":["add-feedback"],
    "answer-actions": ["add-answer-action"],
    "add-answer-action": "button",
    "answer-action": ["radio-type-answer-action"],
    "radio-type-answer-action": "radiobuttons",
    "type-answer-action": {"optin":"optin", "optout": "optout", "enrolling":"enrolling", "delayed-enrolling": "delayed-enrolling", "tagging":"tagging", "reset":"reset", "feedback":"feedback"},
    "add-request":"button",
    "Request": ["keyword", "checkbox-set-no-request-matching-try-keyword-only", "responses", "actions"],
    //"radio-type-request-matching": "radiobuttons",
    //"type-request-matching": {"exact-matching":"exact-matching", "keyword-only-matching": "keyword-only-matching"},
    "checkbox-set-no-request-matching-try-keyword-only": "checkboxes",
    "set-no-request-matching-try-keyword-only": {"no-request-matching-try-keyword-only": "no-request-matching-try-keyword-only"},
    "responses":["add-response"],
    "actions":["add-action"],
    "add-response":"button",
    "response":["content"],
    "radio-type-action": "radiobuttons",
    "add-action":"button",
    "add-feedback":"button",
    "action":["radio-type-action"],
    "type-action": {"optin": "optin", "optout": "optout", "enrolling":"enrolling", "delayed-enrolling": "delayed-enrolling", "tagging":"tagging", "reset":"reset", "feedback":"feedback"},
    "choice":"text",
    "tagging":["tag"],
    "tag":"text",
    "enrolling":["enroll"],
    "enroll":"select",
    "delayed-enrolling":["enroll", "offset-days"],
    "add-request-reply":'button',
    "request-reply":["keyword","add-feedback","radio-type-action"],
    "id":"text",
    "type":"text",
    "radio-type-interaction":"radiobuttons",
    "type-interaction": {
        "announcement":"announcement",
        "question-answer":"question",
        "question-answer-keyword": "question-multi-keyword"},
    "question-answer-keyword": ["content", "label-for-participant-profiling", "answer-keywords", "checkbox-set-reminder"],
    "answer-keywords":["add-answer-keyword"],
    "add-answer-keyword":"button",
    "answer-keyword": ["keyword","feedbacks", "answer-actions"],
    "radio-type-schedule":"radiobuttons",
    "type-schedule": {
        "fixed-time":"fixed-time",
        "offset-days":"offset-days",
        "offset-time":"offset-time",
        "offset-condition": "offset-condition"},
    "radio-type-schedule-reminder":"radiobuttons",
    "type-schedule-reminder": {
        "reminder-offset-time":"offset-time",
        "reminder-offset-days":"offset-days"},
    "content":"textarea",
    "date": "text",
    "fixed-time":["date-time"],
    "date-time":"text",
    "offset-time":["minutes"],
    "offset-days":["days","at-time"],
    "reminder-offset-time":["reminder-minutes"],
    "reminder-offset-days":["reminder-days","reminder-at-time"],
    "reminder-days":"text",
    "reminder-minutes":"text",
    "reminder-at-time":"text",
    "offset-condition": ["offset-condition-interaction-id"],
    "offset-condition-interaction-id": "select",
    "wait-answer": ["minutes"],
    "days":"text",
    "minutes":"text",
    "at-time":"text",
    "time": "text",
    "keyword":"text",
    "feedback":["content"],
    "checkbox-set-reminder":"checkboxes",
    "set-reminder": {"reminder":"reminder"},
    "reminder":["reminder-number","radio-type-schedule-reminder","reminder-actions"],
    "reminder-number":"text",
    "reminder-actions": ["add-reminder-action"],
    "add-reminder-action": "button",
    "reminder-action": ["radio-type-action"],
};

(function($)
{

    function _addToObject(obj, data, fn)
    {
        if (typeof (data) == "string")
        {
            if (!$.isArray(obj[data])) {
                obj[data] = [];
            }
            obj[data].push(fn);
        } else if (typeof (data) == "object")
        {
            $.each(data, function(name, fn)
            {
                _addToObject(obj, name, fn);
            });
        }
    }
    
    /**
     * @page plugin Plugin
     * @parent index
     *
     * Functions that will be used as jQuery plugins.
     */
    $.fn.extend(
    {

        buildTtcForm : function(type, object, submitCall) {
            $(this).empty().buildForm(fromBackendToFrontEnd(type, object, submitCall));
            activeForm();
            //On load fold every element 
            $('.ttc-fold-icon').each(function(){ $(this).trigger('click') })
            /*$("[name='Dialogue.interactions']").sortable({axis: 'y', cancel: 'button'});
            $("[name='Dialogue.interactions'] input").bind('click.sortable mousedown.sortable',function(ev){
                ev.target.focus();
            });
            $("[name='Dialogue.interactions'] textarea").bind('click.sortable mousedown.sortable',function(ev){
                ev.target.focus();
            });
            $("[name='Dialogue.interactions']").disableSelection();*/
        },
    });
})(jQuery);

function saveFormOnServer(){
        
    var formData = form2js('dynamic-generic-program-form', '.', true);
    //alert();
    var indata= JSON.stringify(formData, null, '\t');

    var saveUrl = location.href.indexOf("edit/")<0 ? "./save.json" : "../save.json";

    $.ajax({
        url: saveUrl,
        type:'POST',
        data: indata, 
        contentType: 'application/json; charset=utf-8',
        dataType: 'json', 
        success: function(response) {
            if (response['status'] == 'fail') {
                $("#flashMessage").attr('class', 'message error').show().text(response['message']);
                reactivateSaveButtons();
                return;
            }
            if (location.href.indexOf(response['dialogue-obj-id'])<0){
                $("#flashMessage").show().attr('class', 'message success').text(response['message']+" "+localized_messages['wait_redirection']);
                setTimeout( function() { 
                    if (location.href.indexOf("edit/")<0) 
                        window.location.replace("edit/" + response['dialogue-obj-id']);
                    else 
                        window.location.replace(response['dialogue-obj-id']);
                    }, 3000);
            } else {
                $("#flashMessage").attr('class', 'message success').show().text(response['message']);
                $("#flashMessage").delay(3000).fadeOut(1000);
                reactivateSaveButtons();
            }
        },
        timeout: 3000,
        error: saveAjaxError,
        userAction: localized_actions['save_dialogue'],
    });
}

function saveRequestOnServer(){
        
    var formData = form2js('dynamic-generic-program-form', '.', true);
    //alert();
    var indata= JSON.stringify(formData, null, '\t');

    var saveUrl = location.href.indexOf("add")>0 ? "./add.json" : "../edit.json";

    $.ajax({
        url: saveUrl,
        type:'POST',
        data: indata, 
        contentType: 'application/json; charset=utf-8',
        dataType: 'json', 
        success: function(response) {
            if (response['status'] == 'fail') {
                $("#flashMessage").attr('class', 'message error').show().text(response['message']);
                reactivateSaveButtons();
                return;
            }
            if (location.href.indexOf("add")>0 && location.href.indexOf(response['request-id'])<0){
                $("#flashMessage").show().attr('class', 'message success').text(response['message']);
            setTimeout( function() { 
                    window.location.replace("edit/"+response['request-id']);
                }, 3000);
            } else {
                $("#flashMessage").attr('class', 'message success').show().text(response['message']);
                $("#flashMessage").delay(3000).fadeOut(1000)
                reactivateSaveButtons();
            }
        },
        timeout: 1000,
        error: saveAjaxError,
        userAction: localized_actions['save_request'],
    });
}

function convertDateToIso(data) {   
    return data;
}    

function clickBasicButton(){
                    
    //alert("click on add element "+$(this).prev('legend'));
    var object = null;
    var id = $(this).prevAll("fieldset").length;
    var eltLabel = $(this).attr('label');
    var tableLabel = $(this).parent().attr('name');
    //in case of radio button the parent name need the name to be added
    var r = new RegExp("\\]$","g");
    if (r.test(tableLabel)){
        tableLabel = tableLabel +"."+ eltLabel;
    }
    var parent = $(this).parent();
    
    var expandedElt = {"type":"fieldset","name":tableLabel+"["+id+"]","caption": localize_label(eltLabel),"elements":[]}
    
    if (eltLable='interaction') {
        object = {"interaction-id":guid()}
    }    
    configToForm(eltLabel, expandedElt, tableLabel+"["+id+"]", object);
    
    $(parent).formElement(expandedElt);
    
    $(this).parent().children("button").each(function(index,elt){
        $(elt).clone().appendTo($(parent));
        $(elt).remove();    
    })
    
    activeForm();
    
};

function hiddeUndisabled(key, item){
    $("[name='"+$(item).attr('name')+"']").attr("disabled", true);
    $(item).after("<input type='hidden' name='"+$(item).attr('name')+"' value='"+$(item).val()+"'/>")
}

function activeForm(){
    $.each($('.ui-dform-addElt'),function(item,value){
            if (!$.data(value,'events')) {
                $(value).click(clickBasicButton);
            }
    });
    $.each($("input[name*='type-']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    $.each($("select[name*='offset-condition-interaction-id']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).mouseover(updateOffsetConditions);
            };
    });
    $.each($(".ui-dform-fieldset:[name$=']']:not([radiochildren])").children(".ui-dform-legend:first-child"), function (key, elt){
            var deleteButton = document.createElement('img');
            $(deleteButton).attr('class', 'ttc-delete-icon').attr('src', '/img/delete-icon-16.png').click(function() {
                    $(this).parent().remove();
            });
            var foldButton = document.createElement('img');
            $(foldButton).attr('class', 'ttc-fold-icon').attr('src', '/img/minimize-icon-16.png').on('click', foldForm);
            $(elt).before(foldButton);
            $(elt).before(deleteButton);
            
    });
    $.each($("input[name*='at-time']"), function (key,elt){
            if (!$.data(elt,'events')){
                $(elt).timepicker({
                timeFormat: 'hh:mm'});
            };
    });
    $.each($("input[name*='reminder']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateCheckboxSubmenu);
            };
    });
    $.each($("input[name*='max-unmatching-answers']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateCheckboxSubmenu);
            };
    });
    $("input[name*='date-time']").each(function (key, item) {
            if ($(this).parent().parent().find("input[type='hidden'][name$='activated'][value='1']").length>0 && !isInFuture($(this).val())) {
                $(this).parent().parent().find("input").attr("readonly", true);
                $(this).parent().parent().find("textarea").attr("readonly", true);
                $(this).parent().parent().find("input[type='radio']:checked").each(hiddeUndisabled);
                $(this).parent().parent().find("input[type='checkbox']:checked").each(hiddeUndisabled);
                $(this).parent().parent().addClass("ttc-interaction-disabled");
            } else {
                if (!$.data(item,'events')){
                    $(item).datetimepicker({
                            timeFormat: 'hh:mm',
                            timeOnly: false,
                            dateFormat:'dd/mm/yy',
                            defaultDate: moment($("#local-date-time").text(), "DD/MM/YYYY HH:mm:ss").toDate(),
                            onSelect:function(){
                                $("#dynamic-generic-program-form").valid()},
                            onClose: function(){
                                $("#dynamic-generic-program-form").valid()
                    }});
                    $(item).rules("add",{
                            required:true,
                            isInThePast: $("#local-date-time").html(),
                            messages:{
                                required: wrapErrorMessage(localized_errors.validation_required_error),
                            }
                    });
                };
            } 
    });
    $("input[name*='at-time']").each(function (item) {
            $(this).rules("add",{
                required:true,
                messages:{
                    required: wrapErrorMessage(localized_errors.validation_required_error),
                }
            });
    });
    $("input[name*='\.keyword']").each(function (item) {
               $(this).rules("add",{
                    required:true,
                    doubleSpace:true,
                    keywordFormat:true,
                    keywordUnique:true,
                    messages:{
                         required: wrapErrorMessage(localized_errors.validation_required_error),
                         keywordFormat: wrapErrorMessage(localized_errors.validation_keywords_invalid_character_error),
                    }
                });
    });
    $("input[name*='type-question']:checked").each(function (item) {
        if ($("input[name*='type-question']:checked").val() == "close-question") {
            if($("input[name*='choice']").length == 0) {
                $("button[label='answer']").click();
                }
        }
    });
    $("input[name*='choice']").each(function (item) {
        $(this).rules("add",{
            required:true,
            choiceUnique: true,
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_error),
                choiceUnique: wrapErrorMessage(localized_errors.validation_choice_duplicate),
            }
        });
    });
    $("input[name*='name']").each(function (item) {
        $(this).rules("add",{
            required:true,
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_error),
            }
        });
    });
    $("input[name*='type-schedule']").each(function (item) {
        $(this).rules("add",{
            atLeastOneIsChecked:true,
            messages:{
                atLeastOneIsChecked: wrapErrorMessageInClass(
                    localized_errors.validation_required_checked,
                    "ttc-radio-validation-error"),
            }
        });
    });
    $("input[name*='type-interaction']").each(function (item) {
        $(this).rules("add",{
            atLeastOneIsChecked:true,
            messages:{
                atLeastOneIsChecked: wrapErrorMessageInClass(
                    localized_errors.validation_required_checked,
                    "ttc-radio-validation-error"),
            }
        });
    });
    $("input[name*='type-question']").each(function (item) {
        $(this).rules("add",{
            atLeastOneIsChecked:true,
            messages:{
                atLeastOneIsChecked: wrapErrorMessageInClass(
                    localized_errors.validation_required_checked,
                    "ttc-radio-validation-error"),
            }
        });
    });
    $("input[name*='type-unmatching-feedback']").each(function (item) {
        $(this).rules("add",{
            atLeastOneIsChecked:true,
            messages:{
                atLeastOneIsChecked: wrapErrorMessageInClass(
                    localized_errors.validation_required_checked,
                    "ttc-radio-validation-error"),
            }
        });
    });
    $("input[name*='answer-label']").each(function (item) {
        $(this).rules("add",{
            required:true,
            requireLetterDigitSpace: true,
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_answer_label),
                requireLetterDigitSpace: wrapErrorMessage(localized_errors.validation_required_letter_digit_space),
            }
        });
    });
    $("input[name$='label-for-participant-profiling']").each(function (item) {
        $(this).rules("add",{
            requireLetterDigitSpace: true,
            messages:{
                requireLetterDigitSpace: wrapErrorMessage(localized_errors.validation_required_letter_digit_space),
            }
        });
    });
    $("textarea[name*='content']").each(function (item) {
        $(this).rules("add",{
            required:true,
            messages:{
                required: wrapErrorMessageInClass(localized_errors.validation_required_content, "ttc-textarea-validation-error"),
            }
        });
    });
    $("input[name$='days']").each(function (item) {
        $(this).rules("add",{
            required:true,
            min: 1,
            messages:{
                required: wrapErrorMessageInClass(localized_errors.validation_required_error, "ttc-input-validation-error"),
                min: wrapErrorMessageInClass(localized_errors.validation_offset_days_min, "ttc-input-validation-error"),
            }
        });
    });
    $("input[name$='minutes']").each(function (item) {
        $(this).rules("add",{
            required:true,
            minutesSeconds: true,
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_error),
                minutesSeconds: wrapErrorMessage(localized_errors.validation_offset_time_min),
            }
        });
    });
    $("input[name$='number']").each(function (item) {
        $(this).rules("add",{
            required:true,
            min: 1,
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_error),
                min: wrapErrorMessage(localized_errors.validation_number_min),
            }
        });
    });
    
    addContentFormHelp();
    addCounter();

    
}

function expandForm(){
    $(this).parent().children().each(function(){ 
        if ($(this).attr('type')=='text')
            $(this).show();      //workaround for webkit bug that doesnt display sometimes the text input element       
        $(this).slideDown('fast')});
    $(this).parent().children('[class="ttc-fold-summary"]').remove();
    $(this).attr('src','/img/minimize-icon-16.png').attr('class', 'ttc-fold-icon').off().on('click', foldForm);
}

function foldForm(){
    var name = $(this).parent().attr('name');
    $(this).parent().children(":not(img):not(.ui-dform-legend)").slideUp('fast');
    var summary = $('[name="'+name+'.content"]').val();
    if (summary && summary != "")
        $(this).parent().append('<div class="ttc-fold-summary">'+summary.substring(0,70)+'...</div>');
    else {        
        var elt = $(this);
        if ($('[name="'+name+'"]').children('[name*=".choice"]').length > 0) {
	    generateFieldSummary(elt, name, 'choice');
        } else if ($('[name="'+name+'"]').children('[name*=".keyword"]').length > 0) {
	    generateFieldSummary(elt, name, 'keyword');
	} else {
    	    var action = $('[name="'+name+'.type-action"]:checked').val();
    	    if (action == null)
    	    	    action = $('[name="'+name+'.type-answer-action"]:checked').val();
    	    if (action == null)
    	    	    action = $('[name="'+name+'.type-reminder-action"]:checked').val();
    	    if (action && action != "") {
    	    	    $(elt).parent().append('<div class="ttc-fold-summary">'+action+'</div>');
    	    }
	}
    }
    $(this).attr('src','/img/expand-icon-16.png').attr('class', 'ttc-expand-icon').off().on('click', expandForm);
}

function generateFieldSummary(elt, parentName, field)
{
    var fieldValue = $('[name="'+parentName+'.'+field+'"]').val();
    if (fieldValue && fieldValue != "") {
    	    $(elt).parent().append('<div class="ttc-fold-summary">'+fieldValue+'</div>');
    }
}

//TODO need to generate a interaction id there.
function updateOffsetConditions(index, elt){
    var bucket = []; 
    var i =0;
    $(this).children().each(function(){bucket[i]=this.value; i++;});
    if (!(bucket instanceof Array)) {
        bucket = [bucket];
    }
    currentQA = $('[name$="type-interaction"]:checked:[value="question-answer"],[name$="type-interaction"]:checked:[value="question-answer-keyword"]').parent().parent();
    //Adding present interaction if not already there
    for (var i=0; i<currentQA.length; i++) {
        var interactionId = $(currentQA[i]).children('[name$="interaction-id"]').val();
        bucket.splice(bucket.indexOf(interactionId), 1);
        if ($(this).children("[value='"+interactionId+"']").length==0)
            $(this).append("<option class='ui-dform-option' value='"+
                interactionId+"'>"+
                $(currentQA[i]).find('[name$="content"]').val()+"</option>")
        else
            $(this).children("[value='"+interactionId+"']").text($(currentQA[i]).find('[name$="content"]').val());
    } 
    //Removing deleted interactions
    for (var i=0; i<bucket.length; i++) {
        //Do not delete the default choice
        if (bucket[i]==0) {
            continue
        }
        $(this).children("[value='"+bucket[i]+"']").remove();
        defaultOptions = window.app['offset-condition-interaction-idOptions']
        defaultOptions.splice(defaultOptions.indexOf(bucket[i]),1);
    }
}

function getAnswerAcceptNoSpaceKeywords(element, keywords){
    if ($(element).parent().find("[name$='answer-accept-no-space']:checked").length == 0) {
        return keywords;
    }
    var noSpacedKeywords = []
    for(var i=0; i<keywords.length; i++) {
        $(element).parent().find("input[name$='choice']").each(function(){ 
                noSpacedKeywords.push(keywords[i]+$(this).val());
        })
    }

    return keywords.concat(noSpacedKeywords);
}

function isDialogueView() {
    if ($("[name$=dialogue-id]").length > 0) {
        return true;
    }
    return false;
}

function formatKeywordValidation(value, element, param) {
    var errors = {};
    
    if (isDialogueView()) {
        var keywordRegex = new RegExp('^[a-zA-Z0-9]+(,(\\s)?[a-zA-Z0-9]+)*$','i');
    } else {
        var keywordRegex = new RegExp('^[a-zA-Z0-9\\s]+(,(\\s)?[a-zA-Z0-9\\s]+)*$','i');
    }
    
    if (keywordRegex.test(value)) {    	  
        return true;
    }
    return false;
}

function doubleSpaceValidation(value, element, param) {         
    var errors = {}    
    var doubleSpaceRegex = new RegExp('\\s\\s','g');    
    if (doubleSpaceRegex.test(value)) { 
        errors[$(element).attr('name')] = wrapErrorMessage(value + localized_errors.validation_double_space);
        this.showErrors(errors);        
    }
    return true;    
}

function duplicateKeywordValidation(value, element, param) {    
    var isValid = false;
    var keywordInput = element;
    var isKeywordUsedInSameScript = false;
    var errors = {}
    var keywords = $(keywordInput).val().replace(/\s/g, '').split(',');
    keywords = getAnswerAcceptNoSpaceKeywords(element, keywords);
    var pattern = /[^a-zA-Z0-9]/g;
    for(var x=0;x<keywords.length;x++) {
        if (pattern.test(keywords[x])) {
            errors[$(element).attr('name')] = wrapErrorMessage(keywords[x] + localized_errors.validation_keyword_invalid_character_error);  
            this.showErrors(errors); 
            return true;
        }
        if (keywords[x].length <= 0) {
            errors[$(element).attr('name')] = wrapErrorMessage(keywords[x] + localized_errors.validation_keyword_blank_error);  
            this.showErrors(errors);
            return true;
        }
    }
    $.each($("input[name*='keyword']"), function(index, element){
    		    var elementWords = $(element).val().replace(/\s/g, '').split(',');
        for(var x=0;x<keywords.length;x++) {
            if (!$(keywordInput).is(element)) {
                elementWords = getAnswerAcceptNoSpaceKeywords(element, elementWords);
                for (var y=0;y<elementWords.length;y++) {                
                    if (keywords[x].toLowerCase() == elementWords[y].toLowerCase()) {
                        errorMessage = wrapErrorMessage(elementWords[y]+ localized_errors.validation_keyword_used_same_script_error);
                        errors[$(element).attr('name')] = errorMessage;
                        $(element).prev("label").children('img.ttc-ok').remove();
                        isKeywordUsedInSameScript = true;
                    }
                    if ($(element).hasClass('error')) { // a kind of re-validation 
                    	$(element).next("label").children('span.ttc-validation-error').remove();
                        $(element).removeClass('error').addClass('valid');
                        $(element).prev("label").not(":has('.ttc-ok')").append("<img class='ttc-ok' src='/img/ok-icon-16.png'/>");                        
                    }
                }
            }
        }
    });
    
    if(isKeywordUsedInSameScript) {
    	errors[$(element).attr('name')] = errorMessage;    
        this.showErrors(errors);
        $(element).prev("label").children('img.ttc-ok').remove();
        return true;
    }
        
    var url = location.href.indexOf("edit/")<0 ? "./validateKeyword.json" : "../validateKeyword.json"; 
    
    function validateKeywordReply(data, textStatus) {
        var elt = $("[name='"+this.inputName+"']");
        $('#connectionState').hide();
        if (data.status=='fail') { //not used
            if ($(elt).prev("label").has('.ttc-ok')) {
                $(elt).prev("label").children('img.ttc-ok').remove();
            }
                errors[$(elt).attr('name')] = wrapErrorMessage(data.message);
                isValid = false;
        } else {
    	    $(elt).prev("label").not(":has('.ttc-ok')").append("<img class='ttc-ok' src='/img/ok-icon-16.png'/>");
    	    isValid = true;
    	}
    };


    $.ajax({
            url: url,
            type: "POST",
            async: false,
            data: { 'keyword': keywords.join(", "), 
                'dialogue-id': $("[name$=dialogue-id]").val(),
                'object-id': $("[name$='_id']").val()},
            inputName: $(keywordInput).attr('name'),
            success: validateKeywordReply,
            timeout: 1000,
            error: vusionAjaxError,
    });
    if (!isValid) {
        this.showErrors(errors);
    }   
    return true;
}


function duplicateChoiceValidation(value, element, param) {
    var isValid = true;
    var elementName = $(element).attr('name');
    $(element).parent().parent().find("[name$='choice']:not([name='"+$(element).attr('name')+"'])").each( function(key, otherChoice) {
            if (value == $(otherChoice).val()) { 
                isValid = false;
                return;
            }
    });
    return isValid;
}

function atLeastOneIsChecked(value, element, param) {
    if ($("[name='"+$(element).attr('name')+"']:checked").length==0) {
        return false;
    }
    return true;
}

function requireLetterDigitSpace(value, element, param) {
    r = new RegExp('^[\\w\\s]*$')
    if (r.test(value)) {
        return true;
    }
    return false;
}

function minutesSeconds(value, element, param) {
    r = new RegExp('^([0-9]{1,4}|[0-9]{2,4}:[0-9]{1,2})$')
    if (r.test(value)) {
        return true;
    }
    return false;
}

function isArray(obj) {
    if (obj.constructor.toString().indexOf("Array") == -1)
        return false;
    return true;
};
        
function updateRadioButtonSubmenu() {
    //var elt = event.currentTarget;
    var elt = this;
    var box = $(elt).parent().next("fieldset"); 
    var name = $(elt).parent().parent().attr("name");
    if (name == null) {
        name = $(elt).parent().parent().parent().attr("name");
        if (name == null) {
            name = $(elt).parent().parent().parent().parent().attr("name");
        }
    }
    var label = $(elt).next().text();
    if (box && $(box).attr('radiochildren')){
        $(box).remove();
    } 
    
    var newContent = {
         "type":"fieldset",
         "caption": label,
         "radiochildren":"radiochildren",
         "name":name,
             "elements":[]};
    //var name = $(elt).parent().parent().attr('name');
    configToForm($(elt).attr('value'), newContent, name);
    
    $(elt).parent().formElement(newContent);
    
    var newElt = $(elt).nextAll('fieldset');
    
    $(elt).parent().after($(newElt).clone());
    //$(newElt).clone().appendTo($(elt).parent());
    $(newElt).remove();
    //$(elt).parent().after($(newElt).clone());
    
    activeForm();
};

function updateCheckboxSubmenu() {
    //var elt = event.currentTarget;
    var elt = this;
    var box = $(elt).parent().next("fieldset"); 
    var name = $(elt).parent().parent().attr("name");
    if (name == null)
        name = $(elt).parent().parent().parent().attr("name");
    var label = $(elt).next().text();
    if (box && $(box).attr('radiochildren')){
        $(box).remove();
    }
    
    if ($(elt).attr('checked')) {
        var newContent = {
             "type":"fieldset",
             "caption": label,
             "radiochildren":"radiochildren",
             "name":name,
                "elements":[]};
        //var name = $(elt).parent().parent().attr('name');
        configToForm($(elt).attr('value'), newContent, name);
    
        $(elt).parent().formElement(newContent);
    
        var newElt = $(elt).nextAll('fieldset');
    
        $(elt).parent().after($(newElt).clone());
        //$(newElt).clone().appendTo($(elt).parent());
        $(newElt).remove();
        //$(elt).parent().after($(newElt).clone());
    }
    activeForm();
};


function configToForm(item,elt,id_prefix,configTree){
    if (!program[item]){
        elt['type']=null;    
        return;
    }
    if (!isArray(program[item])){
        alert("structure is wrong, no array for: "+item);
    }
    var rabioButtonAtThisIteration = false;
    var checkBoxAtThisIteration = false;
    program[item].forEach(function (sub_item){
            if (!isArray(program[sub_item]))
            {
                if (program[sub_item]=="button"){
                    var label = sub_item.substring(4);
                    if (rabioButtonAtThisIteration && configTree && configTree[label]){
                        tmpConfigTree = configTree[label];
                    } else {
                        tmpConfigTree = configTree;
                    };
                    if (tmpConfigTree && tmpConfigTree.length>0){
                        var i = 0;
                        tmpConfigTree.forEach(function (configElt){
                                if (rabioButtonAtThisIteration) {
                                    tmpIdPrefix = id_prefix + "."+label;
                                }else{
                                    tmpIdPrefix = id_prefix;
                                }
                                var myelt = {
                                    "type":"fieldset",
                                    "caption": localize_label(label), //+" "+ i,
                                    "name": tmpIdPrefix+"["+i+"]",
                                    "elements": []
                                };
                                configToForm(label,myelt,tmpIdPrefix+"["+i+"]",configElt);
                                i = i + 1;
                                elt["elements"].push(myelt);
                        });
                        
                    }
                    elt["elements"].push({
                        "type":"addElt",
                        "alert":"add message",
                        "label": label
                    });
                } else if (program[sub_item]=="radiobuttons") {
                	rabioButtonAtThisIteration = true;
                	var radio_type = sub_item.substring(6);
                	var checkedRadio = {};
                	var checkedItem;
                	var checkedItemLabel;
                	$.each(program[radio_type],function(k,v) {
                	        if (configTree && k==configTree[radio_type]) {
                	            checkedRadio[k] = {
                	                "value": k, 
                	                "caption": localize_label(v),
                	                "checked":"checked"
                	            }
                	            checkedItem = k;
                	            checkedItemLabel = localize_label(v);
                	        } else {
                	            checkedRadio[k] = localize_label(v);
                	        }     
                	})
                	elt["elements"].push({
                            "name":id_prefix+"."+radio_type,
                            "type": program[sub_item],
                            "options": checkedRadio
                    });
                    if (checkedItem){
                        if (program[checkedItem]){
                            var box = {
                                "type":"fieldset",
                                "caption": localize_label(checkedItem),
                                "radiochildren":"radiochildren",
                                "elements":[]
                            };
                            configToForm(checkedItem, box,id_prefix,configTree);
                            if (box['type'])
                                elt["elements"].push(box);
                        };
                    }
                } else if (program[sub_item]=="checkboxes") {
                	checkBoxAtThisIteration = true;
                	var checkbox_type = sub_item.substring(9);
                	var checkedCheckBox = {};
                	var checkedItem;
                	var checkedItemLabel;
                	$.each(program[checkbox_type],function(k,v) {
                	        if (configTree && k==configTree[checkbox_type]) {
                	            checkedCheckBox[k] = {
                	                "value": k, 
                	                "caption": localize_label(v),
                	                "checked":"checked"
                	            }
                	            checkedItem = k;
                	            checkedItemLabel = localize_label(v);
                	        } else {
                	            checkedCheckBox[k] = localize_label(v);
                	        }     
                	})
                	elt["elements"].push({
                            "name":id_prefix+"."+checkbox_type,
                            "type": program[sub_item],
                            "options": checkedCheckBox
                    });
                    if (checkedItem){
                        if (program[checkedItem]){
                            var box = {
                                "type":"fieldset",
                                "caption": localize_label(checkedItem),
                                "radiochildren":"radiochildren",
                                "elements":[]
                            };
                            configToForm(checkedItem, box,id_prefix,configTree);
                            if (box['type'])
                                elt["elements"].push(box);
                        };
                    }
                } else if (program[sub_item]=="select") {
                    var eltValue = "";
                    var options = [];
                    if (window.app && window.app[sub_item+'Options']) {
                        /**need to clone otherwise the selected will be share by all form*/
                        options = clone(window.app[sub_item+'Options']);
                    } else {
                        options = program[sub_item+'-options'];
                    }
                    if (configTree) {
                        for (var j=0; j<options.length; j++){
                            if (options[j]['value']==configTree[sub_item])
                                options[j]['selected'] = true;
                        }
                    }
                    var label = null;
                    if (program[sub_item]!="hidden"){
                        label = localize_label(sub_item)
                    }
                    elt["elements"].push(
                        {
                            "type":"fieldset",
                            'class': "actions",
                            'elements': [{
                                    "name":id_prefix+"."+sub_item,
                                    "caption": label,
                                    "type": program[sub_item],
                                    "options": options
                            }]
                        });
                } else {  //It's not a Radio, Select or a button  
                    var eltValue = "";
                    if (configTree) {
                        eltValue = configTree[sub_item];
                        if (sub_item == 'date-time')
                            eltValue = fromIsoDateToFormDate(eltValue);
                    }
                    var label = null;
                    if (program[sub_item]!="hidden"){
                        label = localize_label(sub_item)
                    } 
                    elt["elements"].push(
                        {
                            "name":id_prefix+"."+sub_item,
                            "caption": label,
                            "type": program[sub_item],
                            "value": eltValue
                        });
                }
            } else { //It's an array
                var myelt = {
                    "type":"fieldset",
                    "caption": localize_label(sub_item),
                    "name": id_prefix+"."+sub_item,
                    "elements": []
                };
                //alert("start recursive call "+sub_item);
                if (configTree) {
                    configToForm(sub_item,myelt,id_prefix+"."+sub_item, configTree[sub_item]);
                } else {
                    configToForm(sub_item,myelt,id_prefix+"."+sub_item);
                }
                elt["elements"].push(myelt);
        }
    });
};

function localize_label(label) {
	if (label in localized_labels)
		return localized_labels[label];
	else
		return null;
}

function clone(obj) {
     if (null == obj || "object" != typeof obj) return obj;

     if (obj instanceof Array) {
        var copy = [];
        for (var i = 0; i < obj.length; ++i) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        var copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
        }
        return copy;
    }
}

function fromIsoDateToFormDate(dateString) {
	if (dateString == null)
		return '';
	return Date.parse(dateString).toString('dd/MM/yyyy HH:mm');
}

function wrapErrorMessage(error) {
     return wrapErrorMessageInClass(error, null);
}

function wrapErrorMessageInClass(error, inClasses){
    if (inClasses != null) {
        inClasses = inClasses + " ttc-validation-error"
    } else {
        inClasses = "ttc-validation-error"
    }
    return '<span class="'+inClasses+'"><nobr>'+error+'</nobr></span>';
}

//TODO: consider renaming radiochildren so that the names are not the same as those for the interactions 
function showSummaryError() {
    errors = {};
    $.each($(":regex(name,^Dialogue.interactions\\[\\d+\\]$):not([radiochildren='radiochildren'])"),
        function(key, elt){
	    if ($(elt).children(':has(".error")').length > 0) {
	    	    $(elt).children('.ttc-fold-summary').append('<span class="ttc-summary-error"><nobr>'+localized_errors.interaction_summary_error+'</nobr></span>');
    	    }
    	});
}

function isInFuture(dateTime) {
    if (dateTime=="")
        return true;
    var time = moment(dateTime, "DD/MM/YYYY HH:mm")
    var localTime = moment($('#local-date-time').text(), "DD/MM/YYYY HH:mm:ss")
    if (time.diff(localTime) > 0)
        return true;
    return false;
}

function fromBackendToFrontEnd(type, object, submitCall) {
    //alert("function called");
    
    $.dform.addType("addElt", function(option) {
            return $("<button type='button'>").dformAttr(option).html(localize_label("add")+' '+localize_label(option["label"]))        
        });
    $.dform.addType("removeElt", function(option) {
            return $("<button type='button'>").dformAttr(option).html(localize_label("remove")+' '+localize_label(option["label"]))        
        });
    
    
    $.validator.addMethod(
        "isInThePast", 
        function(value, element, params) {
            if (!/Invalid|NaN/.test(moment(value, "DD/MM/YYYY HH:mm"))) {
               return isInFuture(value);
            }
            
            return isNaN(value) && isNaN(params) 
            || (parseFloat(value) >= parseFloat(params)); 
        },
        wrapErrorMessage(localized_errors.past_date_error));
    
    $.validator.addMethod(
        "keywordUnique",
        duplicateKeywordValidation,
        wrapErrorMessage(Error));
    
    $.validator.addMethod(
        "doubleSpace",
    	 doubleSpaceValidation,
    	 wrapErrorMessage(Error));
    
    $.validator.addMethod(
        "keywordFormat",
        formatKeywordValidation,
        wrapErrorMessage(Error));

    $.validator.addMethod(
        "choiceUnique",
        duplicateChoiceValidation,
        wrapErrorMessage(Error));

    $.validator.addMethod(
        "atLeastOneIsChecked",
        atLeastOneIsChecked,
        wrapErrorMessage(Error));

    $.validator.addMethod(
        "requireLetterDigitSpace",
        requireLetterDigitSpace,
        wrapErrorMessage(Error));

    $.validator.addMethod(
        "minutesSeconds",
        minutesSeconds,
        wrapErrorMessage(Error));

        
    $.dform.subscribe("alert", function(option, type) {
            //alert("message alert "+type);
            if (type=="add")
            {
                this.click(function (){
                    //alert(option +" "+ $(this).prev().prev().text());
                    $(this).prev().after($(this).prev().prev().clone());
                });
            };
            if (type=="removeElt"){
                alert("todo");    
            }
            if (type=="addElt")
            {
                this.click(clickBasicButton);
            };
    });
        
    
    var myform = {
        "action": submitCall,
        "validate": {
             submitHandler: function(form) {
                 form.submit();
             }, 
             invalidHandler: function(form, validator){
                 reactivateSaveButtons();
                 validator.showErrors();
                 showSummaryError();
             },
             onkeyup: false,
             ignore: '',
        },  
        "method": "post",
                "elements": 
                [    
                    {
                        "type":"hidden",
                        "value": (object) ?  object['_id'] : null,
                        "name": type+"._id"
                    },
                    {
                        "type": "p",
                        }
                ]
        };
        
        configToForm(type, myform, type, object);
        
        myform["elements"].push({
                        "type": "submit",
                        "value": localize_label("save")
                })
        
        return myform;
}

