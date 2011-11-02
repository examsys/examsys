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

  require '../include/sysadmin_auth.inc';
?>
<html>
<head>
<title>Photo rename</title>
</head>

<body>

<h1>Renaming Photos</h1>
<?php
  $d = dir("./new_photos/");
  while (false !== ($filename = $d->read())) {
    if ($filename != '.' and $filename != '..') {
      $query_string = "SELECT username FROM users, sid WHERE sid.userID=users.id AND student_id='" . str_replace('.jpg','',$filename) . "'";
      $results = $mysqli->query($query_string,$link_id);
      if ($results->num_rows == 1) {
        $row = $results->fetch_assoc();
        echo $filename . " = " . $row['username'] . "<br />\n";
        if (!rename($cfg_web_root . "users/new_photos/$filename", $cfg_web_root . "users/new_photos/" . $row['username'] . '.jpg')) {
          echo "Fail - \"" . $cfg_web_root . "users/new_photos/$filename\", \"/users/new_photos/" . $row['username'] . '.jpg<br />';
        }
      } else {
        echo "<span style=\"color:red\">" . $query_string ."</span><br />\n";
      }
    }
  }
  $d->close();
  $mysqli->close();
?>
</body>
</html>
