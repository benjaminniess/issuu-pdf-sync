<?php
class IPS_Shortcodes {

    function __construct() {
        add_shortcode('pdf', array( __CLASS__, 'issuu_pdf_embeder' ) );
    }

    /**
     * The ISSUU PDF shortcode. Usage doc on the admin pannel
     */
    public static function issuu_pdf_embeder($atts, $content = null) {
        global $ips_options;

        if (isset($ips_options['layout']) && $ips_options['layout'] == 2)
            $layout = "presentation";
        else
            $layout = "browsing";

        extract(shortcode_atts(array(
            'issuu_pdf_id' => null,
            'width' => $ips_options['width'],
            'height' => $ips_options['height'],
            'layout' => $layout,
            'backgroundColor' => $ips_options['bgcolor'],
            'autoFlipTime' => $ips_options['flip_timelaps'],
            'autoFlip' => (isset($ips_options['autoflip']) && $ips_options['autoflip'] == 1) ? 'true' : 'false',
            'showFlipBtn' => (isset($ips_options['show_flip_buttons']) && $ips_options['show_flip_buttons'] == 1) ? 'true' : 'false',
            'allowfullscreen' => (isset($ips_options['allow_full_screen']) && $ips_options['allow_full_screen'] == 1) ? 'true' : 'false',
            'customLayout' => (isset($ips_options['custom_layout']) && $ips_options['custom_layout'] != 'default') ? $ips_options['custom_layout'] : false
        ), $atts));

        // Check if the required param is set
        if (empty($issuu_pdf_id)) {
            return false;
        }

        // Parameters
        $parameters = array(
            'mode' => 'embed',
            'backgroundColor' => empty($backgroundColor) ? false : $backgroundColor,
            'viewMode' => $layout,
            'showFlipBtn' => $showFlipBtn,
            'documentId' => $issuu_pdf_id,
            'autoFlipTime' => $autoFlipTime,
            'autoFlip' => $autoFlip,
            'loadingInfoText' => __('Loading...', 'ips')
        );


        $issuu_api = new IPS_Issuu_Api();
        $pdf_embed_data = $issuu_api->get_embed_id( $issuu_pdf_id, array( 'width' => $width, 'height' => $height ) );
        if ( empty( $pdf_embed_data ) ) {
            return false;
        }

        // Start to get the content to return it at the end
        ob_start();

        echo $pdf_embed_data;

        do_action('after-ips-shortcode', $issuu_pdf_id);

        // Return the shortcode content
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}