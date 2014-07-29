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

    public $type = 'hh_novosib';
    public $url = 'http://novosibirsk.hh.ru/search/vacancy?clusters=true&specialization=1&page=';
    public $text = '';

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getPage()
    {
        for ($x = 0; $x <= 100; $x++) {

            if(!empty($this->text))
                $url_p = $this->url . $x . "&text=" . $this->text;
            else
                $url_p = $this->url . $x ;

            $page = $this->http->get($url_p);
            $comOnPage = $this->vacancyOnPage($page);

            foreach ($comOnPage as $com) {
                $url = 'http://novosibirsk.hh.ru/employer/' . trim($com);
                if (!$this->db->checkURL($url)) {
                    $com_page = $this->http->get($url);

                    $name = $this->name($com_page);
                    $site = $this->site($com_page);

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

    function vacancyOnPage($page)
    {
        if (preg_match_all('/<a href=\"\/employer\/(.*?)\" data-qa=\"vacancy-serp__vacancy-employer\">/', $page, $res))
            return $res[1];

        return false;

    }

    function name($page)
    {
        if (preg_match('/<h1 class=\"employer-name\">.*? \«(.*?)\»<\/h1>/', $page, $res)) {
            return $res[1];
        }

        if (preg_match('/RSS<\/a>Вакансии компании «(.*?)»<\/h3>/', $page, $res))
            return $res[1];

        if (preg_match('/ <h1 class=\"b-terrasoft-content_colomn_text_title\">(.*?)<\/h1>/', $page, $res))
            return $res[1];

        return '';
    }

    function site($page)
    {
        if (preg_match('/<em class=\"company-linkview-container\"><a href=\"(.*?)"/', $page, $res)) {
            return $res[1];
        }

        return false;
    }


    static function run()
    {
        $spark = new hh_vacancy_novosib;
        $spark->getPage();
    }


}

//hh_vacancy_novosib::run();