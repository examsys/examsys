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

require '../include/staff_auth.inc';
require '../include/errors.php';

$screen				= check_var('screen', 'GET', true, false, true);
$paperID			= check_var('paperID', 'GET', true, false, true);
$questionID		= check_var('questionID', 'GET', true, false, true);
$display_pos	= check_var('display_pos', 'GET', true, false, true);

// Change the screen number of the actual question.
if ($result = $mysqli->prepare("UPDATE papers SET screen = ? WHERE paper = ? AND p_id = ?")) {
  $result->bind_param('iii', $screen, $paperID, $questionID);
  $result->execute();
  $result->close();
} else {
  display_error("Papers Update Error 1", $string['showerror']);
}

// Increase the screen of all questions with a higher display_pos that the question we are dealing with.
if ($result = $mysqli->prepare("UPDATE papers SET screen = screen+1 WHERE paper = ? AND display_pos > ?")) {
  $result->bind_param('ii', $paperID,  $display_pos);
  $result->execute();
  $result->close();
} else {
  display_error("Papers Update Error 2", $string['showerror']);
}

// Redirect back to paper/details.php
header("location: " . $configObject->get('cfg_root_path') . "/paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder'] . "&scrOfY=" . $_GET['scrOfY']);
?>