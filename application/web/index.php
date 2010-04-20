<?php

// by using PEAR-type naming conventions, autoload will always know where to 
// find class definitions
function __autoload($class)
{
    require(str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php');
}

// deduce the application root from this file's location
define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

// put most likely paths first
set_include_path('.'
    . PATH_SEPARATOR . APP_ROOT . '/application/lib'
    . PATH_SEPARATOR . APP_ROOT . '/application/models'
    . PATH_SEPARATOR . APP_ROOT . '/application/controllers'
    . PATH_SEPARATOR . get_include_path()
    . PATH_SEPARATOR . APP_ROOT . '/thirdpartylibs'
);


// be careful if modifying the following line.
// phing replaces it when deploying this project.
$env = 'development';

// get a new config and save it in registry
$config = new Zend_Config_Ini(APP_ROOT . '/config/config.ini', $env);
Zend_Registry::set('config', $config);

// get a new logger and save it in registry
$logger = new Logger();
Zend_Registry::set('logger', $logger);

// set up error handling
new Errors();

// get a new database and save it in registry
$db = new Database();
Zend_Registry::set('db', $db);

// get a new object factory and save it in registry
$factory = new ObjectFactory();
Zend_Registry::set('factory', $factory);

session_start();

// run the request
Router::route($_SERVER['REQUEST_URI']);

session_write_close();
