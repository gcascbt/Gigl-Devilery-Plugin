jQuery(document).ready(function(){
	var getMode = jQuery('.gigl_mode').val();
		if(getMode == 'live'){
			jQuery('.gigl_test').css('display','none');
			jQuery('.gigl_test').css('opacity','0');
			jQuery('.gigl_live').css('display','block');
			jQuery('.gigl_live').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(6)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4)').css('display','none');
		}else{
			jQuery('.gigl_live').css('display','none');
			jQuery('.gigl_live').css('opacity','0');
			jQuery('.gigl_test').css('display','block');
			jQuery('.gigl_test').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(6)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(4)').css('display','table-row');

		}
		

	// jQuery('.gigl_live').css('display','none');
	// jQuery('.gigl_live').css('opacity','0');
	// jQuery('.gigl_test').css('display','block');
	// jQuery('.gigl_test').css('opacity','1');
	// jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','none');
	// jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','0');
	// jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','none');
	// jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','0');
	// jQuery('#mainform table tbody tr:nth-child(6) th label').css('display','none');
	// jQuery('#mainform table tbody tr:nth-child(6) th label').css('opacity','0');
	// jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('display','none');
	// jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('opacity','0');
	// jQuery('#mainform table tbody tr:nth-child(5)').css('display','none');
	// jQuery('#mainform table tbody tr:nth-child(6)').css('display','none');

	jQuery('.gigl_mode').change(function(){
		var getChangeVal = jQuery(this).val();
		if(getChangeVal == 'live'){
			jQuery('.gigl_test').css('display','none');
			jQuery('.gigl_test').css('opacity','0');
			jQuery('.gigl_live').css('display','block');
			jQuery('.gigl_live').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(6)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4)').css('display','none');
		}else{
			jQuery('.gigl_live').css('display','none');
			jQuery('.gigl_live').css('opacity','0');
			jQuery('.gigl_test').css('display','block');
			jQuery('.gigl_test').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(6) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(6) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(6)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(4)').css('display','table-row');
		}
	});
});   

