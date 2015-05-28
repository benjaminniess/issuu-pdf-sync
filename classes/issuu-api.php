<?php

class IPS_Issuu_Api {

    private $is = false;

    function __construct() {
        $this->setInstance();
    }

    public function setInstance() {
        $this->is = true;
        return true;
    }


    /*
     * Send a WordPress PDF to Issuu webservice
     *
     * @param $post_id the WP post id
     * @return string the issuu document id | false
     * @author Benjamin Niess
     */
    public function send_pdf_to_issuu( $post_id = 0 ){
        global $ips_options;

        if ( (int)$post_id == 0 ) {
            return false;
        }

        if ( self::has_api_keys() == false ) {
            return false;
        }

        // Get attachment infos
        $post_data = get_post( $post_id );

        // Check if the attachment exists and is a PDF file
        if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
            return false;

        $access = 'public';
        if ( isset( $ips_options['access'] ) ) {
            $access = $ips_options['access'];
        }

        // Parameters
        $parameters = array(
            'access'   => $access,
            'action'   => 'issuu.document.url_upload',
            'apiKey'   => $ips_options['issuu_api_key'],
            'format'   => 'json',
            'name'     => $post_data->post_name,
            'slurpUrl' => $post_data->guid,
            'title'    => sanitize_title( $post_data->post_title )
        );

        // Sort request parameters alphabetically (e.g. foo=1, bar=2, baz=3 sorts to bar=2, baz=3, foo=1)
        ksort( $parameters );

        // Prepare the MD5 signature for the Issuu Webservice
        $string = $ips_options['issuu_secret_key'];

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
        if( is_wp_error($response) || isset($response->errors) || $response == null ) {
            return false;
        }
        // Decode the Json
        $response = json_decode( $response['body'] );

        if ( empty( $response) )
            return false;

        // Check stat of the action

        if ( $response->rsp->stat == "fail" )
            return false;

        // Check if the publication id exists
        if ( !isset( $response->rsp->_content->document->documentId ) || empty( $response->rsp->_content->document->documentId ) )
            return false;

        // Update the attachment post meta with the Issuu PDF ID
        $document = $response->rsp->_content->document;

        update_post_meta( $post_id, 'issuu_pdf_id', $document->documentId );
        update_post_meta( $post_id, 'issuu_pdf_username', $document->username );
        update_post_meta( $post_id, 'issuu_pdf_name', $document->name );

        return $response->rsp->_content->document->documentId;
    }

    public function get_embed_id ( $document_id = '', $params = array() ) {
        $default_params = array(
            'action'   => 'issuu.document_embed.add',
            'documentId' => $document_id,
        );

        $final_params = array_merge( $default_params, $params );

        $document_embed_data = $this->call_issuu_api( $final_params );

        if ( !is_object( $document_embed_data ) || !isset( $document_embed_data->documentEmbed ) || !isset( $document_embed_data->documentEmbed->dataConfigId ) ) {
            return false;
        }

        $document_embed_code = $this->call_issuu_api( array(
            'action' => 'issuu.document_embed.get_html_code',
            'embedId' => $document_embed_data->documentEmbed->id
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
    public function delete_pdf_from_issuu( $post_id = 0 ){
        global $ips_options;

        if ( (int)$post_id == 0 )
            return false;

        // Get attachment infos
        $post_data = get_post( $post_id );

        // Check if the attachment exists and is a PDF file
        if ( !isset( $post_data->post_mime_type ) || $post_data->post_mime_type != "application/pdf" || !isset( $post_data->guid ) || empty ( $post_data->guid ) )
            return false;

        $issuu_pdf_name = get_post_meta( $post_id, 'issuu_pdf_name', true );
        if ( empty( $issuu_pdf_name ) )
            return false;

        // Prepare the MD5 signature for the Issuu Webservice
        $md5_signature = md5( $ips_options['issuu_secret_key'] . "actionissuu.document.deleteapiKey" . $ips_options['issuu_api_key'] . "formatjsonnames" . $issuu_pdf_name );

        // Call the Webservice
        $url_to_call = "http://api.issuu.com/1_0?action=issuu.document.delete&apiKey=" . $ips_options['issuu_api_key'] . "&format=json&names=" . $issuu_pdf_name . "&signature=" . $md5_signature;

        // Cath the response
        $response = wp_remote_get( $url_to_call, array( 'timeout' => 25 ) );

        // Check if no sever error
        if( is_wp_error($response) || isset($response->errors) || $response == null ) {
            return false;
        }
        // Decode the Json
        $response = json_decode( $response['body'] );

        if ( empty( $response) )
            return false;

        // Check stat of the action
        if ( $response->rsp->stat == "fail" )
            return false;

        // Update the attachment post meta with the Issuu PDF ID
        delete_post_meta( $post_id, 'issuu_pdf_id' );
        delete_post_meta( $post_id, 'issuu_pdf_name' );
        update_post_meta( $post_id, 'disable_auto_upload', 1 );

        return true;
    }

    private function call_issuu_api ( $custom_parameters = array() ) {
        global $ips_options;

        if (self::has_api_keys() == false ) {
            return false;
        }

        $access = 'public';
        if ( isset( $ips_options['access'] ) ) {
            $access = $ips_options['access'];
        }

        // Parameters
        $default_parameters = array(
            'access'   => $access,
            'apiKey'   => $ips_options['issuu_api_key'],
            'format'   => 'json',
        );

        $parameters = array_merge( $custom_parameters, $default_parameters );

        // Sort request parameters alphabetically (e.g. foo=1, bar=2, baz=3 sorts to bar=2, baz=3, foo=1)
        ksort( $parameters );

        // Prepare the MD5 signature for the Issuu Webservice
        $string = $ips_options['issuu_secret_key'];

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
        if( is_wp_error($response) || isset($response->errors) || $response == null ) {
            return false;
        }
        // Decode the Json
        if ( $parameters['action'] == 'issuu.document_embed.get_html_code' ) {
            return $response['body'];
        }

        $response = json_decode( $response['body'] );

        if ( empty( $response) ) {
            return false;
        }

        // Check stat of the action
        if ( $response->rsp->stat == "fail" ) {
            return false;
        }

        return $response->rsp->_content;
    }

    /*
     * Check if the Issuu API Key and Secret Key are entered
     * @return true | false
     * @author Benjamin Niess
     */
    function has_api_keys(){
        global $ips_options;

        if ( !isset( $ips_options['issuu_api_key'] ) || empty( $ips_options['issuu_api_key'] ) || !isset( $ips_options['issuu_secret_key'] ) || empty( $ips_options['issuu_secret_key'] ) )
            return false;

        return true;
    }
}