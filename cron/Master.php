<?php

//
// Be sure that the crontab schedule corresponds to the Slave::RUN_INTERVAL
// constant.
//
// */2 * * * * cd /path/to/this/file; php Master.php 1>> cron.out 2>&1; cd $HOME
//

class Master
{
    public function crackwhip()
    {
        if (! file_exists("jobs"))
        {
            exit;
        }

        $jobs = scandir("jobs");
 
        foreach ($jobs as $job)
        {
            if (strpos($job, ".") !== false)
            {
                continue;
            }

            chdir("jobs/$job");

            // from http://us3.php.net/manual/en/function.exec.php#43834
            if (strncasecmp(PHP_OS, "Win", 3) == 0)
            {
                pclose(popen("start \"blah\" /B \"php.exe\" ../../Slave.php $job", "r"));
            }
            else
            {
                exec("php ../../Slave.php $job 1> /dev/null 2>&1 &");   
            }

            chdir("../..");
        }
    }
}

$master = new Master();
$master->crackwhip();
