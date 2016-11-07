<?php

class IPS_Issuu_Api {

	private $is = false;
	private $issuu_api_key = false;
	private $issuu_secret_key = false;
	private $access = false;

	private $messages = array();

	function __construct() {
		$this->set_instance();
	}

	public function set_instance() {
		global $ips_options;

		if ( ! isset( $ips_options['issuu_api_key'] ) || empty( $ips_options['issuu_api_key'] ) || ! isset( $ips_options['issuu_secret_key'] ) || empty( $ips_options['issuu_secret_key'] ) ) {
			return false;
		}

		$this->issuu_api_key = esc_attr( $ips_options['issuu_api_key'] );
		$this->issuu_secret_key = esc_attr( $ips_options['issuu_secret_key'] );
		$this->access = isset( $ips_options['access'] ) ? $ips_options['access'] : 'public';

		$this->messages = array(
			// Error messages
			10 => array( 'type' => 'error', 'message' => esc_html__( 'The options page seems to not set correctly', 'ips' ) ),
			20 => array( 'type' => 'error', 'message' => esc_html__( 'There is a problem with the attachment file', 'ips' ) ),
			30 => array( 'type' => 'error', 'message' => esc_html__( 'Unable to reach the Issuu servers', 'ips' ) ),
			40 => array( 'type' => 'error', 'message' => esc_html__( "Unable to reach the Issuu servers. Please check that your PDF file name doesn't include any special characters", 'ips' ) ),
			50 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu error: Invalid API key', 'ips' ) ),
			51 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu error: Bad signature. Please check you API keys', 'ips' ) ),
			55 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu error: Required field is missing', 'ips' ) ),
			60 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu error: Invalid field format', 'ips' ) ),
			65 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu error: Exceeding allowed amount of unlisted publications', 'ips' ) ),
			70 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu error: Exceeding allowed amount of monthly uploads', 'ips' ) ),
			75 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu error: A document already exists on your account with this name', 'ips' ) ),
			80 => array( 'type' => 'error', 'message' => esc_html__( 'Issuu unknown error', 'ips' ) ),
			90 => array( 'type' => 'error', 'message' => esc_html__( 'No PDF ID returned by Issuu', 'ips' ) ),
			100 => array( 'type' => 'error', 'message' => esc_html__( 'Error while trying to get the PDF name', 'ips' ) ),
			110 => array( 'type' => 'error', 'message' => esc_html__( 'Unable to reach the Issuu servers', 'ips' ) ),

			// Success messages
			500 => array( 'type' => 'success', 'message' => esc_html__( 'Your PDF has been sent to Issuu servers', 'ips' ) ),
			510 => array( 'type' => 'success', 'message' => esc_html__( 'Your PDF has been removed from Issuu servers', 'ips' ) ),
		);

		$this->is = true;
		return true;
	}

	/**
	 * Check if the class initialisation went well.
	 */
	public function is() {
		if ( true === (bool) $this->is ) {
			return true;
		}

		return false;
	}



	public function send_pdf_to_issuu( $attachment_data = array() ) {
		if ( ! $this->is() ) {
			return $this->get_message( 10 );
		}

		if ( ! is_array( $attachment_data ) || empty( $attachment_data ) ) {
			$this->message_id = esc_html__( 'Please check the Issuu options page', 'ips' );
			return $this->get_message( 20 );
		}

		// Parameters
		$default_parameters = array(
			'access'   => $this->access,
			'action'   => 'issuu.document.url_upload',
			'apiKey'   => $this->issuu_api_key,
			'format'   => 'json',
		);

		$parameters = array_merge( $attachment_data, $default_parameters );

		// Issuu API is expecting for a 50 characters max string
		$parameters['name'] = substr( $parameters['name'], 0, 45 );

		// Sort request parameters alphabetically (e.g. foo=1, bar=2, baz=3 sorts to bar=2, baz=3, foo=1)
		ksort( $parameters );

		// Prepare the MD5 signature for the Issuu Webservice
		$string = $this->issuu_secret_key;

		foreach ( $parameters as $key => $value ) {
			$string .= $key . $value;
		}

		$md5_signature = md5( $string );

		// Call the Webservice
		$parameters['signature'] = $md5_signature;

		$url_to_call = add_query_arg( $parameters, 'http://api.issuu.com/1_0' );

		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );

		// Check if no sever error
		if ( is_wp_error( $response ) || isset( $response->errors ) || null === $response ) {
			return $this->get_message( 30 );
		}
		// Decode the Json
		$response = json_decode( $response['body'] );
		if ( empty( $response) ) {
			return $this->get_message( 40 );
		}

		// Check stat of the action
		if ( 'fail' === $response->rsp->stat ) {
			switch ( $response->rsp->_content->error->code ) {
				case '010' :
					return $this->get_message( 50 );
					break;
				case '011' :
					return $this->get_message( 51 );
					break;
				case '200' :
					return $this->get_message( 55 );
					break;
				case '201' :
					return $this->get_message( 60 );
					break;
				case '294' :
					return $this->get_message( 65 );
					break;
				case '295' :
					return $this->get_message( 70 );
					break;
				case '302' :
					return $this->get_message( 75 );
					break;
				default :
					return $this->get_message( 80 );
			}
		}

		// Check if the publication id exists
		if ( ! isset( $response->rsp->_content->document->documentId ) || empty( $response->rsp->_content->document->documentId ) ) {
			return $this->get_message( 90 );
		}

		// Update the attachment post meta with the Issuu PDF ID
		$document = $response->rsp->_content->document;

		return $this->get_message( 500, $document );
	}

	public function get_embed_id( $document_id = '', $params = array() ) {
		if ( ! $this->is() ) {
			return false;
		}

		if ( (int) $document_id <= 0 ) {
			return false;
		}

		$default_params = array(
			'action'   => 'issuu.document_embed.add',
			'documentId' => $document_id,
		);

		$final_params = array_merge( $default_params, $params );

		$document_embed_data = $this->call_issuu_api( $final_params );

		if ( ! is_object( $document_embed_data ) || ! isset( $document_embed_data->documentEmbed ) || ! isset( $document_embed_data->documentEmbed->dataConfigId ) ) {
			return false;
		}

		$document_embed_code = $this->call_issuu_api( array(
			'action' => 'issuu.document_embed.get_html_code',
			'embedId' => $document_embed_data->documentEmbed->id,
		) );

		return $document_embed_code;
	}

	/*
     * Delete an Issuu PDF from Issuu webservice
     *
     * @param $post_id the WP post id
     * @return true | false
     * @author Benjamin Niess
     */
	public function delete_pdf_from_issuu( $issuu_pdf_name = '' ) {
		global $ips_options;

		if ( empty( $issuu_pdf_name ) ) {
			return $this->get_message( 100 );
		}

		// Prepare the MD5 signature for the Issuu Webservice
		$md5_signature = md5( $this->issuu_secret_key . 'actionissuu.document.deleteapiKey' . $this->issuu_api_key . 'formatjsonnames' . $issuu_pdf_name );

		// Call the Webservice
		$url_to_call = 'http://api.issuu.com/1_0?action=issuu.document.delete&apiKey=' . $this->issuu_api_key . '&format=json&names=' . $issuu_pdf_name . '&signature=' . $md5_signature;

		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );

		// Check if no sever error
		if ( is_wp_error( $response ) || isset( $response->errors ) || null === $response ) {
			return $this->get_message( 110 );
		}
		// Decode the Json
		$response = json_decode( $response['body'] );
		if ( empty( $response) ) {
			return $this->get_message( 120 );
		}

		// Check stat of the action
		if ( 'fail' === $response->rsp->stat ) {
			switch ( $response->rsp->_content->error->code ) {
				case '010' :
					return $this->get_message( 50 );
					break;
				case '011' :
					return $this->get_message( 51 );
					break;
				case '200' :
					return $this->get_message( 55 );
					break;
				case '201' :
					return $this->get_message( 60 );
					break;
				default :
					return $this->get_message( 80 );
			}
		}

		return $this->get_message( 510 );

	}

	private function call_issuu_api( $custom_parameters = array() ) {
		$access = 'public';
		if ( isset( $ips_options['access'] ) ) {
			$access = $ips_options['access'];
		}

		// Parameters
		$default_parameters = array(
			'access'   => $access,
			'apiKey'   => $this->issuu_api_key,
			'format'   => 'json',
		);

		$parameters = array_merge( $custom_parameters, $default_parameters );

		// Sort request parameters alphabetically (e.g. foo=1, bar=2, baz=3 sorts to bar=2, baz=3, foo=1)
		ksort( $parameters );

		// Prepare the MD5 signature for the Issuu Webservice
		$string = $this->issuu_secret_key;

		foreach ( $parameters as $key => $value ) {
			$string .= $key . $value;
		}

		$md5_signature = md5( $string );

		// Call the Webservice
		$parameters['signature'] = $md5_signature;
		$url_to_call = add_query_arg( $parameters, 'http://api.issuu.com/1_0' );

		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );
		// Check if no sever error
		if ( is_wp_error( $response ) || isset( $response->errors ) || null === $response ) {
			return false;
		}
		// Decode the Json
		if ( 'issuu.document_embed.get_html_code' === $parameters['action'] ) {
			return $response['body'];
		}

		$response = json_decode( $response['body'] );

		if ( empty( $response) ) {
			return false;
		}

		// Check stat of the action
		if ( 'fail' === $response->rsp->stat ) {
			return false;
		}

		return $response->rsp->_content;
	}

	/**
	 * Return an error or success message by checking the message class variable
	 *
	 * @param $message_code The ID of the message (see the $message variable)
	 * @param (array) $data: Some extra data returned by the API needed for the next steps
	 *
	 * @return (array) the message details
	 * @author Benjamin Niess
	 *
	 */
	public function get_message( $message_code = 0, $data = false ) {
		if ( ! isset( $this->messages[ $message_code ] ) ) {
			return array(
				'status' => 'error',
				'code' => 0,
				'message' => esc_html__( 'Unknown error, please contact the administrator', 'ips' ),
			);
		}
		return array(
			'status' => $this->messages[ $message_code ]['type'],
			'code' => $message_code,
			'message' => $this->messages[ $message_code ]['message'],
			'data' => $data,
		);
	}
}
