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

  require '../include/sysadmin_auth.inc';
?>
<html>
<head>
<title>Photo rename</title>
</head>

<body>

<h1>Renaming Photos</h1>
<?php
  $d = dir("/tmp/new_photos/");
  while (false !== ($filename = $d->read())) {
    if ($filename != '.' and $filename != '..') {
      $result = $mysqli->prepare("SELECT username FROM users, sid WHERE sid.userID=users.id AND student_id='" . str_replace('.jpg','',$filename) . "'");
      $result->execute();
      $result->store_result();
      $result->bind_result($username);
      if ($result->num_rows() == 1) {
        $result->fetch();
        echo $filename . " = " . $username . "<br />\n";
        if (!rename("/tmp/new_photos/$filename", $cfg_web_root . "users/new_photos/" . $username . '.jpg')) {
          echo "Fail - \"/tmp/new_photos/$filename\", \"/users/new_photos/" . $username . '.jpg<br />';
        }
      } else {
        echo "<span style=\"color:red\">" . $query_string ."</span><br />\n";
      }
      $result->close();
    }
  }
  $d->close();
  $mysqli->close();
?>
</body>
</html>
