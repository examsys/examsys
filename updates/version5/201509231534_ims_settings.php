<?php

if (!$updater_utils->does_table_exist('ims_settings')) {

    // Create new table.
    $createsql = "CREATE TABLE `ims_settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `file_location` text COLLATE utf8_bin,
      `logto_location` text COLLATE utf8_bin,
      `delete_users` tinyint(4) DEFAULT ''0'',
      `fixcase_usernames` tinyint(4) DEFAULT ''0'',
      `fixcase_names` tinyint(4) DEFAULT ''0'',
      `rolemap01` tinyint(4) NOT NULL DEFAULT ''0'',
      `rolemap02` tinyint(4) NOT NULL DEFAULT ''0'',
      `rolemap03` tinyint(4) NOT NULL DEFAULT ''0'',
      `rolemap04` tinyint(4) NOT NULL DEFAULT ''0'',
      `rolemap05` tinyint(4) NOT NULL DEFAULT ''0'',
      `rolemap06` tinyint(4) NOT NULL DEFAULT ''0'',
      `rolemap07` tinyint(4) NOT NULL DEFAULT ''0'',
      `rolemap08` tinyint(4) NOT NULL DEFAULT ''0'',
      `truncate_coursecodes` tinyint(4) NOT NULL DEFAULT ''0'',
      `createnew_coursecodes` tinyint(4) NOT NULL DEFAULT ''0'',
      `unenrol` tinyint(4) NOT NULL DEFAULT ''0'',
      `mapshortname` varchar(45) COLLATE utf8_bin NOT NULL DEFAULT ''coursecode'',
      `mapfullname` varchar(45) COLLATE utf8_bin NOT NULL DEFAULT ''shortname'',
      `mapsummary` varchar(45) COLLATE utf8_bin NOT NULL DEFAULT ''long'',
      `restricttarget` varchar(45) COLLATE utf8_bin NOT NULL DEFAULT '''',
      `capitafix` tinyint(4) NOT NULL DEFAULT ''0'',
      PRIMARY KEY (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
    
    $updater_utils->execute_query($createsql, true);
    
    // Select Permissions for everyone.
    $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".ims_settings TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($grantsql, true);
    $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".ims_settings TO '" . $cfg_db_student_user . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($grantsql, true);
    $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".ims_settings TO '" . $cfg_db_external_user . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($grantsql, true);
    $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".ims_settings TO '" . $cfg_db_inv_username . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($grantsql, true);

    // Default data
    $insertsql = "INSERT INTO `ims_settings` "
        . "(`id`,`file_location`,`logto_location`,`delete_users`,`fixcase_usernames`,`fixcase_names`,`rolemap01`,`rolemap02`,"
        . "`rolemap03`,`rolemap04`,`rolemap05`,`rolemap06`,`rolemap07`,`rolemap08`,`truncate_coursecodes`,`createnew_coursecodes`,"
        . "`unenrol`,`mapshortname`,`mapfullname`,`mapsummary`,`restricttarget`,`capitafix`)"
        . " VALUES (1,'/var/www/IMS/COMBINED.xml','/var/www/IMSlog/COMBINED.log',1,1,1,5,3,3,5,0,4,0,4,0,1,1,'coursecode','shortname','long','',0);";
    $updater_utils->execute_query($insertsql, true);
    
    $altersql = "ALTER TABLE modules CHANGE COLUMN `moduleid` CHAR(255) NULL DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);

}
