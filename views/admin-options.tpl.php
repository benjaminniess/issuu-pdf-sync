<div class="wrap" id="ips_options">
	<h2><?php esc_html_e( 'Issuu PDF Sync', 'ips' ); ?></h2>

	<form method="post" action="#">
		<table class="form-table describe media-upload-form">

			<tr><td colspan="2"><h3><?php esc_html_e( 'Issuu configuration', 'ips' ); ?></h3></td></tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[issuu_api_key]"><span class="alignleft"><?php esc_html_e( 'Issuu API Key', 'ips' ); ?>
							<br /><a href="http://issuu.com/" target="_blank"><?php esc_html_e( 'Get an Issuu API Key', 'ips' ); ?></span>
					</label></th>
				<td><input id="ips[issuu_api_key]" type="text" class="text" name="ips[issuu_api_key]" value="<?php echo isset( $ips_options['issuu_api_key'] ) ? esc_attr( $ips_options['issuu_api_key'] ) : '' ; ?>" /></a>
				</td>
			</tr>
			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[issuu_secret_key]"><span class="alignleft"><?php esc_html_e( 'Issuu private key', 'ips' ); ?></span></label></th>
				<td><input id="ips[issuu_secret_key]" type="text" name="ips[issuu_secret_key]" value="<?php echo isset( $ips_options['issuu_secret_key'] ) ? esc_attr( $ips_options['issuu_secret_key'] ) : ''; ?>" /></td>
			</tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[auto_upload]"><span class="alignleft"><?php esc_html_e( 'Automatically upload PDFs to Issuu', 'ips' ); ?></span></label></th>
				<td><input id="ips[auto_upload]" type="checkbox" <?php checked( isset( $ips_options['auto_upload'] ) ? (int) $ips_options['auto_upload'] : '' , 1 ); ?> name="ips[auto_upload]" value="1" /></td>
			</tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[add_ips_button]"><span class="alignleft"><?php esc_html_e( 'Add the Issuu PDF Sync button to TinyMCE', 'ips' ); ?></span></label></th>
				<td><input id="ips[add_ips_button]" type="checkbox" <?php checked( isset( $ips_options['add_ips_button'] ) ? (int) $ips_options['add_ips_button'] : '' , 1 ); ?> name="ips[add_ips_button]" value="1" /></td>
			</tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[access]"><span class="alignleft"><?php esc_html_e( 'Access', 'ips' ); ?></span></label></th>
				<td>
					<select id="ips[access]" name="ips[access]">
						<option value="public" <?php selected( $access, 'public' ); ?>><?php esc_html_e( 'Public', 'ips' ); ?></option>
						<option value="private" <?php selected( $access, 'private' ); ?>><?php esc_html_e( 'Private', 'ips' ); ?></option>
					</select>
				</td>
			</tr>

			<tr><td colspan="2"><h3><?php esc_html_e( 'Default embed code configuration', 'ips' ); ?></h3></td></tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[new_api_version]"><span class="alignleft"><?php esc_html_e( 'API version', 'ips' ); ?></span></label></th>
				<td>
					<p style="height:25px;"><input id="ips-new-api-version" type="checkbox" name="ips[new_api_version]" value="1" <?php checked( ( isset( $ips_options['new_api_version'] ) && 1 === (int) $ips_options['new_api_version'] ) ? 1 : 0 , 1 ); ?> /> <label for="ips-new-api-version"><?php esc_html_e( 'Use the new Issuu API', 'ips' ); ?></label></p>
					<p><?php esc_html_e( 'The new Issuu API has less customization params than the old one but it works on mobiles and tablets. The old API let you customize more options but works on desktop only.', 'ips' ); ?></p>
				</td>
			</tr>

			<tr valign="top" class="field old-api">
				<th class="label" scope="row"><label for="ips[layout]"><span class="alignleft"><?php esc_html_e( 'Layout', 'ips' ); ?></span></label></th>
				<td>
					<p style="height:25px;"><input id="ips-layout-two-up" type="radio" name="ips[layout]" value="1" <?php checked( isset( $ips_options['layout'] ) ? (int) $ips_options['layout'] : 0 , 1 ); ?> /> <label for="ips-layout-two-up"><?php esc_html_e( 'Two up', 'ips' ); ?></label><img src="<?php echo IPS_URL . '/images/layout-double-pages.png' ; ?>" height="16" style="margin-left:5px;" /></p>
					<p><input type="radio" id="ips-layout-single" name="ips[layout]" value="2" <?php checked( isset( $ips_options['layout'] ) ? (int) $ips_options['layout'] : 0 , 2 ); ?> /> <label for="ips-layout-single"><?php esc_html_e( 'Single page', 'ips' ); ?></label><img src="<?php echo IPS_URL . '/images/layout-single-page.png' ; ?>" height="16" style="margin-left:5px;" /></p>
				</td>
			</tr>

			<tr valign="top" class="field old-api">
				<th class="label" scope="row"><label for="ips[custom_layout]"><span class="alignleft"><?php esc_html_e( 'Custom layout', 'ips' ); ?></span></label></th>
				<td>
					<p style="height:150px;"><input id="ips-custom-layout-default" type="radio" name="ips[custom_layout]" value="default" <?php checked( ! isset( $ips_options['custom_layout'] ) || 'default' === $ips_options['custom_layout'] ? true : false, true ); ?> /> <label for="ips-custom-layout-default"><?php esc_html_e( 'Default', 'ips' ); ?></label><br /><img src="<?php echo IPS_URL . '/images/default.png' ; ?>" height="100" style="margin-left:15px;" /></p>
					<?php $skins = array( 'basicBlue', 'crayon', 'whiteMenu' );
					foreach ( $skins as $skin ) : ?>
						<p><input type="radio" id="ips-custom-layout-<?php echo $skin; ?>" name="ips[custom_layout]" value="<?php echo esc_attr( $skin) ; ?>" <?php checked( isset( $ips_options['custom_layout'] )  && $ips_options['custom_layout'] === $skin ? true : false , true ); ?> /> <label for="ips-custom-layout-<?php echo esc_attr( $skin ); ?>"><?php echo esc_html( $skin ); ?></label><br /><img src="<?php echo IPS_URL . '/images/sample_' . esc_attr( $skin ) . '.jpg' ; ?>" height="100" style="margin-left:5px;" /></p>
					<?php endforeach; ?>
				</td>
			</tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[width]"><span class="alignleft"><?php esc_html_e( 'Width', 'ips' ); ?></span></label></th>
				<td><input id="ips[width]" type="number" min="0" max="2000" name="ips[width]" value="<?php echo isset(  $ips_options['width'] ) ? (int) $ips_options['width'] : ''; ?>" /></td>
			</tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label for="ips[height]"><span class="alignleft"><?php esc_html_e( 'Height', 'ips' ); ?></span></label></th>
				<td><input id="ips[height]" type="number" min="0" max="2000" name="ips[height]" value="<?php echo isset(  $ips_options['height'] ) ? (int) $ips_options['height'] : ''; ?>" /></td>
			</tr>

			<tr valign="top" class="field old-api">
				<th class="label" scope="row"><label for="ips[bgcolor]"><span class="alignleft"><?php esc_html_e( 'Background color', 'ips' ); ?></span></label></th>
				<td><input id="ips[bgcolor]" style="width:65px;" class="ips-colorp" type="text" maxlength="6" name="ips[bgcolor]" value="<?php echo isset(  $ips_options['bgcolor'] ) ? esc_attr( $ips_options['bgcolor'] ) : ''; ?>" /></td>
			</tr>

			<tr valign="top" class="field old-api">
				<th class="label" scope="row"><label for="ips[allow_full_screen]"><span class="alignleft"><?php esc_html_e( 'Allow full screen', 'ips' ); ?></span></label></th>
				<td><input id="ips[allow_full_screen]" type="checkbox" <?php checked( isset( $ips_options['allow_full_screen'] ) ? (int) $ips_options['allow_full_screen'] : '' , 1 ); ?> name="ips[allow_full_screen]" value="1" /></td>
			</tr>

			<tr valign="top" class="field old-api">
				<th class="label" scope="row"><label for="ips[show_flip_buttons]"><span class="alignleft"><?php esc_html_e( 'Always show flip buttons', 'ips' ); ?></span></label></th>
				<td><input id="ips[show_flip_buttons]" type="checkbox" <?php checked( isset( $ips_options['show_flip_buttons'] ) ? (int) $ips_options['show_flip_buttons'] : '' , 1 ); ?> name="ips[show_flip_buttons]" value="1" /></td>
			</tr>

			<tr valign="top" class="field old-api">
				<th class="label" scope="row"><label for="ips[autoflip]"><span class="alignleft"><?php esc_html_e( 'Auto flip', 'ips' ); ?></span></label></th>
				<td>
					<input type="checkbox" id="ips[autoflip]" name="ips[autoflip]" value="1" <?php checked( isset( $ips_options['autoflip'] ) ? (int) $ips_options['autoflip'] : 0 , 1 ); ?> />
				</td>
			</tr>

			<tr valign="top" class="field old-api">
				<th class="label" scope="row"><label for="ips[flip_timelaps]"><span class="alignleft"><?php esc_html_e( 'Flip time laps', 'ips' ); ?></span></label></th>
				<td><input id="ips[flip_timelaps]" type="number" step="100" min="1000" max="200000" name="ips[flip_timelaps]" value="<?php echo isset(  $ips_options['flip_timelaps'] ) ? (int) $ips_options['flip_timelaps'] : '6000'; ?>" />
					<p class="description"><?php esc_html_e( '(in miliseconds - default : 6000)', 'ips' ); ?></p>
				</td>
			</tr>
			<td>
				<p class="submit">
					<?php wp_nonce_field( 'ips-update-options' ); ?>
					<input type="submit" name="save" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'ips' ) ?>" />
				</p>
			</td>
			<tr>

			</tr>

			<tr><td colspan="2"><h3><?php esc_html_e( 'How to insert a PDF Flipbook ?', 'ips' ); ?></h3></td></tr>

			<tr><td colspan="2">

					<ol>
						<li><?php esc_html_e( 'Make sure that the "Automatically upload PDFs to Issuu" box is checked or that you\'ve manually send the PDF to the Issuu website.', 'ips' ); ?></li>
						<li>
							<?php esc_html_e( 'Click to Issuu button on the TinyMCE main bar', 'ips' ); ?><br />
							<img src="<?php echo IPS_URL; ?>/images/screenshot-5.png" width="510" style="padding: 10px;" />
						</li>
						<li>
							<?php esc_html_e( 'Select your PDF file in the dropdown list and add some specific params if you need. Note that you can change the default settings in the settings page', 'ips' ); ?><br />
							<img src="<?php echo IPS_URL; ?>/images/screenshot-6.png" width="510" style="padding: 10px;" />
						</li>
						<li>
							<?php esc_html_e( 'Click to the insert button and then the shortcode will be generated. You can easily cut and past this shortcode everywhere in your content', 'ips' ); ?><br />
							<img src="<?php echo IPS_URL; ?>/images/screenshot-2.png" />
						</li>
					</ol>

				</td></tr>

			<tr>
				<td colspan="2">
					<h3><?php esc_html_e( 'How to manually use the shortcode ? (advanced usage)', 'ips' ); ?></h3>
				</td></tr>
			<tr><td colspan="2">
					<p><code><?php esc_html_e( '[pdf issuu_pdf_id="id_of_your_PDF" width="500" height="300"]', 'ips' ); ?></code></p>
					<p class="description"><?php esc_html_e( 'In this example, we want to specify a width and a height only for this PDF', 'ips' ); ?></p>

					<p><code><?php esc_html_e( '[pdf issuu_pdf_id="id_of_your_PDF" layout="browsing" autoFlip="true" autoFlipTime="4000"]', 'ips' ); ?></code></p>
					<p class="description"><?php esc_html_e( '<strong>OLD API ONLY</strong> In this other example, we want to specify the browsing layout (one page presentation) and we want the PDF pages to autoflip each 4 seconds', 'ips' ); ?></p>


			<tr><td colspan="2"><h3><?php esc_html_e( 'Which params can be used with the shortcode?', 'ips' ); ?></h3></td></tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label><span class="alignleft">issuu_pdf_id</span></label></th>
				<td><p class="description"><?php esc_html_e( 'The ISSUU PDF ID', 'ips' ); ?></p></td>
			</tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label><span class="alignleft">width</span></label></th>
				<td><p class="description"><?php esc_html_e( 'The width of the animation in pixels', 'ips' ); ?></p></td>
			</tr>

			<tr valign="top" class="field">
				<th class="label" scope="row"><label><span class="alignleft">height</span></label></th>
				<td><p class="description"><?php esc_html_e( 'The height of the animation in pixels', 'ips' ); ?></p></td>
			</tr>

			<?php if ( ! isset( $ips_options['new_api_version'] ) || 0 === (int) $ips_options['new_api_version'] ) : ?>
				<tr valign="top" class="field">
					<th class="label" scope="row"><label><span class="alignleft">layout</span></label><br /></th>
					<td><p class="description"><?php esc_html_e( 'The layout of the animation. Possible values : "<strong>presentation</strong>" (double page), "<strong>browsing</strong>" (single page)', 'ips' ); ?></p></td>
				</tr>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label><span class="alignleft">backgroundColor</span></label></th>
					<td><p class="description"><?php esc_html_e( 'The background color - In hexadecimal format - without "#" ', 'ips' ); ?></p></td>
				</tr>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label><span class="alignleft">autoFlip</span></label></th>
					<td><p class="description"><?php esc_html_e( 'Enable or disable the Auto Flip feature. Possible values : "true", "false"', 'ips' ); ?></p></td>
				</tr>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label><span class="alignleft">autoFlipTime</span></label></th>
					<td><p class="description"><?php esc_html_e( 'The timelaps for the page flipe in milliseconds', 'ips' ); ?></p></td>
				</tr>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label><span class="alignleft">showFlipBtn</span></label></th>
					<td><p class="description"><?php esc_html_e( 'Allways show the right left flip buttons. Possible values : "true", "false"', 'ips' ); ?></p></td>
				</tr>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label><span class="alignleft">allowfullscreen</span></label></th>
					<td><p class="description"><?php esc_html_e( 'Allow the full screen mode (if not, open in a new window). Possible values : "true", "false"', 'ips' ); ?></p></td>
				</tr>
			<?php endif; ?>

		</table>

	</form>
</div>
