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

require '../lang/' . $language . '/include/paper_options.php';
require '../lang/' . $language . '/include/months.php';
require '../lang/' . $language . '/paper/new_paper2.php';

$string['importoscemarks'] = 'Import OSCE Station Marks';
$string['marksloaded'] = 'Marks loaded.';
$string['csvfile'] = 'CSV File:';
$string['topmsg'] = 'This area is for bulk uploading of marks from a paper-based OSCE. A CSV file in the following format should be used:';
$string['headerrow'] = 'File contains header row';
$string['import'] = 'Import';
$string['usernotfound'] = 'username not found!';
$string['saveerror'] = 'Error saving user data for %s';
$string['gradedmsg'] = 'Import failed. This OSCE station has already been graded.';
