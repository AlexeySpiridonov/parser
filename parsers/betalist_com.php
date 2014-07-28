<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 28.07.14
 * Time: 7:31
 */
class betalist_com
{

    public $http;
    public $db;
    public $url = "http://betalist.com/?page=";
    public $type = 'betalist.com';

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function process()
    {
        $p = 1;
        while (true) {
            $page = $this->http->get($this->url . $p);
            $appPost = $this->allPostOnPage($page);
            if ($appPost) {
                print_r($appPost);
                foreach ($appPost[1] as $k => $post) {
                    $url = 'http://betalist.com' . $post;
                    if (!$this->db->checkURL($url)) {
                        $page = $this->http->get($url);
                        $site = $this->site($page);
                        $name = $appPost[2][$k];

                        if ($site) {
                            $email = Helper::getEmail($site);
                            $domain = Helper::domain($email);

                            $this->db->addItem($this->type, $name, $email, $domain, $site, $url);
                        }
                    }
                }
            } else {
                break;
            }

            if ($p > 500)
                break;

            $p++;

        }
    }

    function allPostOnPage($page)
    {
        if (preg_match_all('/<h2><a href=\"(.*?)\">(.*?)<\/a><\/h2>/', $page, $res))
            return $res;

        return false;
    }

    function site($page)
    {
        if (preg_match('/<meta content=\'(.*?)\' property=\'og:url\'>/', $page, $res)){
            $this->http->get($res[1] . '/visit');
            $info = $this->http->curlInfo();
            return $info['url'];
        }


        return false;
    }


    static function go()
    {
        $base = new betalist_com();
        $base->process();
    }

}

betalist_com::go();