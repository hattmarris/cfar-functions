//OLD FILTER / TESTING
/*
function awardFilter() {
	var piClass = '.populate-pi select',
            awardClass  = '.award-name select';
        
        jQuery(piClass).change(function(){
		var piSelect = $(this),
		    pi = piSelect.val(),
		    awardSelect = piSelect.parents('form').find(awardClass);
        
		if(pi != "default") {
	
		    jQuery.ajax({
			type: 'POST',
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: { awardPi : pi, action: 'get_award_name' },
			success: function(data){
			    awardSelect.empty();
			    var options = jQuery.parseJSON(data);
			    for(i=0;i<options.length;i++){
				awardSelect.append('<option value="'+options[i].value+'">'+options[i].text+'</option>');
			    }
			    awardSelect.removeAttr('disabled');
			}
		    });
	
		}
        
	});
}
*/
