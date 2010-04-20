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

        $caching = Zend_Registry::get('config')->application->caching->enabled;
        // if not caching, always re-compile templates
        $this->force_compile = ! $caching;
        $this->cache_lifetime = 600; // 10 mins.
        //$this->caching = $caching;

        // subdomain determines if we are on 'm', 'touch', etc.
        $segments = explode('.', $_SERVER['HTTP_HOST']);
        $mobileType = strtolower($segments[0]);

        // get the venue name from the path
        $segments = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $venue = $segments[1];

        if (   $mobileType != 'm'
            && $mobileType != 'touch'
            && $mobileType != 'gomobile'
            && $mobileType != 'mobilize')
        {
            Router::notFound();
        }

        // URL determines compile_dir, template_dir
        $this->compile_id = "{$_SERVER['HTTP_HOST']}-$venue";
        $this->compile_dir = APP_ROOT . '/artifacts/templates';
        $this->cache_dir = APP_ROOT . '/artifacts/templates_cache';
        $this->use_sub_dirs = true;

        $this->template_dir = array();
        $this->template_dir[] = APP_ROOT . "/application/web/$venue/$mobileType";
        $this->template_dir[] = APP_ROOT . "/application/web/_common/$mobileType";

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

