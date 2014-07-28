<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 28.07.14
 * Time: 8:14
 */
class geekwire_com
{
    public $http;
    public $db;
    public $url = "http://www.geekwire.com/startup-list/";
    public $type = 'geekwire.com';

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function process()
    {

        $page = $this->http->get($this->url);
        // echo $page;
        $allSite = $this->allSiteOnPage($page);
        foreach ($allSite[1] as $k => $site) {
            if (!$this->db->checkURL($site)) {

                $name = trim($allSite[2][$k]);


                $email = Helper::getEmail($site);
                $domain = Helper::domain($email);

                $this->db->addItem($this->type, $name, $email, $domain, $site, $site);

            }
        }

    }

    function allSiteOnPage($page)
    {
        if (preg_match_all('~<a target=\"_blank\" href=\"(.*?)\">(.*?)<\/a>~is', $page, $res))
            return $res;

        return false;
    }


    static function go()
    {
        $base = new geekwire_com();
        $base->process();
    }

}

geekwire_com::go();
