<?php

require('Smarty/libs/Smarty.class.php');

/**
 * Smarty wrapper class, simply enables syntax like:
 *
 * $smarty->title = "My Title";
 *
 * instead of:
 *
 * $smarty->assign('title', "My Title");
 */

class SmartyView extends Smarty
{
    public function __construct()
    {
        parent::__construct();

        // setup paths
        $this->template_dir = array(APP_ROOT . "/application/web/views");
        $this->compile_id = $_SERVER['HTTP_HOST'];
        $this->compile_dir = APP_ROOT . '/artifacts/templates';
        $this->cache_dir = APP_ROOT . '/artifacts/templates_cache';
        $this->use_sub_dirs = true;

        // setup caching
        $config = Zend_Registry::get('config');
        $caching = $config->application->caching->enabled;
        //$this->caching = $caching;
        $this->cache_lifetime = $config->application->caching->ttl;
        // if not caching, always re-compile templates
        $this->force_compile = ! $caching;

        if ($_GET['smarty'] == 1)
        {
            $this->debugging = true;
        }
    }

    public function __set($var, $value)
    {
        $this->assign($var, $value);
    }

    public function __get($var)
    {
        return $this->get_template_vars($var);
    }

    public function __unset($var)
    {
        $this->clear_assign($var);
    }

    public function __isset($var)
    {
        return ($this->get_template_vars($var)) ? true : false;
    }

    public function display($resource_name, $cache_id = null, $compile_id = null)
    {
        // we may have some headers to send back after Smarty has started
        // printing contents. so save the output, and send after all processing.

        ob_start();

        header('Content-Type: text/html; charset=utf-8');

        parent::display($resource_name, $cache_id, $compile_id);

        ob_end_flush();
    }
}

