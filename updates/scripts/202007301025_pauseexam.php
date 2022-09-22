<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

if ($updater_utils->check_version('7.2.0')) {
    if (!$updater_utils->has_updated('rogo_2767')) {
        // New break time column.
        $sql = 'ALTER TABLE special_needs ADD COLUMN `break_time` smallint default NULL';
        $updater_utils->execute_query($sql, false);

        // Break time scale configuration.
        $breaktimescale = array(5, 10, 25, 33, 50, 100, 200, 300);
        $configObject->set_setting('paper_breaktime_scale', $breaktimescale, Config::CSV);
        $configObject->set_setting('paper_breaktime_mins', 0, Config::BOOLEAN);
        // Pause exam enabled configuration.
        $configObject->set_setting('paper_pause_exam', 0, Config::BOOLEAN);

        // New break time log table.
        $sql = 'CREATE TABLE `log_break_time` (
          `paperID` mediumint(8) unsigned NOT NULL,
          `userID` int(10) unsigned NOT NULL,
          `time` smallint unsigned NOT NULL,
          PRIMARY KEY (`paperID`,`userID`)
        )';
        $updater_utils->execute_query($sql, false);

        // New break time table.
        $sql = 'CREATE TABLE `breaks` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `userID` int(10) unsigned NOT NULL,
          `paperID` mediumint(8) unsigned NOT NULL,
          `break_taken` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `paperID` (`paperID`)
        )';
        $updater_utils->execute_query($sql, false);

        // log_break_time grants.
        $sql = 'GRANT SELECT, INSERT, UPDATE ON ' . $configObject->get('cfg_db_database') . ".log_break_time TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        // breaks grants.
        $sql = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".breaks TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT SELECT, UPDATE ON ' . $configObject->get('cfg_db_database') . ".breaks TO '"
            . $configObject->get('cfg_db_staff_user') . "'@'" . $configObject->get('cfg_web_host') . "'";

        $updater_utils->execute_query($sql, false);
        $updater_utils->record_update('rogo_2767');
    }
}
