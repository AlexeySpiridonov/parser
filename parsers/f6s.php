<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 26.07.14
 * Time: 13:09
 */
class f6s
{

    public $http;
    public $db;
    private $url = "http://www.f6s.com/main/frontpage/ajax-startups-list?page=";

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getPage()
    {
        $p = 0;
        while (true) {
            $url_page = $this->url . $p;
            echo "URL PAGE: " . $url_page . "\n";
            $page = $this->http->get($url_page);
            $allSt = $this->getAllStOnpage($page);

            if ($allSt) {
                foreach ($allSt as $st) {
                    if (!$this->db->checkURL($st)) {
                        $page = $this->http->get($st);
                        $site = $this->site($page);
                        $name = $this->name($page);
                        if ($site) {
                            $email = Helper::getEmail($site);
                            $domain = Helper::domain($email);
                        } else {
                            $site = '';
                            $email = '';
                            $domain = '';
                        }


                        $this->db->addItem('f6s', $name, $email, $domain, $site, $st);
                    }
                }
            } else {
                break;
            }

            if ($p > 10000) {
                break;
            }

            $p++;

        }
    }

    function getAllStOnpage($page)
    {
        if (preg_match_all('/<a href=\"(.*?)\" target=\"_blank\" class=\"name no \"/', $page, $res))
            return $res[1];

        return false;
    }

    function site($page)
    {
        if (preg_match('/<a href=\"(.*?)\" itemprop=\"url\" target=\"_blank\" title=\"Website\"/', $page, $res))
            return $res[1];

        return false;
    }

    function name($page)
    {
        if (preg_match('/<title>(.*?) \|/', $page, $res))
            return $res[1];

        return '';
    }


    static function run()
    {
        $base = new f6s();
        $base->getPage();

    }


}

f6s::run();