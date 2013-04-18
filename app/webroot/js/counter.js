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
            	
            }*/            
            $("<span style='float: right; padding-right: 13px;'> <span id="+$(elt).attr('name')+">"+$(elt).val().replace(/{.*}/g, '').length+"</span> Characters</span>").insertBefore($(elt));
            $(elt).keydown(function(){
                    //alert("hi");                   
                    if ($(elt).val().length > 160) {
                    	//alert("hi no more");
                    	$(elt).val($(elt).val().substring(0, 160));                    	
                    }
                     $('[id="'+$(elt).attr('name')+'"]').text($(this).val().replace(/{.*}/g, '').length);                    
            }); 
        });
    
}     
