<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 28.07.14
 * Time: 2:02
 */
class lifehacker_ru
{
    private $http;
    private $db;
    public $url = "http://lifehacker.ru/topics/technology/page/";
    public $type = 'lifehacker.ru';

    private $apple;
    private $google;

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;

        $this->apple = new appadvice_updated_free;
        $this->google = new google;
    }

    function process($x)
    {

        $page = $this->http->get($this->url . $x);
        foreach ($this->allPostOnPage($page) as $url) {
            if (!$this->db->checkURL($url)) {
                $page = $this->http->get($url);
                $apps = $this->appOnPage($page);
                if ($apps) {
                    print_r($apps);
                    foreach ($apps as $app) {
                        $page = $this->http->get($app);
                        if (preg_match('/itunes.apple.com/', $app)) {
                            if (!empty($page)) {
                                $mail = $this->apple->getEmail($page);
                                $this->db->addItem(
                                    $this->type,
                                    $this->apple->devName($page),
                                    $mail,
                                    $this->apple->domain(trim($mail)),
                                    $this->apple->siteDev($page),
                                    $app
                                );
                            }
                        } else if(preg_match('/play.google.com/', $app)){
                            if (!empty($page)) {
                                $mail = $this->google->devMail($page);
                                $this->db->addItem(
                                    $this->type,
                                    $this->google->nameAutor($page),
                                    $mail,
                                    $this->google->domain(trim($mail)),
                                    $this->google->siteDev($page),
                                    $app
                                );
                            }
                        }
                    }
                }
            }
        }

    }

    function allPostOnPage($page)
    {
        if (preg_match_all('/<h1>\n\s+<a class=\"black\" href=\"(.*?)\" title=/', $page, $res))
            return $res[1];

        return false;
    }

    function appOnPage($page)
    {
        if (preg_match_all('/<div class=\"apptitle\"><a target=\"_blank\" href=\"(.*?)\"/', $page, $res))
            return $res[1];

        return false;

    }

    static function go()
    {
        $base = new lifehacker_ru();
        for ($x = 1; $x <= 585; $x++) {
            $base->process($x);
        }
    }

}

lifehacker_ru::go();
