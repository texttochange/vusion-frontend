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
            var phone = options.phone.replace(/\D/g,''),
		    url = '../../programParticipants/view/' + phone,
		    timeLastPulled = null;
		    
		    
		    // private functions
		    function processResponse(data, textStatus) {
		        $('#connectionState').hide();
		        if (data['status'] == 'fail') {
		            return;
		        }
		        // success
		        if (timeLastPulled == null) {
		            $('#simulator-output').empty();	
		        }
		        timeLastPulled = data['program-time'];
		        processHistory(data['histories']);
		        processParticipant(data['participant']);
		    }
		    
		    
		    function processHistory(history) {
		        var container = $('.ttc-simulator-output');
		        for (var i = 0; i< history.length; i++) {
		            message = history[i]['History'];
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
		        var template =  "<div class='simulator-msg'>"+
                                "<div" +((message['message-direction'] == 'incoming') ? " class='simulator-incoming'" : " class='simulator-outgoing'" )+">"+
                                "<div class='simulator-message-text'>"+
                                "MESSAGE_CONTENT"+
                                "</div>"+
                                "<div class='simulator-datetime'>"+
                                "MESSAGE_TIMESTAMP"+
                                "</div>"+
                                "</div>"+
                                "</div>"
		        
		        template = template.replace('MESSAGE_CONTENT', message['message-content']);
		        template = template.replace('MESSAGE_TIMESTAMP', moment(message['timestamp']).calendar());
		        return  template;
		    }
		    
		    function generateHtmlParticipant(participant) {
		        var template =  "<dl>"+
                                localize_label("Phone")+": PARTICIPANT_PHONE "+
                                "</dl><dl>"+
                                "<dt>"+localize_label("Last Optin Date")+":</dt><dt> PARTICIPANT_OPTIN_DATE </dt>"+
                                "</dl><dl>"+
                                ((participant['last-optout-date']) ? "<dt>"+
                                    localize_label("Last Optout Date")+":</dt><dt>"+moment(participant['last-optout-date']).format("DD/MM/YYYY HH:mm:ss")+"</dt></dl>" :  " </dt></dl>" )+
                                (((participant['enrolled'].length) > 0) ? "<dl><dt>"+localize_label("Enrolled")+": </dt><dt>PARTICIPANT_ENROLLED</dt></dl>" : " </dt></dl>")+
                                (((participant['profile'].length) > 0) ? "<dl><dt>"+localize_label("Labels")+":</dt><dt>PARTICIPANT_LABELS</dt></dl>" : "  </dt></dl>")+
                                (((participant['tags'].length) > 0) ? "<dl><dt>"+localize_label("Tags")+": </dt><dt>PARTICIPANT_TAGS</dt></dl>" : " </dt></dl>")
                
                
                template = template.replace('PARTICIPANT_PHONE', participant['phone']);
                template = template.replace('PARTICIPANT_OPTIN_DATE', moment(participant['last-optin-date']).format("DD/MM/YYYY HH:mm:ss"));
                template = template.replace('PARTICIPANT_ENROLLED', generateHtmlParticipantEnrolled(participant));
                template = template.replace('PARTICIPANT_LABELS', generateHtmlParticipantLabels(participant));
                template = template.replace('PARTICIPANT_TAGS', generateHtmlParticipantTags(participant));
                return  template;
                
                function generateHtmlParticipantLabels(participant) {
                    var labels = '';
                    for (var i = 0; i < participant['profile'].length; i++) {
                        labels += "<div class='simulator-profile-value'>"+
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
                        tags += "<div class='simulator-profile-value'>"+
                                participant['tags'][i]+
                                "</div>"
                    }
                    return tags;
                }
                
                function generateHtmlParticipantEnrolled(participant) {
                    var enrolled = '';
                    for (var i = 0; i < participant['enrolled'].length; i++) {
                        enrolled += "<div class='simulator-profile-value'>"+
                                participant['enrolled'][i]['dialogue-id']+
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
                    success: processResponse,
                    error: vusionAjaxError,
                    timeout: 1000
            });
            
            
            // public functions 
            
            // Get history since last pull
            el.update = function() {
                $.ajax({
                        url: url,
                        type: 'GET',
                        data: {
                            'history_from': timeLastPulled,
                        },
                        dataType: 'json',
                        success: processResponse,
                        error: vusionAjaxError,
                        timeout: 1000
                });
            };
            
            setInterval(el.update, 3000);
            
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
                    //logMessageSent(event)
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
