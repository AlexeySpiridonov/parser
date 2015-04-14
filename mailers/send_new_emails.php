<?php

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding("UTF-8");
set_time_limit(0);

require_once(dirname(__FILE__) . '/Mailer.php');

$date = date("Y-m-d");

$filename = "list-dif-{$date}.csv";
$template = "week_later";

$mailer = new Mailer($filename, $template, "New emails Subject");
$mailer->send();