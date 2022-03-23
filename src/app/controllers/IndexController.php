<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    public function indexAction()
    {

        if (!$this->session->get("user")) {
            header("location:/auth");
        }
        print_r($this->cookies->get("remember-me"));
        // return '<h1>Hello World!</h1>';
    }
}
