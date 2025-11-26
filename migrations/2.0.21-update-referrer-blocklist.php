<?php

use KokoAnalytics\Blocklist;

if (class_exists(Blocklist::class)) {
    (new Blocklist())->update(true);
}
