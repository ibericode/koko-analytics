<?php

use KokoAnalytics\Plugin;

defined('ABSPATH') || exit;

(new Plugin())->create_and_protect_uploads_dir();
