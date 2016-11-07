<?php

class IPS_Admin_Main {

	function __construct() {
		global $pagenow;

		add_filter( 'attachment_fields_to_edit', array( __CLASS__, 'insert_ips_sync_link' ), 10, 2 );
		add_filter( 'media_send_to_editor', array( __CLASS__, 'send_to_editor' ) );

		if ( 'media.php' === $pagenow ) {
			add_action( 'admin_head', array( __CLASS__, 'edit_media_js' ), 50 );
		}

		add_action( 'admin_init', array( __CLASS__, 'check_js_pdf_edition' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_plugin_menu' ) );

		add_action( 'admin_init', array( __CLASS__, 'enqueue_scripts' ) );

		// Add the tinyMCE button
		add_action( 'admin_init', array( __CLASS__, 'add_buttons' ) );
		add_action( 'wp_ajax_ips_shortcodePrinter', array( __CLASS__, 'wp_ajax_fct' ) );

	}

	/**
	 * Enqueue the required scripts on the correct pages
	 */
	public static function enqueue_scripts() {
		global $pagenow;

		wp_enqueue_script( 'jquery' );

		if ( 'options-general.php' === $pagenow ) {
			wp_enqueue_script( 'ips-admin-main', IPS_URL . '/js/admin-main.js', array( 'jquery', 'wp-color-picker' ), '1.0', true );
			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	/**
	 * Register the WordPress options page
	 */
	public static function add_plugin_menu() {
		add_options_page( esc_html__( 'Options for Issuu PDF Sync', 'ips' ), esc_html__( 'Issuu PDF Sync', 'ips' ), 'manage_options', 'ips-options', array( __CLASS__, 'display_options' ) );
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
			foreach ( (array) $_POST['ips'] as $key => $value ) {
				$new_options[ $key ] = stripslashes( $value );
			}

			update_option( 'ips_options', $new_options );
			$ips_options = get_option( 'ips_options' );
		}

		if ( isset($_POST['save']) ) {
			echo '<div class="message updated"><p>'. esc_html__( 'Options updated!', 'ips' ).'</p></div>';
		}

		if ( empty( $ips_options ) ) {
			$ips_options = array();
		}

		$access = 'public';
		if ( isset( $ips_options['access'] ) ) {
			$access = esc_attr( $ips_options['access'] );
		}

		$tpl = IPS_Main::load_template( 'admin-options' );
		if ( empty( $tpl ) ) {
			return false;
		}

		include( $tpl );
	}

	/**
	 * On the media edition screen, add a link to Sync/unsync the PDF to Issuu servers + extra data about the PDF
	 *
	 * @return the amended form_fields structure
	 * @param $form_fields Object
	 * @param $attachment Object
	 */
	public static function insert_ips_sync_link( $form_fields = array(), $attachment = false ) {
		global $wp_version, $ips_options;

		if ( ! isset( $attachment ) || empty( $attachment ) ) {
			return $form_fields;
		}

		// Only add the extra button if the attachment is a PDF file
		if ( 'application/pdf' !== $attachment->post_mime_type ) {
			return $form_fields;
		}

		// Allow plugin to stop the auto-insertion
		$check = apply_filters( 'insert-ips-button', true, $attachment, $form_fields );
		if ( true !== (bool) $check  ) {
			return $form_fields;
		}

		// Check on post meta if the PDF has already been uploaded on Issuu
		$issuu_pdf_id = get_post_meta( $attachment->ID, 'issuu_pdf_id', true );
		$issuu_pdf_username = get_post_meta( $attachment->ID, 'issuu_pdf_username', true );
		$issuu_pdf_name = get_post_meta( $attachment->ID, 'issuu_pdf_name', true );
		$disable_auto_upload = get_post_meta( $attachment->ID, 'disable_auto_upload', true );

		$issuu_url = ( ! empty( $issuu_pdf_username ) && ! empty( $issuu_pdf_name ) ) ? sprintf( 'http://issuu.com/%s/docs/%s', $issuu_pdf_username, $issuu_pdf_name ) : false;

		// The extra data array just for debugging info
		$pdf_data = array(
			'issuu_pdf_sync_id' => array( 'name' => esc_html__( 'Issuu PDF ID', 'ips' ), 'value' => $issuu_pdf_id ),
			'issuu_pdf_username' => array( 'name' => esc_html__( 'Issuu PDF username', 'ips' ), 'value' => $issuu_pdf_username ),
			'issuu_pdf_name' => array( 'name' => esc_html__( 'Issuu PDF file name', 'ips' ), 'value' => $issuu_pdf_name ),
			'issuu_pdf_url' => array( 'name' => esc_html__( 'Issuu PDF URL', 'ips' ), 'value' => $issuu_url ),
		);

		// The Issuu sync/unsync link
		$form_fields['issuu_pdf_sync'] = array(
			'show_in_edit'   => true,
			'label'          => esc_html__( 'Issuu PDF Sync', 'ips' ),
			'input'          => 'issuu_pdf_sync',
			'issuu_pdf_sync' => self::get_sync_input( $attachment->ID, $pdf_data ),
		);

		if ( ! empty( $issuu_pdf_id ) ) {
			foreach ( $pdf_data as $field_key => $field ) {
				$form_fields[ $field_key ] = array(
					'show_in_edit' => true,
					'label' => $field['name'],
					'input' => $field_key,
					$field_key => ! empty( $field['value'] ) ? $field['value'] : esc_html__( 'Empty', 'ips' ),
				);
			}
		}

		return $form_fields;
	}

	/**
	 * The Issuu sync/unsync link HTML structure + javascript
	 *
	 * @param $attachment_id
	 * @param $pdf_data
	 * @return bool|string
	 */
	public static function get_sync_input( $attachment_id, $pdf_data ) {
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
		if ( preg_match( '|\[pdf (.*?)\]|i', $html, $matches ) ) {
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
	public static function check_js_pdf_edition() {
		if ( ! isset( $_GET['attachment_id'] ) || 0 === (int) $_GET['attachment_id'] || ! isset( $_GET['action'] ) || empty( $_GET['action'] ) ) {
			return false;
		}

		if ( 'send_pdf' === $_GET['action'] ) {
			//check if the nonce is correct
			check_admin_referer( 'issuu_send_' . $_GET['attachment_id'] );

			$sync = IPS_Main::sync_pdf( (int) $_GET['attachment_id'] );
			echo wp_json_encode( $sync );
			exit();
		} elseif ( 'delete_pdf' === $_GET['action'] ) {

			//check if the nonce is correct
			check_admin_referer( 'issuu_delete_' . $_GET['attachment_id'] );

			$sync = IPS_Main::unsync_pdf( (int) $_GET['attachment_id'] );
			echo wp_json_encode( $sync );
			exit();
		}
	}

	/*
     * Print some JS code for the media.php page (for PDFs only)
     * @author Benjamin Niess
	 */
	public static function edit_media_js() {
		global $ips_options;

		if ( ! isset( $_GET['attachment_id'] ) || (int) $_GET['attachment_id'] <= 0 || ! isset( $ips_options['issuu_api_key'] ) || empty( $ips_options['issuu_api_key'] ) || ! isset( $ips_options['issuu_secret_key'] ) || empty( $ips_options['issuu_secret_key'] ) ) {
			return false;
		}

		// Get attachment infos
		$post_data = get_post( $_GET['attachment_id'] );

		// Check if the attachment exists and is a PDF file
		if ( ! isset( $post_data->post_mime_type ) || 'application/pdf' !== $post_data->post_mime_type || ! isset( $post_data->guid ) || empty ( $post_data->guid ) ) {
			return false;
		}

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
	public static function wp_ajax_fct() {
		global $ips_options, $wp_styles;

		$pdf_files = new WP_Query( array(
			'post_type'      => 'attachment',
			'posts_per_page' => 100,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => 'issuu_pdf_id',
					'value'   => '',
					'compare' => '!=',
				),
			),
		) );

		if ( ! empty($wp_styles->concat) ) {
			$dir = $wp_styles->text_direction;
			$ver = md5( "$wp_styles->concat_version{$dir}" );

			// Make the href for the style of box
			$href = $wp_styles->base_url . "/wp-admin/load-styles.php?c={$zip}&dir={$dir}&load=media&ver=$ver";
			echo "<link rel='stylesheet' href='" . esc_attr( $href ) . "' type='text/css' media='all' />\n";
		}

		if ( ! $pdf_files->have_posts() ) {
			$tpl = IPS_Main::load_template( 'admin-no-pdf-yet' );
		} else {
			$tpl = IPS_Main::load_template( 'admin-insert-modal' );
		}

		if ( empty( $tpl ) ) {
			return false;
		}

		$api_version = ( isset( $ips_options['new_api_version'] ) && 1 === (int) $ips_options['new_api_version'] ) ? 'new' : 'old';

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
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return false; }

		// Does the admin want to display the Issuu button ?
		if ( ! isset( $ips_options['add_ips_button'] ) || 1 !== (int) $ips_options['add_ips_button'] ) {
			return false;
		}

		if ( true === (bool) get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array( __CLASS__, 'add_script_tinymce' ) );
			add_filter( 'mce_buttons', array( __CLASS__, 'register_the_button' ) );
		}
	}

	/*
     * Add buttons to the tiymce bar
     *ter
     * @author Benjamin Niess
	 */
	public static function register_the_button( $buttons ) {
		array_push( $buttons, '|', 'ips' );
		return $buttons;
	}

	/*
     * Load the custom js for the tinymce button
     *
     * @author Benjamin Niess
	 */
	public static function add_script_tinymce( $plugin_array ) {
		$plugin_array['ips'] = IPS_URL . '/js/tinymce.js';
		return $plugin_array;
	}
}
