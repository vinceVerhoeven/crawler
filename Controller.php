<?php

/**
 * Created by PhpStorm.
 * User: vince
 * Date: 8/14/2016
 * Time: 8:41 PM
 */

include 'CurlHandler.php';

class Controller
{

    public function indexAction($url)
    {
        $curl = new CurlHandler();
        $curl->curlRequest($url);
        $curl->debug();
    }

}

$view = new Controller();
$view->indexAction('http://localhost/testwebsite/blog.html');







