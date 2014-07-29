<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 29.07.14
 * Time: 14:06
 */
class hh_vacancy_text extends hh_vacancy_novosib
{

    public $text = 'php';
    public $type = 'hh_vacancy';

    static function start($text)
    {
        $hh_vacancy_text = new hh_vacancy_text;
        $hh_vacancy_text->text = $text;
        $hh_vacancy_text->getPage();
    }
}

hh_vacancy_text::start('php');