<?php

/**
 * This class extends the PEAR logger and has the following capabilities:
 * - log to a default or specified file
 * - log to a popup window
 * - turn DEBUG logging level on/off
 * - profile to a popup window
 * - turn on xdebug execution tracing
 *
 * any combination of above can be enabled using GET parameters.
 */

class Logger extends Log_composite
{
    private $_loglevel;
    private $_lastTime;

    public function __construct()
    {
        // construct a composite logger, from which any number of loggers can be 
        // attached
        parent::__construct('composite');

        // if debug on, log DEBUG messages, else use INFO as default
        $debug = (bool) Zend_Registry::get('config')->application->debug->enabled;
        $loglevel = ($debug || $_GET['debug']) ? PEAR_LOG_DEBUG : PEAR_LOG_INFO;
        $this->_loglevel = Log::MAX($loglevel);

        $logfile = APP_ROOT . "/artifacts/logs/" . basename(APP_ROOT) . "_out.log";
        ini_set('error_log', $logfile);

        // by default, log messages will always be logged to a default log file
        $logger = new Log_file($logfile);
        $logger->setMask($this->_loglevel);
        $this->addChild($logger);

        // now, check if user wants logging via GET params
        $this->attachPopup();
        $this->attachLogFile();
        $this->enableXdebug();

        // define a new log level for profiling
        define('PEAR_LOG_PROFILE', PEAR_LOG_DEBUG + 1);

        // now, check if user wants profiling
        $this->attachProfiler();
    }

    /**
     * This function is a profiling function and returns the elapsed time 
     * between the current invocation and the previous invocation.
     */

    private function _chronometer()
    {
        $now = explode(" ", microtime());
        $now = (double)($now[1]) + (double)($now[0]);

        if ($this->_lastTime > 0) 
        {
            $elapsed = round($now - $this->_lastTime, 6);
            $this->_lastTime = $now;
            return $elapsed;
        } 
        else 
        {
            // Start the chronometer : save the starting time
            $this->_lastTime = $now;
            return 0;
        }
    }

    /**
     * when this line is called:
     *
     *    $logger->profile(__METHOD__, __FILE__, __LINE__);
     *
     * the execution profile is output to a popup window
     */

    public function profile($method, $file, $line)
    {
        $file = basename($file);
        $this->log($this->_chronometer() . "s [$file:$line] $method()", PEAR_LOG_PROFILE);
    }

    /**
     * the popup profiling window will be enabled if the user passes a 
     * "profile=1" on the query string.
     */

    public function attachProfiler()
    {
        if ($_GET['profile'])
        {
            $logger = new Log_win('profile_window');
            $logger->setMask(Log::MASK(PEAR_LOG_PROFILE));
            $this->addChild($logger);

            // start the timer
            $this->_chronometer();
        }
    }

    /**
     * the popup logging window will be enabled if the user passes a 
     * "log_window=1" on the query string.
     */

    public function attachPopup()
    {
        if ($_GET['log_window'])
        {
            $logger = new Log_win('log_window');
            $logger->setMask($this->_loglevel);
            $this->addChild($logger);
        }
    }

    /**
     * logging will be sent to the specified log file in the logs directory.
     * for example, appending "log_file=my_log.log" on the query string
     * will create a log file in the logs directory for just this request.
     */

    public function attachLogFile()
    {
        if ($_GET['log_file'])
        {
            $logfile = APP_ROOT . "/artifacts/logs/" . $_GET['log_file'];
            $logger = new Log_file($logfile);
            $logger->setMask($this->_loglevel);
            $this->addChild($logger);
        }
    }

    /**
     * an xdebug execution trace file will be created in the logs directory.
     * for example, appending "xdebug_trace=my_trace.xt" on the query string
     * will create a trace file in the logs directory for just this request.
     *
     * see: http://www.xdebug.org/docs/execution_trace
     */

    public function enableXdebug()
    {
        if ($_GET['xdebug_trace'])
        {
            $logfile = APP_ROOT . "/artifacts/logs/" . $_GET['xdebug_trace'];
            xdebug_start_trace($logfile);
        }
    }
}

