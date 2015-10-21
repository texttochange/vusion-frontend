(function()
{
    
    $.fn.extend({
            pullBackend: function(url) {
                if (typeof $(this).data('pullBackend') === 'undefined') {
                    $(this).data('pullBackend', pullBackendNotifications($(this), url));
                }
            }
    });
    
    
    function pullBackendNotifications(el, url) {
        var numberOfConsecutiveErrors = 0,
            normalRefreshRate = 20000, //pull every 20seconds if everything is ok
            currentRefreshRate = normalRefreshRate;  //initialy the refresh rate is ok

        function processError(jqXHR, textStatus, errorThrown) {
            numberOfConsecutiveErrors++;
            //if (errorThrown == 'Forbidden') {  //let's slow down in all error case
            //var refreshRate = 2000;
            currentRefreshRate = currentRefreshRate * numberOfConsecutiveErrors;  //update the current refresh rate
            setTimeout(function(){el.update();}, currentRefreshRate);            
            //}
            vusionAjaxError(jqXHR, textStatus, errorThrown);
        }
        
        function processResponse(data) {
            numberOfConsecutiveErrors = 0;
            currentRefreshRate = normalRefreshRate;
            $('#connectionState').hide();
            startPulling(data);   //name not appropriate more like "displayLogs"
            setTimeout(function(){el.update();}, currentRefreshRate);
        } 
        
        
        function startPulling(data) {               
            if (data['logs']) {
                $("#notifications").empty();
                for (var x = 0; x < data['logs'].length; x++) {
                    data['logs'][x] = data['logs'][x].replace(data['logs'][x].substr(1,19),"<span style='font-weight:bold'>"+data['logs'][x].substr(1,19)+"</span>");
                    $("#notifications").append(data['logs'][x]+"<br \>");
                }
            }   // and else what do javascript should do?
        }
        
        
        /*$.ajax({ 
                url: url,
                type: 'GET',
                reschedule: true,
                success: processResponse,
                timeout: 500,     
                error: processError,
        });*/
        
        el.update = function(reschedule) {
            $.ajax({ 
                    url: url,
                    type: 'GET',
                    success: processResponse,
                    timeout: 500,
                    error: processError,
            });
        };

        el.update();  //initial call
    }
    
})();
