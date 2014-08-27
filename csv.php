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
$fp = fopen( dirname(__FILE__) . '/reports/list-dif-'. date("Y-m-d") . '.csv', 'w');
foreach($all as $item){
    fputcsv($fp, array($item['email'], $item['name']) );
    $new_emails[]=$item['email'];
    $new_names[]=$item['name'];
}




// Ваш ключ доступа к API (из Личного Кабинета)
$api_key = "5aqr6s55iwbfk8j5uxqy5zj3nehckto7ubr7e18y";


// Список, куда их добавить
$list = "4068999";

// Создаём POST-запрос
$POST = array (
  'api_key' => $api_key,
  'field_names[0]' => 'email',
  'field_names[1]' => 'Name',
  'field_names[21]' => 'email_list_ids'
);
for ($i=0;$i<5;$i++){
  $POST['data[' . $i .'][0]'] = $new_emails[$i];
  $POST['data[' . $i .'][1]'] = iconv('cp1251', 'utf-8', $new_names[$i]);
  $POST['data[' . $i .'][2]'] = $list;
}

// Устанавливаем соединение
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_URL, 
            'http://api.unisender.com/ru/api/importContacts?format=json');
$result = curl_exec($ch);

if ($result) {
  // Раскодируем ответ API-сервера
  $jsonObj = json_decode($result);

  if(null===$jsonObj) {
    // Ошибка в полученном ответе
    echo "Invalid JSON";

  }
  elseif(!empty($jsonObj->error)) {
    // Ошибка импорта
    echo "An error occured: " . $jsonObj->error . "(code: " . $jsonObj->code . ")";

  } else {
    // Новые подписчики успешно добавлены
    echo "Success! Added " . $jsonObj->result->new_emails . " new e-mail addresses";

  }
} else {
  // Ошибка соединения с API-сервером
  echo "API access error";
}
