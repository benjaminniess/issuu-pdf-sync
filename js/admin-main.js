jQuery(document).ready(function(){
	var api_version = jQuery( "#ips-new-api-version");

	hide_useless_options();

	api_version.change(function(){
		hide_useless_options();
	});

	/**
	 * If the old API is selected, show all the old api fields. Otherwise, hide every old params
	 */
	function hide_useless_options() {
		if ( typeof( api_version ) != 'undefined' && api_version.attr('checked') == 'checked' ) {
			jQuery('.old-api').hide('fast');
		} else {
			jQuery('.old-api').show('fast');
		}

	}

	jQuery('.ips-colorp').wpColorPicker();
});