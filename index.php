<?php
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/ini.php';
require_once dirname(__FILE__).'/class/http.php';
require_once dirname(__FILE__).'/class/db.php';



require_once dirname(__FILE__).'/parsers/appadvice_updated_free.php';
require_once dirname(__FILE__).'/parsers/appadvice_updated_paid.php';
require_once dirname(__FILE__).'/parsers/appadvice_new_social_free.php';

require_once dirname(__FILE__).'/parsers/google.php';
require_once dirname(__FILE__).'/parsers/collection_topselling_new_free.php';
require_once dirname(__FILE__).'/parsers/collection_topselling_new_paid.php';
require_once dirname(__FILE__).'/parsers/game_collection_topselling_new_free.php';
require_once dirname(__FILE__).'/parsers/game_collection_topselling_new_paid.php';
require_once dirname(__FILE__).'/parsers/social_collection_topselling_free.php';
require_once dirname(__FILE__).'/parsers/social_collection_topselling_paid.php';



/*
$db = new db;
$allDev = $db->getReport();
$fp = fopen('./reports/list-'. date("Y-m-d") . '.csv', 'w');
foreach($allDev as $dev){
    $apps = $db->appToDev($dev['id']);
    $names = array();
    foreach($apps as $a){
        $names[] = $a['name'];
    }
    unset($dev['id']);

    fputcsv($fp, array_merge($dev, $names));
}
*/