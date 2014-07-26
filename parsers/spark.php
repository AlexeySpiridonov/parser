<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 24.07.14
 * Time: 19:47
 */
class spark
{
    private $http;
    private $db;

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }


    function getPage()
    {
        $p = 1;
        while (true) {

            $postdata = [
                'ajax' => 'infinite'
            ];

            $page = json_decode($this->http->post("http://spark.ru/startups/0/0/" . $p, $postdata), true);

            if (preg_match_all('/<a href=\"(.*?)\" class=\"block\">/', $page['content'], $url)) {

                foreach ($url[1] as $u) {
                    $url_spark = 'http://spark.ru' . $u;
                    if (!$this->db->checkURL($url_spark)) {
                        $page = $this->http->get($url_spark);


                        $name = $this->name($page);
                        $site = $this->site($page);

                        if ($site) {
                            $email = Helper::getEmail($site);
                            $domain = Helper::domain($email);
                        } else {
                            $site = '';
                            $email = '';
                            $domain = '';
                        }

                        $this->db->addItem('spark', $name, $email, $domain, $site, $url_spark);
                    }
                }


            } else {
                break;
            }
            $p++;
        }
    }

    function name($page)
    {
        preg_match('/<title>(.*?)<\/title>/', $page, $res);

        return $res[1];
    }

    function site($page)
    {
        if (preg_match('/<a class=\"s_site ajaxfree\" target=\"_blank\" href=\"(.*?)\">/', $page, $res)) {
            return $res[1];
        }

        return false;
    }

    static function run()
    {
        $spark = new spark;
        $spark->getPage();
    }


}


spark::run();