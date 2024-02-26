<?php

// What we should have done in version 1.0.20

// Unlink the custom endpoint file to ensure we get the latest logic for determining whether to use this
// Supporess warnings because it may not be there (and that is fine)
@unlink(ABSPATH . '/koko-analytics-collect.php');

// Update option that says to use custom endpoint, this will be recalculated the next time logic for custom endpoint runs
update_option('koko_analytics_use_custom_endpoint', false);
