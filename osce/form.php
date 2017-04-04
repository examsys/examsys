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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';
require './osce.inc';

check_var('id', 'GET', true, false, false);

if (isset($_REQUEST['userID'])) {
  $userID = $_REQUEST['userID'];

  if (!UserUtils::userid_exists($userID, $mysqli)) {   // Check the passed through user ID actually exists.
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
}

// Get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($_GET['id'], $mysqli, $string, true);

$paperID      = $propertyObj->get_property_id();
$marking      = $propertyObj->get_marking();
$start_date   = $propertyObj->get_start_date();
$end_date     = $propertyObj->get_end_date();
$number_of_qs = $propertyObj->get_question_no();

$killer_questions = new Killer_question($paperID, $mysqli);
$killer_questions->load();
  

if (isset($_POST) and count($_POST) > 0) {
  
  if (!isset($_GET['dont_record'])) {
    save_osce_form($propertyObj, $userID, $_POST, $mysqli);
  }
  if (isset($_GET['dont_redirect']) and $_GET['dont_redirect'] == true) {
    // Output the randomID so the JavaScript can check for success
    echo $_GET['rnd'];
  } else {
    // Redirect back to the class list to get the next student.
    header("location: " . $configObject->get('cfg_root_path') . "/osce/class_list.php?id=" . $_GET['id']);
  }

  exit();
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
      echo "<html><head>\n<title>" . $string['Access Denied'] . "</title>\n<style type=\"text/css\">\nbody {font-size:120%;font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#C00000;font-size:140%}\n</style></head>\n<body style=\"font-family:Arial,sans-serif\"><div style=\"position:absolute;left:10px;top:10px\"><img src=\"../artwork/summative_scheduling.png\" width=\"48\" height=\"48\" /></div>\n";
      echo "<h1 style=\"margin-left:60px\">" . $string['Access Denied'] . "</h1>\n";
      echo "<hr size=\"1\" align=\"left\" width=\"500\" noshade=\"noshade\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0;height:1px;border:0\" />\n<p style=\"margin-left:60px\">" . $string['paperavailable'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['from'] . " - " . date('d/m/Y H:i',$start_date) . "</li>\n<li>" . $string['to'] . " - " . date('d/m/Y H:i',$end_date) . "</li>\n</ul>\n<br /><p style=\"margin-left:60px\"v><form autocomplete=\"off\"><input type=\"button\" value=\"&lt; Back\" style=\"width:100px\" name=\"back\" onclick=\"history.back();\"></form></p>\n</body>\n</html>";
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
      echo "body {background-color:" . $propertyObj->get_bgcolor() . "; margin-bottom:15px; color:" . $propertyObj->get_fgcolor() . "; font-size:100%}\n";
    } else {
      echo "body {background-color:" . $propertyObj->get_bgcolor() . "; margin-bottom:15px; color:" . $propertyObj->get_fgcolor() . "; font-size:90%}\n";
    }
    ?>
    .t {color:<?php echo $propertyObj->get_themecolor(); ?>}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    function ans(q_id, rating) {
      $('#q' + q_id + '_val').val(rating);
      if (rating == 1) {
        $('#c' + q_id + '_1').css('background-color', '#D99594');
        $('#c' + q_id + '_2').css('background-color', '');
        if (document.getElementById('c' + q_id + '_3')) {
          $('#c' + q_id + '_3').css('background-color', '');
        }
      } else if (rating == 2) {
        $('#c' + q_id + '_2').css('background-color', '#FABF8F');
        $('#c' + q_id + '_1').css('background-color', '');
        if (document.getElementById('c' + q_id + '_3')) {
          $('#c' + q_id + '_3').css('background-color', '');
        }
      } else if (rating == 3) {
        $('#c' + q_id + '_3').css('background-color', '#C2D69B');
        $('#c' + q_id + '_1').css('background-color', '');
        $('#c' + q_id + '_2').css('background-color', '');
      }

      checkTotals();
    }

    function checkTotals() {
      var rated = 0;
      var fails = 0;
      var borderlines = 0;
      var passes = 0;
      for (i=1; i<=<?php echo $number_of_qs; ?>; i++) {
        if ($('#q' + i + '_val').val() == '1') {
          fails++;
        } else if ($('#q' + i + '_val').val() == '2') {
          borderlines++;
        } else if ($('#q' + i + '_val').val() == '3') {
          passes++;
        }
      }
      rated = fails + borderlines + passes;

      $('#fails').val(fails);
      $('#borderlines').val(borderlines);
      $('#passes').val(passes);

   <?php
     if ($marking == '5') {
       echo "if (rated == $('#q_no').val()) {\n";
     } else {
       echo "if (rated == $('#q_no').val() && $('#overall_val').val() != '0') {\n";
     }
   ?>
        $('#save').prop('disabled', false);

      } else {
        $('#save').prop('disabled', true);

      }
    }

    function overallset(q_id, rating) {
      var colors = new Array();
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
        case '7':
          $labels = $string['marking7'];
          $colors = array('#D99594', '#C2D69B');
          break;
      }
      for ($i=0; $i<count($colors); $i++) {
        echo "colors[" . ($i+1) . "]=\"" . $colors[$i] ."\";\n";
      }
      ?>

      for (i=1; i<colors.length; i++) {
        if (i == rating) {
          $('#overall' + i).css('background-color', colors[i]);
        } else {
          $('#overall' + i).css('background-color', '');
        }
      }
      $('#overall_val').val(rating);
      checkTotals();
    }

    $(document).ready(checkTotals);
  </script>
  <?php require './ajaxsave.js.php'; ?>
  </head>

  <body>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $_GET['id']; ?>" id="osceform" name="osceform" autocomplete="off">
  <table cellpadding="0" cellspacing="0" border="0" style="width:100%"><tr>
<?php
$photodirectory = rogo_directory::get_directory('user_photo');
$photoname = UserUtils::student_photo_exist($username);
  if ($photoname) {
    $photo_size = getimagesize($photodirectory->fullpath($photoname));
    echo '<td class="photo"><img src="' . $photodirectory->url($photoname) . '" ' . $photo_size[3] . ' alt="Photo" /></td>';
  } else {
    echo '<td></td>';
  }
  echo "<td style=\"vertical-align:top; text-align:left\"><div class=\"osce_title\">" . $propertyObj->get_paper_title() . "</div><div class=\"student_name\">$title $surname, <span style=\"color:#808080\">$first_names</span></div><div class=\"student_id\">($student_id)</div></td></table>\n<table cellpadding=\"2\" cellspacing=\"0\" style=\"width:100%\">";

  if ($test == false) {
    // Query Log4 just in case form has already been submitted for this user.
    $stored_results = array();
    $result = $mysqli->prepare("SELECT q_id, rating, q_parts, feedback, overall_rating FROM log4, log4_overall WHERE log4.log4_overallID = log4_overall.id AND q_paper = ? AND userID = ?");
    $result->bind_param('ii', $paperID, $userID);
    $result->execute();
    $result->bind_result($q_id, $rating, $q_parts, $feedback, $overall_rating);
    while ($result->fetch()) {
      $stored_results[$q_id] = $rating;
      $stored_q_parts[$q_id] = $q_parts;
    }
    $result->close();
  }

  // Get the questions.
  $question_no = 1;
  $cell_colors = array('#D99594', '#FABF8F', '#C2D69B');
	
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, notes, scenario, leadin, display_method FROM papers, questions WHERE paper = ? AND papers.question = questions.q_id ORDER BY display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($q_id, $q_type, $theme, $notes, $scenario, $leadin, $display_method);
  while ($result->fetch()) {
    if ($question_no == 1) {				// Header row
      $cols = substr_count($display_method, '|');
    }

    if (trim($theme) != '') echo "<tr><td colspan=\"4\" class=\"t\">$theme</td></tr>\n";
    
    if ($killer_questions->is_killer_question($q_id)) {
      $killer = 'killer';
    } else {
      $killer = 'non_killer';
    }
    
    echo "<tr><td class=\"q {$killer}\">";
    if (trim($notes) != '') {
      echo "<span style=\"color:" . $propertyObj->get_labelcolor() . "\"><img src=\"../artwork/small_note_icon.png\" width=\"14\" height=\"14\" alt=\"note\" />&nbsp;$notes</span><br />\n";
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

  if ($marking == '3' or $marking == '4' or $marking == '6' or $marking == '7') {
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

  echo '<div id="saveError"><img src="../artwork/no_save.png" width="60" height="60" alt="Warning" /> <div><span style="color:#C42828; font-weight:bold">' .  $string['savefailed'] . '</span><br />' . $string['tryagain'] . '</div></div>';

    // For external examiners just close the window without saving.
    if ($userObject->has_role('External Examiner')) {
  ?>
    <div style="text-align:center"><input type="submit" name="submitButton" id="save" value="<?php echo $string['save']; ?>" class="ok" style="font-size:120%; height:35px; font-weight:bold" onclick="window.close(); return false;" disabled /><input type="hidden" name="q_no" id="q_no" value="<?php echo ($question_no - 1); ?>" /><input type="hidden" name="userID" value="<?php if (isset($userID)) echo $userID; ?>" /><input type="hidden" name="grade" value="<?php echo $grade; ?>" /><input type="hidden" name="year" value="<?php echo $year; ?>" /></div>
  <?php
    } else {
  ?>
    <div style="text-align:center"><input id="save" type="submit" name="submitButton" value="<?php echo $string['save']; ?>" class="ok" style="font-size:120%; height:35px; font-weight:bold" disabled /><input type="hidden" name="q_no" id="q_no" value="<?php echo ($question_no - 1); ?>" /><input type="hidden" name="userID" value="<?php if (isset($userID)) echo $userID; ?>" /><input type="hidden" name="grade" value="<?php echo $grade; ?>" /><input type="hidden" name="year" value="<?php echo $year; ?>" /></div>
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