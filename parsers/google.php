<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 18.07.14
 * Time: 3:54
 */
class google
{

    private $http;
    private $db;

    public $url = 'https://play.google.com/store/apps/collection/topselling_new_paid';

    public $type = "gplay";

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getAppOnPage($page = 0)
    {
        $page = $page * 60;
        $postdata = array(
            'start' => $page,
            'num' => '60',
            'numChildren' => '0',
            'ipf' => '1',
            'xhr' => '1',
            'hl' => 'ru',
            );
        $page = $this->http->post($this->url, $postdata);
        if (preg_match_all('/<a class=\"card-click-target\" href=\"(.*?)\"/', $page, $res)) {
            return array_unique($res[1]);
        }

        return false;
    }

    function getAppParam($url)
    {
        if ($this->db->checkURL($url)) return;

        $page = $this->http->get($url);

        $mail = $this->devMail($page);

        $this->db->addItem(
            $this->type,
            $this->nameAutor($page),
            $mail,
            $this->domain(trim($mail)),
            $this->siteDev($page),
            $url
        );
    }

    /**
     * get App Name
     * @param $page
     * @return mixed
     */
    function nameApp($page)
    {
        preg_match('/<div class=\"document-title\" itemprop=\"name\"> <div>(.*?)<\/div>/', $page, $res);
        return $res[1];
    }

    /**
     * get name Autor
     * @param $page
     * @return mixed
     */
    function nameAutor($page)
    {
        preg_match('/<span itemprop=\"name\">(.*?)<\/span>/', $page, $res);
        return $res[1];
    }

    /**
     * Email Dev
     * @param $page
     * @return mixed
     */
    function devMail($page)
    {
        if (preg_match('/<a class=\"dev-link\" href=\"mailto:(.*?)\"/', $page, $res))
            return $res[1];

        return '';

    }

    function siteDev($page)
    {
        if (preg_match('/href=\"https\:\/\/www.google.com\/url\?q=(.*?)\" rel=\"nofollow\" target=\"_blank\">.*?<\/a>/', $page, $res)) {
            return parse_url( urldecode(htmlspecialchars_decode($res[1])),  PHP_URL_HOST);
        }
        return '';
    }

    function domain($mail)
    {
        return Helper::domain($mail);
    }


    static function run()
    {
        $google = new google;

        for ($p = 0; $p < 30; $p++) {
            echo "Page: " . $p . "\n";

            $apps = $google->getAppOnPage($p);
            if (!$apps)
                break;

            foreach ($apps as $url) {
                echo "Page: " . $p . "\n";
                $google->getAppParam('https://play.google.com/' . $url);
            }

        }

    }
}

//google::run();

