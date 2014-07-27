<?php
/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 26.07.14
 * Time: 13:50
 */

class Helper {

    static function getEmail($url)
    {
        PRFLR::Begin('Helper.getEmail');

        $http = new http;
        $page = $http->get($url);
        $m = "";
        if (!empty($page) && preg_match('/([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/', $page, $mail)) {
            print_r($mail);
            $m = $mail[0];
        }
        PRFLR::End('Helper.getEmail');
        return $m;
    }

    static function domain($email){
        return preg_replace('/^(.*?)\@/', '', $email);
    }
} 
