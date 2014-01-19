<?php

class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $assetsDomain = Zend_Registry::get('config')->assetsDomain;

        $this->view->headScript()->appendFile($assetsDomain . '/js/vendor/jquery-1.8.3.min.js');
        $this->view->headScript()->appendFile($assetsDomain . '/js/vendor/modernizr-2.6.2.min.js');
        $this->view->headScript()->appendFile($assetsDomain . '/js/vendor/jquery.iframe-transport.js');
        $this->view->headScript()->appendFile($assetsDomain . '/js/vendor/jquery.ui.widget.js');
        $this->view->headScript()->appendFile($assetsDomain . '/js/vendor/jquery.fileupload.js');
        $this->view->headScript()->appendFile($assetsDomain . '/js/plugins.js');
        $this->view->headScript()->appendFile($assetsDomain . '/js/main.js');

        if (APPLICATION_ENV != 'development') {
            $this->view->headScript()->appendFile($assetsDomain . '/js/track.js');
        }

        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/normalize.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/h5bp.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/main.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/sizes.css');
    }

    public function indexAction()
    {
    }
}