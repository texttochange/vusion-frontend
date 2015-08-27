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
		    var myTemplate = "<div class='simulator-msg'>"+
		    "<div" +((message['message-direction'] == 'incoming') ? " class='simulator-incoming'" : " class='simulator-outgoing'" )+">"+
		    "<div>"+
		    "MESSAGE_CONTENT"+
		    "</div>"+
		    "<div class='simulator-datetime'>"+
		    "MESSAGE_TIMESTAMP"+
		    "</div>"+
		    "</div>"+
		    "</div>"
		    
		    myTemplate = myTemplate.replace('MESSAGE_CONTENT', message['message-content']);
		    myTemplate = myTemplate.replace('MESSAGE_TIMESTAMP', moment(message['timestamp']).calendar());
		    return  myTemplate;
		}
		
		function generateHtmlParticipant(participant) {
		    var myTemplate = "<dl> <dt>"+
		    "Phone: "+
		    "PARTICIPANT_PHONE"+
		    "</dt></dl>"+
		    "<dl><dt>"+
		    "Last Optin Date:</br>"+
		    "PARTICIPANT_OPTIN_DATE"+
		    "</dt></dl>"+
		    "<dl><dt>"+
            "Last Optout Date: </br>"+
		    ((participant['last-optout-date']) ? moment(participant['last-optout-date']).format("DD/MM/YYYY HH:mm:ss")+
		        "</dt></dl>" :  "&nbsp;  </dt></dl>" )+
		    (((participant['profile'].length) > 0) ? "<dl><dt>"+
                    "Labels:<div id='simulator-labels'> PARTICIPANT_LABELS </div>" : "&nbsp;  </dt></dl>")+
            (((participant['tags'].length) > 0) ? "<dl><dt>"+
                    "Tags: <div id='simulator-tags'> PARTICIPANT_TAGS </div>": "&nbsp;  </dt></dl>")
            
            
		    myTemplate = myTemplate.replace('PARTICIPANT_PHONE', participant['phone']);
		    myTemplate = myTemplate.replace('PARTICIPANT_OPTIN_DATE', moment(participant['last-optin-date']).format("DD/MM/YYYY HH:mm:ss"));
            myTemplate = myTemplate.replace('PARTICIPANT_LABELS', generateHtmlParticipantLabels(participant));
            myTemplate = myTemplate.replace('PARTICIPANT_TAGS', generateHtmlParticipantTags(participant));
		    return  myTemplate;
            
            function generateHtmlParticipantLabels(participant) {
                for (var i = 0; i < participant['profile'].length; i++) {
                   $("#simulator-labels").append("<div class='simulator-profile-value'>"+
                    participant['profile'][i]['label']+
                    ": "+
                    participant['profile'][i]['value']+
                    "</div></dt></dl>")
                }
                
            }
            
            function generateHtmlParticipantTags(participant) {
                for (var i = 0; i < participant['tags'].length; i++) {
                    $("#simulator-tags").append("<div class='simulator-profile-value'>"+
                    participant['tags'][i]+
                    "</div></dt></dl>")
                }
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

	    setInterval(el.update, 300000);

	    return el;
	}
})(jQuery);
