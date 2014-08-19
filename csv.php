<?php
date_default_timezone_set('Europe/Moscow');

include(dirname(__FILE__) . '/PRFLR.SDK.PHP/prflr.php');

PRFLR::init('hive', 'tOa0U4uBphHNQZUO7yWajR1SVoUmUWR1');

require_once dirname(__FILE__) . '/ini.php';
require_once dirname(__FILE__) . '/class/http.php';
require_once dirname(__FILE__) . '/class/db.php';
require_once __DIR__ . '/class/Helper.php';


$db = new db;
$all = $db->getReport();
$fp = fopen('./reports/list-'. date("Y-m-d") . '.csv', 'w');
foreach($all as $item){
    fputcsv($fp, array($item->email, $item->name) );
}
