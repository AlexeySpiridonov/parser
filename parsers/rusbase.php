<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 24.07.14
 * Time: 18:12
 */
class rusbase
{

    public $http;
    public $db;
    private $url = "http://rusbase.vc/company/";

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function comOnPage($p)
    {
        $url = $this->url . "?&page=" . $p;
        $page = $this->http->get($url);

        preg_match('/<div class=\"g_240\">(.*?)<\/div>\n/', $page, $content);
        preg_match_all('/<a href=\"(.*?)\">/', $content[1], $com);

        foreach ($com[1] as $c) {
            $url = "http://rusbase.vc" . $c;
            if (!$this->db->checkURL($url)) {
                $page_c = $this->http->get($url);

                $name = $this->name($page_c) . "\n";
                $url = $this->url_com($page_c) . "\n";

                $site = $this->site($page_c) . "\n";
                if ($site) {
                    $email = $this->getEmail($site);
                    $domain = $this->domain($email);
                } else {
                    $site = '';
                    $email = '';
                    $domain = '';
                }

                $this->db->addItem('rusbase', $name, $email, $domain, $site, $url);

            }
            echo "\n\n\n\n\n";
        }
    }


    function name($page)
    {
        if (preg_match('/<meta property=\"og:title\" content=\"(.*?)\" \/>/', $page, $res))
            return $res[1];

        return '';
    }

    function url_com($page)
    {
        if (preg_match('/<meta property=\"og:url\" content=\"(.*?)\" \/>/', $page, $res))
            return $res[1];

        return '';
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

    function domain($email)
    {
        return preg_replace('/^(.*?)\@/', '', $email);
    }


}

rusbase::run();