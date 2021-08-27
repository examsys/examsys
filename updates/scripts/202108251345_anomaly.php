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

if ($updater_utils->check_version('7.4.0')) {
    if (!$updater_utils->has_updated('rogo_3062')) {
        // Create audit permissions schema.
        $sqlroles = 'CREATE TABLE `anomaly` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `type` tinyint NOT NULL,
            `timestamp` timestamp NOT NULL,
            `details` TEXT,
            `userID` int(10) unsigned NOT NULL,
            `paperID` mediumint(8) unsigned NOT NULL,
            `screen` tinyint(3) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `anomaly_log_key0` (`type`, `userID`, `paperID`),
            FOREIGN KEY anomaly_log_fk0 (userID) REFERENCES users(id),
            FOREIGN KEY anomaly_log_fk1 (paperID) REFERENCES properties(property_id)
        )';
        $updater_utils->execute_query($sqlroles, false);
        // Grant access to new tables.
        $sqlgrantstu = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".anomaly TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantstu, false);
        $updater_utils->record_update('rogo_3062');

    }
}
