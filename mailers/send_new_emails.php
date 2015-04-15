<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding("UTF-8");
set_time_limit(0);

require_once(dirname(__FILE__) . '/Mailer.php');

$date = date("Y-m-d");
$filename = "/var/www/parser/reports/list-dif-{$date}.csv";
$template = "first_letter.php";

$mailer = new Mailer($filename, $template, "Content Moderation Cloud Service");
$mailer->send();
