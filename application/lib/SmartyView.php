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

        $this->compile_id = $_SERVER['HTTP_HOST'];
        $this->compile_dir = APP_ROOT . '/artifacts/templates';
        $this->use_sub_dirs = true;

        // if developing, always re-compile templates
        $mode = Zend_Registry::get('config')->getSectionName();
        $this->force_compile = ($mode != 'production') ? true : false;
        $this->template_dir = array(APP_ROOT . "/application/web/views");

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
}

