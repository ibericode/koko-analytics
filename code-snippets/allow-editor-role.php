<?php

add_action( 'admin_init', function() {
	// only run on koko analytics page
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'koko-analytics' ) {
		return;
	}

	// add "view_koko_analytics" capability to "editor" role
	$role = get_role( 'editor' );
	$role->add_cap( 'view_koko_analytics' );
});
