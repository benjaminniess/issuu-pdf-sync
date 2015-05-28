<?php
class IPS_Main {

    function __construct() {

    }

    public static function install() {
        // Enable default features on plugin activation
        $ips_options = get_option ( 'ips_options' );

        if ( empty( $ips_options ) ) {
            update_option( 'ips_options', array(
                'allow_full_screen' => 1,
                'auto_upload'       => 1,
                'add_ips_button'    => 1,
                'width'             => 640,
                'height'            => 480,
                'layout'            => 1,
                'autoflip'          => 0,
                'show_flip_buttons' => 0,
                'bgcolor'           => 'FFFFFF',
                'flip_timelaps'     => 6000
            ) );
        }
    }


    /**
     * Load a view depending on its directory (child theme, parent theme or inside this plugin)
     */
    public static function load_template( $file = '' ) {
        if ( empty( $file ) || !is_string( $file ) ) {
            return false;
        }
        if ( is_file(STYLESHEETPATH .'views/ips/' . $file .'.tpl.php' ) ) { // Use custom type from child theme
            return( STYLESHEETPATH .'views/ips/' . $file .'.tpl.php' );
        } elseif ( is_file(TEMPLATEPATH .'views/ips/' . $file .'.tpl.php' ) ) { // Use custom type from parent theme
            return( TEMPLATEPATH .'views/ips/' . $file .'.tpl.php' );
        }

        return( IPS_DIR . '/views/' . $file .'.tpl.php' );
    }
}