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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  function check4Images($html,&$images) {
    if (stripos($html, '<img') !== false) {
      $img_parts = explode('src="',$html);
      for ($i=1; $i<count($img_parts); $i++) {
        $quote_parts = explode('"',$img_parts[$i]);
        $images[] = $quote_parts[0];
      }
    }
  }

  require '../../include/sysadmin_auth.inc';    // Only let staff delete pages.
  require '../../include/errors.inc';

  $path = $cfg_web_root . 'student_help';
  
  header('Content-Type: text/html; charset=UTF-8');
  $image_list = array();

  // Is the current page real or a pointer.
  $results = $mysqli->query("SELECT type, body FROM student_help WHERE id = " . $_GET['id']);
  $row = $results->fetch_assoc();
  $results->close();
  $type = $row['type'];
  $body = $row['body'];
  check4Images($body,$image_list);
  
  if (count($image_list) > 0) {
    foreach ($image_list as $filename) {
      // Check to see if the image is used on any other help pages.
      $results = $mysqli->query("SELECT id FROM student_help WHERE body LIKE '%$filename%' AND id != " . $_GET['id']);
      if ($results->num_rows == 0) {
        // No records found - delete file
        $target = $path . substr($filename,1);
        unlink($target);
      }
      $results->close();
    }
  }

  if ($type == 'page') {
    // Search for any pointers to the current page.
    $results = $mysqli->query("SELECT id, body FROM student_help WHERE type='pointer' AND id != " . $_GET['id'] . " AND body=" . $_GET['id']);
    while ($row = $results->fetch_assoc()) {
      $deleteQuery = "UPDATE student_help SET deleted=NOW() WHERE id=" . $row['id'];
      if (!$mysqli->query($deleteQuery)) {
        display_error("Error deleting from 'student_help' table.",$mysqli->error);
      }
    }
  }
  
  $deleteQuery = "UPDATE student_help SET deleted=NOW() WHERE id=" . $_GET['id'];
  if (!$mysqli->query($deleteQuery)) {
    display_error("Error deleting from 'student_help' table.",$mysqli->error);
  }
  $mysqli->close();
?>
<html>
<head>
<title>Delete Page</title>
<script language="JavaScript">
  function reloadHelp() {
    window.top.location='index.php';
  }
</script>
</head>
<body onload="reloadHelp()">

</body>
</html>