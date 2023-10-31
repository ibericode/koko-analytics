<?php
/**
* @var KokoAnalytics\Dashboard $this
 */
defined('ABSPATH') or exit; ?><!DOCTYPE html>
<html lang="<?php bloginfo('language'); ?>">
<head>
    <meta name="charset" content="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta name="robots" content="noindex, nofollow">
    <title>Koko Analytics</title>
</head>
<body class="ka-dashboard">
    <?php $this->show(); ?>
</body>
</html>
