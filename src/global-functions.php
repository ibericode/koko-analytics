<?php

/**
 * Prints the Koko Analytics tracking script.
 *
 * You should only need to call this manually if your theme does not use the `wp_head()` and `wp_footer()` functions.
 *
 * @since 1.0.25
 */
function koko_analyics_tracking_script() {
	$script_loader = new KokoAnalytics\Script_Loader();
	$script_loader->maybe_enqueue_script( true );
}
