<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 05.09.14
 * Time: 1:59
 */
class google_dating
{

    private $http;
    private $db;
    private $type = 'google_dating';
    private $url = 'https://www.google.ru/search?q=dating&newwindow=1&start=';


    function __construct()
    {
        $this->http = new http();

        $this->db = new db;
    }

    function getPage($num = 0)
    {
        $num = $num * 10;
        echo $this->url . $num . "\n\n\n\n";
        $html = $this->http->get($this->url . $num);
        $this->http->referer = $this->url . $num;
        if (preg_match_all("/<h3 class=\"r\"><a href=\"http:\/\/www.google.com\/url\?url=(.*?)&amp;rct=/", $html, $num_all)) {
            return $num_all[1];
        }

        echo $html;

        if(preg_match_all('/<a href=\"http:\/\/www.google.ru\/url\?url=(.*?)&amp;rct=/', $html, $num_all)) {
            unset($num_all[1][0]);
            return $num_all[1];
        }


        return;
    }

    function getSite($url)
    {

        if ($this->db->checkURL($url)) return;

        $page = $this->http->get($url);
        $pars_url = parse_url($url);
        $site = $pars_url['scheme'] . '://' . $pars_url['host'];

        $email = Helper::getEmail($site);
        $domain = Helper::domain($email);

        $this->db->addItem($this->type, $this->name($page), $email, $domain, $site, $url);

    }

    function name($html)
    {
        if (preg_match('~<title>(.*?)</title>~is', $html, $tit)) {
            return trim($tit[1]);
        }

        return '';
    }

    static function run(){
        $gl = new google_dating;
        for ($p = 0; $p <= 50; $p++) {
            $searchSite = $gl->getPage($p);

           // print_r($searchSite);

            if ($searchSite) {
                foreach ($searchSite as $url) {
                    $gl->getSite($url);
                }
                sleep(rand(10, 20));
            }
        }
    }

}

google_dating::run();
