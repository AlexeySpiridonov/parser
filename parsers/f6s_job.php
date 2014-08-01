<?php
/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 26.07.14
 * Time: 15:40
 */

class f6s_job extends f6s{
    public $url = "http://www.f6s.com/main/frontpage/ajax-jobs-list?page=";
    public $type = "f6c_job";

    static function run()
    {
        $base = new f6s_job();
        $base->getPage();

    }

    function getAllStOnpage($page){
        preg_match_all('/<a href=\"(.*?)\" title=\".*?\"  class=\"name no\">/', $page, $res);
        $url = array();
        foreach($res[1] as $p){
            preg_match('/^\/(.*?)\//', $p, $j);
            $url[] = "http://www.f6s.com/".$j[1]."#/about";
        }

        return $url;
    }
}

f6s_job::run();