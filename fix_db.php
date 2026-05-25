<?php
require_once dirname(dirname(dirname(__DIR__))) . '/wp-load.php';
require_once 'includes/class-geoscale-db.php';
GeoScale_DB::create_table();
echo "Tables created successfully.";
