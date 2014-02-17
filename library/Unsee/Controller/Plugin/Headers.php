<?php

class Unsee_Controller_Plugin_Headers extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(\Zend_Controller_Request_Abstract $request)
    {
        $locale = new Zend_Locale(Zend_Locale::findLocale());
        $this->getResponse()->setHeader('Content-Language', $locale->getLanguage());
    }
}
