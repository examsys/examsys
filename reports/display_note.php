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
  
require '../include/staff_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Note</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:#FFFFCC}
  </style>
</head>

<body>

<?php
  $result = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date,'%d/%m/%Y %H:%i') AS note_date, title, initials, surname FROM student_notes, users WHERE student_notes.note_authorID=users.id AND paper_id=? AND student_notes.userID=?");
$result->bind_param('is', $_GET['paperID'], $_GET['userID']);
$result->execute();
$result->bind_result($note, $note_date, $title, $initials, $surname);
while ($result->fetch()) {
    echo "<p>$note</p>";
    echo "<p><em>$title $initials $surname - $note_date</em></p>";
}
  $result->close();
$mysqli->close();
?>
<br />
<div align="center"><input type="button" value="Close" name="close" onclick="window.close();" /></div>
</body>
</html>
