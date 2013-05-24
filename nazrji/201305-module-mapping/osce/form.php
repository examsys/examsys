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
require './osce.inc';
require_once '../classes/userutils.class.php';
require_once '../classes/paperproperties.class.php';

check_var('id', 'GET', true, false, false);

if (isset($_REQUEST['userID'])) {
  $userID = $_REQUEST['userID'];
  
  if (!UserUtils::userid_exists($userID, $mysqli)) {   // Check the passed through user ID actually exists.
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
}

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli);
if ($propertyObj == false) {  // No properties found, this crypt_name
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Get properties of the paper.
$paperID      = $propertyObj->get_property_id();
$marking      = $propertyObj->get_marking();
$start_date   = $propertyObj->get_start_date();
$end_date     = $propertyObj->get_end_date();
$number_of_qs = $propertyObj->get_question_no();


if (isset($_POST['submit'])) {
  if ($_POST['userID'] != '') {
    $started = DATE('YmdHis');
    $total_score = 0;

    // Delete any Log4 previous submissions for this student.
    $result = $mysqli->prepare("DELETE FROM log4 WHERE q_paper = ? AND userID = ?");
    $result->bind_param('ii', $paperID, $userID);
    $result->execute();

    // Delete any Log4_overall record for this student.
    $result = $mysqli->prepare("DELETE FROM log4_overall WHERE q_paper = ? AND userID = ?");
    $result->bind_param('ii', $paperID, $userID);
    $result->execute();

    // Write individual ratings into Log4.
    for ($question = 1; $question <= $number_of_qs; $question++) {
      $tmp_val = ($_POST['q' . $question . '_val'] - 1);
      if (isset( $_POST[$_POST['q' . $question . '_id'] . '_parts'] )) {
        $q_parts = $_POST[$_POST['q' . $question . '_id'] . '_parts'];
      } else {
        $q_parts = '';
      }
      $result = $mysqli->prepare("INSERT INTO log4 VALUES (NULL, ?, ?, ?, ?, ?, ?)");
      $result->bind_param('isssss', $userID, $started, $paperID, $_POST['q' . $question . '_id'], $tmp_val, $q_parts);
      $result->execute();
      $result->close();
      $total_score += ($_POST['q' . $question . '_val'] - 1);
    }

    // Write summary information into Log4_overall.
    if ($marking == '5') {
      if ($total_score < 12) {
        $overall_val = '1';
      } else {
        $overall_val = '2';
      }
    } else {
      $overall_val = $_POST['overall_val'];
    }
    $result = $mysqli->prepare("INSERT INTO log4_overall VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, 'electronic', ?)");
    $result->bind_param('isisissii', $userID, $started, $paperID, $overall_val, $total_score, $_POST['fback'], $_POST['grade'], $userObject->get_user_ID(), $_POST['year']);
    $result->execute();
    $result->close();
  }
  
  // Redirect back to the class list to get the next student.
  header("location: " . $configObject->get('cfg_root_path') . "/osce/class_list.php?id=" . $_GET['id']);
  exit;
} else {
  // Get the module ID and calendar year of the OSCE station.
  if (isset($_GET['username']) and $_GET['username'] == 'test') {
    $title = 'Mr';
    $surname = 'Student';
    $first_names = 'Test';
    $student_id = '0123456';
    $username = 't;est';
    $grade = 'A100';
    $year = '1';
    $test = true;
  } else {
    $result = $mysqli->prepare("SELECT username, title, surname, first_names, grade, yearofstudy, student_id FROM (users, sid) WHERE users.id = ? AND users.id = sid.userID");
    $result->bind_param('i', $userID);
    $result->execute();
    $result->bind_result($username, $title, $surname, $first_names, $grade, $year, $student_id);
    $result->fetch();
    $result->close();
    $test = false;
  }

  
  // Check time security
  if ($test == false) {
    if (time() < $start_date or time() > $end_date) {
      echo "<html><head>\n<title>".$string['Access Denied']."</title>\n<style type=\"text/css\">\nbody {font-size:120%;font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#4465A2;font-size:140%}\n</style></head>\n<body style=\"font-family:Arial,sans-serif\"><div style=\"position:absolute;left:10px;top:10px\"><img src=\"../artwork/clock_48.png\" width=\"48\" height=\"48\" /></div>\n";
      echo "<h1 style=\"margin-left:60px\">".$string['Access Denied']."</h1>\n";
      echo "<hr size=\"1\" align=\"left\" width=\"500\" noshade=\"noshade\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0;height:1px;border:0px\" />\n<p style=\"margin-left:60px\">" . $string['paperavailable'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>From - " . date('d/m/Y H:i',$start_date) . "</li>\n<li>To - " . date('d/m/Y H:i',$end_date) . "</li>\n</ul>\n<br /><p style=\"margin-left:60px\"v><form><input type=\"button\" value=\"&lt; Back\" style=\"width:100px\" name=\"back\" onclick=\"history.back();\"></form></p>\n</body>\n</html>";
      $mysqli->close();
      exit;
    }
  }
?>
<!DOCTYPE html>
<html>
  <head>
  <?php
  if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') or strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
    echo "  <meta name=\"viewport\" content=\"user-scalable=no\">\n";
  } else {
    echo "  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n";
  }
  ?>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  
  <title><?php echo $string['osceform']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/osce.css" />
  <style type="text/css">
  <?php
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') or strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
      echo "body {background-color:" . $propertyObj->get_bgcolor() . "; color:" . $propertyObj->get_fgcolor() . "; font-size:100%}\n";
    } else {
      echo "body {background-color:" . $propertyObj->get_bgcolor() . "; color:" . $propertyObj->get_fgcolor() . "; font-size:90%}\n";
    }
    ?>
    .t {color:<?php echo $propertyObj->get_themecolor(); ?>}
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript">
    function ans(q_id, rating) {
      document.getElementById('q' + q_id + '_val').value = rating;
      if (rating == 1) {
        document.getElementById('c' + q_id + '_1').style.backgroundColor = '#D99594';
        document.getElementById('c' + q_id + '_2').style.backgroundColor = '';
        if (document.getElementById('c' + q_id + '_3')) {
          document.getElementById('c' + q_id + '_3').style.backgroundColor = '';
        }
      } else if (rating == 2) {
        document.getElementById('c' + q_id + '_2').style.backgroundColor = '#FABF8F';
        document.getElementById('c' + q_id + '_1').style.backgroundColor = '';
        if (document.getElementById('c' + q_id + '_3')) {
          document.getElementById('c' + q_id + '_3').style.backgroundColor = '';
        }
      } else if (rating == 3) {
        document.getElementById('c' + q_id + '_3').style.backgroundColor = '#C2D69B';
        document.getElementById('c' + q_id + '_1').style.backgroundColor = '';
        document.getElementById('c' + q_id + '_2').style.backgroundColor = '';
      }

      checkTotals();
    }

    function checkTotals() {
      var rated = 0;
      var fails = 0;
      var borderlines = 0;
      var passes = 0;
      for (i=1; i<=<?php echo $number_of_qs; ?>; i++) {
        if (document.getElementById('q' + i + '_val').value == '1') {
          fails++;
        } else if (document.getElementById('q' + i + '_val').value == '2') {
          borderlines++;
        } else if (document.getElementById('q' + i + '_val').value == '3') {
          passes++;
        }
      }
      rated = fails + borderlines + passes;

      document.getElementById('fails').value = fails;
      document.getElementById('borderlines').value = borderlines;
      document.getElementById('passes').value = passes;

   <?php
     if ($marking == '5') {
       echo "if (rated == document.getElementById('q_no').value) {\n";
     } else {
       echo "if (rated == document.getElementById('q_no').value && document.getElementById('overall_val').value != '0') {\n";
     }
   ?>
        document.osceform.submit.disabled = false;
      } else {
        document.osceform.submit.disabled = true;
      }
    }

    function overallset(q_id, rating) {
      var colors=new Array();
      <?php
      switch ($marking) {
        case '3':
          $labels = $string['marking3'];
          $colors = array('#D99594', '#FABF8F', '#C2D69B');
          break;
        case '4':
          $labels = $string['marking4'];
          $colors = array('#D99694', '#E5B9B7', '#FFC169', '#D7E3BC', '#C2D69B');
          break;
        case '5':
          $labels = $string['marking5'];
          $colors = array('#D99594', '#C2D69B');
          break;
        case '6':
          $labels = $string['marking6'];
          $colors = array('#D99694', '#E5B9B7', '#D7E3BC', '#C2D69B');
          break;
      }
      for ($i=0; $i<count($colors); $i++) {
        echo "colors[" . ($i+1) . "]=\"" . $colors[$i] ."\";\n";
      }
      ?>

      for (i=1; i<colors.length; i++) {
        if (i == rating) {
          document.getElementById('overall' + i).style.backgroundColor = colors[i];
        } else {
          document.getElementById('overall' + i).style.backgroundColor = '';
        }
      }
      document.getElementById('overall_val').value = rating;
      checkTotals();
    }
    
    $(document).ready(checkTotals);
  </script>
  </head>

  <body>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $_GET['id']; ?>" name="osceform">
  <table cellpadding="0" cellspacing="0" border="0" style="width:100%"><tr>
<?php
  if (file_exists($cfg_web_root . 'users/photos/' . $username . '.jpg')) {
    echo '<td class="photo"><img src="/users/photos/' . $username . '.jpg" width="90" height="135" style="border:1px solid #7F9DB9" alt="Photo" /></td>';
  } else {
    echo '<td class="photo"><img src="./test_photo.png" width="90" height="135" border="1" alt="Photo" /></td>';
  }
  echo "<td style=\"vertical-align:top; font-weight:bold; text-align:left\"><div class=\"title\">" . $propertyObj->get_paper_title() . "</div><br /><br /><div style=\"font-size:150%\">$title $surname, <span style=\"color:#808080\">$first_names</span></div><span style=\"color:#808080\">($student_id)</span></td></table>\n<table cellpadding=\"2\" cellspacing=\"0\" style=\"width:100%\">";

  if ($test == false) {
    // Query Log4 just in case form has already been submitted for this user.
    $stored_results = array();
    $result = $mysqli->prepare("SELECT q_id, rating, q_parts FROM log4 WHERE q_paper = ? AND userID = ?");
    $result->bind_param('ii', $paperID, $userID);
    $result->execute();
    $result->bind_result($q_id, $rating, $q_parts);
    while ($result->fetch()) {
      $stored_results[$q_id] = $rating;
      $stored_q_parts[$q_id] = $q_parts;
    }
    $result->close();

    $result = $mysqli->prepare("SELECT feedback, overall_rating FROM log4_overall WHERE q_paper = ? AND userID = ?");
    $result->bind_param('ii', $paperID, $userID);
    $result->execute();
    $result->bind_result($feedback, $overall_rating);
    $result->fetch();
    $result->close();
  }

  // Get the questions.
  $question_no = 1;
  $cell_colors = array('#D99594','#FABF8F','#C2D69B');
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, notes, scenario, leadin, display_method FROM papers, questions WHERE paper = ? AND papers.question=questions.q_id ORDER BY display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($q_id, $q_type, $theme, $notes, $scenario, $leadin, $display_method);
  while ($result->fetch()) {
    if ($question_no == 1) {
      // Header row
      $cols = substr_count($display_method, '|');
    }

    if (trim($theme) != '') echo "<tr><td colspan=\"4\" class=\"t\">$theme</td></tr>\n";

    echo "<tr><td class=\"q\">";
    if (trim($notes) != '') {
      echo "<span style=\"color:" . $propertyObj->get_labelcolor() . "\"><img src=\"../artwork/small_note_icon.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"note\" />&nbsp;$notes</span><br />\n";
    }
    echo strip_tags($leadin, '<b><i><strong><em><br><br />');
    if (isset($stored_results[$q_id])) {
      echo "<input type=\"hidden\" name=\"q" . $question_no . "_val\" id=\"q" . $question_no . "_val\" value=\"" . ($stored_results[$q_id] + 1) . "\">";
    } else {
      echo "<input type=\"hidden\" name=\"q" . $question_no . "_val\" id=\"q" . $question_no . "_val\" value=\"0\">";
    }
    echo "<input type=\"hidden\" name=\"q" . $question_no . "_id\" value=\"$q_id\"></td>";
    
    for ($i=0; $i<$cols; $i++) {
      if (isset($stored_results[$q_id]) and $stored_results[$q_id] == $i) {
        echo "<td style=\"background-color:" . $cell_colors[$i] . "\" class=\"r\" id=\"c" . $question_no . "_" . ($i+1) . "\" onclick=\"ans($question_no," . ($i+1) . ")\">$i</td>";
      } else {
        echo "<td class=\"r\" id=\"c" . $question_no . "_" . ($i+1) . "\" onclick=\"ans($question_no," . ($i+1) . ")\">$i</td>";
      }
    }
    echo "</tr>\n";
    $question_no++;
  }
  $result->close();
  
  if ($cols == 2) {
    echo "<tr><td></td><td class=\"totals r\"><input type=\"text\" name=\"fails\" id=\"fails\" size=\"4\" style=\"font-size:60%; font-weight:bold; border:0px; text-align:right; background-color:#EAEAEA\" value=\"0\" /></td><td class=\"totals r\"><input type=\"text\" name=\"borderlines\" size=\"4\" id=\"borderlines\" style=\"font-size:60%; font-weight:bold; border:0px; text-align:right; background-color:#EAEAEA\" value=\"0\" /></td></tr>\n";
  } else {
    echo "<tr><td></td><td class=\"totals r\"><input type=\"text\" name=\"fails\" id=\"fails\" size=\"4\" style=\"font-size:60%; font-weight:bold; border:0px; text-align:right; background-color:#EAEAEA\" value=\"0\" /></td><td class=\"totals r\"><input type=\"text\" name=\"borderlines\" size=\"4\" id=\"borderlines\" style=\"font-size:60%; font-weight:bold; border:0px; text-align:right; background-color:#EAEAEA\" value=\"0\" /></td><td class=\"totals r\"><input type=\"text\" name=\"passes\" size=\"4\" id=\"passes\" style=\"font-size:60%; font-weight:bold; border:0px; text-align:right; background-color:#EAEAEA\" value=\"0\" /></td></tr>\n";
  }
  
  if ($marking == '3' or $marking == '4' or $marking == '6') {
    if (!isset($overall_rating)) $overall_rating = '0';
    echo "<tr><td colspan=\"4\" style=\"text-align:left\">" . $propertyObj->get_paper_postscript() . "</td></tr><tr><td colspan=\"4\" style=\"font-weight:bold; text-align:left\">" . $string['overallclassification'] . "<input type=\"hidden\" name=\"overall_val\" id=\"overall_val\" value=\"" . $overall_rating . "\" /></td></tr><tr><td colspan=\"4\" id=\"overall\"><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr>";

    for ($i=0; $i<count($labels); $i++) {
      echo '<td';
      if (($i+1) == $overall_rating) echo ' style="background-color:' . $colors[$i] . '"';
      echo ' class="overall" id="overall' . ($i+1). '" onclick="overallset(' . $question_no . ',\'' . ($i+1). '\')">' . $labels[$i] . '</td>';
    }
    echo "</tr></table>\n</td></tr>";
  }
  echo "</table>\n";
?>
  <br />
  <blockquote>
  <div><strong><?php echo $string['feedback']; ?></strong></div>
  <textarea name="fback" id="fback" style="border:1px solid #C0C0C0; width:100%" cols="60" rows="4"><?php if (isset($feedback)) echo $feedback; ?></textarea>
  </blockquote>
  <br />
  <?php
    // For external examiners just close the window without saving.
    if ($userObject->has_role('External Examiner')) {
  ?>
    <div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" style="font-size:120%; width:120px; height:35px; font-weight:bold" onclick="window.close(); return false;" disabled /><input type="hidden" name="q_no" id="q_no" value="<?php echo ($question_no - 1); ?>" /><input type="hidden" name="userID" value="<?php if (isset($userID)) echo $userID; ?>" /><input type="hidden" name="grade" value="<?php echo $grade; ?>" /><input type="hidden" name="year" value="<?php echo $year; ?>" /></div>
  <?php
    } else {
  ?>
    <div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" style="font-size:120%; width:120px; height:35px; font-weight:bold" disabled /><input type="hidden" name="q_no" id="q_no" value="<?php echo ($question_no - 1); ?>" /><input type="hidden" name="userID" value="<?php if (isset($userID)) echo $userID; ?>" /><input type="hidden" name="grade" value="<?php echo $grade; ?>" /><input type="hidden" name="year" value="<?php echo $year; ?>" /></div>
  <?php
  }
  ?>
  </form>
<?php
  $mysqli->close();
?>
</body>
</html>
<?php
  }
?>