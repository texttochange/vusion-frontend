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
            
            function processError(jqXHR, textStatus, errorThrown) {                
                if (errorThrown == 'Forbidden') {
                    var refreshRate = 2000;
                    while (true) {
                        if (this.reschedule) {
                            setTimeout(function(){el.update(true);}, refreshRate);                            
                        }
                        refreshRate *= 2;
                    }
                }
                vusionAjaxError(jqXHR, textStatus, errorThrown);
            }
            
            function processResponse(data) {
                $('#connectionState').hide();
                startPulling(data);
                if (this.reschedule) {
                    setTimeout(function(){el.update(true);}, 1000);
                }
            } 
            
            
            function startPulling(data) {               
                if (data['logs']) {
                    $("#notifications").empty();
                    for (var x = 0; x < data['logs'].length; x++) {
                        data['logs'][x] = data['logs'][x].replace(data['logs'][x].substr(1,19),"<span style='font-weight:bold'>"+data['logs'][x].substr(1,19)+"</span>");
                        $("#notifications").append(data['logs'][x]+"<br \>");
                    }
                }
            }
            
            
            $.ajax({ 
                    url: url,
                    type: 'GET',
                    reschedule: true,
                    success: processResponse,
                    timeout: 500,
                    error: processError,
            });
            
            el.update = function(reschedule) {
                $.ajax({ 
                        url: url,
                        type: 'GET',
                        reschedule: true,
                        success: processResponse,
                        timeout: 500,
                        error: processError,
                });
            };
        }
        
    })(jQuery);
