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

if ($updater_utils->check_version('7.3.0')) {
    // Create the new tables.
    if (!$updater_utils->has_updated('rogo_2997')) {
        // Add new column.
        $newcolumn = 'ALTER TABLE roles ADD COLUMN api_enabled BOOLEAN NOT NULL default false';
        $updater_utils->execute_query($newcolumn, false);
        // Add data.
        $newdata = 'UPDATE roles SET api_enabled = 1 WHERE name IN (
            "left", "Student", "graduate", "Locked", "Suspended", "Staff", "Inactive Staff"
        )';
        $updater_utils->execute_query($newdata, false);
        $updater_utils->record_update('rogo_2997');
    }
}
