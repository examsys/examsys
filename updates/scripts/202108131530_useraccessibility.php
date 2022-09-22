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
    if (!$updater_utils->has_updated('rogo2945_useraccess')) {
        $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database') . ".special_needs TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $sql = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".track_changes TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $configObject->set_setting('system_user_accessibility', 0, Config::BOOLEAN);
        $updater_utils->record_update('rogo2945_useraccess');
    }
}
