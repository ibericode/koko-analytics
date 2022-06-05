<?php
/**
 * WP Engine does not allow the creation of .php files using file_put_content which Koko uses, so we change
 * it to use a file with .txt extension. Make sure to change SECRET-NAME to something unique
 * so that nobody can see your temporary visitor logs. Additionally, make sure this is added to your themes functions.php
 * BEFORE you activate Koko analytics. If Koko has previously been activated, you need to delete the file /koko-analytics-collect.php
 * and then go to the Koko Analytics Settings page to trigger it to install the file again.
 *
 * See https://github.com/timber/timber/issues/1311 for more info on this behaviour surrounding creation of .php files on WP Engine.
 */
define('KOKO_ANALYTICS_BUFFER_FILE', rtrim( wp_upload_dir( null, false )['basedir'], '/' ) . '/pageviews-SECRET-NAME.txt');
