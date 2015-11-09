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
        normalRefreshRate = 10000, 
        currentRefreshRate = normalRefreshRate;
        
        function processError(jqXHR, textStatus, errorThrown) {
            numberOfConsecutiveErrors++;
            displayAjaxLoader(el);
            //update the current refresh rate
            currentRefreshRate = currentRefreshRate * numberOfConsecutiveErrors;  
            setTimeout(function(){el.update();}, currentRefreshRate);            
            vusionAjaxError(jqXHR, textStatus, errorThrown);
        }
        
        function processResponse(data) {
            numberOfConsecutiveErrors = 0;
            currentRefreshRate = normalRefreshRate;
            $('#connectionState').hide();
            displayLogs(data);
            setTimeout(function(){el.update();}, currentRefreshRate);
        } 
        
        
        function displayLogs(data) {               
            if (data['logs']) {
                $("#notifications").empty();
                for (var x = 0; x < data['logs'].length; x++) {
                    data['logs'][x] = data['logs'][x].replace(data['logs'][x].substr(1,19),"<span style='font-weight:bold'>"+data['logs'][x].substr(1,19)+"</span>");
                    $("#notifications").append(data['logs'][x]+"<br \>");
                }
            } else {
                displayAjaxLoader(el);
            }
        }
        
        function displayAjaxLoader(el) {
            if ($('#notifications .ttc-ajax-loader-box').length == 0) {
                el.prepend('<div class="ttc-ajax-loader-box"><img src="/img/ajax-loader.gif" class="simulator-image-load"></div>');
            }
        }
        
        el.update = function() {
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
