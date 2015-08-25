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
                    el.append(
                        "<div class='simulator-msg'><div class='simulator-incoming'> <div>"+
                        message['message-content']+
                        "</div><div class='simulator-datetime'>"+
                        moment(message['timestamp']).calendar()+
                        "</div></div></div>")
                    container[0].scrollTop = container[0].scrollHeight;
                } else if (message['message-direction'] == "outgoing") {
                    el.append(
                        "<div class='simulator-msg'><div class='simulator-outgoing'> <div>"+                                
                        message['message-content']+
                        "</div><div class='simulator-datetime'>"+
                        moment(message['timestamp']).calendar()+
                        "</div></div></div>")
                    container[0].scrollTop = container[0].scrollHeight;
                } else {
                    $("#simulator-output").append("")
                }
            }
		}

		function processParticipant(participant) {
			$('#simulator-profile').empty()
            $("#simulator-profile").append(
                "<dl> <dt>"+
                "Phone: "+
                participant['phone']+
                "</dt></dl>")
            $("#simulator-profile").append(
                "<dl> <dt>"+
                "Last Optin Date:  </br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"+
                 moment(participant['last-optin-date']).format("DD/MM/YYYY HH:mm:ss")+
                "</dt></dl>")
            $("#simulator-profile").append(
                    "<dl> <dt>"+
                    "Last Optout Date: </br>")
            if (participant['last-optout-date']) {
                $("#simulator-profile").append(
                    "<dl><dt>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"+
                     moment(participant['last-optout-date']).format("DD/MM/YYYY HH:mm:ss")+
                    "</dt></dl>")
            } else {
                $("#simulator-profile").append("&nbsp;  </dt></dl>")
            }
            
            if ((participant['profile'].length) > 0) {
                $("#simulator-profile").append(
                    "<dl><dt>"+
                    "Labels: ")
                for (var i = 0; i < participant['profile'].length; i++) {
                    $("#simulator-profile").append(
                        "<dl><dt>"+
                        "<div>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"+
                        participant['profile'][i]['label']+
                        ": "+
                        participant['profile'][i]['value']+
                        "</div></dt></dl>"
                        )
                }
            } else {
                $("#simulator-profile").append("&nbsp;  </dt></dl>")
            }
            
            if ((participant['tags'].length) > 0) {
                $("#simulator-profile").append(
                    "<dl><dt>"+
                    "Tags: ")
                for (var i = 0; i < participant['tags'].length; i++) {
                    $("#simulator-profile").append(
                        "<dl><dt>"+
                        "<div>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"+
                        participant['tags'][i]+
                        "</div></dt></dl>"
                        )
                }
            } else {
                $("#simulator-profile").append("&nbsp;  </dt></dl>")
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

	    return el;
	}
})(jQuery);
