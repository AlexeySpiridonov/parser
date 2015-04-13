<?php

set_time_limit(0);

require_once('Mailer');

$date = date("Y-m-d");

$filename = "list-dif-{$date}.csv";
$template = "week_later";

Mailer::send($filename, $template);