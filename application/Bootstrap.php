<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('config', $config);

        return $config;
    }
    
    public function _initViewVars()
    {
        $this->bootstrap('layout');
        $this->bootstrap('view');
        $view = $this->getResource('layout')->getView();
        $config = Zend_Registry::get('config');
        $view->assetsDomain = '//' . $config->assetsSubdomain . '.' . $config->domainName;
        $config->assetsDomain = $view->assetsDomain;
    }

    public function _initTimezone()
    {
        date_default_timezone_set(Zend_Registry::get('config')->timezone);
    }

    public function _initTranslate()
    {
        $locale = new Zend_Locale();

        $translate = new Zend_Translate(
            array(
                'adapter' => 'tmx',
                'content' => APPLICATION_PATH . '/configs/lang.xml',
                'locale'  => $locale->getLanguage()
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
        $dbUrl = "mongodb://$dbConf[user]:$dbConf[password]@$dbConf[host]:$dbConf[port]/$dbConf[database]";
        Shanty_Mongo::addMaster(new Shanty_Mongo_Connection($dbUrl));
    }
}
