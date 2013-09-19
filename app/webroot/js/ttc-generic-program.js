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
                message = handleResponseValidationErrors(response['message']);
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
        timeout: 4000,
        error: saveAjaxError,
        userAction: localized_actions['save_dialogue'],
    });
}

function handleResponseValidationErrors(validationErrors){
   showErrorMessages(localized_errors.validation_error);
   errorMessages = new Object();
   errors = object2array(validationErrors);
   $.each(errors, function(k, error) {
           if (error['value'] == null) {
               return;
           }
           error['name'] = error['name'].replace(/\[0\]$/g,'');
           item = error['name'].match(/[\-\w]*$/g)[0];
           errorClass = null;
           style = null;
           switch (item) {
           case 'condition-operator':
               errorClass = "ttc-radio-validation-error";
               break;
           case 'type-action':
               errorClass = "ttc-radio-validation-error";
               break;
           case 'subcondition-field':
               style = 'left:-80px';
               break;
           case 'subcondition-operator':
               style = 'left:-80px';
               break;
           case 'subcondition-parameter':
               style = 'left:-200px';
               break;
           case 'content':
               errorClass = "ttc-textarea-validation-error dialogue";
               break;
           case 'unmatching-feedback-content':
               errorClass = "ttc-textarea-validation-error dialogue";
               break;
           default:
               if (dynamicForm[item]['type'] == 'list') {
                   style = 'left:20px;top:-76px';
                   $('[name="'+error['name']+'"] > button').on('click', function() {hideValidationLabel(error['name']);});
               }
           }
           errorMessages[error['name']] = wrapErrorMessageInClass(error['value'], errorClass, style, null);
           if (dynamicForm[item]['type'] != 'list') {
               $('[name="'+error['name']+'"]').on('click', function() {hideValidationLabel(error['name']);});
           }
   });
   $('.ttc-expand-icon').click(); //Expand all folded part to show the errors properly
   $('#dynamic-generic-program-form').validate().showErrors(errorMessages);
}

function hideValidationLabel(name) {
    $("span[name='"+name+"']").remove();
}

function showErrorMessages(errorMessage){
        $("#flashMessage").attr('class', 'message error').show().text(errorMessage);
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
                message = handleResponseValidationErrors(response['message']);
                //showErrorMessages(message);
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
        timeout: 3000,
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
    var itemToAdd = $(this).attr('adds');
    var listName = $(this).parent().attr('name');
    //in case of radio button the parent name need the name to be added
    var r = new RegExp("\\]$","g");
    if (r.test(listName)){
        listName = listName +"."+ itemToAdd;
    }
    var parent = $(this).parent();
    
    var newElt = {
        "type": "fieldset",
        "name": listName+"["+id+"]",
        "item": itemToAdd,
        "caption": localize_label(itemToAdd),
        "elements": []}
      
    configToForm(itemToAdd, newElt, listName+"["+id+"]");
    
    $(parent).formElement(newElt);
    
    $(this).parent().children("button").each(
        function(index,elt){
            $(elt).clone().appendTo($(parent));
            $(elt).remove();    
    });
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
    $.each($("input[name*='condition']"),function (key, elt){
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
            doubleSpace:true,
            choiceUnique: true,
            choiceFormat:true,
            choiceIndex:true,
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_error),
                choiceUnique: wrapErrorMessage(localized_errors.validation_choice_duplicate),
                choiceFormat: wrapErrorMessage(localized_errors.validation_choice_format),
                choiceIndex: wrapErrorMessage(localized_errors.validation_choice_index),
            } 
        });
    });
    $("input[name*='name']").each(function (item) {
        $(this).rules("add",{
            required:true,
            uniqueDialogueName: true,
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_error),
                uniqueDialogueName: wrapErrorMessage(localized_errors.validation_unique_dialogue_name),
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
    $("input[name*='type-interaction'], input[name*='type-action']").each(function (item) {
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
    $("textarea[name*='content']").each(function (key, elt) {          
            $(this).rules("add",{
                    required:true,
                    forbiddenApostrophe: true,
                    messages:{                        
                        required: function(){
                            if($(elt).attr('name') == $(":regex(name,^Dialogue.interactions\\[\\d+\\].content$)").attr('name')){                               
                                return wrapErrorMessageInClass(localized_errors.validation_required_content, "ttc-textarea-validation-error dialogue");
                            } else {                                
                                return wrapErrorMessageInClass(localized_errors.validation_required_content, "ttc-textarea-validation-error request");
                            }
                        },
                        forbiddenApostrophe: function(){
                            if($(elt).attr('name') == $(":regex(name,^Dialogue.interactions\\[\\d+\\].content$)").attr('name')){                               
                                return wrapErrorMessageInClass(localized_errors.validation_apostrophe, "ttc-textarea-validation-error dialogue");
                            } else {                                
                                return wrapErrorMessageInClass(localized_errors.validation_apostrophe, "ttc-textarea-validation-error request");
                            }
                        }
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
        $(this).slideDown('fast');
    });
    $(this).parent().children('[class="ttc-fold-summary"]').remove();
    $(this).attr('src','/img/minimize-icon-16.png').attr('class', 'ttc-fold-icon').off().on('click', foldForm);
}

function foldForm(){
//    var name = $(this).parent().attr('name');
    var parent = $(this).parent(); 
    $(parent).children(":not(img):not(.ui-dform-legend)").slideUp('fast');
    $(parent).children(":not(img):not(.ui-dform-legend) > label.error").hide();
    var itemToFold = $(this).parent().attr('item');
    var nameToFold = $(this).parent().attr('name');
    var summary = "";
    switch (itemToFold) {
    case "interaction":
        summary = $('[name="'+nameToFold+'.content"]').val();
        break;
    case "response":
        summary = $('[name="'+nameToFold+'.content"]').val();
        break;
    case "feedback":
        summary = $('[name="'+nameToFold+'.content"]').val();
        break;
    case "answer":
        summary = $('[name="'+nameToFold+'.choice"]').val();
        break;
    case "answer-keyword":
        summary = $('[name="'+nameToFold+'.keyword"]').val();
        break;
    case "action":
        summary = $('[name="'+nameToFold+'.type-action"]:checked').val();
        if (summary == null) {
            summary = '';
        }
        break;
    case "subcondition":
        summary = $('[name="'+nameToFold+'.subcondition-field"]').val()
        break;
    case "proportional-tag":
        summary = $('[name="'+nameToFold+'.tag"]').val() +" "+$('[name="'+nameToFold+'.weight"]').val();
        break;
    default:
        summary = "not summarized view available for this item";
    }
    $(parent).append('<div class="ttc-fold-summary">'+summary.substring(0,70)+'...</div>');
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
function updateOffsetConditions(elt){
    var bucket = []; 
    var i =0;
    $(elt).children().each(function(){bucket[i]=$(this).val(); i++;});
    if (!(bucket instanceof Array)) {
        bucket = [bucket];
    }
    currentQA = $('[name$="type-interaction"]:checked:[value="question-answer"],[name$="type-interaction"]:checked:[value="question-answer-keyword"]').parent().parent();
    //Adding present interaction if not already there
    for (var i=0; i<currentQA.length; i++) {
        var interactionId = $(currentQA[i]).children('[name$="interaction-id"]').val();
        bucket.splice(bucket.indexOf(interactionId), 1);
        if ($(elt).children("[value='"+interactionId+"']").length==0)
            $(elt).append("<option class='ui-dform-option' value='"+
                interactionId+"'>"+
                $(currentQA[i]).find('[name$="content"]').val()+"</option>")
        else
            $(elt).children("[value='"+interactionId+"']").text($(currentQA[i]).find('[name$="content"]').val());
    } 
    //Removing deleted interactions
    for (var i=0; i<bucket.length; i++) {
        //Do not delete the default choice
        if (bucket[i]==0) {
            continue
        }
        $(elt).children("[value='"+bucket[i]+"']").remove();
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

function duplicateDialogueNameValidation(value, element, param) {
    var isValid = false;
    var dialogueNameInput = element;    
    var errors = {};
    var dialogueName = $(dialogueNameInput).val();
    
    var url = location.href.indexOf("edit/")<0 ? "./validateName.json" : "../validateName.json"; 
    
    function validateNameReply(data, textStatus) {
        var elt = $("[name='"+this.inputName+"']");
        $('#connectionState').hide();
        if (data.status=='fail') {
        	errors[$(elt).attr('name')] = wrapErrorMessage(data.message);
			isValid = false;
        } else {
    	    isValid = true;
    	}
    };


    $.ajax({
            url: url,
            type: "POST",
            async: false,
            data: {  'name' : dialogueName,
                'dialogue-id': $("[name$=dialogue-id]").val(),
                'object-id': $("[name$='_id']").val()},
            inputName: $(dialogueNameInput).attr('name'),
            success: validateNameReply,
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


function formatChoiceValidation(value, element, param) {    
    var choiceRegex = new RegExp('^[\\w\\s]*$','i');
    if (choiceRegex.test(value)) { 
          return true;
    }
    return false;    
}


function isInt(someNumber) {
    var intRegex = /^\d+$/;
    if(intRegex.test(someNumber)) {
        return true;
    }
    return false;
}

function extractIndex(elementName, indexName) {
    indexedName = elementName.match(/\w*\[(\d*)\]/gm);
    for (var i = 0; i < indexedName.length; i++) {
        var indexNameRegex = new RegExp(indexName,"g");
        if (indexNameRegex.test(indexedName[i])) {
            index = indexedName[i].match(/\[(\d*)\]/gm)[0].slice(1, -1);
            return parseInt(index);
        }
    }
    return null;
}

function indexChoiceValidation(value, element, param) {
    // The value is not an Int => no ambiguity
    if (!isInt(value)) {
        return true;
    }

    var choiceInput = $(element).attr('name');
    var interactionIndex = extractIndex(choiceInput, 'interactions');
    var numberOfAnswers = $(":regex(name,^Dialogue.interactions\\["+interactionIndex+"\\].answers\\[\\d+\\].choice$)").length;             
    var answerIndex = extractIndex(choiceInput, 'answers');
    var equivalentParticipantChoice = answerIndex + 1;
    
    // The answer index is out of boundary => no ambiguity
    if (value < 1 || value > numberOfAnswers) { 
        return true;
    }

    // The answer index it equal to the value
    if (equivalentParticipantChoice == parseInt(value)) {
        return true;
    } 
    
    // All other case are ambigious
    return false;   
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

function forbiddenApostrophe(value, element, param) {
    r = new RegExp('[’`’‘]');
    if (r.test(value)) {
        return false;
    }
    return true;
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
    var item = $(elt).attr('item');
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
    checked = $(elt).attr('value');
    option = dynamicForm[item]['options'].filter(function (option) { return option.value == checked});
    if (option[0]['subfields']) {
        $.each(option[0]['subfields'], function(k, v) {
                configToForm(v, newContent, name);
        });    
    
        $(elt).parent().formElement(newContent);
        var newElt = $(elt).nextAll('fieldset');
        $(elt).parent().after($(newElt).clone());
        $(newElt).remove();
    }
    
    activeForm();
};

function updateCheckboxSubmenu() {
    //var elt = event.currentTarget;
    var elt = this;
    var item = $(elt).parent().attr('item');
    var box = $(elt).parent().next("fieldset"); 
    var name = $(elt).parent().parent().attr("name");
    if (name == null)
        name = $(elt).parent().parent().parent().attr("name");
    var label = $(elt).next().text();
    if (box && $(box).attr('radiochildren')){
        $(box).remove();
    }
    
    if ($(elt).attr('checked') && "subfields" in dynamicForm[item]) {
        var newContent = {
             "type":"fieldset",
             "caption": label,
             "radiochildren":"radiochildren",
             "name":name,
               "elements":[]};
        $.each(dynamicForm[item]['subfields'], function(k, v) {
                configToForm(v, newContent, name);
        });
        $(elt).parent().formElement(newContent);
        var newElt = $(elt).nextAll('fieldset');
        $(elt).parent().after($(newElt).clone());
        $(newElt).remove();
    }
    activeForm();
};

function supplySubconditionOperatorOptions(elt) {
    var item = $(elt).attr("item");
    switch (item) {
    case 'subcondition-field':
        var field = $(elt).val();
        if (field == "")
            return;
        var operatorOptions = window.app[item+'Options'][field]['operators'];
        
        var operatorDropDown = $(elt).nextAll('select')[0];
        operatorValue = $(operatorDropDown).val()
        $(operatorDropDown).empty();
        $(operatorDropDown).append(new Option(localized_messages['select_one']));
        $.each(operatorOptions, function(operator, details) {
                var option = new Option(localize_label(operator), operator);
                if (operatorValue && operator == operatorValue) {
                    $(option).prop('selected', true);
                }
                $(operatorDropDown).append(option);
        });
        break;
    case 'subcondition-operator':
        var fieldDropDown = $(elt).prevAll('select')[0];
        supplySubconditionOperatorOptions(fieldDropDown);
        break;
    }
}


function configToForm(item, elt, id_prefix, configTree){
    if (!dynamicForm[item]){
        elt['type']=null;    
        return;
    }
    if (dynamicForm[item]['type'] == 'container') {
        if (!dynamicForm[item]['skip']) {
            var myelt = {
                "type":"fieldset",
                "caption": localize_label(item),
                "name": id_prefix,
                "elements": []
            }; 
            elt["elements"].push(myelt);
            if ('add-prefix' in dynamicForm[item]) {
                id_prefix = id_prefix + '.' + item;
                if (configTree && item in configTree) {
                    configTree = configTree[item]
                }
            }
        } else {
            var myelt = elt;
        }
        $.each(dynamicForm[item]['contains'], function(k,v) {
                if (configTree) {
                    configToForm(v , myelt, id_prefix, configTree);
                } else {
                    configToForm(v , myelt, id_prefix);
                }
        });
    } else if (dynamicForm[item]['type'] == 'list') {
        var listName = id_prefix+"."+item;
        var list = {
            "type":"fieldset",
            "caption": localize_label(item),
            "name": listName,
            "elements": []
        };
        if (configTree && configTree[item] && configTree[item].length>0){
            var i = 0;
            configTree[item].forEach(function (listEltValues){
                    listEltName =  listName + "["+i+"]";
                    var listElt = {
                        "type":"fieldset",
                        "item": dynamicForm[item]['adds'],
                        "caption": localize_label(dynamicForm[item]['adds']),
                        "name": listEltName,
                        "elements": []
                    };
                    configToForm(dynamicForm[item]['adds'], listElt, listEltName, listEltValues);
                    i = i + 1;
                    list["elements"].push(listElt);
            }); 
        }
        if (dynamicForm[item]['add-button']) {
            list["elements"].push({
                    "type":"addElt",
                    "adds": dynamicForm[item]['adds']});
        }
        elt["elements"].push(list);
    } else if (dynamicForm[item]['type'] == "radiobuttons") {
        var checkedRadio = {};
        var checkedItem;
        //In order to support old model for action that used type-answer-action
        if (item == 'type-action' && configTree && 'type-answer-action' in configTree) {
             configTree['type-action'] = configTree['type-answer-action'];
        }
        $.each(dynamicForm[item]['options'],function(k,v) {
                if (configTree && v['value']==configTree[item]) {
                    checkedRadio[v['value']] = {
                        "value": v['value'],
                        "item": item,
                        "caption": localize_label(v['value']),
                        "checked":"checked"
                    }
                    checkedItem = v;
                    //checkedItemLabel = localize_label(v['value']);
                } else {
                    checkedRadio[v['value']] = { 
                        'value': v['value'],
                        "item": item,
                        "caption": localize_label(v['value'])};
                }     
        })
        elt["elements"].push({
                "name":id_prefix+"."+item,
                "type": "radiobuttons",
                "options": checkedRadio
        });
        if (checkedItem && checkedItem['subfields']){
            var box = {
                "type":"fieldset",
                "caption": localize_label(checkedItem),
                "radiochildren":"radiochildren",
                "elements":[]
            };
            $.each(checkedItem['subfields'], function (k,v) {
                    configToForm(v, box, id_prefix, configTree);
            });
            if (box['type'])
                elt["elements"].push(box);
        };
    } else if (dynamicForm[item]['type'] == "checkboxes") {
        var checkedCheckBox = {};
        var checkedItem;
        var checkedItemLabel;
        if (configTree && dynamicForm[item]['value']==configTree[item]) {
            checkedItem = dynamicForm[item];
            checkedItemLabel = localize_label(checkedItem['value']);
            checkedCheckBox[checkedItem['value']] = {
                "caption": localize_label(item),
                "checked":"checked"
            }
        } else {
            checkedCheckBox[dynamicForm[item]['value']] = localize_label(item);
        }
        elt["elements"].push({
                "name": id_prefix+"."+item,
                "item": item,
                "type": 'checkboxes',
                "options": checkedCheckBox
        });
        if (checkedItem && dynamicForm[item]['subfields']){
            var box = {
                "type":"fieldset",
                "caption": localize_label(item),
                "radiochildren":"radiochildren",
                "elements":[]
            };
            $.each(checkedItem['subfields'], function (k,v) {
                    configToForm(v, box, id_prefix, configTree);
            });
            if (box['type'])
                elt["elements"].push(box);
        }
    } else if (dynamicForm[item]["type"] == "select") {
        options = [{
                'value': '',
                'html': localized_messages.select_one}];
        switch (dynamicForm[item]["data"]) {
        case 'server-dynamic':
            for (option in window.app[item+'Options']) {
                if ('value' in window.app[item+'Options'][option]) {
                    options.push({
                         'value': window.app[item+'Options'][option]['value'],
                         'html': window.app[item+'Options'][option]['html']});
                } else {
                    options.push({
                         'value': option,
                         'html': localize_label(option)})
                }
            }
            break;
        case 'static':
            for (option in dynamicForm[item]["options"]) {
                 var opt = dynamicForm[item]["options"][option];
                 options.push({
                         'value': opt,
                         'html': localize_label(opt)})
            }
            break;
        }
        if (configTree && item in configTree) {
            for (var j=0; j<options.length; j++){
                if (options[j]['value'] == configTree[item])
                    options[j]['selected'] = true;
            }
            if (options.length == 1) {
                options.push({
                        'value': configTree[item],
                        'html': localize_label(configTree[item]),
                        'selected': true});
            }
        }
        var label = null;
        if (dynamicForm[item]!="hidden"){
            label = localize_label(item)
        }
        select = {
            "name": id_prefix + "." + item,
            "caption": label,
            "item": item,
            "type": 'select',
            "options": options};
        if (dynamicForm[item]['onchange']) {
            select['onchange'] = dynamicForm[item]['onchange']; 
        }
        if (dynamicForm[item]['onmouseover']) {
            select['onmouseover'] = dynamicForm[item]['onmouseover']; 
        }
        if (dynamicForm[item]['onload']) {
            select['onload'] = dynamicForm[item]['onload']; 
        }
        if (dynamicForm[item]['fieldset']==false) {
            elt["elements"].push(select);
        } else {
            elt["elements"].push({
                    "type":"fieldset",
                    'class': "actions",
                    'elements': [select]
            });            
        }
    } else {
        var eltValue = "";
        if (configTree) {
            eltValue = configTree[item];
            if (item == 'date-time')
                eltValue = fromIsoDateToFormDate(eltValue);
        }
        var label = null;
        if (dynamicForm[item]['type'] != "hidden"){
            label = localize_label(item)
        } 
        newElt = {
                "name":id_prefix+"."+item,
                "caption": label,
                "type": dynamicForm[item]['type'],
                "value": eltValue}
        if (dynamicForm[item]['style']) {
            newElt['style'] = dynamicForm[item]['style']; 
        }
        elt["elements"].push(newElt);
    }
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

function wrapErrorMessageInClass(error, inClasses, style, name){
    if (inClasses != null) {
        inClasses = inClasses + " ttc-validation-error"
    } else {
        inClasses = "ttc-validation-error"
    }
    return '<span class="'+inClasses+'" style="'+style+'" name="'+name+'"><nobr>'+error+'</nobr></span>';
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
            return $("<button type='button'>").dformAttr(option).html(localize_label("add")+' '+localize_label(option["adds"]))        
        });
    $.dform.addType("removeElt", function(option) {
            return $("<button type='button'>").dformAttr(option).html(localize_label("remove")+' '+localize_label(option["adds"]))        
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
        "uniqueDialogueName",
        duplicateDialogueNameValidation,
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
        "choiceFormat",
        formatChoiceValidation,
        wrapErrorMessage(Error));
    
     $.validator.addMethod(
        "choiceIndex",
        indexChoiceValidation,
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

    $.validator.addMethod(
        "forbiddenApostrophe",
        forbiddenApostrophe,
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

