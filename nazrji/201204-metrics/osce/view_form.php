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

  require '../include/staff_auth.inc';
  require '../include/demo_replace.inc';
  require './osce.inc';
  if (strpos($userroles,'Demo') !== false) $demo = true;

  // Get the module ID and calendar year of the OSCE station.
  $result = $mysqli->prepare("SELECT username, title, surname, first_names, grade, yearofstudy, student_id FROM (users, sid) WHERE users.id=? AND users.id=sid.userID");
  $result->bind_param('i', $_GET['userID']);
  $result->execute();
  $result->bind_result($username, $title, $surname, $first_names, $grade, $year, $student_id);
  $result->fetch();
  $result->close();
  
  $original_username = $username;
  if (isset($demo) and $demo == true) {
    $surname = demo_replace($surname,$demo);
    $first_names = demo_replace($first_names,$demo);
    $student_id = demo_replace_number($student_id,$demo);
  }

  // Get properties of the paper.
  $result = $mysqli->prepare("SELECT paper_title, bgcolor, fgcolor, labelcolor, themecolor, marking FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title, $bgcolor, $fgcolor, $labelcolor, $themecolor, $marking);
  $result->fetch();
  $result->close();
?>
  <html>
  <head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['osceform']; ?></title>
  <style type="text/css">
    body {font-family:Arial,sans-serif; font-size:90%; background-color:<?php echo $bgcolor; ?>; color:<?php echo $fgcolor; ?>}
    table {font-size:100%; border-collapse:collapse}
    td {text-align:center}
    textarea, input {font-size:100%}
    .question {text-align:left; border:1px solid #7F9DB9}
    .rating {border:1px solid #7F9DB9}
    .theme {text-align:left; font-size:125%; color:<?php echo $themecolor; ?>; padding-top:10px}
    .overall {border:1px solid #7F9DB9; width:20%; height:35px; text-align:center}
    ul {margin-top:0px; margin-bottom:0px}
    .part {text-decoration:none}
    .part_ok {color:#3A8000; text-decoration:underline}
  </style>
  </head>
  
  <body>
  <table cellpadding="2" cellspacing="0" border="0"><tr>
<?php
  if (file_exists('../users/photos/' . $original_username . '.jpg')) {
    if ($demo == true) {
      echo '<td><img style="filter:progid:DXImageTransform.Microsoft.Pixelate(maxSquare=8)" src="../users/photos/' . $original_username . '.jpg" width="180" height="270" style="border:1px solid #7F9DB9" alt="Photo" /></td>';
    } else {
      echo '<td><img src="../users/photos/' . $original_username . '.jpg" width="180" height="270" style="border-top:1px solid #C0C0C0; border-right:1px solid #C0C0C0; border-bottom:1px solid #808080; border-top:1px solid #808080" alt="Photo" /></td>';
    }
  } else {
    echo '<td><img src="./test_photo.png" width="180" height="270" style="border-top:1px solid #EEEEEE; border-left:1px solid #EEEEEE; border-right:1px solid #C0C0C0; border-bottom:1px solid #C0C0C0" alt="Photo" /></td>';
  }
  echo "<td style=\"vertical-align:top; font-weight:bold; text-align:left\"><div style=\"font-size:150%; color:#7F9DB9\">$paper_title</div><br /><br /><div style=\"font-size:150%\">$title $surname, <span style=\"color:#808080\">$first_names</span></div><span style=\"color:#808080\">($student_id)</span></td></table>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\"><tr>";

  // Query Log4 just in case form has already been submitted for this user.
  $stored_results = array();
  $result = $mysqli->prepare("SELECT q_id, rating, q_parts FROM log4 WHERE q_paper=? AND userID=?");
  $result->bind_param('ii', $_GET['paperID'], $_GET['userID']);
  $result->execute();
  $result->bind_result($q_id, $rating,$q_parts);
  while ($row = $result->fetch()) {
    $stored_results[$q_id] = $rating;
    $stored_q_parts[$q_id] = $q_parts;
  }
  $result->close();
  
  $result = $mysqli->prepare("SELECT feedback, overall_rating FROM log4_overall WHERE q_paper=? AND userID=?");
  $result->bind_param('ii', $_GET['paperID'], $_GET['userID']);
  $result->execute();
  $result->bind_result($feedback, $overall_rating);
  $result->fetch();
  $result->close();

  // Get the questions.
  $question_no = 1;
  $sub_totals = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0);
  $cell_colors = array('#FF8080','#FFC169','#50E850');
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, notes, scenario, leadin, display_method FROM papers, questions WHERE paper=? AND papers.question=questions.q_id ORDER BY display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $q_type, $theme, $notes, $scenario, $leadin, $display_method);
  while ($row = $result->fetch()) {
    if ($question_no == 1) {
      // Header row
      $cols = substr_count($display_method, '|');
      $headings = explode('|', $display_method);
      echo '<tr><td></td>';
      for ($i=0; $i<$cols; $i++) {
        echo "<td style=\"width:80px; color:$labelcolor; font-weight:bold\">" . $headings[$i] . "</td>";
      }
      echo "</tr>\n";
    }
    if (trim($theme) != '') {
      echo "<tr><td colspan=\"4\" class=\"theme\">$theme</td></tr>\n";
    }
    echo "<tr id=\"row_" . $question_no . "\"><td class=\"question\">";
    if (trim($notes) != '') {
      echo "<span style=\"color:$labelcolor\"><img src=\"../artwork/notes_icon.gif\" width=\"14\" height=\"14\" border=\"0\" alt=\"note\" />&nbsp;$notes</span><br />\n";
    }
 
    echo parse_leadin($leadin,$stored_q_parts[$q_id]) . "</td>";
    $sub_totals[$stored_results[$q_id]]++; 
    for ($i=0; $i<$cols; $i++) {
      if (array_key_exists($q_id,$stored_results) and $stored_results[$q_id] == $i) {
        echo "<td style=\"background-color:" . $cell_colors[$i] . "\" class=\"rating\"><br />&nbsp;</td>";
      } else {
        echo "<td class=\"rating\"><br />&nbsp;</td>";
      }
    }
    echo "</tr>\n";
    $question_no++;
  }
  echo "<tr><td></td>";
  for ($i=0; $i<$cols; $i++) {
    echo "<td class=\"rating\"><input type=\"text\" name=\"fails\" size=\"4\" style=\"border:0px; text-align:right\" value=\"" . $sub_totals[$i] . "\" /></td>";
  }  
  echo "</tr></table>\n<br /><div><strong>" . $string['overallclassification'] . "</strong></div><input type=\"hidden\" name=\"overallscore\" id=\"overallscore\" value=\"0\" /><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr id=\"row_overall\">";
  $result->close();

  switch ($marking) {
    case '3':
      $labels = array('Clear Fail','Borderline','Clear Pass');
      $colors = array('#FF8080','#FFC169','#50E850');
      break;
    case '4':
      $labels = array('Fail','Borderline Fail','Borderline pass','Pass','Good Pass');
      $colors = array('#FF2B2B','#FF8080','#FFC169','#50E850','#1DB11D');
      break;
    case '5':
      $labels = array('Unsatisfactory','Competent');
      $colors = array('#FF8080','#50E850');
      break;
    case '6':
      $labels = array('Clear FAIL','BORDERLINE','Clear PASS','Honours PASS');
      $colors = array('#FF2B2B','#FF8080','#50E850','#1DB11D');
      break;
  }
  for ($i=0; $i<count($labels); $i++) {
    if ($overall_rating == ($i+1)) {
      echo "<td class=\"overall\" style=\"background-color:" . $colors[$i] . "\">" . $string[strtolower($labels[$i])] . "</td>\n";
    } else {
      echo "<td class=\"overall\">" . $string[strtolower($labels[$i])] . "</td>\n";
    }
  }
  ?>
  </tr></table>  

  <br />
  <div><strong><?php echo $string['feedback']; ?></strong></div>
  <textarea name="feedback" id="feedback" style="font-family:Arial,sans-serif; border:1px solid #7F9DB9; width:100%" cols="60" rows="4"><?php echo $feedback; ?></textarea>
<?php
  $mysqli->close();
?>
</body>
</html>
