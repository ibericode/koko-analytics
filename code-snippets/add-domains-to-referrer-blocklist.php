<?php

add_filter('koko_analytics_referrer_blocklist', function () {
    return [
        'search.myway.com',
        'bad-website.com',
    ];
});
