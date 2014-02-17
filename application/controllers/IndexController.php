<?php

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        
        $this->view->headScript()->appendFile('js/vendor/jquery-1.8.3.min.js');
        $this->view->headScript()->appendFile('js/vendor/modernizr-2.6.2.min.js');
        $this->view->headScript()->appendFile('js/vendor/jquery.iframe-transport.js');
        $this->view->headScript()->appendFile('js/vendor/jquery.ui.widget.js');
        $this->view->headScript()->appendFile('js/vendor/jquery.fileupload.js');
        $this->view->headScript()->appendFile('js/plugins.js');
        $this->view->headScript()->appendFile('js/main.js');

        $this->view->headLink()->appendStylesheet('css/normalize.css');
        $this->view->headLink()->appendStylesheet('css/h5bp.css');
        $this->view->headLink()->appendStylesheet('css/main.css');
        $this->view->headLink()->appendStylesheet('css/sizes.css');
    }

    public function indexAction()
    {
    }
}