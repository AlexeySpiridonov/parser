<?php
/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 26.07.14
 * Time: 13:09
 */

class f6s {

    public $http;
    public $db;
    private $url = "http://www.f6s.com/main/frontpage/ajax-startups-list?page=";

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getPage(){
        while(true){

        }
    }

    function site($page)
    {
        if (preg_match('/<dd>Сайт: <a href=\"(.*?)\" target=\"_blank\">/', $page, $res))
            return $res[1];

        return false;
    }

    public function getEmail($url)
    {
        $page = $this->http->get($url);

        if (preg_match('/([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/', $page, $mail)) {
            print_r($mail);
            return $mail[0];
        }

        return '';
    }

    static function run()
    {
        $base = new rusbase();

        for ($x = 1; $x <= 16; $x++) {
            $base->comOnPage($x);
        }
    }

    function domain($email){
        return preg_replace('/^(.*?)\@/', '', $email);
    }
} 