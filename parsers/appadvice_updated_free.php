<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 19.07.14
 * Time: 22:03
 */
class appadvice_updated_free
{

    private $db;
    private $http;

    public $url = "http://appadvice.com/apps/new/all/free/all/";

    public $type = "apple";

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getAppOnPage($p)
    {
        $page = $this->http->get($this->url . $p);
        preg_match_all('/class=\"cssUITableCell mentAppListTab forceHOVC\" href=\"(.*?)\"/', $page, $res);
        return $res[1];
    }

    function getPage($url)
    {
        if ($this->db->checkURL($url)) return;

        $page = $this->http->get($url);
        if (!empty($page)) {
            $getHeaders = $this->http->getHeaders();

            $mail = $this->getEmail($page);

            $this->db->addItem(
                $this->type,
                $this->devName($page),
                $mail,
                $this->domain(trim($mail)),
                $this->siteDev($page),
                $url
            );
        }

    }

    function nameApp($page)
    {
        preg_match('/<h1>(.*?)<\/h1>/', $page, $res);
        return $res[1];
    }

    function devName($page)
    {

        if (preg_match_all('/<h2>.*? (.*?)<\/h2>/', $page, $res))
            return $res[1][2];

        if (preg_match('/Seller: <\/span>(.*?)<\/li>/', $page, $res))
            return $res[1];

        if (preg_match('/<li class=\"copyright\">© (.*?)<\/li>/', $page, $res))
            return '';
    }

    function getEmail($page)
    {
        if (preg_match('/href=\"(.*?)\" class=\"see-all\">/', $page, $site)) {

            if (!isset($site[1])) return null;

            echo "Get mail from url: " . $site[1] . "\n";
            return Helper::getEmail($site[1]);
        }

        return null;
    }


    function domain($mail)
    {
        return Helper::domain($mail);
    }

    function getId($header)
    {
        if (preg_match('/x-apple-orig-url.*?\/id(\d{1,})\?/', $header, $res))
            return $res[1];

        return '';

    }

    function siteDev($page)
    {
        preg_match('/href=\"(.*?)\" class=\"see-all\">/', $page, $res);
        return parse_url($res[1], PHP_URL_HOST);
    }

    function run()
    {
        for ($p = 1; $p <= 8; $p++) {
            foreach ($this->getAppOnPage($p) as $url) {
                $this->getPage($url);
            }
        }
    }

}

//$a = new  appadvice_updated_free;
//$a->run();
