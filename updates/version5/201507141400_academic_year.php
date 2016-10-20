<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->does_table_exist('academic_year')) {
    
        // Create new table.
        $createsql = "CREATE TABLE `academic_year` (
            `calendar_year` int(4) NOT NULL,
            `academic_year` varchar(30) NOT NULL,
            `cal_status` boolean NOT NULL DEFAULT '1',
            `stat_status` boolean NOT NULL DEFAULT '1',
            `deleted` datetime default NULL,
            `deletedby` int(10),
            PRIMARY KEY (`calendar_year`)
        )";
        
        $updater_utils->execute_query($createsql, true);
        
        // Select Permissions for everyone.
        $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".academic_year TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
        $updater_utils->execute_query($grantsql, true);
        $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".academic_year TO '" . $cfg_db_student_user . "'@'" . $cfg_web_host . "'";
        $updater_utils->execute_query($grantsql, true);
        $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".academic_year TO '" . $cfg_db_external_user . "'@'" . $cfg_web_host . "'";
        $updater_utils->execute_query($grantsql, true);
        $grantsql = "GRANT SELECT ON " . $cfg_db_database . ".academic_year TO '" . $cfg_db_inv_username . "'@'" . $cfg_web_host . "'";
        $updater_utils->execute_query($grantsql, true);
    
        // Default data
        $insertsql = "INSERT INTO academic_year (calendar_year, academic_year, cal_status, stat_status) VALUES (2002,'2002/03',0,0), "
          . "(2003,'2003/04',0,0), (2004,'2004/05',0,0), (2005,'2005/06',0,0), (2006,'2006/07',0,0), (2007,'2007/08',0,0), (2008,'2008/09',0,1), "
          . "(2009,'2009/10',0,1), (2010,'2010/11',0,1), (2011,'2011/12',0,1), (2012,'2012/13',1,1), (2013,'2013/14',1,1), (2014,'2014/15',1,1), "
          . "(2015,'2015/16',1,1), (2016,'2016/17',1,0), (2017,'2017/18',0,0), (2018,'2018/19',0,0), (2019,'2019/20',0,0)";
        $updater_utils->execute_query($insertsql, true);
    
    }
    
    // 1. users_metadata
    
    if (!$updater_utils->has_updated('rogo1481alter_users_metadata')) {
        $altersql = "ALTER TABLE users_metadata CHANGE calendar_year calendar_year INT(4)";
        $updater_utils->execute_query($altersql, true);
    
        $updatesql = "UPDATE users_metadata SET calendar_year = NULL WHERE calendar_year = 0";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2010 WHERE calendar_year = 1";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2011 WHERE calendar_year = 2";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2012 WHERE calendar_year = 3";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2013 WHERE calendar_year = 4";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2014 WHERE calendar_year = 5";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2015 WHERE calendar_year = 6";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2016 WHERE calendar_year = 7";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2017 WHERE calendar_year = 8";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2018 WHERE calendar_year = 9";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE users_metadata SET calendar_year = 2019 WHERE calendar_year = 10";
        $updater_utils->execute_query($updatesql, true);
    
        $altersql = "ALTER TABLE users_metadata ADD CONSTRAINT users_metadata_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
        $updater_utils->execute_query($altersql, true);
    
        $updater_utils->record_update('rogo1481alter_users_metadata');
    }
    
    // 2. sms_imports
    
    if (!$updater_utils->has_updated('rogo1481alter_sms_imports')) {
        $altersql = "ALTER TABLE sms_imports CHANGE academic_year academic_year INT(4)";
        $updater_utils->execute_query($altersql, true);
    
        $updatesql = "UPDATE sms_imports SET academic_year = NULL WHERE academic_year = 0";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2002 WHERE academic_year = 1";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2003 WHERE academic_year = 2";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2004 WHERE academic_year = 3";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2005 WHERE academic_year = 4";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2006 WHERE academic_year = 5";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2007 WHERE academic_year = 6";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2008 WHERE academic_year = 7";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2009 WHERE academic_year = 8";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2010 WHERE academic_year = 9";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2011 WHERE academic_year = 10";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2012 WHERE academic_year = 11";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2013 WHERE academic_year = 12";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2014 WHERE academic_year = 13";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2015 WHERE academic_year = 14";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2016 WHERE academic_year = 15";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2017 WHERE academic_year = 16";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2018 WHERE academic_year = 17";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sms_imports SET academic_year = 2019 WHERE academic_year = 18";
        $updater_utils->execute_query($updatesql, true);
    
        $altersql = "ALTER TABLE sms_imports ADD CONSTRAINT sms_imports_fk0 FOREIGN KEY (academic_year) REFERENCES academic_year(calendar_year)";
        $updater_utils->execute_query($altersql, true);
    
        $updater_utils->record_update('rogo1481alter_sms_imports');
    }
    
    // 3. sessions
    if (!$updater_utils->has_updated('rogo1481alter_sessions')) {
        $altersql = "ALTER TABLE sessions CHANGE calendar_year calendar_year INT(4)";
        $updater_utils->execute_query($altersql, true);
    
        $updatesql = "UPDATE sessions SET calendar_year = NULL WHERE calendar_year = 0";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2008 WHERE calendar_year = 1";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2009 WHERE calendar_year = 2";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2010 WHERE calendar_year = 3";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2011 WHERE calendar_year = 4";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2012 WHERE calendar_year = 5";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2013 WHERE calendar_year = 6";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2014 WHERE calendar_year = 7";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2015 WHERE calendar_year = 8";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2016 WHERE calendar_year = 9";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2017 WHERE calendar_year = 10";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2018 WHERE calendar_year = 11";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE sessions SET calendar_year = 2019 WHERE calendar_year = 12";
        $updater_utils->execute_query($updatesql, true);
    
        $altersql = "ALTER TABLE sessions ADD CONSTRAINT sessions_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
        $updater_utils->execute_query($altersql, true);
    
        $updater_utils->record_update('rogo1481alter_sessions');
    }
    
    // 4. relationships
    if (!$updater_utils->has_updated('rogo1481alter_relationships')) {
        $altersql = "ALTER TABLE relationships CHANGE calendar_year calendar_year INT(4)";
        $updater_utils->execute_query($altersql, true);
    
        $updatesql = "UPDATE relationships SET calendar_year = NULL WHERE calendar_year = 0";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2006 WHERE calendar_year = 1";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2007 WHERE calendar_year = 2";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2008 WHERE calendar_year = 3";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2009 WHERE calendar_year = 4";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2010 WHERE calendar_year = 5";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2011 WHERE calendar_year = 6";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2012 WHERE calendar_year = 7";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2013 WHERE calendar_year = 8";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2014 WHERE calendar_year = 9";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2015 WHERE calendar_year = 10";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2016 WHERE calendar_year = 11";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2017 WHERE calendar_year = 12";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2018 WHERE calendar_year = 13";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE relationships SET calendar_year = 2019 WHERE calendar_year = 14";
        $updater_utils->execute_query($updatesql, true);
    
        $altersql = "ALTER TABLE relationships ADD CONSTRAINT relationships_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
        $updater_utils->execute_query($altersql, true);
    
        $updater_utils->record_update('rogo1481alter_relationships');
    }
    
    // 5. properties
    
    if (!$updater_utils->has_updated('rogo1481alter_properties')) {
        $altersql = "ALTER TABLE properties CHANGE calendar_year calendar_year INT(4)";
        $updater_utils->execute_query($altersql, true);
    
        $updatesql = "UPDATE properties SET calendar_year = NULL WHERE calendar_year = 0";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2002 WHERE calendar_year = 1";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2003 WHERE calendar_year = 2";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2004 WHERE calendar_year = 3";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2005 WHERE calendar_year = 4";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2006 WHERE calendar_year = 5";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2007 WHERE calendar_year = 6";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2008 WHERE calendar_year = 7";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2009 WHERE calendar_year = 8";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2010 WHERE calendar_year = 9";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2011 WHERE calendar_year = 10";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2012 WHERE calendar_year = 11";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2013 WHERE calendar_year = 12";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2014 WHERE calendar_year = 13";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2015 WHERE calendar_year = 14";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2016 WHERE calendar_year = 15";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2017 WHERE calendar_year = 16";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2018 WHERE calendar_year = 17";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE properties SET calendar_year = 2019 WHERE calendar_year = 18";
        $updater_utils->execute_query($updatesql, true);
    
        $altersql = "ALTER TABLE properties ADD CONSTRAINT properties_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
        $updater_utils->execute_query($altersql, true);
    
        $updater_utils->record_update('rogo1481alter_properties');
    }
    
    // 6. objectives
    
    if (!$updater_utils->has_updated('rogo1481alter_objectives')) {
        $altersql = "ALTER TABLE objectives CHANGE calendar_year calendar_year INT(4)";
        $updater_utils->execute_query($altersql, true);
    
        $updatesql = "UPDATE objectives SET calendar_year = NULL WHERE calendar_year = 0";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2008 WHERE calendar_year = 1";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2009 WHERE calendar_year = 2";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2010 WHERE calendar_year = 3";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2011 WHERE calendar_year = 4";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2012 WHERE calendar_year = 5";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2013 WHERE calendar_year = 6";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2014 WHERE calendar_year = 7";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2015 WHERE calendar_year = 8";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2016 WHERE calendar_year = 9";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2017 WHERE calendar_year = 10";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2018 WHERE calendar_year = 11";
        $updater_utils->execute_query($updatesql, true);
        $updatesql = "UPDATE objectives SET calendar_year = 2019 WHERE calendar_year = 12";
        $updater_utils->execute_query($updatesql, true);
    
        $altersql = "ALTER TABLE objectives ADD CONSTRAINT objectives_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
        $updater_utils->execute_query($altersql, true);
    
        $updater_utils->record_update('rogo1481alter_objectives');
    }
}
