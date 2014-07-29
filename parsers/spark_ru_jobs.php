<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 28.07.14
 * Time: 12:14
 */
class spark_ru_jobs
{
    private $http;
    private $db;
    private $type = 'spark.ru_jobs';

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getPage()
    {
        $x = 0;
        while (true) {
            $datapost = [
                'ajax' => 'infinite'
            ];

            $page = json_decode($this->http->post('http://spark.ru/jobs/0/0/' . $x, $datapost), true);

            if (preg_match_all('/<a href=\"(.*?)\" class=\"vacancy_title\">/', $page['content'], $url)) {
                foreach ($url[1] as $p) {
                    $url = "http://spark.ru" . $p;
                    if (!$this->db->checkURL($url)) {
                        $page = $this->http->get("http://spark.ru" . $p);

                        $name = $this->name($page);
                        $site = $this->site($page);
                        $email = Helper::getEmail($url);
                        $domain = Helper::domain($email);

                        $this->db->addItem($this->type, $name, $email, $domain, $site, $url);

                    }
                }
            } else {
                break;
            }
            $x++;


        }


    }

    function name($page)
    {
        if (preg_match('/<a itemprop=\"name\" class=\"name\">(.*?)<\/a>/', $page, $res))
            return $res[1];

        return '';
    }

    function site($page)
    {
        if (preg_match('/<a itemprop=\"name\" class=\"name\" href=\"(.*?)\">/', $page, $res)) {
            $page = $this->http->get("http://spark.ru" . $res[1]);

            if (preg_match('/<a class=\"s_site ajaxfree\" target=\"_blank\" href=\"(.*?)\">/', $page, $url))
                return $url[1];
        }

        return '';
    }


    static function start()
    {
        $spark = new spark_ru_jobs;
        $spark->getPage();
    }
}

spark_ru_jobs::start();