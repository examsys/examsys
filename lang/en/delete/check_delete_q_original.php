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

require '../lang/' . $language . '/delete/delete.php';

$string['msg'] = 'You are attempting to delete a question from the question bank.<br /><br /><strong>Please confirm that this is your intention.';
$string['warning1'] = 'You cannot delete this question, it is used in the following papers:';
$string['warning2'] = 'Delete all pointers to this question before deleting the original.';
