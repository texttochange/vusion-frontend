function addCounter(){		
    $.each(
        $("[name*='content']"),        
        function (key, elt){
            if ($(elt).prev('span').length>0) {
                return;
            }
            $("<span style='float: right; padding-right: 13px;'> <span class='sms-message-counter' id="+$(elt).attr('name')+">"+$(elt).val().replace(/{.*}/g, '').length+"</span>"+localize_label('characters')+"</span>").insertBefore($(elt));
            $('#predefined-message').on('change', function(event){
            	$('[id="'+$(elt).attr('name')+'"]').text($(elt).val().replace(/{.*}/g, '').length);            	
            });
            $(elt).mouseenter(function(){
            	$('[id="'+$(elt).attr('name')+'"]').text($(this).val().replace(/{.*}/g, '').length);            	
            }).keyup(function(){                   
                $('[id="'+$(elt).attr('name')+'"]').text($(this).val().replace(/{.*}/g, '').length);
            });
        });
}  
