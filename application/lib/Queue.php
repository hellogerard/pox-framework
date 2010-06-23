<?php


class Queue
{
    private static function _db()
    {
        return Zend_Registry::get('db');
    }

    public static function put($job, $args = null)
    {
        $sql =  "insert into queue (job_id, job, args, created_dt_tm)
                    values (null, ?, ?, now())";

        self::_db()->query($sql, array($job, $args));
    }

    public static function consume($job)
    {
        $sql =  "select job_id, args from queue where job = ?
                    order by job_id asc limit 1";

        $row = self::_db()->getRow($sql, array($job));

        $job = null;
        if (! empty($row))
        {
            $sql =  "delete from queue where job_id = ?";

            self::_db()->query($sql, array($row['job_id']));

            $job = true;
            if (! empty($row['args']))
            {
                $job = $row['args'];
            }
        }

        return $job;
    }
}

