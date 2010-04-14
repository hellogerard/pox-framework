<?php


require('CronParser.php');
require('JobHelper.php');


class Slave
{
    const DEFAULT_RUNAS = 'root';
    const DEFAULT_ENABLED = true;
    const DEFAULT_KEEPFOR = 10; // days
    const RUN_INTERVAL = 60; // seconds

    private $_logfile;
    private $_config;
    private $_env;
    private $_windows;


    public function __construct()
    {
        // set up log file
        if (! file_exists("logs"))
        {
            mkdir("logs");
        }

        $now = date('YmdH', $_SERVER['REQUEST_TIME']);
        $this->_logfile = "logs/$now.log";

        // get environment
        $this->_env = Zend_Registry::get('config')->getSectionName();

        // read config.ini
        $this->_config = parse_ini_file("config.ini");

        // determine platform
        $this->_windows = false;
        if (strncasecmp(PHP_OS, "Win", 3) == 0)
        {
            $this->_windows = true;
        }
    }


    private function _maillog()
    {
        $recipients  = (string) $this->_config['recipients'];

        if (! empty($recipients))
        {
            $to = $recipients;
            $subject = "ZIPSCENE: " . basename(getcwd()) . "/{$this->_logfile}";
            $message = file_get_contents($this->_logfile);
            $headers = "From: cron <nobody@zipscene.com>";
            mail($to, $subject, $message, $headers, "-fnobody@zipscene.com");
        }
    }


    private function _getConfig($key, $default)
    {
        return (isset($this->_config[$key])) ? $this->_config[$key] : $default;
    }


    public function work($job)
    {
        $logfile = $this->_logfile;
        $now = date('M d H:i:s', $_SERVER['REQUEST_TIME']);
        $thishost = ($this->_windows) ? $_SERVER['COMPUTERNAME'] : trim(`hostname`);

        // get config parameters
        $runas = (string) $this->_getConfig('runas', self::DEFAULT_RUNAS);
        $runonhost = (string) $this->_getConfig('runonhost', $thishost);
        $enabled = (bool) $this->_getConfig('enabled', self::DEFAULT_ENABLED);
        $keep = (int) $this->_getConfig('keeplogsfor', self::DEFAULT_KEEPFOR);

        // check schedule
        $schedule = (string) $this->_config['schedule'];

        if (empty($schedule))
        {
            system("echo \"$now  Configuration file requires a schedule.\" >> $logfile");
            return;
        }

        // check for lock file
        $tmp = (function_exists("sys_get_temp_dir")) ? sys_get_temp_dir() : "/tmp";
        $lockfile = "$tmp/{$this->_env}-$job.lck";

        if (file_exists($lockfile))
        {
            system("echo \"$now  Lock file found in $lockfile. Skipping.\" >> $logfile");
            return;
        }

        $cp = new CronParser($schedule);

        // if 1) job is enabled
        //    2) and, we are on specified host
        //    3) and, job is scheduled to run
        // then, start execution
        if ($enabled && strcasecmp($runonhost, $thishost) == 0 &&
                $cp->hasRunInTheLast(self::RUN_INTERVAL))
        {
            // create lock file
            touch($lockfile);

            // start execution
            $cmd = (file_exists("$job.php")) ? "php $job.php" : "$job.sh";

            if (! $this->_windows)
            {
                $useSudo = "sudo -u $runas";
            }

            exec("$useSudo $cmd 1>> $logfile 2>&1", $dummy, $retval);

            // remove lock file
            unlink($lockfile);

            // mail log file if error
            if ($retval !== 0)
            {
                $this->_maillog();
            }
        }

        // clean up any old logs
        exec("find logs -mtime +$keep | xargs rm -f");
    }
}

$slave = new Slave();
$slave->work($argv[1]);
