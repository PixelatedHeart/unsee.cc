<?php

class ViewController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headScript()->appendFile('/static/js/view.js');

        if (APPLICATION_ENV != 'development') {
            $this->view->headScript()->appendFile('/static/js/track.js');
        }

        $this->view->headLink()->appendStylesheet('/static/css/normalize.css');
        $this->view->headLink()->appendStylesheet('/static/css/h5bp.css');
        $this->view->headLink()->appendStylesheet('/static/css/view.css');
        $this->view->headLink()->appendStylesheet('/static/css/main.css');
        $this->view->headLink()->appendStylesheet('/static/css/subpage.css');
    }

    public function indexAction()
    {
        $params = $this->getAllParams();
    }
}