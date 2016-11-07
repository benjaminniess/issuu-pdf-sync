<?php if ( ! empty( $pdf_data['issuu_pdf_sync_id']['value'] ) ) : ?>

	<span style="color:#00AA00;" id="admin_delete_pdf">
		<?php esc_html_e( 'This PDF is already synchronised on Issuu', 'ips' ); ?><br />

		<span class="trash">
			<?php

			printf(
				'<a href="#" style="color:#BC0B0B;">%s</a>',
				esc_html__( 'Click here to delete this PDF from Issuu', 'ips' )
			);

			?>
		</span>
	</span>

<?php else : ?>

	<span style="color:#AA0000;" id="admin_send_pdf">
		<?php esc_html_e( 'This PDF is not synchronised on Issuu', 'ips' ); ?><br />

		<?php

		printf(
			'<a href="#">%s</a>',
			esc_html__( 'Click here to send this PDF to Issuu', 'ips' )
		);

		?>
	</span>

<?php endif; ?>

<script type="text/javascript">
	jQuery(function() {

		// Sending PDF
		jQuery('#admin_send_pdf a').click(function( e ) {
			e.preventDefault();
			if( !window.confirm( '<?php echo esc_js( esc_html__( 'Are you sure you want to send this PDF on Issuu ?', 'ips' ) ); ?>' ) ){
				return false;
			}
			jQuery('#admin_send_pdf').html('<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" /> <?php esc_html_e( 'Loading', 'ips' ); ?>...');
			jQuery('#admin_send_pdf').css( 'color', '#000000');
			jQuery.get('<?php echo str_replace( '&amp;', '&', wp_nonce_url( admin_url( 'media.php?attachment_id=' . (int) $attachment_id . '&amp;action=send_pdf' ), 'issuu_send_' . (int) $attachment_id ) ); ?>', function(data) {
				data_obj = JSON.parse( data );
				if ( data_obj.status == 'error' ) {
					jQuery('#admin_send_pdf').html( data_obj.message + ' (err ' + data_obj.code + ')' );
					jQuery('#admin_send_pdf').css( 'color', '#AA0000');
				}
				else if ( data_obj.status == 'success' ) {
					jQuery('#admin_send_pdf').html( data_obj.message );
					jQuery('#admin_send_pdf').css( 'color', '#00AA00');
				}
				else {
					jQuery('#admin_send_pdf').html('<?php echo esc_js( esc_html__( 'An error occurred during synchronisation with Issuu', 'ips' ) ); ?>');
					jQuery('#admin_send_pdf').css( 'color', '#AA0000');
				}
			});
		});

		// Deleting PDF
		jQuery('#admin_delete_pdf a').click(function( e ) {
			e.preventDefault();
			if( !window.confirm( '<?php echo esc_js( esc_html__( 'Are you sure you want to delete this PDF from Issuu ?', 'ips' ) ); ?>' ) ){
				return false;
			}
			jQuery('#admin_delete_pdf').html('<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" /> <?php esc_attr_e( 'Loading', 'ips' ); ?>...');
			jQuery('#admin_delete_pdf').css( 'color', '#000000');
			jQuery.get('<?php echo str_replace( '&amp;', '&', wp_nonce_url( admin_url( 'media.php?attachment_id=' . (int) $attachment_id . '&amp;action=delete_pdf' ), 'issuu_delete_' . (int) $attachment_id ) ); ?>', function(data) {

				data_obj = JSON.parse( data );
				if ( data_obj.status == 'error' ) {
					jQuery('#admin_delete_pdf').html( data_obj.message + ' (err ' + data_obj.code + ')' );
					jQuery('#admin_delete_pdf').css( 'color', '#AA0000');
				}
				else if ( data_obj.status == 'success' ) {
					jQuery('#admin_delete_pdf').html( data_obj.message );
					jQuery('#admin_delete_pdf').css( 'color', '#00AA00');
				}
				else {
					jQuery('#admin_delete_pdf').html('<?php echo esc_js( esc_html__( 'An error occurred during unsynchronisation with Issuu', 'ips' ) ); ?>');
					jQuery('#admin_delete_pdf').css( 'color', '#AA0000');
				}
			});
			e.preventDefault();
		});
	});
</script>
