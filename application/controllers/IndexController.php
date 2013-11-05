<?php

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->headScript()->appendFile('/static/js/vendor/jquery-1.8.3.min.js');
        $this->view->headScript()->appendFile('/static/js/vendor/modernizr-2.6.2.min.js');
        $this->view->headScript()->appendFile('/static/js/vendor/jquery.iframe-transport.js');
        $this->view->headScript()->appendFile('/static/js/vendor/jquery.ui.widget.js');
        $this->view->headScript()->appendFile('/static/js/vendor/jquery.fileupload.js');
        $this->view->headScript()->appendFile('/static/js/plugins.js');
        $this->view->headScript()->appendFile('/static/js/main.js');

        if (APPLICATION_ENV != 'development') {
            $this->view->headScript()->appendFile('/static/js/track.js');
        }

        $this->view->headLink()->appendStylesheet('/static/css/normalize.css');
        $this->view->headLink()->appendStylesheet('/static/css/h5bp.css');
        $this->view->headLink()->appendStylesheet('/static/css/main.css');
        $this->view->headLink()->appendStylesheet('/static/css/sizes.css');
    }

    public function indexAction()
    {
    }
}