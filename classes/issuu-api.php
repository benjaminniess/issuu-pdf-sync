<?php

class IPS_Issuu_Api {

	private $is = false;
	private $issuu_api_key = false;
	private $issuu_secret_key = false;
	private $access = false;

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

		$this->is = true;
		return true;
	}

	public function is() {
		if ( $this->is === true ) {
			return true;
		}

		return false;
	}



	public function send_pdf_to_issuu( $attachment_data = array() ){
		if ( ! $this->is() ) {
			return false;
		}

		if ( ! is_array( $attachment_data ) || empty( $attachment_data ) ) {
			return false;
		}

		// Parameters
		$default_parameters = array(
			'access'   => $this->access,
			'action'   => 'issuu.document.url_upload',
			'apiKey'   => $this->issuu_api_key,
			'format'   => 'json',
		);

		$parameters = array_merge( $attachment_data, $default_parameters );

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
		if ( is_wp_error( $response ) || isset( $response->errors ) || null == $response ) {
			return false;
		}
		// Decode the Json
		$response = json_decode( $response['body'] );

		if ( empty( $response) ) {
			return false;
		}

		// Check stat of the action

		if ( $response->rsp->stat == 'fail' ) {
			return false;
		}

		// Check if the publication id exists
		if ( ! isset( $response->rsp->_content->document->documentId ) || empty( $response->rsp->_content->document->documentId ) ) {
			return false;
		}

		// Update the attachment post meta with the Issuu PDF ID
		$document = $response->rsp->_content->document;

		return $document;
	}

	public function get_embed_id ( $document_id = '', $params = array() ) {
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
	public function delete_pdf_from_issuu( $issuu_pdf_name = '' ){
		global $ips_options;

		if ( empty( $issuu_pdf_name ) ) {
			return false;
		}

		// Prepare the MD5 signature for the Issuu Webservice
		$md5_signature = md5( $this->issuu_secret_key . 'actionissuu.document.deleteapiKey' . $this->issuu_api_key . 'formatjsonnames' . $issuu_pdf_name );

		// Call the Webservice
		$url_to_call = 'http://api.issuu.com/1_0?action=issuu.document.delete&apiKey=' . $this->issuu_api_key . '&format=json&names=' . $issuu_pdf_name . '&signature=' . $md5_signature;

		// Cath the response
		$response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );

		// Check if no sever error
		if ( is_wp_error( $response ) || isset($response->errors) || null == $response ) {
			return false;
		}
		// Decode the Json
		$response = json_decode( $response['body'] );

		if ( empty( $response) ) {
			return false;
		}

		// Check stat of the action
		if ( $response->rsp->stat == 'fail' ) {
			return false;
		}

		return true;

	}

	private function call_issuu_api ( $custom_parameters = array() ) {
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
		if ( is_wp_error( $response ) || isset($response->errors) || null == $response ) {
			return false;
		}
		// Decode the Json
		if ( 'issuu.document_embed.get_html_code' == $parameters['action'] ) {
			return $response['body'];
		}

		$response = json_decode( $response['body'] );

		if ( empty( $response) ) {
			return false;
		}

		// Check stat of the action
		if ( $response->rsp->stat == 'fail' ) {
			return false;
		}

		return $response->rsp->_content;
	}
}