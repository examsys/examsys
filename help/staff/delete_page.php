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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../../include/sysadmin_auth.inc';    // Only let SysAdmin staff delete pages.
  require '../../include/errors.inc';
  
  check_var('id', 'GET', true, false);
  
  // Is the current page real or a pointer.
  $results = $mysqli->query("SELECT type, body FROM staff_help WHERE id = " . $_GET['id']);
  $row = $results->fetch_assoc();
  $results->close();
  $type = $row['type'];
  $body = $row['body'];

  if ($type == 'page') {
    // Search for any pointers to the current page.
    $results = $mysqli->query("SELECT id, body FROM staff_help WHERE type='pointer' AND id != " . $_GET['id'] . " AND body=" . $_GET['id']);
    while ($row = $results->fetch_assoc()) {
      $deleteQuery = "UPDATE staff_help SET deleted=NOW() WHERE id=" . $row['id'];
      if (!$mysqli->query($deleteQuery)) {
        display_error("Error deleting from 'staff_help' table.",$mysqli->error);
      }
    }
  }
  
  $deleteQuery = "UPDATE staff_help SET deleted=NOW() WHERE id=" . $_GET['id'];
  if (!$mysqli->query($deleteQuery)) {
    display_error("Error deleting from 'staff_help' table.",$mysqli->error);
  }

  $mysqli->close();

  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/help/staff/index.php");
?>
