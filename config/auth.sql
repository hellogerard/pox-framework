CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `email` varchar(100) NOT NULL,
  `salt` char(8) default NULL,
  `password` varchar(50) default NULL,
  `created_dt_tm` datetime NOT NULL,
  `last_updated_dt_tm` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sessions` (
  `user_id` int(11) unsigned NOT NULL,
  `session_token` varchar(45) NOT NULL,
  `last_updated_dt_tm` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`),
  KEY `session_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
