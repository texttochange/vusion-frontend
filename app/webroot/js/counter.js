function addCounter(){		
    $.each(
        $("[name*='content']"),        
        function (key, elt){
        	/*if ($(elt).prev('span').length > 0) {
        	return;
        	}
        	$(elt).keydown(function()){
            if ($(elt).prev('span').length > 160) {
            alert("hi");
            $(elt).val() = $(elt).val().substring(0, 160);
            
            }
            if ($(elt).val().length > 160) {
            //alert("hi no more");
            $(elt).val($(elt).val().substring(0, 160));                    	
            } Characters
            */   
            
            var $remaining = $('#remaining'),
            $messages = $remaining.next();     
            $("<span style='float: right; padding-right: 13px;'> <span id="+$(elt).attr('name')+">"+$(elt).val().replace(/{.*}/g, '').length+"</span> / <span id='messages'>0 </span></span>").insertBefore($(elt));            
            $(elt).keydown(function(){
                    //alert("hi");                   
                    var chars = this.value.length,
                    messages = Math.ceil(chars / 160),
                    remaining = messages * 0 + (chars % (messages * 160) || messages * 160);                    
                    
                    $('[id="'+$(elt).attr('name')+'"]').text($(this).val().replace(/{.*}/g, '').length); 
                    $('[id="messages"]').text(messages + '');	                     
            }); 
        });
    
}     
