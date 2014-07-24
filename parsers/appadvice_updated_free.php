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

        echo "URL: " . $url . "\n";
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
        if (preg_match('/<h2>By\s(.*?)<\/h2>/', $page, $res))
            return $res[1];

        if (preg_match('Seller: <\/span>(.*?)<\/li>', $page, $res))
            return $res[1];

        return '';
    }

    function getEmail($page)
    {
        preg_match('/href=\"(.*?)\" class=\"see-all\">/', $page, $site);
        echo "Get mail from url: " . $site[1] . "\n";
        $page = $this->http->get($site[1]);
        if (preg_match('/([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/', $page, $mail)) {
            print_r($mail);
            return $mail[0];
        }


        return '';
    }

    function domain($mail)
    {
        if (preg_match('/\@(.*?)$/', $mail, $domain)) {
            return $domain[1];
        } else {
            return '';
        }
    }

    function getId($header)
    {
        preg_match('/x-apple-orig-url.*?\/id(\d{1,})\?/', $header, $res);
        return $res[1];
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

$a = new  appadvice_updated_free;
$a->run();