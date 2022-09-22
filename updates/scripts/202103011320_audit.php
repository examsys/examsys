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

if ($updater_utils->check_version('7.4.0')) {
    if (!$updater_utils->has_updated('rogo_2943')) {
        // Create audit permissions schema.
        $sqlroles = 'CREATE TABLE `audit_log` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `userID` int(10) unsigned NOT NULL,
            `action` varchar(30) NOT NULL,
            `time` timestamp NOT NULL,
            `sourceID` int NOT NULL,
            `source` TEXT,
            `details` TEXT,
            PRIMARY KEY (`id`),
            KEY `audit_log_key1` (`userID`, `action`),
            FOREIGN KEY audit_log_fk0 (userID) REFERENCES users(id)
        )';
        $updater_utils->execute_query($sqlroles, false);
        // Grant access to new tables.
        $sqlgrantstu = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".audit_log TO '" . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantstu, false);
        $sqlgrantweb = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".audit_log TO '" . $configObject->get('cfg_db_webservice_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantweb, false);
        $sqlstaff2 = 'GRANT SELECT, INSERT ON ' . $configObject->get('cfg_db_database') . ".audit_log TO '" . $configObject->get('cfg_db_staff_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlstaff2, false);
        // Data retention.
        $sqlret = 'CREATE TABLE `retention` (
            `table` varchar(100) NOT NULL,
            `days` integer NOT NULL DEFAULT 90,
            `lastrun` timestamp DEFAULT NULL NULL,
            PRIMARY KEY (`table`))
        ';
        // Grant access to new tables.
        $updater_utils->execute_query($sqlret, false);
        // Create retention definitions.
        $sqlcreateactions = "INSERT INTO retention (`table`, `days`) VALUES ('audit_log', 90)";
        $updater_utils->execute_query($sqlcreateactions, false);
        $updater_utils->record_update('rogo_2943');
    }
}
