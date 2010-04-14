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
    protected $_logger;
    protected $_factory;

    public function __construct($module)
    {
        // convenience variables
        $this->_logger = Zend_Registry::get('logger');
        $this->_factory = Zend_Registry::get('factory');
        $this->view = new SmartyView();
    }

    /**
     * This method is called if a non-existent method (i.e. action) is called on 
     * this object.
     */

    public function __call($action, $args)
    {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

