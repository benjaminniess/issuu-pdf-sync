jQuery(document).ready(function(){
	var api_version = jQuery( "input:radio[name='ips[api_version]']");

	hide_useless_options();

	api_version.change(function(){
		hide_useless_options();
	});

	/**
	 * If the old API is selected, show all the old api fields. Otherwise, hide every old params
	 */
	function hide_useless_options() {
		old_radio_state = jQuery( '#ips-api-version-old');
		if ( typeof( old_radio_state ) != 'undefined' && old_radio_state.attr('checked') == 'checked' ) {
			jQuery('.old-api').show('fast');
		} else {
			jQuery('.old-api').hide('fast');
		}

	}
});