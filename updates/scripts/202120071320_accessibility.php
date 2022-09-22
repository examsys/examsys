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

/**
 * Changes the database so that the extra time column can store a value of more than 128%
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */

if ($updater_utils->check_version('7.4.0')) {
    if (!$updater_utils->has_updated('rogo_2872')) {
        $sql = 'ALTER TABLE special_needs ADD COLUMN `globalthemecolour` varchar(20) default NULL';
        $updater_utils->execute_query($sql, false);
        $sql = 'ALTER TABLE special_needs ADD COLUMN `globalthemefont_colour` varchar(20) default NULL';
        $updater_utils->execute_query($sql, false);
        $sql = 'ALTER TABLE special_needs ADD COLUMN `highlight_bgcolour` varchar(20) default NULL';
        $updater_utils->execute_query($sql, false);
        $updater_utils->record_update('rogo_2872');
    }
}
