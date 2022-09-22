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
    if (!$updater_utils->has_updated('rogo_2805')) {
        $search = '$cfg_db_port';
        $new_lines = '$cfg_db_port = 3306;' . PHP_EOL;
        $target_line = '$cfg_db_host';
        $updater_utils->add_line($string, $search, $new_lines, -1, $cfg_web_root, $target_line);
        $updater_utils->record_update('rogo_2805');
    }
}
