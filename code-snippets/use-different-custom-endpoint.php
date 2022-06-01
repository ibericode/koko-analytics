<?php

# Creating the custom endpoint file
// To use a different custom endpoint file location, first create a file at your preferred location and copy over the contents from the file linked below
// file: https://github.com/ibericode/koko-analytics/blob/master/koko-analytics-collect.php

# Fix path references
// In the file, fix the relative file paths to the uploads directory and the functions.php file from the wp-content/plugins/koko-analytics directory.

# Define a constant
// To tell Koko Analytics of your custom endpoint, define the following constant:
define( 'KOKO_ANALYTICS_CUSTOM_ENDPOINT', '/path-to-your-custom-endpoint-file.php' );

# Test the result
// Finally, ensure the file is accessible through your web server and that Koko Analytics is able to use it correctly.
