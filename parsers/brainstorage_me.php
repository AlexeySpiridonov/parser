<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 29.07.14
 * Time: 12:33
 */
class brainstorage_me
{
    public $http;
    public $db;
    public $url = "http://brainstorage.me/jobs?page=";
    public $type = 'brainstorage.me';

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function process()
    {
        for ($x = 1; $x < 1000; $x++) {
            $page = $this->http->get($this->url . $x);

            if (preg_match_all('/<a href=\"(.*?)\" class=\"job_icon\"/', $page, $u)) {
                foreach ($u[1] as $p) {

                    $url = 'http://brainstorage.me' . $p;
                    if (!$this->db->checkURL($url)) {
                        $page = $this->http->get($url);

                        $name = $this->name($page);
                        $site = $this->site($page);

                        $domain = '';
                        $email = Helper::getEmail($url);
                        if (empty($email))
                            $email = Helper::getEmail($site);

                        if (!empty($email))
                            $domain = Helper::domain($email);


                        $this->db->addItem($this->type, $name, $email, $domain, $site, $url);
                    }
                }

            } else {
                break;
            }
        }
    }

    function name($page)
    {
        if (preg_match('/<div class=\'company_name\'>(.*?)<\/div>/', $page, $res))
            return $res[1];

        return '';
    }

    function site($page)
    {
        if (preg_match('/<div class=\'contact\'><a href=\"(.*?)\">/', $page, $res))
            return $res[1];

        return '';
    }

    static function go()
    {
        $base = new brainstorage_me();
        $base->process();
    }

}

brainstorage_me::go();