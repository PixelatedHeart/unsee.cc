<?php

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
$envFile = APPLICATION_PATH . '/configs/env.php';
file_exists($envFile) && include $envFile;

if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', getenv('APPLICATION_ENV') ? : 'production');
}

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path(),
        )
    )
);

/** Zend_Application */
require_once 'Zend/Application.php';

require_once 'Zend/Loader/Autoloader/Resource.php';
$resourceLoader = new Zend_Loader_Autoloader_Resource(array('basePath' => APPLICATION_PATH, 'namespace' => ''));
// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap()->run();
