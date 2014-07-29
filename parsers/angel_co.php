<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 28.07.14
 * Time: 8:57
 */
class angel_co
{
    public $http;
    public $db;
    public $url = "https://angel.co/company_filters/search_data";
    public $type = 'angel.co';

    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function process()
    {

        $this->http->param_http_header = ['XMLHttpRequest'];
        for ($x = 1; $x < 500; $x++) {
            $postdata = [
                'sort' => 'signal',
                'page' => $x
            ];
            $page = json_decode($this->http->post($this->url, $postdata), true);

            if (isset($page['ids'])) {
                foreach ($page['ids'] as $id) {
                    $url = 'https://api.angel.co/1/startup_roles?v=1&startup_id=' . $id;
                    if (!$this->db->checkURL($url)) {
                        $page = json_decode($this->http->get($url), true);
                        print_r($page['startup_roles'][0]['tagged']);
                        $name = $page['startup_roles'][0]['tagged']['name'];
                        $site = isset($page['startup_roles'][0]['tagged']['company_url']) ? $page['startup_roles'][0]['tagged']['company_url'] : false;
                        if ($site) {
                            $email = Helper::getEmail($site);
                            $domain = Helper::domain($email);

                            $this->db->addItem($this->type."_".$page['startup_roles'][0]['tagged']['type'], $name, $email, $domain, $site, $url);
                        }
                    }
                }
            }
        }

    }


    static function go()
    {
        $base = new angel_co();
        $base->process();
    }

}

angel_co::go();

die();
