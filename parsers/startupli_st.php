<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 28.07.14
 * Time: 7:00
 */
class startupli_st
{

    public $http;
    public $db;
    public $url = "http://startupli.st/startups/latest/";
    public $type = 'startupli.st';

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
                if(count($appPost[1]) < 2)
                    break;
                
                foreach ($appPost[1] as $k => $post) {
                    $url = 'http://startupli.st' . $post;
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
        if (preg_match_all('/<a class=\"profile\" href=\"(.*?)\">(.*?)<\/a>/', $page, $res))
            return $res;

        return false;
    }

    function site($page)
    {
        if (preg_match('/<a class=\"visit\" href=\"(.*?)\"/', $page, $res))
            return $res[1];

        return false;
    }


    static function go()
    {
        $base = new startupli_st();
        $base->process();
    }

}

startupli_st::go();