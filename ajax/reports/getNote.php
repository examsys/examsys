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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
require '../../include/errors.php';

$userID  = check_var('userID', 'GET', true, false, true);
$paperID = check_var('paperID', 'GET', true, false, true);

if ($userObject->has_role('External Examiner')) {
  // Security: Check the external can access this paper.
  if (!ReviewUtils::is_external_on_paper($userObject->get_user_ID(), $paperID, $mysqli)) {
    echo "<div style=\"padding:10px\">" . $string['pagenotfound'] . "</div>\n";
    $mysqli->close();
    exit();
  }
}

$details = StudentNotes::get_note($paperID, $userID, $mysqli);

if ($details === false) {
  echo "<div style=\"padding:10px\">" . $string['err'] . "</div>\n";
} else {
  echo "<div style=\"padding:10px\">" . $details['note'] . "</div>\n";
  echo "<div style=\"padding:10px\"><em>" . $details['author_title'] . " " . $details['author_initials'] . " " . $details['author_surname'] . " - " . $details['date'] . "</em></div>\n";
}

$mysqli->close();
?>