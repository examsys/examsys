<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  require '../touchstone/include/staff_auth.inc';

  // Get the module ID and calendar year of the OSCE station.
  $result = $mysqli->prepare("SELECT paper_title, moduleID, calendar_year FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title, $moduleID, $calendar_year);
  $result->fetch();
  $result->close();
?>
  <html>
  <head>
  <title>OSCE: Class List</title>
  <style>
    body {font-family:Arial,sans-serif; font-size:70%; background-color:white; color:black; margin:0px}
    table {font-size:100%; border-collapse:collapse; width:100%}
    tr {border:1px solid #7F9DB9}
    a {color:black}
    .n {color:#808080}
    .bl {font-weight:bold}
    .l {color:#808080}
  </style>
  <script language="JavaScript">
    function load(userID) {
      window.location.href = "form.php?paperID=<?php echo $_GET['paperID']; ?>&userID=" + userID;
    }
  </script>
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
  <meta name="apple-mobile-web-app-capable" content="yes" />
  </head>

  <body>
  <div style="margin-left:10px; font-size:150%; font-weight:bold; color:#7F9DB9"><?php echo $paper_title; ?></div>
  <form>
  <table cellpadding="6" cellspacing="0" border="0" style="width:100%">
<?php
  if (trim($moduleID) == '') {
    echo "<tr><td style=\"color:#C00000\"><strong>Error:</strong> No module selected so no students could be found.</td></tr>";
  } elseif (trim($calendar_year) == '') {
    echo "<tr><td style=\"color:#C00000\"><strong>Error:</strong> No academic year set so no students could be found.</td></tr>";
  } else {
    // Get the students who are enrolled on the module/session.
    $student_no = 0;
    $result = $mysqli->prepare("SELECT users.id, surname, first_names, title, student_id, started FROM (student_modules, users, sid) LEFT JOIN log4_overall ON users.id=log4_overall.userID AND q_paper=? WHERE student_modules.userID=users.id AND users.id=sid.userID AND moduleid=? AND calendar_year=? ORDER BY surname, initials");
    $result->bind_param('iss', $_GET['paperID'], $moduleID, $calendar_year);
    $result->execute();
    $result->bind_result($tmp_userID, $surname, $first_names, $title, $student_id, $started);
    while ($row = $result->fetch()) {
      if ($started == '') {
        echo "<tr class=\"bl\" onclick=\"load('$tmp_userID')\"><td>$title</td><td>$surname, <span class=\"n\">$first_names</span</td><td>$student_id</td></tr>\n";
      } else {
        echo "<tr class=\"l\" onclick=\"load('$tmp_userID')\"><td>$title</td><td>$surname, $first_names</td><td>$student_id</td></tr>\n";
      }
      $student_no++;
    }
    $result->close();
  }
  echo "</table>\n<input type=\"hidden\" name=\"oldstudent\" id=\"oldstudent\" value=\"\" />\n</form>\n";

  $mysqli->close();
?>
</body>
</html>
