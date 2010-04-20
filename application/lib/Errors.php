<?php

/**
 * This exception is thrown whenever a PHP error is triggered.
 */

class PHPException extends Exception
{
    // take info from the error to populate the exception
    public function __construct($type, $message, $file, $line)
    {
        $this->file = $file;
        $this->line = $line;

        parent::__construct($message, $type);
    }
}

/**
 * This class handles in general capture any PHP errors and any PEAR errors and 
 * creates exceptions out of them. Then it throws these exceptions.  In this 
 * way, there are no PHP errors in the system - everything is an exception. 
 * Developers are free to catch the error/exception at any level, but the 
 * exception MUST be caught.  Any uncaught exceptions will result in a very ugly 
 * screen in production, or result in a Fatal Error in non-production.
 */

class Errors
{
    public function __construct()
    {
        // if developing, show PHP errors in browser and not in log file.
        // otherwise, log them to file, and do not show in browser.
        $mode = Zend_Registry::get('config')->getSectionName();
        $production = ($mode == 'production') ? true : false;

        ini_set('display_errors', ! $production);
        ini_set('log_errors', $production);

        // override the PHP error handler with this class' php() function,
        // using the system error reporting level
        set_error_handler(array($this, 'php'), error_reporting());

        // override the PEAR error handler with this class' pear() function
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'pear'));

        // so now an exception will be created for any problem in the system.
        // developers are forced to handle exceptions. if they do not,
        // there will be an uncaught exeception Fatal Error.
        // in production, let's have a global function to catch any uncaught 
        // exceptions.
        if ($production)
        {
            // call this class' uncaught() function if an exception rises all 
            // the way to the top of the call stack
            set_exception_handler(array($this, 'uncaught'));
        }
    }

    /**
     * This function is called if a PHP error is triggered. It converts a PHP 
     * error into a PHPException, defined above.
     */

    public function php($type, $message, $file, $line)
    {
        // if the "@" operator is used, error_reporting() == 0
        if (error_reporting())
        {
            throw new PHPException($type, $message, $file, $line);
        }
    }

    /**
     * This function is called if a PEAR error is triggered. It converts a PEAR 
     * error into a PEAR exception (defined in PEAR).
     */

    public function pear($error)
    {
        throw new PEAR_Exception($error->getMessage(), $error->getCode());
    }

    /**
     * This function is called if an exception is thrown and rises all the way 
     * to the top of the call stack without being caught.
     */

    public function uncaught($exception)
    {
        // get a string version of this exception,
        // prepending the date/time of this exception
        ob_start();
        var_dump($exception);
        $str = date('Y-m-d H:i:s') . ' ' . ob_get_contents();
        ob_end_clean();

        // the real base64_encode() encoding map
        $orig = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

        // we use a random encoding map, so people cannot decode easily
        $rand = "MNEsSyG8K+Io3=qzTjHvpFgwm9AerDk46xfYQiV7/R1cXnhuLCblW2O05PtJZUdaB";

        // encode it with the random encoding map
        $str = strtr(base64_encode($str), $orig, $rand);

        // make pretty for web page
        $str = wordwrap($str, 75, "<br/>", true);

        echo "<h4>You've encountered a fatal error. Please copy and paste the 
            following <br/>\n";
        echo "block of text into an email and send to
            <a href=\"mailto:bugs@zipscene.com\">bugs@zipscene.com</a>.</h4>\n";
        
        echo "<pre>$str</pre>";
        exit;
    }
}

