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
        $http = new http;
        $page = $http->get($url);

        if (!empty($page) && preg_match('/([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/', $page, $mail)) {
            print_r($mail);
            return $mail[0];
        }

        return '';
    }

    static function domain($email){
        return preg_replace('/^(.*?)\@/', '', $email);
    }
} 