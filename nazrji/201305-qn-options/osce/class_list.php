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
require_once '../classes/paperutils.class.php';

check_var('id', 'GET', true, false, false);

// Get the module ID and calendar year of the OSCE station.
$result = $mysqli->prepare("SELECT property_id, paper_title, calendar_year FROM properties WHERE crypt_name = ?");
$result->bind_param('s', $_GET['id']);
$result->execute();
$result->bind_result($paperID, $paper_title, $calendar_year);
$result->fetch();
$result->close();

$modules = Paper_utils::get_modules($paperID, $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
  <?php
  if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') or strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
    echo "<meta name=\"viewport\" content=\"user-scalable=no\">\n";
  } else {
    echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n";
  }
  ?>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>OSCE: Class List</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/osce_list.css" />
  <style type="text/css">
  <?php
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') or strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
      echo "body {font-size:110%}\n";
    } else {
      echo "body {font-size:90%}\n";
    }
  ?>
  </style>
  
  <script language="JavaScript">
    function load(userID) {
      window.location.href = "form.php?id=<?php echo $_GET['id']; ?>&userID=" + userID;
    }
  </script>
  </head>

  <body>
  <div class="title"><?php echo $paper_title; ?></div>
  <form>
  
  <?php
    echo "<table style=\"width:100%; text-align:center\">\n<tr>\n";
    for ($i=1; $i<=26; $i++) {
      echo "<td class=\"qlink\"><a href=\"#" . chr($i+64) . "\" class=\"qlink\">" . chr($i+64) . "</a></td>";
    }
    echo "</tr>\n</table>\n";
  ?>
  
  <table cellpadding="6" cellspacing="0" border="0" style="width:100%">
<?php
  if (count($modules) == 0) {
    echo "<tr><td style=\"color:#C00000\"><strong>Error:</strong> No module selected so no students could be found.</td></tr>";
  } elseif (trim($calendar_year) == '') {
    echo "<tr><td style=\"color:#C00000\"><strong>Error:</strong> No academic year set so no students could be found.</td></tr>";
  } else {
    // Get the students who are enrolled on the module/session.
    $student_no = 0;
    $old_letter = '';
    
    $result = $mysqli->prepare("SELECT users.id, surname, first_names, title, student_id, started FROM (modules_student, users, sid) LEFT JOIN log4_overall ON users.id=log4_overall.userID AND q_paper=? WHERE modules_student.userID=users.id AND users.id=sid.userID AND modules_student.idMod IN (" . implode(',', array_keys($modules)) . ") AND calendar_year=? ORDER BY surname, initials");
    $result->bind_param('is', $paperID, $calendar_year);
    $result->execute();
    $result->bind_result($tmp_userID, $surname, $first_names, $title, $student_id, $started);
    while ($result->fetch()) {
      $current_letter = strtoupper($surname{0});
      if ($old_letter != $current_letter) {
        echo "<tr><td colspan=\"3\" class=\"letter\"><a name=\"$current_letter\"></a>$current_letter</td></tr>";
      }
      if ($started == '') {
        echo "<tr class=\"bl\" onclick=\"load('$tmp_userID')\"><td class=\"indent\">$title</td><td>$surname, <span class=\"n\">$first_names</span</td><td>$student_id</td></tr>\n";
      } else {
        echo "<tr class=\"l\" onclick=\"load('$tmp_userID')\"><td class=\"indent\">$title</td><td>$surname, $first_names</td><td>$student_id</td></tr>\n";
      }
      $student_no++;
      $old_letter = $current_letter;
    }
    $result->close();
  }
  echo "</table>\n<input type=\"hidden\" name=\"oldstudent\" id=\"oldstudent\" value=\"\" />\n</form>\n";

  $mysqli->close();
?>
</body>
</html>
