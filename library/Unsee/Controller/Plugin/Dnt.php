<?php

/**
 * Controller plugin to optionally include analytics tracking depending on wether DNT header is set
 * @see http://en.wikipedia.org/wiki/Do_Not_Track
 */
class Unsee_Controller_Plugin_Dnt extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(\Zend_Controller_Request_Abstract $request)
    {
        if (APPLICATION_ENV != 'development' && !$request->getHeader('DNT')) {
            $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $view->headScript()->appendFile('js/track.js');
        }
    }
}
