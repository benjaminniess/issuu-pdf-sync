<h3 class="media-title"><?php esc_html_e( 'Insert an Issuu PDF Flipbook', 'ips' ); ?></h3>
<form name="ips_shortcode_generator" id="ips_shortcode_generator">
	<div id="media-items">
		<div class="media-item media-blank">

			<table class="describe" style="width:100%;"><tbody>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label for="ips_layout"><?php esc_html_e( 'Select a PDF file', 'ips' ); ?></th>
					<td>
						<select name="issuu_pdf_id" id="issuu_pdf_id">
							<?php
							while ( $pdf_files->have_posts() ) : $pdf_files->the_post(); ?>
								<option value="<?php echo get_post_meta( get_the_ID(), 'issuu_pdf_id', true ); ?>"><?php echo substr( get_the_title(), 0, 35 ); ?></option>
							<?php endwhile; ?>
						</select>

					</td>
				</tr>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label for="ips_width"><span class="alignleft"><?php esc_html_e( 'Width', 'ips' ); ?></span></label></th>
					<td><input id="ips_width" type="number" min="0" max="2000" name="ips_width" value="<?php echo isset(  $ips_options['width'] ) ? (int) $ips_options['width'] : ''; ?>" /> px</td>
				</tr>

				<tr valign="top" class="field">
					<th class="label" scope="row"><label for="ips_height"><span class="alignleft"><?php esc_html_e( 'Height', 'ips' ); ?></span></label></th>
					<td><input id="ips_height" type="number" min="0" max="2000" name="ips_height" value="<?php echo isset(  $ips_options['height'] ) ? (int) $ips_options['height'] : ''; ?>" /> px</td>
				</tr>

				<?php if ( 'old' === $api_version ) : ?>
					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips_layout"><span class="alignleft"><?php esc_html_e( 'Layout', 'ips' ); ?></span></label></th>
						<td>
							<p style="height:25px;"><input id="ips_layout" type="radio" name="ips_layout" value="1" <?php checked( isset( $ips_options['layout'] ) ? $ips_options['layout'] : 0 , 1 ); ?> /> <?php esc_html_e( 'Two up', 'ips' ); ?><img src="<?php echo IPS_URL . '/images/layout-double-pages.png' ; ?>" height="16" style="margin-left:5px;" /></p>
							<p><input type="radio" name="ips_layout" value="2" <?php checked( isset( $ips_options['layout'] ) ? $ips_options['layout'] : 0 , 2 ); ?> /> <?php esc_html_e( 'Single page', 'ips' ); ?><img src="<?php echo IPS_URL . '/images/layout-single-page.png' ; ?>" height="16" style="margin-left:5px;" /></p>
						</td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips_bgcolor"><span class="alignleft"><?php esc_html_e( 'Background color', 'ips' ); ?></span></label></th>
						<td># <input id="ips_bgcolor" style="width:65px;" type="text" maxlength="6" name="ips_bgcolor" value="<?php echo isset(  $ips_options['bgcolor'] ) ? esc_attr( $ips_options['bgcolor'] ) : ''; ?>" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips_allow_full_screen"><span class="alignleft"><?php esc_html_e( 'Allow full screen', 'ips' ); ?></span></label></th>
						<td><input id="ips_allow_full_screen" name="ips_allow_full_screen_"  type="checkbox" <?php checked( isset( $ips_options['allow_full_screen'] ) ? $ips_options['allow_full_screen'] : '' , 1 ); ?> value="1" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips_show_flip_buttons"><span class="alignleft"><?php esc_html_e( 'Always show flip buttons', 'ips' ); ?></span></label></th>
						<td><input id="ips_show_flip_buttons" name="ips_show_flip_buttons" type="checkbox" <?php checked( isset( $ips_options['show_flip_buttons'] ) ? $ips_options['show_flip_buttons'] : '' , 1 ); ?> value="1" /></td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips_autoflip"><span class="alignleft"><?php esc_html_e( 'Auto flip', 'ips' ); ?></span></label></th>
						<td>
							<input type="checkbox" id="ips_autoflip" name="ips_autoflip" value="1" <?php checked( isset( $ips_options['autoflip'] ) ? $ips_options['autoflip'] : 0 , 1 ); ?> />
						</td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="ips_flip_timelaps"><span class="alignleft"><?php esc_html_e( 'Flip time laps', 'ips' ); ?></span></label></th>
						<td><input id="ips_flip_timelaps" type="number" step="100" min="1000" max="200000" name="ips_flip_timelaps" value="<?php echo isset(  $ips_options['flip_timelaps'] ) ? (int) $ips_options['flip_timelaps'] : '6000'; ?>" />
							<p class="description"><?php esc_html_e( '(in miliseconds - default : 6000)', 'ips' ); ?></p>
						</td>
					</tr>

				<?php endif;?>

				<tr valign="top" class="field">
					<td>
						<input name="insert_issuu_pdf" type="submit" class="button-primary" id="insert_issuu_pdf" tabindex="5" accesskey="p" value="<?php esc_html_e( 'Insert the PDF', 'ips' ) ?>">
					</td>
				</tr>

				</tbody></table>
		</div>
	</div>

</form>
