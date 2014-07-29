<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 29.07.14
 * Time: 14:06
 */
class hh_vacancy_text extends hh_vacancy_novosib
{
    public $url = "http://novosibirsk.hh.ru/search/vacancy?area=2&text=%D0%BC%D0%BE%D0%B4%D0%B5%D1%80%D0%B0%D1%82%D0%BE%D1%80&specialization=&salary=&currency_code=RUR&page=";
    public $type = 'hh.ru_moderator';

    static function start()
    {
        $hh_vacancy_text = new hh_vacancy_text;
        $hh_vacancy_text->getPage();
    }
}

hh_vacancy_text::start();