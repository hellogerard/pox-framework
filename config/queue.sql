CREATE TABLE IF NOT EXISTS `queue` (
  `job_id` int(11) unsigned NOT NULL auto_increment,
  `job` varchar(100) NOT NULL,
  `args` varchar(100) default NULL,
  `created_dt_tm` datetime NOT NULL,
  PRIMARY KEY  (`job_id`),
  KEY `job` (`job`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
