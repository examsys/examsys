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

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';

$userID  = check_var('userID', 'GET', true, false, true);
$paperID = check_var('paperID', 'GET', true, false, true);

$result = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date,'%d/%m/%Y %H:%i') AS note_date, title, initials, surname FROM student_notes, users WHERE student_notes.note_authorID = users.id AND paper_id = ? AND student_notes.userID = ?");
$result->bind_param('ii', $paperID, $userID);
$result->execute();
$result->bind_result($note, $note_date, $title, $initials, $surname);
$result->store_result();
if ($result->num_rows == 0) {
  echo "<div style=\"padding:10px\">" . $string['err'] . "</div>\n";
} else {
  while ($result->fetch()) {
    echo "<div style=\"padding:10px\">$note</div>\n";
    echo "<div style=\"padding:10px\"><em>$title $initials $surname - $note_date</em></div>\n";
  }
}
$result->close();

$mysqli->close();
?>