<!DOCTYPE html>
<html lang="<?php bloginfo('language'); ?>">
<head>
    <meta name="charset" content="<?php bloginfo('charset'); ?>>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta name="robots" content="noindex" />
    <title>Koko Analytics</title>
    <style>
        body {
            background: #f1f1f1;
            color: #3c434a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 13px;
            line-height: 1.4em;
        }
        a {
            color: #3858e9;
        }
        select {
            font-size: 14px;
            line-height: 2;
            color: #2c3338;
            border-color: #8c8f94;
            box-shadow: none;
            border-radius: 3px;
            padding: 0 24px 0 8px;
            min-height: 30px;
            max-width: 25rem;
            -webkit-appearance: none;
            background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E') no-repeat right 5px top 55%;
            background-size: 16px 16px;
            cursor: pointer;
            vertical-align: middle;
        }

        input,
        select {
            box-sizing: border-box;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            box-shadow: 0 0 0 transparent;
            border-radius: 4px;
            border: 1px solid #8c8f94;
            background-color: #fff;
            color: #2c3338;
        }
        input[type="date"] {
            padding: 0 8px;
            line-height: 2;
            min-height: 30px;
            font-size: 14px;
        }
        .description{
            margin: 2px 0 5px;
            color: #646970;
        }
    </style>
    <link rel="stylesheet" href="<?php echo esc_attr(plugins_url('assets/dist/css/admin.css', KOKO_ANALYTICS_PLUGIN_FILE)); ?>">
    <?php wp_print_styles('dashicons'); ?>
</head>
<body>
    <?php require __DIR__ . '/dashboard-page.php'; ?>
    <?php wp_print_scripts('koko-analytics-admin'); ?>
</body>
</html>
