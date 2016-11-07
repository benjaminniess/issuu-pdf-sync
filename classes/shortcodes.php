<?php
class IPS_Shortcodes {

	function __construct() {
		add_shortcode( 'pdf', array( __CLASS__, 'issuu_pdf_embeder' ) );
	}

	/**
	 * The ISSUU PDF shortcode. Usage doc on the admin pannel
	 */
	public static function issuu_pdf_embeder( $atts, $content = null ) {
		global $ips_options;

		if ( isset( $ips_options['new_api_version'] ) && 1 === (int) $ips_options['new_api_version'] ) {
			return self::shortcode_new_api( $atts );
		} else {
			return self::shortcode_old_api( $atts );
		}
	}

	public static function shortcode_old_api( $atts = array() ) {
		global $ips_options;

		if ( isset( $ips_options['layout'] ) && 2 === (int) $ips_options['layout'] ) {
			$layout = 'presentation';
		} else {
			$layout = 'browsing';
		}

		extract(shortcode_atts(array(
			'issuu_pdf_id' => null,
			'width' => $ips_options['width'],
			'height' => $ips_options['height'],
			'layout' => $layout,
			'backgroundColor' => $ips_options['bgcolor'],
			'autoFlipTime' => $ips_options['flip_timelaps'],
			'autoFlip' => ( isset($ips_options['autoflip']) && 1 === (int) $ips_options['autoflip'] ) ? 'true' : 'false',
			'showFlipBtn' => ( isset($ips_options['show_flip_buttons']) && 1 === (int) $ips_options['show_flip_buttons'] ) ? 'true' : 'false',
			'allowfullscreen' => ( isset($ips_options['allow_full_screen']) && 1 === (int) $ips_options['allow_full_screen'] ) ? 'true' : 'false',
			'customLayout' => ( isset($ips_options['custom_layout']) && 'default' !== $ips_options['custom_layout'] ) ? $ips_options['custom_layout'] : false,
		), $atts));

		// Check if the required param is set
		if ( empty( $issuu_pdf_id ) ) {
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
			'loadingInfoText' => esc_html__( 'Loading...', 'ips' ),
		);

		if ( isset( $customLayout ) && ! empty( $customLayout ) ) {
			if ( is_dir( TEMPLATEPATH . '/issuu-skins/' . $customLayout ) ) {
				$parameters['layout'] = get_bloginfo( 'template_directory' ) . '/issuu-skins/' . $customLayout . '/layout.xml';

				$layout_embed = ' layout="' . ( get_bloginfo( 'template_directory' ) . '/issuu-skins/' . $customLayout . '/layout.xml' ) . '"';
			} else {
				$parameters['layout'] = IPS_URL . '/issuu-skins/' . $customLayout . '/layout.xml';

				$layout_embed = ' layout="' . ( IPS_URL . '/issuu-skins/' . $customLayout . '/layout.xml' ) . '"';
			}
		} else {
			$layout_url   = '';
			$layout_embed = '';
		}

		$issuu_swf_url = 'http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf';
		$issuu_swf_url = add_query_arg( $parameters, $issuu_swf_url );

		$flashvars = build_query( $parameters );

		// Dimensions
		if ( strpos( $width, 'px' ) === false && strpos( $width, '%' ) === false ) {
			$width .= 'px';
		}

		if ( strpos( $height, 'px' ) === false && strpos( $height, '%' ) === false ) {
			$height .= 'px';
		}

		$style = sprintf( 'width: %s; height: %s', $width, $height );

		// Start to get the content to return it at the end
		ob_start(); ?>

		<div>
			<object style="<?php echo esc_attr( $style ); ?>" >
				<param name="movie" value="<?php echo esc_attr( $issuu_swf_url ); ?>" />
				<param name="allowfullscreen" value="<?php echo esc_attr( $allowfullscreen ); ?>" />
				<param name="wmode" value="transparent" />
				<param name="menu" value="false" />
				<embed src="http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf" <?php echo esc_attr( $layout_embed ); ?> type="application/x-shockwave-flash" allowfullscreen="<?php echo esc_attr( $allowfullscreen ); ?>" wmode="transparent" menu="false" style="<?php echo esc_attr( $style ); ?>" flashvars="<?php echo esc_attr( $flashvars ); ?>" />
			</object>
		</div>

		<?php do_action( 'after-ips-shortcode', $issuu_pdf_id ); ?>

		<?php

		// Return the shortcode content
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public static function shortcode_new_api( $atts = array() ) {
		global $ips_options;

		extract(shortcode_atts( array(
			'issuu_pdf_id' => null,
			'width' => $ips_options['width'],
			'height' => $ips_options['height'],
		), $atts ));

		// Check if the required param is set
		if ( empty( $issuu_pdf_id ) ) {
			return false;
		}

		$issuu_api = new IPS_Issuu_Api();
		if ( ! $issuu_api->is() ) {
			return false;
		}
		$pdf_embed_data = $issuu_api->get_embed_id( $issuu_pdf_id, array( 'width' => $width, 'height' => $height ) );
		if ( empty( $pdf_embed_data ) ) {
			return false;
		}

		// Start to get the content to return it at the end
		ob_start();

		echo $pdf_embed_data;

		do_action( 'after-ips-shortcode', $issuu_pdf_id );

		// Return the shortcode content
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
