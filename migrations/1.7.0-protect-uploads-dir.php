<?php

use KokoAnalytics\Plugin;

defined('ABSPATH') or exit;

(new Plugin())->create_and_protect_uploads_dir();
