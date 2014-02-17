<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initEnv()
    {
        if (empty($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }

        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = 'cli';
        }
    }

    protected function _initDocType()
    {
        // Strangely it works just like this
        $this->bootstrap('View');

        $doctypeHelper = new Zend_View_Helper_Doctype();
        $doctypeHelper->doctype('XHTML1_STRICT');
    }

    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('config', $config);

        return $config;
    }

    public function _initViewVars()
    {
        $this->bootstrap('layout');
    }

    public function _initFront()
    {
        $this->bootstrap('frontController');
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Unsee_Controller_Plugin_Headers());
        $front->registerPlugin(new Unsee_Controller_Plugin_Dnt());
    }

    public function _initTimezone()
    {
        date_default_timezone_set(Zend_Registry::get('config')->timezone);
    }

    public function _initTranslate()
    {
        $locale = new Zend_Locale(Zend_Locale::findLocale());

        $localeName = $locale->getLanguage();

        if (!in_array($localeName, array('en', 'ru'))) {
            $localeName = 'en';
        }

        $translate = new Zend_Translate(
            array(
                'adapter' => 'tmx',
                'content' => APPLICATION_PATH . '/configs/lang.xml',
                'locale'  => $localeName
            )
        );

        Zend_Registry::set('Zend_Translate', $translate);
    }

    /**
     * @todo Make it lazy
     */
    protected function _initDb()
    {
        $dbConf = Zend_Registry::get('config')->mongo->toArray();
        $dbUrl = "mongodb://$dbConf[host]:$dbConf[port]/$dbConf[database]";
        Shanty_Mongo::addMaster(new Shanty_Mongo_Connection($dbUrl));
    }
}
