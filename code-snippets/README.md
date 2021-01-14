## Code snippets for Koko Analytics

This directory contains a collection of code snippets to modify the default behavior of the [Koko Analytics plugin](https://www.kokoanalytics.com/).

#### My installation is not using the optimized endpoint file. What can I do to force this?
First, allow the plugin to install the optimized endpoint automatically by taking the following steps. 

1. Make your WordPress root directory writable for the user running nginx or Apache.
2. Visit or refresh the Koko Analytics dashboard page. This will make the plugin attempt to install the optimized endpoint file.
3. Search your WordPress root directory for a file named `koko-analytics-collect.php`.
4. (Optional) Revert file permissions on WP root directory.

If you see the file named `koko-analytics-collect.php` then the plugin will now start using this file for improved performance. 
Only if you're still not seeing this file, try the manual approach described below.

#### How to manually install the optimized tracking endpoint?
Go to the settings page in your Koko Analytics dashboard. Follow the instructions at the bottom of the page to manually install the optimized tracking endpoint.
