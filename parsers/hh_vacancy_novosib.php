<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 26.07.14
 * Time: 0:55
 */
class hh_vacancy_novosib
{
    private $http;
    private $db;

    public $url = 'http://novosibirsk.hh.ru/search/vacancy?clusters=true&specialization=1&page=';

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getPage()
    {
        for ($x = 0; $x <= 15; $x++) {
            $page = $this->http->get($this->url . $x);
            $comOnPage = $this->vacancyOnPage($page);

            foreach ($comOnPage as $com) {
                $url = 'http://novosibirsk.hh.ru/employer/' . trim($com);
                if (!$this->db->checkURL($url)) {
                    $com_page = $this->http->get($url);

                    $name = $this->name($com_page);
                    $site = $this->site($com_page);

                    if ($site) {
                        $email = $this->email($site);
                        $domain = $this->domain($email);
                    } else {
                        $site = '';
                        $email = '';
                        $domain = '';
                    }

                    $this->db->addItem('hh_novosib', $name, $email,$domain, $site, $url);
                }

            }
        }
    }

    function vacancyOnPage($page)
    {
        if (preg_match_all('/<a href=\"\/employer\/(.*?)\" data-qa=\"vacancy-serp__vacancy-employer\">/', $page, $res))
            return $res[1];

        return false;

    }

    function name($page)
    {
        preg_match('/<h1 class=\"employer-name\">.*? \«(.*?)\»<\/h1>/', $page, $res);
        echo "name: " . $res[1] . "\n";
        return $res[1];
    }

    function site($page)
    {
        if (preg_match('/<em class=\"company-linkview-container\"><a href=\"(.*?)"/', $page, $res)) {
            return $res[1];
        }

        return false;
    }

    function email($url)
    {
        $page = $this->http->get($url);

        if (preg_match('/([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}/', $page, $mail)) {
            print_r($mail);
            return $mail[0];
        }

        return '';
    }


    static function run()
    {
        $spark = new hh_vacancy_novosib;
        $spark->getPage();
    }

    function domain($email)
    {
        return preg_replace('/^(.*?)\@/', '', $email);
    }
}

hh_vacancy_novosib::run();