<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 19.07.14
 * Time: 22:03
 */
class appadvice_social_free extends appadvice_updated_free
{

    public $url = "http://appadvice.com/apps/new/social-networking/free/all/";

}

$a = new  appadvice_social_free;
$a->run();