(function()
    {
        // Add a jQuery function to set the simulator
        $.fn.extend({
                simulator: function(options) {
                    if (typeof $(this).data('simulator') === 'undefined') {
                        $(this).data('simulator', Simulator($(this), options));
                    }
                }
        });

        // Simulator class
        function Simulator(el, options) {
            
            // private variables
            var moment = require('moment');
            var phone = options.phone.replace(/\#/g,'%23'),
		    url = '../../programParticipants/view/' + phone,
		    timeLastHistoryPulled = null;
		    
		    
		    // private functions
		    function processResponse(data, textStatus) {
		        $('#connectionState').hide();
		        if (data['status'] == 'fail') {
		            return;
		        }
		        // success
		        if (timeLastHistoryPulled == null) {
		            $('#simulator-output').empty();	
		        }
		        processHistory(data['histories']);
		        processParticipant(data['participant']);
                if (this.reschedule) {
                    setTimeout(function(){el.update(true);}, 3000);
                }
		    }

            function processError(jqXHR, textStatus, errorThrown) {
                if (this.reschedule) {
                    setTimeout(function(){el.update(true);}, 6000);
                }
                vusionAjaxError(jqXHR, textStatus, errorThrown);
            }
		    
		    
		    function processHistory(history) {
		        var container = $('.simulator-output');
		        for (var i = 0; i< history.length; i++) {
		            message = history[i]['History'];
                    if ($('#' + message['_id']).length) {
                        continue;
                    }
		            if (message['message-direction'] == "incoming") {
		                el.append(generateHtmlHistoryMessage(message))
		                container[0].scrollTop = container[0].scrollHeight;
		            } else if (message['message-direction'] == "outgoing") {
		                el.append(generateHtmlHistoryMessage(message))
		                container[0].scrollTop = container[0].scrollHeight;
		            } else {
		                $("#simulator-output").append("")
		            }
		        }
		    }
		    
		    function processParticipant(participant) {
		        $('#simulator-profile').empty()
		        $("#simulator-profile").append(generateHtmlParticipant(participant));
		    }
		    
		    function generateHtmlHistoryMessage(message) {
		        var template =  "<div id='MESSAGE_ID' class='simulator-msg'>"+
                                "<div" +((message['message-direction'] == 'incoming') ? " class='simulator-incoming'" : " class='simulator-outgoing'" )+">"+
                                "<div class='simulator-message-text'>"+
                                "MESSAGE_CONTENT"+
                                "</div>"+
                                "<div class='simulator-datetime'>"+
                                "MESSAGE_TIMESTAMP"+
                                "</div>"+
                                "</div>"+
                                "</div>"
		        template = template.replace('MESSAGE_ID', message['_id']);
		        template = template.replace('MESSAGE_CONTENT', message['message-content']);
		        template = template.replace('MESSAGE_TIMESTAMP', moment(message['timestamp']).calendar());
                //last history processed
                timeLastHistoryPulled = message['timestamp'];
		        return  template;
		    }
		    
		    function generateHtmlParticipant(participant) {
		        var template =  "<dl>"+
                                "<dt>"+localize_label("Phone")+": </dt><dd>PARTICIPANT_PHONE</dd>"+
                                "<dt>"+localize_label("Last Optin Date")+":</dt><dd> PARTICIPANT_OPTIN_DATE </dd>"+
                                ((participant['last-optout-date']) ?
                                    "<dt>"+localize_label("Last Optout Date")+":</dt><dd>"+moment(participant['last-optout-date']).calendar()+"</dd>" :  "" )+
                                (((participant['enrolled'].length) > 0) ? 
                                    "<dt>"+localize_label("Enrolled")+": </dt><dd>PARTICIPANT_ENROLLED</dd>" : "")+
                                (((participant['tags'].length) > 0) ? 
                                    "<dt>"+localize_label("Tags")+": </dt><dd>PARTICIPANT_TAGS</dd>" : "")+
                                (((participant['profile'].length) > 0) ? 
                                    "<dt>"+localize_label("Labels")+":</dt><dd>PARTICIPANT_LABELS</dd>" : "")
                               
                
                
                template = template.replace('PARTICIPANT_PHONE', participant['phone']);
                template = template.replace('PARTICIPANT_OPTIN_DATE', moment(participant['last-optin-date']).calendar());
                template = template.replace('PARTICIPANT_ENROLLED', generateHtmlParticipantEnrolled(participant));
                template = template.replace('PARTICIPANT_LABELS', generateHtmlParticipantLabels(participant));
                template = template.replace('PARTICIPANT_TAGS', generateHtmlParticipantTags(participant));
                return  template;
                
                function generateHtmlParticipantLabels(participant) {
                    var labels = '';
                    for (var i = 0; i < participant['profile'].length; i++) {
                        labels += "<div>"+
                                    participant['profile'][i]['label']+
                                    ": "+
                                    participant['profile'][i]['value']+
                                    "</div>"
                    }
                    return labels;
                }
                
                function generateHtmlParticipantTags(participant) {
                    var tags = '';
                    for (var i = 0; i < participant['tags'].length; i++) {
                        tags += "<div>"+
                                participant['tags'][i]+
                                "</div>"
                    }
                    return tags;
                }
                
                function generateHtmlParticipantEnrolled(participant) {
                    var enrolled = '';
                    for (var i = 0; i < participant['enrolled'].length; i++) {
                        enrolled += "<div>"+
                                participant['enrolled'][i]['dialogue-name']+
                                " - "+
                                moment(participant['enrolled'][i]['date-time']).calendar()
                                "</div>"
                    }
                    return enrolled;
                }
                
            }
            
            // initial pull
            $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    reschedule: true,
                    success: processResponse,
                    error: processError,
                    timeout: 2000
            });
            
            
            // public functions 
            
            // Get history since last pull
            el.update = function(reschedule) {
                $.ajax({
                        url: url,
                        type: 'GET',
                        data: {
                            'history_from': timeLastHistoryPulled,
                        },
                        dataType: 'json',
                        success: processResponse,
                        error: processError,
                        reschedule: reschedule,
                        timeout: 1000
                });
            };
            
            
            // Initialize user control mechanisms
            // Action when clicking on the Send button
            function enterEventTriggerClick(event) {
                if (event.keyCode == 13) {
                    $('#send-button').click();
                }
            }

            // The action of posting
            function sendMo(event) {
                event.preventDefault();
                function processResponseSendMo(data, textStatus) {
                    $('#connectionState').hide();
                    if (data['status'] == 'fail') {
                        return;
                    }
                    $('[name="message"]').val('');
                    el.update(false);
                }

                $.ajax({
                        url: '../../programParticipants/simulateMo.json',
                        type: 'POST',
                        dataType: 'json',
                        async: true,
                        dataExpression: true,
                        data: $("#simulator-input").serialize(),
                        success: processResponseSendMo,
                        error: vusionAjaxError,
                        timeout: 1000
                });
            }

            $('#send-button').click(sendMo);
            $('#smessage').keyup(enterEventTriggerClick);


            return el;
        }
    })(jQuery);
