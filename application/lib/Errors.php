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
        // override the PHP error handler with php() function, using the system
        // error reporting level. this means php.ini settings 'display_errors'
        // and 'log_errors' are ignored.
        set_error_handler(array($this, 'php'), error_reporting());

        // same with the PEAR error handler - override with pear() function
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'pear'));

        // so now an exception will be created for any problem in the system.
        // developers are forced to handle exceptions. if they do not,
        // there will be an uncaught exeception Fatal Error.

        // if developing, show exceptions in browser and not in log file.
        // in production, log them to file, and do not show in browser.

        $mode = Zend_Registry::get('config')->getSectionName();
        if ($mode == 'production')
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
        $message = $exception->getMessage() . "\n" . print_r($exception->getTrace(), true);
        error_log($message);

        // get a string version of this exception,
        // prepending the date/time of this exception
        ini_set('xdebug.var_display_max_children', 256);
        ini_set('xdebug.var_display_max_data', 1024);
        ini_set('xdebug.var_display_max_depth', 6);
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

        echo "<h4>You've discovered a fatal error! Please copy and paste the 
            following <br/>\n";
        echo "block of text into an email and send to
            <a href=\"mailto:bugs@example.com\">bugs@example.com</a>. After that,<br/>\n";
        echo "you can always <a href=\"javascript:location.reload();\">try again</a>.</h4>\n";

        echo "<pre>$str</pre>";
        exit;
    }
}

