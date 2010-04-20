CREATE DATABASE blogs;
USE blogs;

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL auto_increment,
  `post_id` varchar(100) collate latin1_general_ci NOT NULL default '',
  `name` varchar(100) collate latin1_general_ci NOT NULL default '',
  `comment` varchar(100) collate latin1_general_ci NOT NULL default '',
  `create_dt_tm` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`comment_id`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci; 

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL auto_increment,
  `title` varchar(100) collate latin1_general_ci NOT NULL default '',
  `body` varchar(100) collate latin1_general_ci NOT NULL default '',
  `create_dt_tm` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`post_id`)
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci; 

INSERT INTO `posts` VALUES (NULL, 'My First Post', 'This is my first post.', NOW());

