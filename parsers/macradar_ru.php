<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 26.07.14
 * Time: 19:41
 */
class macradar_ru extends appadvice_updated_free
{

    public $http;
    public $db;
    public $type = 'macradar.ru';


    function __construct()
    {
        $this->http = new http;
        $this->db = new db;
    }

    function getPageMacradar()
    {

        $p = 0;
        while (true) {
            $postdata = array(
                'action' => 'infinite_scroll',
                'page' => $p,
                'currentday' => '25.07.14',
                'order' => 'DESC',
                'scripts' =>
                    array(
                        'jquery-core',
                        'jquery-migrate',
                        'jquery',
                        'q2w3-fixed-widget',
                        'userpro_min',
                        'userpro_swf',
                        'userpro_spinners',
                        'userpro_lightview',
                        'waq-masonry',
                        'prettyPhoto',
                        'wpajax',
                        'thsp-sticky-header-plugin-script',
                        'spin',
                        'jquery.spin',
                        'tiled-gallery',
                        'fitvids',
                        'dorayaki-custom',
                        'userpro_sc',
                        'userpro_ed',
                        'the-neverending-homepage',
                        'jquery-ui-core',
                        'jquery-ui-datepicker',
                        'devicepx',
                        'jetpack-carousel',
                        'mediaelement',
                        'wp-mediaelement',
                        'new-royalslider-main-js',
                    ),
                'query_args' =>
                    array(
                        'category_name' => 'software',
                        'error' => '',
                        'm' => '',
                        'p' => '0',
                        'post_parent' => '',
                        'subpost' => '',
                        'subpost_id' => '',
                        'attachment' => '',
                        'attachment_id' => '0',
                        'name' => '',
                        'static' => '',
                        'pagename' => '',
                        'page_id' => '0',
                        'second' => '',
                        'minute' => '',
                        'hour' => '',
                        'day' => '0',
                        'monthnum' => '0',
                        'year' => '0',
                        'w' => '0',
                        'tag' => '',
                        'cat' => '1',
                        'tag_id' => '',
                        'author' => '',
                        'author_name' => '',
                        'feed' => '',
                        'tb' => '',
                        'paged' => '0',
                        'comments_popup' => '',
                        'meta_key' => '',
                        'meta_value' => '',
                        'preview' => '',
                        's' => '',
                        'sentence' => '',
                        'fields' => '',
                        'menu_order' => '',
                        'posts_per_page' => '10',
                        'ignore_sticky_posts' => 'false',
                        'suppress_filters' => 'false',
                        'cache_results' => 'false',
                        'update_post_term_cache' => 'true',
                        'update_post_meta_cache' => 'true',
                        'post_type' => '',
                        'nopaging' => 'false',
                        'comments_per_page' => '50',
                        'no_found_rows' => 'false',
                        'order' => 'DESC',
                    ),
                'last_post_date' => '2014-07-22 12:20:07',
            );
            $page = json_decode($this->http->post('http://macradar.ru/?infinity=scrolling', $postdata), true);
            $allMore = $this->allReadMoreOnPage($page);
            if ($allMore && count($allMore) > 0) {
                foreach ($allMore as $url) {
                    if (!$this->db->checkURL($url)) {
                        $page = $this->http->get($url);
                        $appsOnPage = $this->allApp($page);
                        foreach ($appsOnPage as $app) {
                            $page = $this->http->get($app);
                            if (!empty($page)) {
                                $mail = $this->getEmail($page);
                                $this->db->addItem(
                                    $this->type,
                                    $this->devName($page),
                                    $mail,
                                    $this->domain(trim($mail)),
                                    $this->siteDev($page),
                                    $app
                                );
                            }
                        }
                    }
                }
            } else {
                break;
            }
            $p++;
        }
    }

    function allReadMoreOnPage($page)
    {
        $page['html'] = isset($page['html']) ? $page['html'] : '';
        if (preg_match_all('/<a href=\"(.*?)\" class=\"more-link\">/', $page['html'], $res))
            return $res[1];

        return false;
    }

    function allApp($page)
    {
        preg_match_all('/<div class=\"apptitle\"><a target=\"_blank\" rel=\"nofollow\" href=\"(.*?)\"/', $page, $app);
        return $app[1];
    }


    static function start()
    {
        $base = new macradar_ru();

        for ($x = 1; $x <= 16; $x++) {
            $base->getPageMacradar($x);
        }
    }
}
