<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding("UTF-8");
set_time_limit(0);

require_once(dirname(__FILE__) . '/Mailer.php');

$date = date("Y-m-d");

$filename = dirname(__FILE__) . '/test.csv';
$template = dirname(__FILE__) . '/first_letter.php';

(new Mailer($filename, $template, "Content Moderation Test Mailer"))->send();
