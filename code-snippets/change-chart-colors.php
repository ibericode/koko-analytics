<?php

/**
 * Plugin Name: Koko Analytics: Change chart colors
 */

add_action('admin_head', function () {
    ?>
<style>
.ka--pageviews {
    border-top-color: #72aee6 !important;
    fill: #72aee6 !important;
}
.ka--visitors {
    border-top-color: #2271b1 !important;
    fill: #2271b1 !important;
}
</style>
    <?php
});
