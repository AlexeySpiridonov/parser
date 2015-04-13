<?php

set_time_limit(0);

require_once('Mailer');

$date = date("Y-m-d", strtotime('-1 week'));

$filename = "list-dif-{$date}.csv";
$template = "week_later";

Mailer::send($filename, $template);