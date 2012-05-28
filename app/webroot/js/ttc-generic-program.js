var program = {"script": [ 
        "dialogues",
        ],
    "name" : "text",
    "customer": "text",
    "shortcode" : "text",
    "participants":["add-participant"],
    "add-group":"button",
    "add-participant":"button",
    "participant":["phone","name"],
    "phone":"text",
    "dialogues": ["add-dialogue"],
    "add-dialogue":"button",
    "Dialogue": ["name", "auto-enrollment", "interactions","dialogue-id"],
    "dialogue-id": "hidden",
    "auto-enrollment": "select",
    "auto-enrollment-options": [{"value":"none", "html":"None"}, {"value": "all", "html": "All participants"}],    
    "interactions":["add-interaction"],
    "interaction":["radio-type-schedule", "radio-type-interaction","interaction-id"],
    "interaction-id":"hidden",
    "add-interaction":"button",
    "announcement": ["content"],
    "question-answer": ["content","keyword", "radio-type-question"],
    "radio-type-reminder":"radiobuttons",
    "type-reminder":{"no-reminder":"No reminder","reminder":"Reminder"},
    "reminder":["number","every"],
    "number":"text",
    "every":"text",
    "radio-type-question": "radiobuttons", 
    "type-question":{"closed-question":"closed-question","open-question":"open-question"},
    "close-question": ["label-for-participant-profiling", "answers"],
    "label-for-participant-profiling": "text",
    "open-question": ["answer-label", "feedback"],
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
    "type-answer-action": {"optout": "optout", "enrolling":"enrolling",  "tagging":"tagging"},
    "add-request":"button",
    "Request": ["keyword", "responses", "actions"],
    "responses":["add-response"],
    "actions":["add-action"],
    "add-response":"button",
    "response":["content"],
    "radio-type-action": "radiobuttons",
    "add-action":"button",
    "add-feedback":"button",
    "action":["radio-type-action"],
    "type-action": {"optin": "optin", "optout": "optout", "enrolling":"enrolling",  "tagging":"tagging"},
    "choice":"text",
    "tagging":["tag"],
    "tag":"text",
    "enrolling":["enroll"],
    "enroll":"select",
    "add-request-reply":'button',
    "request-reply":["keyword","add-feedback","radio-type-action"],
    "id":"text",
    "type":"text",
    "radio-type-interaction":"radiobuttons",
    "type-interaction": {
        "announcement":"announcement",
        "question-answer":"question"},
    "radio-type-schedule":"radiobuttons",
    "type-schedule": {
        "immediately":"immediately",
        "fixed-time":"fixed-time",
        "wait":"wait"},
    "content":"text",
    "date": "text",
    //"fixed-time":["date-time","year","month","day","hour","minute"],
    "fixed-time":["date-time"],
    "date-time":"text",
    //"year":"text",
    //"month":"text",
    //"day":"text",
    //"hour":"text",
    //"minute":"text",
    "wait":["minutes"],
    "wait-answer": ["minutes"],
    "minutes":"text",
    "time": "text",
    "keyword":"text",
    "feedback":["content"]
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
                return;
            }
            if (location.href.indexOf(response['dialogue-obj-id'])<0){
                $("#flashMessage").show().attr('class', 'message success').text(response['message']+" "+localized_messages['vait_redirection']);
                setTimeout( function() { 
                    if (location.href.indexOf("edit/")<0) 
                        window.location.replace("edit/" + response['dialogue-obj-id']);
                    else 
                        window.location.replace(response['dialogue-obj-id']);
                    }, 3000);
            } else {
                $("#flashMessage").attr('class', 'message success').show().text(response['message']);
                $("#flashMessage").delay(3000).fadeOut(1000)
            }
        },
        timeout: 1000,
        error: vusionAjaxError
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
            }
        },
        timeout: 1000,
        error: vusionAjaxError
    });
}

function convertDateToIso(data) {   
    return data;
}    

function clickBasicButton(){
                    
    //alert("click on add element "+$(this).prev('legend'));
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
        
    configToForm(eltLabel, expandedElt, tableLabel+"["+id+"]");
    
    $(parent).formElement(expandedElt);
    
    $(this).parent().children("button").each(function(index,elt){
        $(elt).clone().appendTo($(parent));
        $(elt).remove();    
    })
    
    activeForm();
    
};

function getSelectableGotTo() {
    //get dialogue elements id or name
    var list = [];
    $(":regex(name,program.dialogues\\[*\\d\\]$)").each(function(index, elt){
            list = list.concat([$(elt).attr('name')]);
    })
    return list;
}

function populateSelectableGoTo(){
    
    function doubleEscape(str){
        return str.replace("[","\\\\[").replace("]","\\\\]").replace(".","\\\\.");
    }    
    
    var selectableGoTo = getSelectableGotTo();
    //var selectableGoTo = {};
    //selectableGoTo["value"] = "option1";
    //selectableGoTo["html"] = "Option1";
    //selectableGoTo["type"] = "option";
    $(":regex(name,goto$)").each(function(index, elt){
            selectableGoTo.forEach(function(item){
                    if ($(elt).children('[value="'+doubleEscape(item)+'"]').length<=0){
                        var toInsert = {};
                        toInsert["type"] = "option";
                        toInsert["value"] = item;
                        toInsert["html"] = item;
                        $(elt).formElement(toInsert);
                    }
                });
    });
}

function activeForm(){
    $.each($('.ui-dform-addElt'),function(item,value){
            if (!$.data(value,'events')) {
                $(value).click(clickBasicButton);
            }
    });
    $.each($("input[name*='type-interaction']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    $.each($("input[name*='type-schedule']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    $.each($("input[name*='type-action']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    $.each($("input[name*='type-answer-action']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    $.each($("input[name*='type-routing']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    $.each($("input[name*='type-reminder']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    $.each($("input[name*='type-question']"),function (key, elt){
            if (!$.data(elt,'events')){    
                $(elt).change(updateRadioButtonSubmenu);
            };
    });
    /*$.each($("input[name*='keyword']"), function (key,elt){
            if (!$.data(elt,'events')){
                $(elt).focusout(duplicateKeywordValidation);
            };
    });*/
    $.each($("input[name*='date-time']"), function (key,elt){
            if (!$.data(elt,'events')){
                $(elt).datetimepicker({
                timeFormat: 'hh:mm',
                dateFormat:'dd/mm/yy'});
            };
    });

    $("#dynamic-generic-program-form").validate(/*{
            submitHandler: function(form) {
                alert('hey');
            },
    }*/);
    $("input[name*='date-time']").each(function (item) {
            $(this).rules("add",{
                required:true,
                greaterThanOrEqualTo: Date.now().toString("dd/MM/yyyy HH:mm"),
                messages:{
                    required: wrapErrorMessage(localized_errors.validation_required_error),
                }
            });
    });
    $("input[name*='keyword']").each(function (item) {
               $(this).rules("add",{
                     required:true,
                    keywordUnique:true,
                    messages:{
                         required: wrapErrorMessage(localized_errors.validation_required_error),
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
            messages:{
                required: wrapErrorMessage(localized_errors.validation_required_error),
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
    
    addContentFormHelp();
    populateSelectableGoTo();
}


function duplicateKeywordValidation(value, element, param) {    
    var keywordInput = element;
    var isKeywordUsedInSameScript = false;
    var errors = {}
    var keywords = $(keywordInput).val().replace(/\s/g, '').split(',');
    var pattern = /[^a-zA-Z0-9]/g;
    for(var x=0;x<keywords.length;x++) {
        if (pattern.test(keywords[x])) {
            //$(keywordInput).before("<p style='color:red'>'"+keywords[x]+"' has some invalid characters. Keywords must contain only numbers or letters separated by a comma.</p>");
            errors[$(element).attr('name')] = wrapErrorMessage(keywords[x] + localized_errors.validation_keyword_invalid_character_error);  
            this.showErrors(errors); 
            return true;
        }
        if (keywords[x].length <= 0) {
            //$(keywordInput).before("<p style='color:red'>You cannot have a blank keyword.</p>");
            errors[$(element).attr('name')] = wrapErrorMessage(keywords[x] + localized_errors.validation_keyword_blank_error);  
            this.showErrors(errors);
            return true;
        }
    }
    
    $.each($("input[name*='keyword']"), function(index, element){
        var elementWords = $(element).val().replace(/\s/g, '').split(',');
        
        for(var x=0;x<keywords.length;x++) {
            if (!$(keywordInput).is(element)) {
                for (var y=0;y<elementWords.length;y++) {                
                    if (keywords[x].toLowerCase() == elementWords[y].toLowerCase()) {
                        //$(keywordInput).before("<p style='color:red'>'"+elementWords[y]+"' already used by the same script in another question</p>");
                        errorMessage = wrapErrorMessage(elementWords[y]+ localized_errors.validation_keyword_used_same_script_error);
                        errors[$(element).attr('name')] = errorMessage;
                        isKeywordUsedInSameScript = true;
                    }
                }
            }
        }
    });
    
    if(isKeywordUsedInSameScript) {
    	errors[$(element).attr('name')] = errorMessage;    
        this.showErrors(errors)
        return true;
    }

    var url = location.href.indexOf("edit/")<0 ? "./validateKeyword.json" : "../validateKeyword.json"; 
    
        $.ajax({
            url: url,
            type: "POST",
            async: false,
            data: { 'keyword': $(keywordInput).val(), 
            	    'dialogue-id': $("[name$=dialogue-id]").val(),
                    'object-id': $("[name$='_id']").val()},
            inputName: $(keywordInput).attr('name'),
        success: validateKeywordReply,
        timeout: 1000,
        error: vusionAjaxError,
    });
    return true;
}

function validateKeywordReply(data, textStatus) {
    var elt = $("[name='"+this.inputName+"']");
    $('#flashMessage').hide();
    if (data.status=='fail') { //not used
    //    $(elt).before("<p style='color:green'> ok </p>");
        //else    //already used in another Program
            var errors = {};
            errors[$(elt).attr('name')] = wrapErrorMessage(data.message);
            $("#dynamic-generic-program-form").validate().showErrors(errors);
        }    
};


function isArray(obj) {
    if (obj.constructor.toString().indexOf("Array") == -1)
        return false;
    return true;
};

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
        
function updateRadioButtonSubmenu() {
    //var elt = event.currentTarget;
    var elt = this;
    var box = $(elt).parent().next("fieldset"); 
    var name = $(elt).parent().parent().attr("name");
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
    var name = $(elt).parent().parent().attr('name');
    configToForm($(elt).attr('value'), newContent, name);
    
    $(elt).parent().formElement(newContent);
    
    var newElt = $(elt).nextAll('fieldset');
    
    $(elt).parent().after($(newElt).clone());
    //$(newElt).clone().appendTo($(elt).parent());
    $(newElt).remove();
    //$(elt).parent().after($(newElt).clone());
    
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
                            "name":id_prefix+"."+sub_item,
                            "caption": label,
                            "type": program[sub_item],
                            "options": options
                        });
                } else {    
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
            } else {
                //alert("add fieldset "+sub_item)
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
     return '<span class="ttc-validation-error">'+error+'</span>';
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
        "greaterThanOrEqualTo", 
        function(value, element, params) {    
            if (!/Invalid|NaN/.test(Date.parse(value))) {
                if (Date.parse(value).compareTo(Date.now())>0)
                    return true;
                return false;
            }
            
            return isNaN(value) && isNaN(params) 
            || (parseFloat(value) >= parseFloat(params)); 
        },
        wrapErrorMessage(localized_errors.past_date_error)
        );
    
    $.validator.addMethod(
        "keywordUnique",
        duplicateKeywordValidation,
        wrapErrorMessage(Error)
        );

        
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

