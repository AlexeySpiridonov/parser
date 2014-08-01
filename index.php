<?php
date_default_timezone_set('Europe/Moscow');

include(dirname(__FILE__) . '/PRFLR.SDK.PHP/prflr.php');

PRFLR::init('hive', 'tOa0U4uBphHNQZUO7yWajR1SVoUmUWR1');

require_once dirname(__FILE__) . '/ini.php';
require_once dirname(__FILE__) . '/class/http.php';
require_once dirname(__FILE__) . '/class/db.php';
require_once __DIR__ . '/class/Helper.php';


require_once __DIR__ . '/parsers/angel_co.php';

//apple
require_once dirname(__FILE__) . '/parsers/appadvice_updated_free.php';
require_once dirname(__FILE__) . '/parsers/appadvice_updated_paid.php';
require_once dirname(__FILE__) . '/parsers/appadvice_new_social_free.php';
require_once dirname(__FILE__) . '/parsers/appadvice_new.php';


//others
require_once __DIR__ . '/parsers/hh_vacancy_novosib.php';
require_once __DIR__ . '/parsers/hh_vacancy_moderator.php';
require_once __DIR__ . '/parsers/startupli_st.php';
require_once __DIR__ . '/parsers/betalist_com.php';
require_once __DIR__ . '/parsers/geekwire_com.php';


require_once __DIR__ . '/parsers/macradar_ru.php';
macradar_ru::start();

require_once __DIR__ . '/parsers/f6s.php';
require_once __DIR__ . '/parsers/f6s_job.php';
require_once __DIR__ . '/parsers/rusbase.php';
require_once __DIR__ . '/parsers/rusbase_invest.php';
require_once __DIR__ . '/parsers/spark.php';
require_once __DIR__ . '/parsers/spark_ru_jobs.php';
require_once __DIR__ . "/parsers/brainstorage_me.php";

$a = new  appadvice_updated_free;
$a->run();
$a = new  appadvice_social_free;
$a->run();
$a = new  appadvice_updated_paid;
$a->run();
$a = new  appadvice_new;
$a->run();



//google
require_once dirname(__FILE__) . '/parsers/google.php';
require_once dirname(__FILE__) . '/parsers/collection_topselling_new_free.php';
require_once dirname(__FILE__) . '/parsers/collection_topselling_new_paid.php';
require_once dirname(__FILE__) . '/parsers/game_collection_topselling_new_free.php';
require_once dirname(__FILE__) . '/parsers/game_collection_topselling_new_paid.php';
require_once dirname(__FILE__) . '/parsers/social_collection_topselling_free.php';
require_once dirname(__FILE__) . '/parsers/social_collection_topselling_paid.php';
require_once dirname(__FILE__) . '/parsers/app_collection_lifestyle_top_free.php';


//apple and google
require_once __DIR__ . '/parsers/lifehacker_ru.php';


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
