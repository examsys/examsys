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

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Photo rename</title>
</head>

<body>
<h1>Renaming Photos</h1>
<?php
  $allowed_modes = array('sid', 'fullname');
$mode = 'sid';
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
}

if (!in_array($mode, $allowed_modes)) {
    echo 'Error - unknown mode';
    exit;
}

  $d = dir($configObject->get('cfg_tmpdir') . 'new_photos/');
while (false !== ($filename = $d->read())) {
    if ($filename != '.' and $filename != '..' and $filename != 'thumbs.db' and $filename != 'renamed') {
        $filename = str_replace('.jpg', '', $filename);
        if ($mode == 'sid') {
            $result = $mysqli->prepare("SELECT username FROM users, sid WHERE sid.userID=users.id AND student_id='$filename'");
        } elseif ($mode == 'fullname') {
            $name_parts = explode(' ', $filename);
            $surname = mb_strtolower($name_parts[0]);
            $firstname = mb_strtolower($name_parts[1]);
            $len = mb_strlen($firstname);
            $result = $mysqli->prepare("SELECT username FROM users WHERE LOWER(surname)=? AND LEFT(LOWER(first_names), $len)=?");
            $result->bind_param('ss', $surname, $firstname);
        }
        $result->execute();
        $result->store_result();
        $result->bind_result($username);
        if ($result->num_rows() == 1) {
            $result->fetch();
            echo $filename . ' = ' . $username . "<br />\n";
            if (!rename($configObject->get('cfg_tmpdir') . "new_photos/{$filename}.jpg", $configObject->get('cfg_tmpdir') . 'new_photos/renamed/' . $username . '.jpg')) {
                echo 'Fail - "' .  $configObject->get('cfg_tmpdir') . "new_photos/$filename\", \"/users/new_photos/" . $username . '.jpg<br />';
            }
        } elseif ($result->num_rows() > 1) {
            echo "<span style=\"color:red\">Multiple records returned ($filename)</span><br />\n";
        } else {
            echo "<span style=\"color:red\">User not found ($filename)</span><br />\n";
        }
        $result->close();
    }
}
  $d->close();
$mysqli->close();
?>
</body>
</html>
