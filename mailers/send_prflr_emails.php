<?php
date_default_timezone_set('Europe/Moscow');
mb_internal_encoding("UTF-8");
set_time_limit(0);
require_once(dirname(__FILE__) . '/Mailer.php');
$date = date("Y-m-d", strtotime('-1 day'));
$filename = "/var/www/parser/mailers/test.csv";
$template = "/var/www/parser/mailers/prflr.php";
$mailer = new Mailer($filename, $template, "Analyze application performance by using PRFLR");
$mailer->send();
