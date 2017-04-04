<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* Class that holds the SQL create statments for the Rogo database.
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
class databaseTables {

  private $tableList = array();

  function __construct($charset) {
    $this->tableList['access_log'] = <<<QUERY
      CREATE TABLE `access_log` (
        `id` int(11) unsigned NOT NULL auto_increment,
        `userID` int(11) unsigned default NULL,
        `type` varchar(255) default NULL,
        `accessed` datetime default NULL,
        `ipaddress` char(60) default NULL,
        `page` varchar(255) default NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['admin_access'] = <<<QUERY
      CREATE TABLE `admin_access` (
        `adminID` int(11) NOT NULL auto_increment,
        `userID` int(10) unsigned default NULL,
        `schools_id` int(11) default NULL,
        PRIMARY KEY (`adminID`),
        KEY idx_schoolsid_userid (schools_id, userID )
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['announcements'] = <<<QUERY
      CREATE TABLE `announcements` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) DEFAULT NULL,
        `staff_msg` text,
        `student_msg` text,
        `icon` varchar(255) DEFAULT NULL,
        `startdate` datetime DEFAULT NULL,
        `enddate` datetime DEFAULT NULL,
        `deleted` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['cache_median_question_marks'] = <<<QUERY
      CREATE TABLE `cache_median_question_marks` (
        `paperID` mediumint(8) unsigned NOT NULL,
        `questionID` int(10) unsigned NOT NULL DEFAULT '0',
        `median` decimal(10,5) DEFAULT NULL,
        `mean` decimal(10,5) DEFAULT NULL,
        PRIMARY KEY (`paperID`,`questionID`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['cache_paper_stats'] = <<<QUERY
      CREATE TABLE `cache_paper_stats` (
        `paperID` mediumint(8) unsigned NOT NULL,
        `cached` int(10) unsigned DEFAULT NULL,
        `max_mark` decimal(10,5) DEFAULT NULL,
        `max_percent` decimal(10,5) DEFAULT NULL,
        `min_mark` decimal(10,5) DEFAULT NULL,
        `min_percent` decimal(10,5) DEFAULT NULL,
        `q1` decimal(10,5) DEFAULT NULL,
        `q2` decimal(10,5) DEFAULT NULL,
        `q3` decimal(10,5) DEFAULT NULL,
        `mean_mark` decimal(10,5) DEFAULT NULL,
        `mean_percent` decimal(10,5) DEFAULT NULL,
        `stdev_mark` decimal(10,5) DEFAULT NULL,
        `stdev_percent` decimal(10,5) DEFAULT NULL,
        PRIMARY KEY (`paperID`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['cache_student_paper_marks'] = <<<QUERY
      CREATE TABLE `cache_student_paper_marks` (
        `paperID` mediumint(8) unsigned NOT NULL,
        `userID` int(10) unsigned NOT NULL DEFAULT '0',
        `mark` decimal(10,5) DEFAULT NULL,
        `percent` decimal(10,5) DEFAULT NULL,
        PRIMARY KEY (`paperID`,`userID`),
        KEY `idx_userID` (`userID`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;


    $this->tableList['class_totals_test_local'] = <<<QUERY
        CREATE TABLE `class_totals_test_local` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(10) unsigned DEFAULT NULL,
          `paper_id` mediumint(8) unsigned DEFAULT NULL,
          `status` enum('in_progress','success','failure') DEFAULT NULL,
          `errors` text,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;
        
    $this->tableList['config'] = <<<QUERY
        CREATE TABLE `config` (
          `component` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'core',
          `setting` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
          `value` text COLLATE utf8_unicode_ci,
          `type` VARCHAR(10) NULL,
          PRIMARY KEY (`component`,`setting`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['courses'] = <<<QUERY
        CREATE TABLE `courses` (
          `id` int(11) NOT NULL auto_increment,
          `name` varchar(255) default NULL,
          `description` varchar(255) default NULL,
          `deleted` datetime default NULL,
          `schoolid` int(11) default NULL,
          `externalid` varchar(255) default NULL,
          `externalsys` varchar(255) default NULL,
          PRIMARY KEY (`id`),
          UNIQUE INDEX `externalid` (`externalid`),
          KEY `degree` (`name`),
          KEY `idx_courses_name` (`name`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['denied_log'] = <<<QUERY
      CREATE TABLE `denied_log` (
        `id` int(11) unsigned NOT NULL auto_increment,
        `userID` int(11) unsigned default NULL,
        `tried` datetime default NULL,
        `ipaddress` char(60) default NULL,
        `page` varchar(255) default NULL,
        `title` varchar(255) default NULL,
        `msg` text default NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['ebel'] = <<<QUERY
          CREATE TABLE `ebel` (
            `std_setID` int(10) unsigned NOT NULL,
            `category` char(3) default NULL,
            `percentage` float default NULL,
            PRIMARY KEY (`std_setID`,`category`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['ebel_grid_templates'] = <<<QUERY
          CREATE TABLE `ebel_grid_templates` (
            `id` int(11) NOT NULL auto_increment,
            `EE` tinyint(4) default NULL,
            `EI` tinyint(4) default NULL,
            `EN` tinyint(4) default NULL,
            `ME` tinyint(4) default NULL,
            `MI` tinyint(4) default NULL,
            `MN` tinyint(4) default NULL,
            `HE` tinyint(4) default NULL,
            `HI` tinyint(4) default NULL,
            `HN` tinyint(4) default NULL,
            `EE2` tinyint(4) default NULL,
            `EI2` tinyint(4) default NULL,
            `EN2` tinyint(4) default NULL,
            `ME2` tinyint(4) default NULL,
            `MI2` tinyint(4) default NULL,
            `MN2` tinyint(4) default NULL,
            `HE2` tinyint(4) default NULL,
            `HI2` tinyint(4) default NULL,
            `HN2` tinyint(4) default NULL,
            `name` varchar(255) default NULL,
            PRIMARY KEY  (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['exam_announcements'] = <<<QUERY
          CREATE TABLE `exam_announcements` (
            `paperID` mediumint(8) unsigned NOT NULL,
            `q_id` int(4) unsigned NOT NULL DEFAULT '0',
            `q_number` smallint(5) unsigned NOT NULL DEFAULT '0',
            `screen` tinyint(4) unsigned NOT NULL DEFAULT '0',
            `msg` text,
            `created` datetime,
            UNIQUE INDEX `idx_paperID_q_id` (`paperID`,`q_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['extra_cal_dates'] = <<<QUERY
          CREATE TABLE `extra_cal_dates` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `message` text,
            `thedate` datetime NOT NULL,
            `duration` int(11) NOT NULL,
            `bgcolor` varchar(16) NOT NULL,
            `deleted` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['faculty'] = <<<QUERY
          CREATE TABLE `faculty` (
            `id` int(11) NOT NULL auto_increment,
            `code` varchar(30) default NULL,
            `name` varchar(80) default NULL,
            `deleted` datetime default NULL,
            `externalid` varchar(255) default NULL,
            `externalsys` varchar(255) default NULL,
            PRIMARY KEY  (`id`),
            UNIQUE INDEX `code` (`code`),
            UNIQUE INDEX `externalid` (`externalid`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['feedback_release'] = <<<QUERY
        CREATE TABLE `feedback_release` (
          `idfeedback_release` int(11) NOT NULL auto_increment,
          `paper_id` mediumint(8) unsigned default NULL,
          `date` datetime NOT NULL,
          `type` enum('objectives','questions','cohort_performance','external_examiner') default NULL,
          PRIMARY KEY  (`idfeedback_release`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['folders'] = <<<QUERY
        CREATE TABLE `folders` (
          `id` int(4) NOT NULL auto_increment,
          `ownerID` int(10) unsigned default NULL,
          `name` text,
          `created` datetime default NULL,
          `color` enum('yellow','red','green','blue','grey') default NULL,
          `deleted` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['folders_modules_staff'] = <<<QUERY
        CREATE TABLE `folders_modules_staff` (
          `folders_id` int(10) unsigned NOT NULL DEFAULT '0',
          `idMod` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY  (`folders_id`,`idMod`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['help_log'] = <<<QUERY
        CREATE TABLE `help_log` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` int(10) unsigned default NULL,
          `accessed` datetime default NULL,
          `pageID` int(11) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['help_searches'] = <<<QUERY
        CREATE TABLE `help_searches` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` int(10) unsigned default NULL,
          `searched` datetime default NULL,
          `searchstring` text,
          `hits` int(11) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['help_tutorial_log'] = <<<QUERY
        CREATE TABLE `help_tutorial_log` (
          `id` int(11) NOT NULL auto_increment,
          `type` enum('student','staff') default NULL,
          `userID` int(10) unsigned default NULL,
          `accessed` datetime default NULL,
          `tutorial` varchar(255) default NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['hofstee'] = <<<QUERY
        CREATE TABLE `hofstee` (
          `std_setID` int(10) unsigned NOT NULL,
          `whole_numbers` tinyint(4) DEFAULT NULL,
          `x1_pass` tinyint(4) DEFAULT NULL,
          `x2_pass` tinyint(4) DEFAULT NULL,
          `y1_pass` tinyint(4) DEFAULT NULL,
          `y2_pass` tinyint(4) DEFAULT NULL,
          `x1_distinction` tinyint(4) DEFAULT NULL,
          `x2_distinction` tinyint(4) DEFAULT NULL,
          `y1_distinction` tinyint(4) DEFAULT NULL,
          `y2_distinction` tinyint(4) DEFAULT NULL,
          `marking` tinyint(4) DEFAULT NULL,
           PRIMARY KEY (`std_setID`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['client_identifiers'] = <<<QUERY
        CREATE TABLE `client_identifiers` (
          `id` int(11) NOT NULL auto_increment,
          `lab` smallint(5) unsigned default NULL,
          `address` char(60) default NULL,
          `hostname` char(255) default NULL,
          `low_bandwidth` tinyint(4) default '0',
          PRIMARY KEY (`id`),
          KEY `lab` (`lab`),
          KEY `address_idx` (`address`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['keywords_question'] = <<<QUERY
        CREATE TABLE `keywords_question` (
          `q_id` int(11) default NULL,
          `keywordID` int(11) default NULL,
          PRIMARY KEY (`q_id`, `keywordID`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['keywords_link'] = <<<QUERY
        CREATE TABLE `keywords_link` (
          `q_id` INT(4) NOT NULL,
          `keyword_id` INT(11) NOT NULL,
          PRIMARY KEY (`q_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['keywords_user'] = <<<QUERY
        CREATE TABLE `keywords_user` (
          `id` int(11) NOT NULL auto_increment,
          `userID` int(10) unsigned default NULL,
          `keyword` char(255) default NULL,
          `keyword_type` enum('personal','team') default NULL,
          PRIMARY KEY (`id`),
          KEY `username` (`userID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['killer_questions'] = <<<QUERY
        CREATE TABLE `killer_questions` (
          `id` int(4) unsigned NOT NULL auto_increment,
          `paperID` mediumint(8) unsigned NOT NULL,
          `q_id` int(4) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `idx_paperID` (`paperID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['labs'] = <<<QUERY
        CREATE TABLE `labs` (
          `id` smallint(5) unsigned NOT NULL auto_increment,
          `name` varchar(255) default NULL,
          `campus` int(8) NOT NULL,
          `building` varchar(255) default NULL,
          `room_no` varchar(255) default NULL,
          `timetabling` text,
          `it_support` text,
          `plagarism` text,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log0'] = <<<QUERY
        CREATE TABLE `log0` (
          `id` int(8) NOT NULL auto_increment,
          `q_id` int(4) NOT NULL DEFAULT '0',
          `mark` float DEFAULT NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) DEFAULT NULL,
          `user_answer` text,
          `errorstate` tinyint(3) NOT NULL DEFAULT '0',
          `screen` tinyint(3) unsigned DEFAULT NULL,
          `duration` mediumint(9) DEFAULT NULL,
          `updated` datetime DEFAULT NULL,
          `dismiss` char(20) DEFAULT NULL,
          `option_order` varchar(100) DEFAULT NULL,
          `metadataID` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `idx_metadataID_qid_screen` (`metadataID`,`q_id`,`screen`),
          KEY `q_id` (`q_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log0_deleted'] = <<<QUERY
        CREATE TABLE `log0_deleted` (
          `id` int(8) NOT NULL UNIQUE,
          `q_id` int(4) NOT NULL DEFAULT '0',
          `mark` float DEFAULT NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) DEFAULT NULL,
          `user_answer` text,
          `errorstate` tinyint(3) NOT NULL DEFAULT '0',
          `screen` tinyint(3) unsigned DEFAULT NULL,
          `duration` mediumint(9) DEFAULT NULL,
          `updated` datetime DEFAULT NULL,
          `dismiss` char(20) DEFAULT NULL,
          `option_order` varchar(100) DEFAULT NULL,
          `metadataID` int(11) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log1'] = <<<QUERY
        CREATE TABLE `log1` (
          `id` int(8) NOT NULL auto_increment,
          `q_id` int(4) NOT NULL DEFAULT '0',
          `mark` float DEFAULT NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) DEFAULT NULL,
          `user_answer` text,
          `errorstate` tinyint(3) NOT NULL DEFAULT '0',
          `screen` tinyint(3) unsigned DEFAULT NULL,
          `duration` mediumint(9) DEFAULT NULL,
          `updated` datetime DEFAULT NULL,
          `dismiss` char(20) DEFAULT NULL,
          `option_order` varchar(100) DEFAULT NULL,
          `metadataID` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `idx_metadataID_qid_screen` (`metadataID`,`q_id`,`screen`),
          KEY `q_id` (`q_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log1_deleted'] = <<<QUERY
        CREATE TABLE `log1_deleted` (
          `id` int(8) NOT NULL UNIQUE,
          `q_id` int(4) NOT NULL DEFAULT '0',
          `mark` float DEFAULT NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) DEFAULT NULL,
          `user_answer` text,
          `errorstate` tinyint(3) NOT NULL DEFAULT '0',
          `screen` tinyint(3) unsigned DEFAULT NULL,
          `duration` mediumint(9) DEFAULT NULL,
          `updated` datetime DEFAULT NULL,
          `dismiss` char(20) DEFAULT NULL,
          `option_order` varchar(100) DEFAULT NULL,
          `metadataID` int(11) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log2'] = <<<QUERY
        CREATE TABLE `log2` (
          `id` int(8) NOT NULL auto_increment,
          `q_id` int(4) NOT NULL DEFAULT '0',
          `mark` float DEFAULT NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) DEFAULT NULL,
          `user_answer` text,
          `errorstate` tinyint(3) NOT NULL DEFAULT '0',
          `screen` tinyint(3) unsigned DEFAULT NULL,
          `duration` mediumint(9) DEFAULT NULL,
          `updated` datetime DEFAULT NULL,
          `dismiss` char(20) DEFAULT NULL,
          `option_order` varchar(100) DEFAULT NULL,
          `metadataID` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `idx_metadataID_qid_screen` (`metadataID`,`q_id`,`screen`),
          KEY `q_id` (`q_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log3'] = <<<QUERY
        CREATE TABLE `log3` (
          `id` int(8) NOT NULL auto_increment,
          `q_id` int(4) NOT NULL DEFAULT '0',
          `mark` float DEFAULT NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) DEFAULT NULL,
          `user_answer` text,
          `errorstate` tinyint(3) NOT NULL DEFAULT '0',
          `screen` tinyint(3) unsigned DEFAULT NULL,
          `duration` mediumint(9) DEFAULT NULL,
          `updated` datetime DEFAULT NULL,
          `dismiss` char(20) DEFAULT NULL,
          `option_order` varchar(100) DEFAULT NULL,
          `metadataID` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `idx_metadataID_qid_screen` (`metadataID`,`q_id`,`screen`),
          KEY `q_id` (`q_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset} PACK_KEYS=1
QUERY;

    $this->tableList['log4'] = <<<QUERY
        CREATE TABLE `log4` (
          `id` int NOT NULL auto_increment,
          `q_id` int(11) DEFAULT NULL,
          `rating` text,
          `q_parts` varchar(50) DEFAULT NULL,
          `log4_overallID` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `q_id` (`q_id`),
          INDEX `log4_overallID` (`log4_overallID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log4_overall'] = <<<QUERY
        CREATE TABLE `log4_overall` (
          `id` int(11) NOT NULL auto_increment,
          `userID` int(10) unsigned default NULL,
          `started` datetime default NULL,
          `q_paper` mediumint unsigned DEFAULT NULL,
          `overall_rating` text,
          `numeric_score` int(11) DEFAULT NULL,
          `feedback` text,
          `student_grade` char(25) DEFAULT NULL,
          `examinerID` mediumint(8) unsigned DEFAULT NULL,
          `osce_type` enum('electronic','paper') DEFAULT NULL,
          `year` tinyint(4) DEFAULT NULL,
          PRIMARY KEY  (`id`),
          KEY `q_paper` (`q_paper`),
          KEY `username` (`userID`),
          KEY `started` (`started`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log5'] = <<<QUERY
        CREATE TABLE `log5` (
          `id` int(11) NOT NULL auto_increment,
          `q_id` int(11) DEFAULT NULL,
          `mark` float DEFAULT NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) DEFAULT NULL,
          `metadataID` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `idx_metadataID_qid` (`metadataID`,`q_id`),
          KEY `q_id` (`q_id`)
       ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log6'] = <<<QUERY
        CREATE TABLE `log6` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` mediumint(8) unsigned DEFAULT NULL,
          `reviewerID` int(10) unsigned default NULL,
          `peerID` int(10) unsigned default NULL,
          `started` datetime default NULL,
          `q_id` int(11) default NULL,
          `rating` tinyint(4) default NULL,
          PRIMARY KEY (`id`),
          KEY `started` (`started`),
          KEY `q_id` (`q_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log_extra_time'] = <<<QUERY
        CREATE TABLE `log_extra_time` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `labID` smallint(5) unsigned NOT NULL,
          `paperID` mediumint(8) unsigned NOT NULL,
          `invigilatorID` int(10) unsigned NOT NULL,
          `userID` int(10) unsigned NOT NULL,
          `extra_time` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `key_lab_id_paper_id_user_id` (`labID`,`paperID`,`userID`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log_lab_end_time'] = <<<QUERY
        CREATE TABLE `log_lab_end_time` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `labID` smallint(5) unsigned NOT NULL,
          `paperID` mediumint(8) unsigned NOT NULL,
          `invigilatorID` int(10) unsigned NOT NULL,
          `start_time` int(10) unsigned DEFAULT NULL,
          `end_time` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `key_lab_paper_invig_time` (`labID`,`paperID`,`invigilatorID`,`end_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log_late'] = <<<QUERY
        CREATE TABLE `log_late` (
          `id` int(8) NOT NULL auto_increment,
          `q_id` int(4) NOT NULL default '0',
          `mark` float default NULL,
          `adjmark` float DEFAULT NULL,
          `totalpos` tinyint(4) default NULL,
          `user_answer` text,
          `errorstate` tinyint(3) NOT NULL DEFAULT '0',
          `screen` tinyint(3) unsigned default NULL,
          `duration` mediumint(9) default NULL,
          `updated` datetime default NULL,
          `dismiss` char(20) default NULL,
          `option_order` varchar(100) default NULL,
          `metadataID` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `idx_metadataID_qid_screen` (`metadataID`,`q_id`,`screen`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log_metadata'] = <<<QUERY
        CREATE TABLE `log_metadata` (
          `id` int(11) unsigned NOT NULL auto_increment,
          `userID` int(10) unsigned default NULL,
          `paperID` mediumint(8) unsigned default NULL,
          `started` datetime default NULL,
          `ipaddress` varchar(100) default NULL,
          `student_grade` char(25) default NULL,
          `year` tinyint(4) default NULL,
          `attempt` tinyint(4) default NULL,
          `completed` datetime DEFAULT NULL,
          `lab_name` varchar(255) DEFAULT NULL,
          `highest_screen` tinyint(3) unsigned DEFAULT NULL,
          PRIMARY KEY  (`id`),
          KEY `userID` (`userID`,`paperID`,`started`),
          KEY `idx_log_metadata_student_grade` (`student_grade`),
          KEY `idx_log_metadata_paperID` (`paperID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['log_metadata_deleted'] = <<<QUERY
        CREATE TABLE `log_metadata_deleted` (
          `id` int(11) unsigned NOT NULL UNIQUE,
          `userID` int(10) unsigned DEFAULT NULL,
          `paperID` mediumint(8) unsigned DEFAULT NULL,
          `started` datetime DEFAULT NULL,
          `ipaddress` varchar(100) DEFAULT NULL,
          `student_grade` char(25) DEFAULT NULL,
          `year` tinyint(4) DEFAULT NULL,
          `attempt` tinyint(4) DEFAULT NULL,
          `completed` datetime DEFAULT NULL,
          `lab_name` varchar(255) DEFAULT NULL,
          `highest_screen` tinyint(3) unsigned DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['lti_context'] = <<<QUERY
          CREATE TABLE IF NOT EXISTS `lti_context` (
          `lti_context_key` VARCHAR(255) NOT NULL,
          `c_internal_id` int(11) NOT NULL,
          `updated_on` DATETIME NOT NULL,
          PRIMARY KEY (`lti_context_key`),
          KEY `c_internal_id` (`c_internal_id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['lti_keys'] = <<<QUERY
          CREATE TABLE IF NOT EXISTS `lti_keys` (
          `id` mediumint(9) NOT NULL AUTO_INCREMENT,
          `oauth_consumer_key` char(255) NOT NULL,
          `secret` char(255) DEFAULT NULL,
          `name` char(255) DEFAULT NULL,
          `context_id` char(255) DEFAULT NULL,
          `deleted` datetime,
          `updated_on` datetime,
          PRIMARY KEY (`id`),
          KEY `oauth_consumer_key` (`oauth_consumer_key`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['lti_resource'] = <<<QUERY
        CREATE TABLE IF NOT EXISTS `lti_resource` (
        `lti_resource_key` varchar(255) NOT NULL,
        `internal_id` varchar(255) DEFAULT NULL,
        `internal_type` varchar(255) NOT NULL,
        `updated_on` datetime,
        PRIMARY KEY (`lti_resource_key`),
        KEY `destination2` (`internal_type`),
        KEY `destination` (`internal_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['lti_user'] = <<<QUERY
          CREATE TABLE IF NOT EXISTS `lti_user` (
          `lti_user_key` varchar(255) NOT NULL,
          `lti_user_equ` int(10) unsigned,
          `updated_on` datetime NOT NULL,
          PRIMARY KEY (`lti_user_key`),
          KEY `lti_user_equ` (`lti_user_equ`)
         ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['marking_override'] = <<<QUERY
        CREATE TABLE `marking_override` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `log_id` INT(11) UNSIGNED NOT NULL,
          `log_type` TINYINT(4) UNSIGNED NOT NULL,
          `user_id` INT(10) UNSIGNED NOT NULL,
          `q_id` INT(4) UNSIGNED NOT NULL,
          `paper_id` MEDIUMINT(8) UNSIGNED NOT NULL,
          `marker_id` INT(10) UNSIGNED NOT NULL,
          `date_marked` DATETIME NOT NULL,
          `new_mark_type` ENUM('correct', 'partial', 'incorrect') NOT NULL,
          `reason` VARCHAR(255) NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `log_id` (`log_id`, `log_type`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['modules'] = <<<QUERY
        CREATE TABLE `modules` (
          `id` int(11) NOT NULL auto_increment,
          `moduleid` char(255) default NULL,
          `fullname` text,
          `active` tinyint(4) default NULL,
          `vle_api` varchar(255) default NULL,
          `checklist` varchar(255) default NULL,
          `sms` varchar(255) default NULL,
          `selfenroll` tinyint(4) default NULL,
          `schoolid` int(11) default NULL,
          `neg_marking` tinyint(1) default NULL,
          `ebel_grid_template` int(11) default NULL,
          `mod_deleted` datetime default NULL,
          `timed_exams` tinyint(4) default NULL,
          `exam_q_feedback` tinyint(4) default NULL,
          `add_team_members` tinyint(4) default NULL,
          `map_level` smallint(2) NOT NULL DEFAULT '0',
          `academic_year_start` char(5) NOT NULL,
          `externalid` varchar(255) default NULL,
          PRIMARY KEY (`id`),
          UNIQUE INDEX `externalid` (`externalid`),
          KEY `guideid` (`moduleid`),
          KEY `idx_moduleid_deleted` (`moduleid`,`mod_deleted`),
          KEY `idx_schoolid_deleted` (`schoolid`,`mod_deleted`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['modules_staff'] = <<<QUERY
        CREATE TABLE `modules_staff` (
          `groupID` int(4) NOT NULL auto_increment,
          `idMod` int(11) NOT NULL,
          `memberID` int(10) UNSIGNED NOT NULL,
          `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`groupID`),
          KEY `name` (`idMod`),
          KEY `idx_memberID` (`memberID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['academic_year'] = <<<QUERY
        CREATE TABLE `academic_year` (
          `calendar_year` int(4) NOT NULL,
          `academic_year` varchar(30) NOT NULL,
          `cal_status` tinyint(1) NOT NULL DEFAULT '1',
          `stat_status` tinyint(1) NOT NULL DEFAULT '1',
          `deleted` datetime DEFAULT NULL,
          `deletedby` int(10),
          PRIMARY KEY (`calendar_year`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['modules_student'] = <<<QUERY
        CREATE TABLE `modules_student` (
          `id` int(11) NOT NULL auto_increment,
          `userID` int(10) unsigned DEFAULT NULL,
          `idMod` int(11) unsigned DEFAULT NULL,
          `calendar_year` int(4),
          `attempt` tinyint(4) DEFAULT NULL,
          `auto_update` tinyint(4) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_userID` (`userID`),
          KEY `idx_mod_calyear` (`calendar_year`,`idMod`),
          KEY `idx_mod` (`idMod`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['objectives'] = <<<QUERY
        CREATE TABLE `objectives` (
        `obj_id` int(11) NOT NULL,
        `objective` text NOT NULL,
        `idMod` int(11) unsigned NOT NULL DEFAULT '0',
        `identifier` bigint(20) unsigned NOT NULL,
        `calendar_year` INT(4),
        `sequence` int(11) DEFAULT NULL,
        PRIMARY KEY (`obj_id`,`idMod`,`calendar_year`),
        KEY `idx_identifier_calendar_year_sequence` (`identifier`,`calendar_year`,`sequence`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['options'] = <<<QUERY
        CREATE TABLE `options` (
          `o_id` int(4) NOT NULL default '0',
          `option_text` text,
          `o_media` varchar(255) default NULL,
          `o_media_width` varchar(4) default NULL,
          `o_media_height` varchar(4) default NULL,
          `feedback_right` text,
          `feedback_wrong` text,
          `correct` text,
          `id_num` int(11) NOT NULL auto_increment,
          `marks_correct` float default NULL,
          `marks_incorrect` float default NULL,
          `marks_partial` float default NULL,
          PRIMARY KEY (`id_num`),
          KEY `o_id` (`o_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['paper_feedback'] = <<<QUERY
        CREATE TABLE `paper_feedback` (
          `id` int(11) unsigned NOT NULL auto_increment,
          `paperID` mediumint(8) unsigned NOT NULL,
          `boundary` tinyint(3) unsigned NOT NULL,
          `msg` text,
          PRIMARY KEY (`id`),
          KEY `idx_paperID` (`paperID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['paper_metadata_security'] = <<<QUERY
        CREATE TABLE `paper_metadata_security` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` mediumint(8) unsigned NOT NULL,
          `name` varchar(255) default NULL,
          `value` varchar(255) default NULL,
          PRIMARY KEY (`id`),
          KEY `idx_paperID` (`paperID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['paper_notes'] = <<<QUERY
        CREATE TABLE `paper_notes` (
          `note_id` int(11) NOT NULL auto_increment,
          `note` text,
          `note_date` datetime default NULL,
          `paper_id` mediumint(8) unsigned default NULL,
          `note_authorID` int(10) unsigned default NULL,
          `note_workstation` char(100) default NULL,
          PRIMARY KEY (`note_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['papers'] = <<<QUERY
        CREATE TABLE `papers` (
          `p_id` int(4) NOT NULL auto_increment,
          `paper` mediumint(8) unsigned DEFAULT NULL,
          `question` int(4) unsigned NOT NULL default '0',
          `screen` tinyint(2) unsigned NOT NULL default '0',
          `display_pos` smallint(5) unsigned default NULL,
          PRIMARY KEY (`p_id`),
          KEY `paper` (`paper`),
          KEY `question_idx` (`question`),
          KEY `screen` (`screen`),
          KEY `paper_2` (`paper`,`display_pos`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['password_tokens'] = <<<QUERY
        CREATE TABLE `password_tokens` (
          `id` int(11) NOT NULL auto_increment,
          `user_id` int(11) unsigned DEFAULT NULL,
          `token` char(16) NOT NULL,
          `time` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['performance_details'] = <<<QUERY
          CREATE TABLE `performance_details` (
          `perform_id` int(11) DEFAULT NULL,
          `part_no` tinyint(4) DEFAULT NULL,
          `p` tinyint(4) DEFAULT NULL,
          `d` tinyint(4) DEFAULT NULL,
          KEY `idx_perform_id` (`perform_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['performance_main']  = <<<QUERY
          CREATE TABLE `performance_main` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `q_id` int(10) unsigned DEFAULT NULL,
          `paperID` int(10) unsigned DEFAULT NULL,
          `percentage` tinyint(4) DEFAULT NULL,
          `cohort_size` int(10) unsigned DEFAULT NULL,
          `taken` date DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_q_id` (`q_id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['properties'] = <<<QUERY
        CREATE TABLE `properties` (
          `property_id` mediumint(8) unsigned NOT NULL auto_increment,
          `paper_title` varchar(255) default NULL,
          `start_date` datetime default NULL,
          `end_date` datetime default NULL,
          `timezone` varchar(255) default NULL,
          `paper_type` enum('0','1','2','3','4','5','6') default NULL,
          `paper_prologue` text,
          `paper_postscript` text,
          `bgcolor` varchar(20) DEFAULT 'white',
          `fgcolor` varchar(20) default 'black',
          `themecolor` varchar(20) default '#316AC5',
          `labelcolor` varchar(20) default '#C00000',
          `fullscreen` enum('0','1') NOT NULL default '1',
          `marking` char(60) default '1',
          `bidirectional` enum('0','1') NOT NULL default '1',
          `pass_mark` tinyint(4) default '40',
          `distinction_mark` tinyint(4) default '70',
          `paper_ownerID` int(10) unsigned default NULL,
          `folder` varchar(255) default NULL,
          `labs` text,
          `rubric` text,
          `calculator` tinyint(4) default NULL,
          `exam_duration` smallint(6) default NULL,
          `deleted` datetime default NULL,
          `created` datetime default NULL,
          `random_mark` float default 0,
          `total_mark` mediumint(9) default 0,
          `display_correct_answer` enum('0','1') default '1',
          `display_question_mark` enum('0','1') default '1',
          `display_students_response` enum('0','1') default '1',
          `display_feedback` enum('0','1') default '1',
          `hide_if_unanswered` enum('0','1') default '0',
          `calendar_year` INT(4),
          `external_review_deadline` date default NULL,
          `internal_review_deadline` date default NULL,
          `sound_demo` enum('0','1') default '0',
          `latex_needed` tinyint(4) default '0',
          `password` char(255) default NULL,
          `retired` datetime default NULL,
          `crypt_name` varchar(32) default NULL,
          `recache_marks` tinyint(3) unsigned DEFAULT '0',
          `externalid` varchar(255) default NULL,
          `externalsys` varchar(255) default NULL,
          PRIMARY KEY (`property_id`),
          UNIQUE INDEX `externalid` (`externalid`),
          KEY `paper_title` (`paper_title`),
          KEY `paper_owner` (`paper_ownerID`),
          KEY `question_type` (`paper_type`),
          KEY `crypt_name_idx` (`crypt_name`),
          KEY `idx_owner_deleted` (`paper_ownerID`,`deleted`),
          KEY `date_idx` (`start_date`, `end_date`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['properties_modules'] = <<<QUERY
        CREATE TABLE `properties_modules` (
          `property_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
          `idMod` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`property_id`,`idMod`),
          KEY `idx_idmod` (`idMod`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['properties_reviewers'] = <<<QUERY
         CREATE TABLE `properties_reviewers` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `paperID` mediumint(8) unsigned DEFAULT NULL,
          `reviewerID` int(11) unsigned DEFAULT NULL,
          `type` enum('internal','external') DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_paperID` (`paperID`),
          KEY `idx_type` (`type`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['question_exclude'] = <<<QUERY
        CREATE TABLE `question_exclude` (
          `id` int(11) NOT NULL auto_increment,
          `q_paper` int(11) default NULL,
          `q_id` int(11) default NULL,
          `parts` varchar(255) default NULL,
          `userID` int unsigned default NULL,
          `date` datetime default NULL,
          `reason` text,
          KEY `idx_q_id` (`q_id`),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['question_statuses'] = <<<QUERY
        CREATE TABLE `question_statuses` (
          `id` int(4) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `exclude_marking` tinyint(4) NOT NULL DEFAULT '0',
          `retired` tinyint(3) NOT NULL,
          `is_default` tinyint(4) NOT NULL DEFAULT '0',
          `change_locked` tinyint(3) NOT NULL DEFAULT '1',
          `validate` tinyint(3) NOT NULL DEFAULT '1',
          `display_warning` tinyint(3) DEFAULT '0',
          `colour` char(7) DEFAULT '#000000',
          `display_order` tinyint(3) unsigned NOT NULL DEFAULT '255',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['questions'] = <<<QUERY
        CREATE TABLE `questions` (
          `q_id` int(4) NOT NULL auto_increment,
          `q_type` enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false','area','enhancedcalc') default NULL,
          `theme` text,
          `scenario` text,
          `leadin` text,
          `correct_fback` text,
          `incorrect_fback` text,
          `display_method` text,
          `notes` text,
          `ownerID` int(11) default NULL,
          `q_media` text,
          `q_media_width` varchar(100) default NULL,
          `q_media_height` varchar(100) default NULL,
          `creation_date` datetime default NULL,
          `last_edited` datetime default NULL,
          `bloom` enum('Knowledge','Comprehension','Application','Analysis','Synthesis','Evaluation') default NULL,
          `scenario_plain` text,
          `leadin_plain` text,
          `checkout_time` datetime default NULL,
          `checkout_authorID` int(10) unsigned default NULL,
          `deleted` datetime default NULL,
          `locked` datetime default NULL,
          `std` varchar(100) default NULL,
          `status` int(4) NOT NULL,
          `q_option_order` enum('display order','alphabetic','random') default NULL,
          `score_method` enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark') default NULL,
          `settings` text,
          `guid` char(40),
          PRIMARY KEY (`q_id`),
          KEY `idx_owner_deleted` (`ownerID`,`deleted`),
          KEY `idx_deleted` (`deleted`),
          KEY `idx_guid` (`guid`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['questions_metadata'] = <<<QUERY
        CREATE TABLE `questions_metadata` (
          `id` int(11) NOT NULL auto_increment,
          `questionID` int(11) default NULL,
          `type` varchar(255) default NULL,
          `value` varchar(255) default NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['questions_modules'] = <<<QUERY
        CREATE TABLE `questions_modules` (
          `q_id` int(4) NOT NULL,
          `idMod` int(11) NOT NULL ,
          KEY `idx_idmod` (`idMod`),
          PRIMARY KEY (`q_id`,`idMod`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['random_link'] = <<<QUERY
        CREATE TABLE `random_link` (
          `id` INT(4) NOT NULL,
          `q_id` INT(4) NOT NULL,
          PRIMARY KEY (`id`, `q_id`),
          INDEX `random_link_fk2` (`q_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['recent_papers'] = <<<QUERY
        CREATE TABLE `recent_papers` (
          `userID` int(10) unsigned NOT NULL default '0',
          `paperID` mediumint(8) unsigned NOT NULL default '0',
          `accessed` datetime default NULL,
          PRIMARY KEY  (`userID`,`paperID`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['reference_material'] = <<<QUERY
        CREATE TABLE `reference_material` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) DEFAULT NULL,
          `content` text,
          `width` smallint(5) unsigned DEFAULT NULL,
          `created` datetime DEFAULT NULL,
          `deleted` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['reference_modules'] = <<<QUERY
        CREATE TABLE `reference_modules` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `refID` mediumint(8) unsigned DEFAULT NULL,
          `idMod` int(11) unsigned DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['reference_papers'] = <<<QUERY
        CREATE TABLE `reference_papers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `paperID` mediumint(8) unsigned DEFAULT NULL,
          `refID` mediumint(9) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['relationships'] = <<<QUERY
        CREATE TABLE `relationships` (
          `rel_id` int(11) NOT NULL auto_increment,
          `idMod` int(11) unsigned DEFAULT NULL,
          `paper_id` mediumint(8) unsigned DEFAULT NULL,
          `question_id` int(11) NOT NULL,
          `obj_id` int(11) NOT NULL,
          `calendar_year` INT(4),
          `vle_api` varchar(255) NOT NULL DEFAULT '',
          `map_level` smallint(2) NOT NULL DEFAULT '0',
          PRIMARY KEY (`rel_id`),
          KEY `module_id_idx` (`idMod`),
          KEY `paper_id_idx` (`paper_id`),
          KEY `calendar_year` (`calendar_year`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['review_comments'] = <<<QUERY
        CREATE TABLE `review_comments` (
          `id` int(11) NOT NULL auto_increment,
          `q_id` int(11) default NULL,
          `category` tinyint(4) default NULL,
          `comment` text,
          `action` enum('Not actioned','Read - disagree','Read - actioned') default NULL,
          `response` text,
          `duration` mediumint(9) default NULL,
          `screen` tinyint(4) default NULL,
          `metadataID` int(11) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['review_metadata'] = <<<QUERY
        CREATE TABLE `review_metadata` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `reviewerID` int(10) unsigned NOT NULL,
          `paperID` mediumint(8) unsigned NOT NULL,
          `started` datetime DEFAULT NULL,
          `complete` datetime DEFAULT NULL,
          `review_type` enum('External','Internal') DEFAULT NULL,
          `ipaddress` varchar(100) DEFAULT NULL,
          `paper_comment` text,
          PRIMARY KEY (`id`),
          KEY `idx_paperID` (`paperID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['save_fail_log'] = <<<QUERY
          CREATE TABLE `save_fail_log` (
          `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
          `userID` int(10) unsigned NOT NULL,
          `paperID` mediumint(8) unsigned NOT NULL DEFAULT '0',
          `screen` tinyint(2) unsigned NOT NULL DEFAULT '0',
          `ipaddress` varchar(100) DEFAULT NULL,
          `failed` int(4) unsigned NOT NULL DEFAULT '0',
          `status` varchar(50) DEFAULT NULL,
          `request_url` varchar(255) DEFAULT NULL,
          `response_data` varchar(50) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_paperID` (`paperID`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['scheduling'] = <<<QUERY
          CREATE TABLE `scheduling` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `paperID` mediumint(8) unsigned DEFAULT NULL,
          `period` varchar(255) DEFAULT NULL,
          `barriers_needed` tinyint(4) DEFAULT NULL,
          `cohort_size` varchar(20) DEFAULT NULL,
          `notes` text,
          `sittings` tinyint(4) DEFAULT NULL,
          `campus` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `idx_paperID` (`paperID`)
           ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['schools'] = <<<QUERY
        CREATE TABLE `schools` (
          `id` int(11) NOT NULL auto_increment,
          `code` varchar(30) default NULL,
          `school` char(255) default NULL,
          `facultyID` int(11) default NULL,
          `deleted` datetime default NULL,
          `externalid` varchar(255) default NULL,
          `externalsys` varchar(255) default NULL,
          PRIMARY KEY (`id`),
          UNIQUE INDEX `code` (`code`),
          UNIQUE INDEX `externalid` (`externalid`),
          KEY `idx_facultyID` (`facultyID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sct_reviews'] = <<<QUERY
        CREATE TABLE `sct_reviews` (
          `id` int(11) NOT NULL auto_increment,
          `reviewer_name` text,
          `reviewer_email` text,
          `paperID` mediumint(8) unsigned default NULL,
          `q_id` int(4) default NULL,
          `answer` tinyint(4) default NULL,
          `reason` text,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sessions'] = <<<QUERY
        CREATE TABLE `sessions` (
          `sess_id` int(11) NOT NULL auto_increment,
          `identifier` bigint(20) unsigned NOT NULL,
          `idMod` int(11) unsigned NOT NULL DEFAULT '0',
          `title` text NOT NULL,
          `source_url` text,
          `calendar_year` INT(4),
          `occurrence` datetime default NULL,
          PRIMARY KEY (`identifier`,`idMod`,`calendar_year`),
          KEY `sess_id` (`sess_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sid'] = <<<QUERY
        CREATE TABLE `sid` (
          `student_id` char(15) default NULL,
          `userID` int(10) unsigned NOT NULL default 0,
          PRIMARY KEY  (`userID`,`student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sms_imports'] = <<<QUERY
        CREATE TABLE `sms_imports` (
          `id` int(11) NOT NULL auto_increment,
          `updated` date default NULL,
          `idMod` int(11) unsigned default NULL,
          `enrolements` int(11) default NULL,
          `enrolement_details` text,
          `deletions` int(11) default NULL,
          `deletion_details` text,
          `import_type` varchar(255) default NULL,
          `academic_year` INT(4),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['special_needs'] = <<<QUERY
        CREATE TABLE `special_needs` (
          `special_id` int(11) NOT NULL auto_increment,
          `userID` int(10) unsigned default NULL,
          `background` varchar(20) default NULL,
          `foreground` varchar(20) default NULL,
          `textsize` int(11) default NULL,
          `extra_time` tinyint(4) default NULL,
          `marks_color` varchar(20) default NULL,
          `themecolor` varchar(20) default NULL,
          `labelcolor` varchar(20) default NULL,
          `font` varchar(50) default NULL,
          `unanswered` varchar(20) default NULL,
					`dismiss` varchar(20) default NULL,
					`medical` text,
					`breaks` text,
					PRIMARY KEY (`special_id`),
          UNIQUE KEY `idx_userID` (`userID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['staff_help'] = <<<QUERY
        CREATE TABLE `staff_help` (
          `id` smallint(6) NOT NULL auto_increment,
          `title` mediumtext,
          `body` mediumtext,
          `body_plain` mediumtext,
          `type` enum('page','pointer') default NULL,
          `checkout_time` datetime default NULL,
          `checkout_authorID` int(10) unsigned default NULL,
          `roles` enum('SysAdmin','Admin','Staff') default NULL,
          `deleted` datetime default NULL,
          `language` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
          `articleid` smallint(6) unsigned NOT NULL,
          `lastupdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY  (`id`),
          KEY `language` (`language`),
          KEY `articleid` (`articleid`),
          FULLTEXT KEY `title` (`title`,`body_plain`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
QUERY;

    $this->tableList['std_set'] = <<<QUERY
        CREATE TABLE `std_set` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `setterID` int(10) unsigned NOT NULL,
          `paperID` mediumint(8) unsigned NOT NULL,
          `std_set` datetime DEFAULT NULL,
          `method` enum('Modified Angoff','Angoff (Yes/No)','Ebel','Hofstee') DEFAULT NULL,
          `group_review` text,
          `pass_score` decimal(10,6) DEFAULT NULL,
          `distinction_score` decimal(10,6) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['std_set_questions'] = <<<QUERY
        CREATE TABLE `std_set_questions` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `std_setID` int(10) unsigned NOT NULL,
          `questionID` int(11) unsigned NOT NULL,
          `rating` text,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['state'] = <<<QUERY
        CREATE TABLE `state` (
          `userID` int(10) unsigned DEFAULT NULL,
          `state_name` varchar(255) DEFAULT NULL,
          `content` varchar(255) DEFAULT NULL,
          `page` varchar(255) DEFAULT NULL,
          UNIQUE KEY `idx_user_state` (`userID`,`state_name`,`page`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['student_help'] = <<<QUERY
        CREATE TABLE `student_help` (
          `id` smallint(6) NOT NULL auto_increment,
          `title` mediumtext,
          `body` mediumtext,
          `body_plain` mediumtext,
          `type` enum('page','pointer') default NULL,
          `checkout_time` datetime default NULL,
          `checkout_authorID` int(10) unsigned default NULL,
          `deleted` datetime default NULL,
          `language` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
          `articleid` smallint(6) unsigned NOT NULL,
          `lastupdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `language` (`language`),
          KEY `articleid` (`articleid`),
          FULLTEXT KEY `title` (`title`,`body_plain`)
        ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
QUERY;

    $this->tableList['student_notes'] = <<<QUERY
        CREATE TABLE `student_notes` (
          `note_id` int(11) NOT NULL auto_increment,
          `userID` int(10) unsigned default NULL,
          `note` text,
          `note_date` datetime default NULL,
          `paper_id` mediumint(8) unsigned DEFAULT NULL,
          `note_authorID` int unsigned default NULL,
          PRIMARY KEY (`note_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sys_errors'] = <<<QUERY
        CREATE TABLE `sys_errors` (
          `id` int(11) NOT NULL auto_increment,
          `occurred` datetime default NULL,
          `userID` int(11) unsigned default NULL,
          `auth_user` varchar(45) default NULL,
          `errtype` enum('Notice','Warning','Fatal Error','Unknown','Application Warning','Application Error') DEFAULT NULL,
          `errstr` text,
          `errfile` text,
          `errline` int(11) default NULL,
          `fixed` datetime default NULL,
          `php_self` text,
          `query_string` text,
          `request_method` enum('GET','HEAD','POST','PUT','DELETE') default NULL,
          `paperID` mediumint unsigned default NULL,
          `post_data` text,
          `variables` longtext,
          `backtrace` longtext,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['sys_updates'] = <<<QUERY
        CREATE TABLE `sys_updates` (
          `name` varchar(255) DEFAULT NULL,
          `updated` datetime NOT NULL,
          KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['temp_users'] = <<<QUERY
        CREATE TABLE `temp_users` (
          `id` int(11) NOT NULL auto_increment,
          `first_names` char(60) default NULL,
          `surname` char(50) default NULL,
          `title` enum('Dr','Miss','Mr','Mrs','Ms','Professor','Mx') default NULL,
          `student_id` char(10) default NULL,
          `assigned_account` char(10) default NULL,
          `reserved` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['textbox_marking'] = <<<QUERY
				CREATE TABLE `textbox_marking` (
					`id` int(11) NOT NULL auto_increment,
					`paperID` mediumint(8) unsigned default NULL,
					`q_id` int(11) default NULL,
					`answer_id` int(11) default NULL,
					`markerID` int(10) unsigned default NULL,
					`mark` float default NULL,
					`comments` text,
					`date` datetime default NULL,
					`phase` tinyint(4) default NULL,
					`logtype` tinyint(4) default NULL,
					`student_userID` int(10) unsigned default NULL,
          `reminders` VARCHAR(255) NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `idx_unique` (`phase`,`answer_id`,`logtype`),
					KEY `paperID` (`paperID`),
					KEY `q_id` (`q_id`)
					) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['textbox_remark'] = <<<QUERY
        CREATE TABLE `textbox_remark` (
          `id` int(11) NOT NULL auto_increment,
          `paperID` mediumint(8) unsigned default NULL,
          `userID` int(10) unsigned default NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['toilet_breaks'] = <<<QUERY
        CREATE TABLE `toilet_breaks` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `userID` int(10) unsigned NOT NULL,
          `paperID` mediumint(8) unsigned NOT NULL,
          `break_taken` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `paperID` (`paperID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['track_changes'] = <<<QUERY
        CREATE TABLE `track_changes` (
          `id` int(4) NOT NULL auto_increment,
          `type` varchar(40) default NULL,
          `typeID` int(4) default NULL,
          `editor` int(10) unsigned default NULL,
          `old` text,
          `new` text,
          `changed` datetime default NULL,
          `part` text,
          PRIMARY KEY (`id`),
          KEY `typeID` (`typeID`),
          KEY `type` (`type`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['users'] = <<<QUERY
        CREATE TABLE `users` (
          `password` char(90) NOT NULL,
          `grade` char(30) default NULL,
          `surname` char(35) NOT NULL,
          `initials` char(10) default NULL,
          `title` varchar(30) default NULL,
          `username` char(60) NOT NULL,
          `email` char(65) default NULL,
          `roles` char(40) default NULL,
          `id` int(10) unsigned NOT NULL auto_increment,
          `first_names` char(60) default NULL,
          `gender` enum('Male','Female', 'Other') default NULL,
          `special_needs` tinyint(4) default '0',
          `yearofstudy` tinyint(4) default NULL,
          `user_deleted` datetime default NULL,
          `password_expire` int(11) unsigned default NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username_index` (`username`),
          KEY `idx_roles` (`roles`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['users_metadata'] = <<<QUERY
        CREATE TABLE `users_metadata` (
          `userID` int(10) unsigned default NULL,
          `idMod` int(11) default NULL,
          `type` varchar(255) default NULL,
          `value` varchar(255) default NULL,
          `calendar_year` INT(4),
          UNIQUE KEY `idx_users_metadata` (`userID`,`idMod`,`type`,`calendar_year`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['webservice_permissions'] = <<<QUERY
        CREATE TABLE webservice_permissions (
            client_id varchar(80) NOT NULL,
            action varchar(80) NOT NULL,
            access BOOLEAN NOT NULL default false,
            PRIMARY KEY (client_id,action)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

    $this->tableList['permissions'] = <<<QUERY
        CREATE TABLE permissions (
            action varchar(80) NOT NULL,
            PRIMARY KEY (action)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['gradebook_paper'] = <<<QUERY
        CREATE TABLE gradebook_paper (
            paperid int(8) NOT NULL,
            timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`paperid`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['gradebook_user'] = <<<QUERY
        CREATE TABLE gradebook_user (
            paperid int(8) NOT NULL,
            userid int(10) NOT NULL,
            raw_grade int(3) DEFAULT NULL,
            adjusted_grade float DEFAULT NULL,
            classification varchar(50) DEFAULT NULL,
            PRIMARY KEY (paperid, userid)
        )  ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['oauth_clients'] = <<<QUERY
        CREATE TABLE oauth_clients (
            client_id VARCHAR(80) NOT NULL,
            client_secret VARCHAR(80),
            redirect_uri VARCHAR(2000) NOT NULL,
            grant_types VARCHAR(80),
            scope VARCHAR(100),
            user_id VARCHAR(80),
            CONSTRAINT clients_client_id_pk PRIMARY KEY (client_id),
            KEY `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['oauth_access_tokens'] = <<<QUERY
        CREATE TABLE oauth_access_tokens (
            access_token VARCHAR(40) NOT NULL,
            client_id VARCHAR(80) NOT NULL,
            user_id VARCHAR(255),
            expires TIMESTAMP NOT NULL,
            scope VARCHAR(2000),
            CONSTRAINT access_token_pk PRIMARY KEY (access_token)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['oauth_authorization_codes'] = <<<QUERY
        CREATE TABLE oauth_authorization_codes (
            authorization_code VARCHAR(40) NOT NULL,
            client_id VARCHAR(80) NOT NULL,
            user_id VARCHAR(255),
            redirect_uri VARCHAR(2000),
            expires TIMESTAMP NOT NULL,
            scope VARCHAR(2000),
            CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['oauth_refresh_tokens'] = <<<QUERY
        CREATE TABLE oauth_refresh_tokens (
            refresh_token VARCHAR(40) NOT NULL,
            client_id VARCHAR(80) NOT NULL,
            user_id VARCHAR(255), expires TIMESTAMP NOT NULL,
            scope VARCHAR(2000),
            CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token),
            KEY `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['oauth_users'] = <<<QUERY
        CREATE TABLE oauth_users (
            username VARCHAR(255) NOT NULL,
            password VARCHAR(2000),
            first_name VARCHAR(255),
            last_name VARCHAR(255),
            CONSTRAINT username_pk PRIMARY KEY (username)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['oauth_scopes'] = <<<QUERY
        CREATE TABLE oauth_scopes (
            scope TEXT,
            is_default BOOLEAN
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['oauth_jwt'] = <<<QUERY
        CREATE TABLE oauth_jwt (
            client_id VARCHAR(80) NOT NULL,
            subject VARCHAR(80),
            public_key VARCHAR(2000),
            CONSTRAINT jwt_client_id_pk PRIMARY KEY (client_id)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['campus'] = <<<QUERY
        CREATE TABLE campus (
            id int(8) NOT NULL AUTO_INCREMENT,
            name VARCHAR(80) NOT NULL UNIQUE,
            isdefault BOOLEAN NOT NULL default false,
            PRIMARY KEY (`id`),
            INDEX `campus_idx` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;

$this->tableList['plugins'] = <<<QUERY
        CREATE TABLE `plugins` (
            `component` VARCHAR(50) NOT NULL,
            `version` VARCHAR(50) NOT NULL,
            `type`  VARCHAR(50) NOT NULL,
            `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`component`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset}
QUERY;
  }
  
  function next() {
    if (count($this->tableList) > 0) {
      return array_pop($this->tableList);
    } else {
      return false;
    }
  }
}
