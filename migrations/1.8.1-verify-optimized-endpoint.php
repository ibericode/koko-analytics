<?php

use KokoAnalytics\Endpoint_Installer;

defined('ABSPATH') or exit;

// re-install AND verify optimized endpoint file
(new Endpoint_Installer())->install();
