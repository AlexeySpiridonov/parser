<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 04.09.14
 * Time: 23:33
 */
class macworl_com
{

    public $type = "macworl_com";
    private $http;
    public $url;

    private $apple;


    function __construct()
    {
        $this->http = new http;

        $this->db = new db;

        $this->apple = new appadvice_updated_free;
    }

    /**
     * Получаем html код по номеру страницы
     * @param int $page
     * @return array
     */
    function getPage($page = 1)
    {
        $html = $this->http->get('http://www.macworld.com/ajaxGetMoreCategory?start=' . $page . '0&ajaxSearchType=1&catId=3027');
        preg_match_all('/<a\s+href=\"\/article\/(.*?)\">/', $html, $posts);
        return array_unique($posts[1]);
    }


    /**
     * Нахожим все статьи
     * @param $url
     * @return array
     */
    function article($url)
    {
        $this->url = 'http://www.macworld.com/article/' . $url;
        if ($this->db->checkURL($this->url)) return;

        $html = $this->http->get($this->url);
        return $this->AppsOnPage($html);
    }

    /**
     * Находить все ссылки на Itunes
     * @param $html
     * @return array
     */
    function AppsOnPage($html)
    {
        preg_match_all('/href=\"(https:\/\/itunes.apple.com.*?)\"/', $html, $urlItues);
        return array_unique($urlItues[1]);
    }

    function apple($app)
    {
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

        }
    }

    static function run()
    {
        $macworl_com = new macworl_com;
        for ($p = 0; $p <= 10; $p++) {
            $articl = $macworl_com->getPage($p);

            foreach ($articl as $at) {
                $resItunesUrl = $macworl_com->article($at);

                print_r($resItunesUrl);
                if ($resItunesUrl) {
                    foreach ($resItunesUrl as $ItunesUrl) {
                        $macworl_com->apple($ItunesUrl);
                    }
                }
            }
        }
    }

}

macworl_com::run();

