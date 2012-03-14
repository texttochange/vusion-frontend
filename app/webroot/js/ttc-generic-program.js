var participant = {"participants": ["add-participant"],
	"participant":["phone","name"],
	"phone":"text",
	"name" : "text"
	}


var program = {"script": [ 
		//"name", 
		//"customer",  
		//"shortcode",
		//"country",
		//"participants",
		"requests-responses",
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
	"country": "text",
	"dialogues": ["add-dialogue"],
	"add-dialogue":"button",
	"dialogue": ["name","interactions","dialogue-id"],
	"dialogue-id": "hidden",	
	"interactions":["add-interaction"],
	"interaction":["radio-type-schedule", "radio-type-interaction","interaction-id"],
	"interaction-id":"hidden",
	"add-interaction":"button",
	"announcement": ["content"],
	"question-answer": ["content","keyword","radio-type-reminder", "radio-type-question"],
	"radio-type-reminder":"radiobuttons",
	"type-reminder":{"no-reminder":"No reminder","reminder":"Reminder"},
	"reminder":["number","every"],
	"number":"text",
	"every":"text",
	"radio-type-question": "radiobuttons", 
	"type-question":{"close-question":"Close question","open-question":"Open question"},
	"close-question": ["answers"],
	"open-question": ["feedback"],
	"requests-responses":["add-request-response"],
	"add-request-response":"button",
	"request-response":["content","responses","actions"],
	"radio-type-routing":"radiobuttons",
	"type-routing":{"keyword-routing": "Keyword routing","phone-routing":"Phone number routing (all incomming message from participant will be root to this program)"},	
	"keyword-routing":["keyword"],
	"answers":["add-answer"],
	"add-answer": "button",
	"answer": ["choice","feedbacks", "actions"],
	"feedbacks":["add-feedback"],
	"add-request":"button",
	//"request": ["content","responses", "actions"],
	"responses":["add-response"],
	"actions":["add-action"],
	"add-response":"button",
	"response":["content"],
	"radio-type-action": "radiobuttons",
	"add-action":"button",
	"add-feedback":"button",
	"action":["radio-type-action"],
	"type-action": {"tagging":"Tag participant", "goingto":"Go to a dialogue"},
	"choice":"text",
	"tagging":["tag"],
	"tag":"select",
	"goingto":["goto"],
	"goto":"select",
	"add-request-reply":'button',
	"request-reply":["keyword","add-feedback","radio-type-action"],
	"id":"text",
	"type":"text",
	"radio-type-interaction":"radiobuttons",
	"type-interaction": {
		"announcement":"Announcement",
		"question-answer":"Question"},
	"radio-type-schedule":"radiobuttons",
	"type-schedule": {
		"immediately":"Immediately",
		"fixed-time":"Fixed time",
		"wait":"Wait previous interation to be send",
		"wait-answer": "Wait previous question to be answered"},
	"content":"text",
	"date": "text",
	"fixed-time":["year","month","day","hour","minute"],
	"year":"text",
	"month":"text",
	"day":"text",
	"hour":"text",
	"minute":"text",
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

		buildTtcForm : function(script) {
			if (script) {
				$(this).empty().buildForm(fromBackendToFrontEnd(script['script'], script['_id']));
			} else {
				$(this).empty().buildForm(fromBackendToFrontEnd());
			}
			activeForm();	
		},
	});
})(jQuery);

function saveFormOnServer(){
		
	var formData = form2js('dynamic-generic-program-form', '.', true);
	//alert();
	var indata= JSON.stringify(formData, null, '\t');
		
	$.ajax({
		url:'../scripts.json',
		type:'POST',
		data: indata, 
		contentType: 'application/json; charset=utf-8',
		dataType: 'json', 
		success: function(data) {
			var response = $.parseJSON(data);
			$("#flashMessage").text('The script has been saved as draft, wait for redirection');
			//$("#flashMessage").attr('class', 'message');
			setTimeout( function() { window.location.href = "draft"}, 3000);
		}
	});
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
	
	var expandedElt = {"type":"fieldset","name":tableLabel+"["+id+"]","caption":eltLabel,"elements":[]}
		
	configToForm(eltLabel, expandedElt,tableLabel+"["+id+"]");
	
	$(parent).formElement(expandedElt);
	
	//need to move down all the button
	/*$(this).clone().appendTo($(parent));
	$(this).remove();*/
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
	$.each($("input[name*='keyword']"), function (key,elt){
			if (!$.data(elt,'events')){
				$(elt).focusout(duplicateKeywordValidation);
			};
	});
	populateSelectableGoTo();
}

function duplicateKeywordValidation() {
	//alert(this.previousSibling);
	if (this.previousSibling.tagName == 'P')
		$(this.previousSibling).remove();
	var keywordInput = this;
	var isKeywordUsedInSameScript = false;
	$.each($("input[name*='keyword']"), function(index, element){
			//alert("$this:"+$(keywordInput).val()+" and elt:"+$(element).val())
			if ((!$(keywordInput).is(element)) && ($(keywordInput).val() == $(element).val()))
			{
				$(keywordInput).before("<p style='color:red'> already used by the same script in another question</p>");
				isKeywordUsedInSameScript = true;
			}
	});
	
	if (isKeywordUsedInSameScript)
		return;

	//Validation on other scripts
	$('#flashMessage').ajaxError(function() {
			$(this).empty();
			$(this).append("http error");
	});
        $(this).load("validateKeyword.json", 
        	{ keyword: $(this).val() }, 
		function(responseText, textStatus){
			// $(this).before("<p style='color:red'> " + textStatus + "</p>");
			if (textStatus=="success") {  //HTTP success
				$('#flashMessage').empty();
				var responseMsg = $.parseJSON(responseText);
				if (responseMsg.status==1)  //not used
					$(this).before("<p style='color:green'> ok </p>");
				else    //already used in another Program
					$(this).before("<p style='color:red'>" + responseMsg.message + "</p>");
			} else {  //HTTP error or Server error ,....
				$(this).before("<p style='color:red'> " + responseText + "</p>");
			}
		});	
}


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
	if (box && $(box).attr('radiochildren')){
		$(box).remove();
	} 
	
	var newContent = {"type":"fieldset","radiochildren":"radiochildren","name":name,"elements":[]};
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
			//alert("for "+sub_item);
			if (!isArray(program[sub_item]))
			{
				//alert("add item ");
				if (program[sub_item]=="button"){
					var label = sub_item.substring(4);
					//populate form
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
									"caption": label, //+" "+ i,
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
				} else {
					if (program[sub_item]=="radiobuttons"){
						rabioButtonAtThisIteration = true;
						var radio_type = sub_item.substring(6);
					        var checkedRadio = {};
					        var checkedItem;
					        if (configTree) {
					        	$.each(program[radio_type],function(k,v){
					        		if (k!=configTree[radio_type])
					        			checkedRadio[k] = v;
					        		else {
					        			checkedRadio[k] = {"value": k, 
					        				"caption":v,
					        				"checked":"checked"
					        			}
					        			checkedItem = k;
					        		}
					        })} else {
					        	checkedRadio = program[radio_type];
					        }
						elt["elements"].push(
						{
							"name":id_prefix+"."+radio_type,
							"caption": label,
							"type": program[sub_item],
							"options": checkedRadio 
						});
						if (checkedItem){
							if (program[checkedItem]){
								var box = {
									"type":"fieldset",
									"radiochildren":"radiochildren",
									"elements":[]
								};
								configToForm(checkedItem, box,id_prefix,configTree);
								if (box['type'])
									elt["elements"].push(box);
							};
						}
					}else if (program[sub_item]=="select") {
						var eltValue = "";
						if (configTree) {
							eltValue = configTree[sub_item];
						}
						var label = null;
						if (program[sub_item]!="hidden"){
							label = sub_item;
						}
						elt["elements"].push(
							{
								"name":id_prefix+"."+sub_item,
								"caption": label,
								"type": program[sub_item],
								"options": [ { 
									"value": eltValue,
									"html":eltValue,
									"checked":"checked"
								}]
							});
					}else{	
						var eltValue = "";
						if (configTree) {
							eltValue = configTree[sub_item];
						}
						var label = null;
						if (program[sub_item]!="hidden"){
							label = sub_item;
						} else {
							eltValue = id_prefix;	
						}
						elt["elements"].push(
							{
								"name":id_prefix+"."+sub_item,
								"caption": label,
								"type": program[sub_item],
								"value": eltValue
							});
					}
				}
			}else{
				//alert("add fieldset "+sub_item)
				var myelt = {
					"type":"fieldset",
					"caption": sub_item,
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


function fromBackendToFrontEnd(configFile, id) {
	//alert("function called");
	
	$.dform.addType("addElt", function(option) {
			return $("<button type='button'>").dformAttr(option).html("add "+option["label"])		
		});
	$.dform.addType("removeElt", function(option) {
			return $("<button type='button'>").dformAttr(option).html("remove "+option["label"])		
		});
	
	
		
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
		"action": "javascript:saveFormOnServer()",
		"method": "post",
                "elements": 
                [	
                	{
                		"type":"hidden",
                		"value": id,
                		"name":"id"
                	},
                	{
                        "type": "p",
                        }
                ]
        };
        
        configToForm("script",myform, "script", configFile);
        
        myform["elements"].push({
                        "type": "submit",
                        "value": "Save"
                })
        
        return myform;
}

function fromDataToForm2(structure, lang, data, id) {
	//alert("function called");
	
	$.dform.addType("addElt", function(option) {
			return $("<button type='button'>").dformAttr(option).html("add "+option["label"])		
		});
	$.dform.addType("removeElt", function(option) {
			return $("<button type='button'>").dformAttr(option).html("remove "+option["label"])		
		});
	
	
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
		"action": "javascript:saveFormOnServer()",
                "elements": 
                [	
                	{
                		"type":"hidden",
                		"value": id,
                		"name":"id"
                	},
                	{
                        "type": "p",
                        }
                ]
        };
        
        configToForm("participants" , myform, "", data);
        
        myform["elements"].push({
                        "type": "submit",
                        "value": "Save as draft"
                })
        
        return myform;
}

/*
function generateForm(elt, item, lang, configTree, id_prefix){
	if (!program[item]){
		elt['type']=null;	
		return;
	}
	if (!isArray(program[item])){
		alert("structure is wrong, no array for: "+item);
	}
	var rabioButtonAtThisIteration = false;
	program[item].forEach(function (sub_item){
			//alert("for "+sub_item);
			if (!isArray(program[sub_item]))
			{
				//alert("add item ");
				if (program[sub_item]=="button"){
					var label = sub_item.substring(4);
					//populate form
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
									"caption": label, //+" "+ i,
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
				} else {
					if (program[sub_item]=="radiobuttons"){
						rabioButtonAtThisIteration = true;
						var radio_type = sub_item.substring(6);
					        var checkedRadio = {};
					        var checkedItem;
					        if (configTree) {
					        	$.each(program[radio_type],function(k,v){
					        		if (k!=configTree[radio_type])
					        			checkedRadio[k] = v;
					        		else {
					        			checkedRadio[k] = {"value": k, 
					        				"caption":v,
					        				"checked":"checked"
					        			}
					        			checkedItem = k;
					        		}
					        })} else {
					        	checkedRadio = program[radio_type];
					        }
						elt["elements"].push(
						{
							"name":id_prefix+"."+radio_type,
							"caption": label,
							"type": program[sub_item],
							"options": checkedRadio 
						});
						if (checkedItem){
							if (program[checkedItem]){
								var box = {
									"type":"fieldset",
									"radiochildren":"radiochildren",
									"elements":[]
								};
								configToForm(checkedItem, box,id_prefix,configTree);
								if (box['type'])
									elt["elements"].push(box);
							};
						}
					}else if (program[sub_item]=="select") {
						var eltValue = "";
						if (configTree) {
							eltValue = configTree[sub_item];
						}
						var label = null;
						if (program[sub_item]!="hidden"){
							label = sub_item;
						}
						elt["elements"].push(
							{
								"name":id_prefix+"."+sub_item,
								"caption": label,
								"type": program[sub_item],
								"options": [ { 
									"value": eltValue,
									"html":eltValue,
									"checked":"checked"
								}]
							});
					}else{	
						var eltValue = "";
						if (configTree) {
							eltValue = configTree[sub_item];
						}
						var label = null;
						if (program[sub_item]!="hidden"){
							label = sub_item;
						} else {
							eltValue = id_prefix;	
						}
						elt["elements"].push(
							{
								"name":id_prefix+"."+sub_item,
								"caption": label,
								"type": program[sub_item],
								"value": eltValue
							});
					}
				}
			}else{
				//alert("add fieldset "+sub_item)
				var myelt = {
					"type":"fieldset",
					"caption": sub_item,
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
*/
