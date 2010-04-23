<?php

// set up autoload so that jobs know where to find classes

// only define __autoload if it has not already been defined (phing may define 
// it in its build script
if (! function_exists('__autoload'))
{
    // by using PEAR-type naming conventions, autoload will always know where to 
    // find class definitions
    function __autoload($class)
    {
        require(str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php');
    }
}

define('CRON_ROOT', dirname(__FILE__));
define('APP_ROOT', dirname(CRON_ROOT));

// convoluted way to find the job root
$callstack = debug_backtrace();
$cwd = explode(DIRECTORY_SEPARATOR, dirname($callstack[0]['file']));
$webroot = explode(DIRECTORY_SEPARATOR, CRON_ROOT);
$diff = array_diff($cwd, $webroot);

define('JOB_ROOT', CRON_ROOT 
        . DIRECTORY_SEPARATOR . current($diff) 
        . DIRECTORY_SEPARATOR . next($diff));

set_include_path('.'
    . PATH_SEPARATOR . JOB_ROOT
    . PATH_SEPARATOR . JOB_ROOT . "/tests"
    . PATH_SEPARATOR . APP_ROOT . '/application/lib'
    . PATH_SEPARATOR . get_include_path()
    . PATH_SEPARATOR . APP_ROOT . '/thirdpartylibs'
);

// be careful if modifying the following line.
// phing replaces it when deploying this project.
$env = 'development';

// merge the global config and this job's config and save it in registry
$params = array('allowModifications' => true);
$config = new Zend_Config_Ini(CRON_ROOT . '/config.ini', $env, $params);
$jobConfig = new Zend_Config_Ini(JOB_ROOT . '/config.ini', $env);
$config->merge($jobConfig);
Zend_Registry::set('config', $config);

// set up logger and save it in registry
$logger = $logger = &Log::singleton('console');
$debug = (bool) $config->debug->enabled;
$loglevel = ($debug) ? PEAR_LOG_DEBUG : PEAR_LOG_INFO;
$logger->setMask(Log::MAX($loglevel));
Zend_Registry::set('logger', $logger);
