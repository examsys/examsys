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
    if (!$updater_utils->has_updated('rogo_2807')) {
        // Add database engines to config file.
        $search = '$cfg_db_engine';
        $new_lines = '$cfg_db_engine = "InnoDB";' . PHP_EOL;
        $target_line = '$cfg_db_host';
        $updater_utils->add_line($string, $search, $new_lines, -1, $cfg_web_root, $target_line);
        $search = '$cfg_db_help_engine';
        $new_lines = '$cfg_db_help_engine = "MyISAM";' . PHP_EOL;
        $target_line = '$cfg_db_engine';
        $updater_utils->add_line($string, $search, $new_lines, -1, $cfg_web_root, $target_line);

        // Schema update - performance_details.
        $sqlperf = 'ALTER TABLE performance_details ADD COLUMN id int NOT NULL AUTO_INCREMENT KEY FIRST';
        $updater_utils->execute_query($sqlperf, false);

        // Fix bad data.
        $sql = 'DELETE FROM state WHERE state_name is NULL';
        $updater_utils->execute_query($sql, false);
        // Schame update - state.
        $sqlstate = 'ALTER TABLE state MODIFY COLUMN userID int(10) unsigned NOT NULL';
        $updater_utils->execute_query($sqlstate, false);
        $sqlstate2 = 'ALTER TABLE state MODIFY COLUMN `state_name` varchar(255) NOT NULL';
        $updater_utils->execute_query($sqlstate2, false);
        $sqlstate3 = 'ALTER TABLE state MODIFY COLUMN `page` varchar(255) NOT NULL';
        $updater_utils->execute_query($sqlstate3, false);
        $sqlstate_pk = 'ALTER TABLE state ADD PRIMARY KEY (userID, state_name, page)';
        $updater_utils->execute_query($sqlstate_pk, false);

        // Schema update - sys_updates.
        $sqlsys = 'ALTER TABLE sys_updates MODIFY COLUMN name varchar(255) NOT NULL';
        $updater_utils->execute_query($sqlsys, false);
        $sqlsys_pk = 'ALTER TABLE sys_updates ADD PRIMARY KEY (name)';
        $updater_utils->execute_query($sqlsys_pk, false);

        // Schame update - users_metadata.
        $sqlmeta = 'ALTER TABLE users_metadata MODIFY COLUMN userID int(10) unsigned NOT NULL';
        $updater_utils->execute_query($sqlmeta, false);
        $sqlmeta2 = 'ALTER TABLE users_metadata MODIFY COLUMN idMod int(11) NOT NULL';
        $updater_utils->execute_query($sqlmeta2, false);
        $sqlmeta3 = 'ALTER TABLE users_metadata MODIFY COLUMN type varchar(255) NOT NULL';
        $updater_utils->execute_query($sqlmeta3, false);
        $sqlmeta4 = 'ALTER TABLE users_metadata MODIFY COLUMN calendar_year INT(4) NOT NULL';
        $updater_utils->execute_query($sqlmeta4, false);
        $sqlsys_pk1 = 'ALTER TABLE users_metadata DROP INDEX idx_users_metadata';
        $updater_utils->execute_query($sqlsys_pk1, false);
        $sqlsys_pk2 = 'ALTER TABLE users_metadata ADD PRIMARY KEY (`userID`,`idMod`,`type`,`calendar_year`)';
        $updater_utils->execute_query($sqlsys_pk2, false);

        // Schame update - oauth_scopes.
        $sqlscope = 'ALTER TABLE oauth_scopes MODIFY COLUMN scope varchar(255) NOT NULL';
        $updater_utils->execute_query($sqlscope, false);
        $sqlscope_pk = 'ALTER TABLE oauth_scopes ADD CONSTRAINT oauth_scopes_pk PRIMARY KEY (scope)';
        $updater_utils->execute_query($sqlscope_pk, false);

        $updater_utils->record_update('rogo_2807');
    }
}
