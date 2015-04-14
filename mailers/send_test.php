<?php

set_time_limit(0);

require_once('/var/www/parser/mailers/Mailer.php');

$date = date("Y-m-d");

$filename = "/var/www/parser/mailers/test.csv";
$template = "/var/www/parser/mailers/test.txt";

Mailer::send($filename, $template);