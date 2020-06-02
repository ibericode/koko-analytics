<?php
add_filter('koko_analytics_load_tracking_script', function() {
	return ! is_404();
});
