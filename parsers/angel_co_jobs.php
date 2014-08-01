<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 01.08.14
 * Time: 9:05
 */
class angel_co_jobs
{

    private $http;
    private $db;
    private $type = __CLASS__;

    function __construct()
    {
        $this->http = new http();
        $this->db = new db;
    }

    function process()
    {
        $firstpage = json_decode($this->http->get("https://api.angel.co/1/jobs/?page=1"), true);
        print_r($firstpage);
        for ($x = 1; $x < $firstpage['last_page']; $x++) {
            $page = json_decode($this->http->get("https://api.angel.co/1/jobs/?page=" . $x), true);

            foreach ($page['jobs'] as $jobs) {
                $url = isset($jobs['startup']['angellist_url']) ? $jobs['startup']['angellist_url'] : '';
                if (!$this->db->checkURL($url)) {
                    $site = isset($jobs['startup']['company_url']) ? $jobs['startup']['company_url'] : false;
                    $name = isset($jobs['startup']['name']) ? $jobs['startup']['name'] : '';

                    if ($site) {
                        $email = Helper::getEmail($site);
                        $domain = Helper::domain($email);
                    } else {
                        $site = '';
                        $email = '';
                        $domain = '';
                    }
                    $this->db->addItem($this->type, $name, $email, $domain, $site, $url);
                }
            }

        }

    }

    static function run()
    {
        $an = new angel_co_jobs();
        $an->process();
    }
}

angel_co_jobs::run();
