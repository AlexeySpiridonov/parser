<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 26.07.14
 * Time: 1:46
 */
class rusbase_invest extends rusbase
{

    public $url = 'http://rusbase.vc/investor/?&page=';


    function investOnPage()
    {
        for ($x = 0; $x <= 34; $x++) {
            $page = $this->http->get($this->url . $x);

            preg_match_all('/<a href=\"(.*?)\" class=\"post_title\">/', $page, $invets);

            foreach ($invets[1] as $inv) {
                $url = "http://rusbase.vc" . $inv;
                if (!$this->db->checkURL($url)) {
                    $page_c = $this->http->get($url);

                    $name = $this->name($page_c) . "\n";

                    $site = $this->site($page_c) . "\n";
                    if ($site) {
                        $email = $this->getEmail($site);
                        $domain = $this->domain($email);
                    } else {
                        $site = '';
                        $email = '';
                        $domain = '';
                    }

                    $this->db->addItem('rusbase_invest', $name, $email, $domain, $site, $url);

                }

            }
        }
    }

    static function run()
    {
        $base = new rusbase_invest();

        for ($x = 1; $x <= 16; $x++) {
            $base->investOnPage($x);
        }
    }
}

rusbase_invest::run();