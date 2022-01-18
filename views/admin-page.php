<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wrap" id="koko-analytics-admin">

	<?php
	if ( false === $is_cron_event_working ) {
		echo '<div class="notice notice-warning inline koko-analytics-cron-warning"><p>';
		echo esc_html__( 'There seems to be an issue with your site\'s WP Cron configuration that prevents Koko Analytics from automatically processing your statistics.', 'koko-analytics' );
		echo ' ';
		echo esc_html__( 'If you\'re not sure what this is about, please ask your webhost to look into this.', 'koko-analytics' );
		echo '</p></div>';
	}

	if ( false === $is_buffer_dir_writable ) {
		echo  '<div class="notice notice-warning inline"><p>';
		echo wp_kses( sprintf( __( 'Koko Analytics is unable to write to the <code>%s</code> directory. Please update the file permissions so that your web server can write to it.', 'koko-analytics' ), $buffer_dirname ), array( 'code' => array() ) );
		echo '</p></div>';
	}
	?>

	<noscript>
		<?php echo esc_html__( 'Please enable JavaScript for this page to work.', 'koko-analytics' ); ?>
	</noscript>

	<div id="koko-analytics-mount"></div>
</div>
