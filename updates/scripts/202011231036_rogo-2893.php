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

if ($updater_utils->check_version('7.2.2')) {
    if (!$updater_utils->has_updated('ROGO-2893')) {
        // Allow user auto-creation during authentication to assign new roles to users.
        $sql = 'GRANT SELECT, INSERT ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '"
            . $configObject->get('cfg_db_username') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);

        $updater_utils->record_update('ROGO-2893');
    }
}
