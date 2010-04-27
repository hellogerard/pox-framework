<?php

/**
 * This is the parent controller class of all controllers. It sets up the view 
 * system (implemented by Smarty), and provides a catch-all method that returns 
 * a 404 Not Found. This catch-all method can be overridden in the child 
 * controller.
 */

abstract class Controller
{
    public $view;
    public $env;
    public $module;
    public $domain;

    protected $_logger;
    protected $_factory;

    public function __construct($module)
    {
        // convenience variables
        $this->_logger = Zend_Registry::get('logger');
        $this->_factory = Zend_Registry::get('factory');

        $this->view = new SmartyView();

        // set the environment variable
        $this->env = Zend_Registry::get('config')->getSectionName();
        $this->view->env = $this->env;

        // set the module (the tab) we're on
        $this->module = $module;
        $this->view->module = $this->module;

        // set the base domain
        // e.g. returns "example.com" from "www.example.com"
        preg_match('/[^\.]+\.[^\.]+$/', $_SERVER['HTTP_HOST'], $matches);
        $this->domain = $matches[0];
        $this->view->domain = $this->domain;

        // set any confirmation message
        $this->view->flash = $this->flash();
    }

    /**
     * This method is called before the action. For example, use this function
     * to check for authorization.
     */

    public function preDispatch()
    {
    }

    /**
     * This method is called after the action. For example, use this function
     * for analytics code.
     */

    public function postDispatch()
    {
    }

    /**
     * This method will store a confirmation message for the subsequent request,
     * and then clear the message after it is used.
     */

    public function flash($message = null)
    {
        if (! empty($message))
        {
            $_SESSION['flash'] = $message;
        }
        else
        {
            $message = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }

        return $message;
    }

    /**
     * This method is called if a non-existent method (i.e. action) is called on
     * this object.
     */

    public function __call($action, $args)
    {
        Router::notFound();
    }
}

