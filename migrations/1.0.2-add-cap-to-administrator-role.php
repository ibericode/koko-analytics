<?php

defined( 'ABSPATH' ) or exit;

$role = get_role( 'administrator' );
$role->add_cap( 'view_koko_analytics' );
$role->add_cap( 'manage_koko_analytics' );
