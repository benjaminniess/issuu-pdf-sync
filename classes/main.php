<?php
class IPS_Main {

	function __construct() {

	}

	public static function install() {
		// Enable default features on plugin activation
		$ips_options = get_option( 'ips_options' );

		if ( empty( $ips_options ) ) {
			update_option( 'ips_options', array(
				'allow_full_screen'	=> 1,
				'auto_upload'		=> 1,
				'add_ips_button'	=> 1,
				'width'				=> 640,
				'height'			=> 480,
				'layout'			=> 1,
				'autoflip'			=> 0,
				'show_flip_buttons'	=> 0,
				'bgcolor'			=> 'FFFFFF',
				'flip_timelaps'		=> 6000,
				'api_version'		=> 'new',
			) );
		}
	}

	public static function sync_pdf( $attachment_id = 0 ) {
		if ( 0 >= (int) $attachment_id ) {
			return false;
		}

		// Get attachment infos
		$attachment = get_post( $attachment_id );

		// Check if the attachment exists and is a PDF file
		if ( is_wp_error( $attachment ) || ! isset( $attachment->post_mime_type ) || $attachment->post_mime_type != 'application/pdf' || ! isset( $attachment->guid ) || empty ( $attachment->guid ) ) {
			return false;
		}

		$issuu = new IPS_Issuu_Api();
		if ( ! $issuu->is() ) {
			return false;
		}

		// Parameters
		$parameters = array(
			'name'     => $attachment->post_name,
			'slurpUrl' => $attachment->guid,
			'title'    => sanitize_title( $attachment->post_title )
		);

		$send_to_issuu = $issuu->send_pdf_to_issuu( $parameters );
		if ( empty( $send_to_issuu ) || ! is_object( $send_to_issuu ) ) {
			return false;
		}

		update_post_meta( $attachment_id, 'issuu_pdf_id', $send_to_issuu->documentId );
		update_post_meta( $attachment_id, 'issuu_pdf_username', $send_to_issuu->username );
		update_post_meta( $attachment_id, 'issuu_pdf_name', $send_to_issuu->name );

		return $send_to_issuu->documentId;
	}

	public static function unsync_pdf( $attachment_id = 0 ){
		if ( 0 >= (int) $attachment_id ) {
			return false;
		}

		// Get attachment infos
		$attachment = get_post( $attachment_id );

		// Check if the attachment exists and is a PDF file
		if ( ! isset( $attachment->post_mime_type ) || $attachment->post_mime_type != 'application/pdf' || ! isset( $attachment->guid ) || empty ( $attachment->guid ) ) {
			return false;
		}

		$issuu_pdf_name = get_post_meta( $attachment_id, 'issuu_pdf_name', true );
		if ( empty( $issuu_pdf_name ) ) {
			return false;
		}

		$issuu = new IPS_Issuu_Api();
		if ( ! $issuu->is() ) {
			return false;
		}

		if ( ! $issuu->delete_pdf_from_issuu( $issuu_pdf_name ) ) {
			return false;
		}

		// Update the attachment post meta with the Issuu PDF ID
		delete_post_meta( $attachment_id, 'issuu_pdf_id' );
		delete_post_meta( $attachment_id, 'issuu_pdf_name' );
		update_post_meta( $attachment_id, 'disable_auto_upload', 1 );

		return true;
	}


	/**
	 * Load a view depending on its directory (child theme, parent theme or inside this plugin)
	 */
	public static function load_template( $file = '' ) {
		if ( empty( $file ) || ! is_string( $file ) ) {
			return false;
		}
		if ( is_file( STYLESHEETPATH .'views/ips/' . $file .'.tpl.php' ) ) { // Use custom type from child theme
			return( STYLESHEETPATH .'views/ips/' . $file .'.tpl.php' );
		} elseif ( is_file( TEMPLATEPATH .'views/ips/' . $file .'.tpl.php' ) ) { // Use custom type from parent theme
			return( TEMPLATEPATH .'views/ips/' . $file .'.tpl.php' );
		}

		return( IPS_DIR . '/views/' . $file .'.tpl.php' );
	}
}
