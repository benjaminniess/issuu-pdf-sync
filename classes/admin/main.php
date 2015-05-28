<?php

class IPS_Admin_Main {

	function __construct() {
		global $pagenow;

		add_filter( 'attachment_fields_to_edit', array( __CLASS__, 'insert_ips_button' ), 10, 2 );
		add_filter( 'media_send_to_editor', array( __CLASS__, 'send_to_editor' ) );

		if ( $pagenow == 'media.php' ) {
            add_action('admin_head', array( __CLASS__, 'edit_media_js'), 50);
        }

		add_action( 'admin_init', array( __CLASS__, 'check_js_pdf_edition' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_plugin_menu' ) );

		add_action( 'admin_init', array( __CLASS__, 'init' ) );

		// Add the tinyMCE button
		add_action( 'admin_init', array( __CLASS__, 'add_buttons' ) );
		add_action( 'wp_ajax_ips_shortcodePrinter', array( __CLASS__, 'wp_ajax_fct' ) );

	}

	public static function init() {
		wp_enqueue_script( 'jquery' );
	}

	public static function add_plugin_menu() {
		add_options_page( __('Options for Issuu PDF Sync', 'ips'), __('Issuu PDF Sync', 'ips'), 'manage_options', 'ips-options', array( __CLASS__, 'display_options' ) );
	}

	/**
	 * Call the admin option template
	 *
	 * @echo the form
	 * @author Benjamin Niess
	 */
    public static function display_options() {
		global $ips_options;
		if ( isset($_POST['save']) ) {
			check_admin_referer( 'ips-update-options' );
			$new_options = array();

			// Update existing
			foreach( (array) $_POST['ips'] as $key => $value ) {
				$new_options[$key] = stripslashes($value);
			}

			update_option( 'ips_options', $new_options );
			$ips_options = get_option ( 'ips_options' );
		}

		if (isset($_POST['save']) ) {
			echo '<div class="message updated"><p>'.__('Options updated!', 'ips').'</p></div>';
		}

		if ( $ips_options == false ) {
			$ips_options = array();
		}

        $tpl = IPS_Main::load_template( 'admin-options' );
        if ( empty( $tpl ) ) {
            return false;
        }

        include( $tpl );
	}





	/**
	 * Inserts Issuu PDF Sync button into media library popup
	 * @return the amended form_fields structure
	 * @param $form_fields Object
	 * @param $post Object
	 */
    public static function insert_ips_button( $form_fields, $attachment ) {
		global $wp_version, $ips_options;

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			if ( !isset( $form_fields ) || empty( $form_fields ) || !isset( $attachment ) || empty( $attachment ) )
				return $form_fields;
		}

		// Only add the extra button if the attachment is a PDF file
		if ( $attachment->post_mime_type != 'application/pdf' )
			return $form_fields;

		// Allow plugin to stop the auto-insertion
		$check = apply_filters( 'insert-ips-button', true, $attachment, $form_fields );
		if ( $check !== true )
			return $form_fields;

		// Check on post meta if the PDF has already been uploaded on Issuu
		$issuu_pdf_id = get_post_meta( $attachment->ID, 'issuu_pdf_id', true );
		$issuu_pdf_username = get_post_meta( $attachment->ID, 'issuu_pdf_username', true );
		$issuu_pdf_name = get_post_meta( $attachment->ID, 'issuu_pdf_name', true );
		$disable_auto_upload = get_post_meta( $attachment->ID, 'disable_auto_upload', true );

		$issuu_url = sprintf( 'http://issuu.com/%s/docs/%s', $issuu_pdf_username, $issuu_pdf_name );

		// Upload the PDF to Issuu if necessary and if the Auto upload feature is enabled
		if ( empty( $issuu_pdf_id ) && isset( $ips_options['auto_upload'] ) && $ips_options['auto_upload'] == 1 && $disable_auto_upload != 1) {
			$issuu_pdf_id = IPS_Main::sync_pdf( $attachment->ID );
        }

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			if ( empty( $issuu_pdf_id ) )
				return $form_fields;

			$form_fields["url"]["html"] .= "<button type=\"button\" class='button urlissuupdfsync issuu-pdf-" . $issuu_pdf_id . "' data-link-url=\"[pdf issuu_pdf_id=" . $issuu_pdf_id . "]\" title='[pdf issuu_pdf_id=\"" . $issuu_pdf_id . "\"]'>" . _( 'Issuu PDF' ) . "</button>";
		} else {
			$form_fields['issuu_pdf_sync_id'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document ID', 'isp' ),
				'value'        => $issuu_pdf_id
			);

			$form_fields['issuu_pdf_username'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document Username', 'isp' ),
				'value'        => $issuu_pdf_username
			);

			$form_fields['issuu_pdf_name'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document Name', 'isp' ),
				'value'        => $issuu_pdf_name
			);

			$form_fields['issuu_pdf_url'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Document URL', 'isp' ),
				'value'        => $issuu_url
			);

			$form_fields['issuu_pdf_sync_auto_upload'] = array(
				'show_in_edit' => true,
				'label'        => __( 'Issuu Auto Upload', 'isp' ),
				'value'        => $disable_auto_upload
			);

			$form_fields['issuu_pdf_sync'] = array(
				'show_in_edit'   => true,
				'label'          => __( 'Issuu PDF Sync', 'isp' ),
				'value'          => $disable_auto_upload,
				'input'          => 'issuu_pdf_sync',
				'issuu_pdf_sync' => self::get_sync_input( $attachment->ID, $issuu_pdf_id )
			);
		}

		return $form_fields;
	}

    public static function get_sync_input( $attachment_id, $issuu_document_id ) {
        $tpl = IPS_Main::load_template( 'admin-sync-input' );
        if ( empty( $tpl ) ) {
            return false;
        }

        $input = '';

		ob_start();

		include ( $tpl );

		$input = ob_get_contents();

		ob_end_clean();

		return $input;
	}


	/**
	 * Format the html inserted when the PDF button is used
	 * @param $html String
	 * @return String The pdf url
	 * @author Benjamin Niess
	 */
    public static function send_to_editor( $html ) {
		if( preg_match( '|\[pdf (.*?)\]|i', $html, $matches ) ) {
			if ( isset($matches[0]) ) {
				$html = $matches[0];
			}
		}
		return $html;
	}

	/*
	 * Check if an action is set on the $_GET var and call the PHP function corresponding
	 * @return true | false
	 * @author Benjamin Niess
	 */
    public static function check_js_pdf_edition(){
		if ( !isset( $_GET['attachment_id'] ) || (int)$_GET['attachment_id'] == 0 || !isset( $_GET['action'] ) || empty( $_GET['action'] ) )
			return false;

        $issuu_api = new IPS_Issuu_Api();

		if ( $_GET['action'] == 'send_pdf' ){
			//check if the nonce is correct
			check_admin_referer( 'issuu_send_' . $_GET['attachment_id'] );


			die(IPS_Main::sync_pdf( (int) $_GET['attachment_id'] ) );
		} elseif ( $_GET['action'] == 'delete_pdf' ){

			//check if the nonce is correct
			check_admin_referer( 'issuu_delete_' . $_GET['attachment_id'] );

			die(IPS_Main::unsync_pdf( (int) $_GET['attachment_id'] ) );
		}
	}

	/*
	 * Print some JS code for the media.php page (for PDFs only)
	 * @author Benjamin Niess
	 */
    public static function edit_media_js(){
		global $ips_options;

		if ( !isset( $_GET['attachment_id'] ) || (int)$_GET['attachment_id'] <= 0 || !isset( $ips_options['issuu_api_key'] ) || empty( $ips_options['issuu_api_key'] ) || !isset( $ips_options['issuu_secret_key'] ) || empty( $ips_options['issuu_secret_key'] ) )
			return false;

		// Get attachment infos
		$post_data = get_post( $_GET['attachment_id'] );

		// Check if the attachment exists and is a PDF file
		if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
			return false;

		// Check on post meta if the PDF has already been uploaded on Issuu
		$issuu_pdf_id = get_post_meta( $_GET['attachment_id'], 'issuu_pdf_id', true );

		$tpl = IPS_Main::load_template( 'admin-media-javascript' );
        if ( empty( $tpl ) ) {
            return false;
        }

        include ( $tpl );
	}

	/*
	 * The content of the javascript popin for the PDF insertion
	 *
	 * @author Benjamin Niess
	 */
    public static function wp_ajax_fct(){
		global $ips_options, $wp_styles;

		$pdf_files = new WP_Query( array(
			'post_type'      => 'attachment',
			'posts_per_page' => 100,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => 'issuu_pdf_id',
					'value'   => '',
					'compare' => '!='
				)
			)
		) );

		if ( !empty($wp_styles->concat) ) {
			$dir = $wp_styles->text_direction;
			$ver = md5("$wp_styles->concat_version{$dir}");

			// Make the href for the style of box
			$href = $wp_styles->base_url . "/wp-admin/load-styles.php?c={$zip}&dir={$dir}&load=media&ver=$ver";
			echo "<link rel='stylesheet' href='" . esc_attr( $href ) . "' type='text/css' media='all' />\n";
		}

        if ( !$pdf_files->have_posts() ) {
            $tpl = IPS_Main::load_template( 'admin-no-pdf-yet' );
        } else {
            $tpl = IPS_Main::load_template( 'admin-insert-modal' );
        }

        if ( empty( $tpl ) ) {
            return false;
        }

        include ( $tpl );
		exit();
	}

	/*
	 * Add buttons to the tiymce bar
	 *
	 * @author Benjamin Niess
	 */
    public static function add_buttons() {
		global $ips_options;

		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return false;

		// Does the admin want to display the Issuu button ?
		if ( !isset( $ips_options['add_ips_button'] ) || (int)$ips_options['add_ips_button'] != 1 )
			return false;

		if ( get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', array ( __CLASS__,'add_script_tinymce' ) );
			add_filter('mce_buttons', array ( __CLASS__,'register_the_button' ) );
		}
	}

	/*
	 * Add buttons to the tiymce bar
	 *
	 * @author Benjamin Niess
	 */
    public static function register_the_button($buttons) {
		array_push($buttons, "|", "ips");
		return $buttons;
	}

	/*
	 * Load the custom js for the tinymce button
	 *
	 * @author Benjamin Niess
	 */
    public static function add_script_tinymce($plugin_array) {
		$plugin_array['ips'] = IPS_URL . '/js/tinymce.js';
		return $plugin_array;
	}
}
