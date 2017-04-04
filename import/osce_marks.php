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
require_once '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);
$moduleID = check_var('module', 'GET', true, false, true);
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string, false);

function marks_from_file($notice, $userObj, $paperID, $fileName, $db, $string) {
  $configObject = Config::get_instance();

  // Get the paper properties
  $propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $db, $string, false);
  if ($propertyObj == false) {  // No properties found
    unlink($configObject->get('cfg_tmpdir') . $userObj->get_user_ID() . '_osce_marks.csv');
    $notice->access_denied($db, $string, 'An error has occurred');    //this will exit php
  }

  $session    = $propertyObj->get_calendar_year();
  $paper_date = $propertyObj->get_raw_start_date();
  $marking    = $propertyObj->get_marking();

  // Get the questions on the paper.
  $paper = array();
  $question_no = 0;
  $result = $db->prepare("SELECT question, marks_correct FROM papers, options WHERE paper = ? AND papers.question = options.o_id ORDER BY screen, display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($question, $marks);
  while ($result->fetch()) {
    $question_no++;
    $paper[$question_no]['id'] = $question;
  }
  $result->close();

  $moduleIDs = implode(',', array_keys(Paper_utils::get_modules($paperID, $db)));

  // Get student data.
  $students = array();
  $result = $db->prepare("SELECT users.id, student_id, username, yearofstudy, grade FROM users, sid, modules_student WHERE users.id = sid.userID AND users.id = modules_student.userID AND idMod IN ($moduleIDs) AND calendar_year = ?");
  $result->bind_param('s', $session);
  $result->execute();
  $result->bind_result($id, $student_id, $username, $year, $grade);
  while ($result->fetch()) {
    $students[$student_id]['username'] = $username;
    $students[$student_id]['year'] = $year;
    $students[$student_id]['grade'] = $grade;
    $students[$student_id]['id'] = $id;
  }
  $result->close();

  $lines = file($fileName);
  $line_written = 0;
  if ($_POST['header_row'] == '1') {
    echo "<ol start=\"1\">\n";
  } else {
    echo "<ol>\n";
  }
  foreach ($lines as $separate_line) {
    if ($_POST['header_row'] != '1' or $line_written > 0) {
      $fields = explode(',',$separate_line);
      $sid = trim($fields[0]);
      if (!isset($students[$sid])) {  // Student is not in class List.
        // Look up to see if anywhere else in Authentication database.
        $result = $db->prepare("SELECT id, student_id, username, yearofstudy, grade FROM users, sid WHERE users.id = sid.userID AND sid.student_id = ?");
        $result->bind_param('s', $sid);
        $result->execute();
        $result->store_result();
        $result->bind_result($id, $student_id, $username, $year, $grade);
        if ($result->num_rows > 0) {
          $result->fetch();
          $students[$student_id]['username'] = $username;
          $students[$student_id]['year'] = $year;
          $students[$student_id]['grade'] = $grade;
          $students[$student_id]['id'] = $id;
        }
        $result->close();
      }
      if (isset($students[$sid]) and $students[$sid]['username'] != '') {  // Student is in class List.

        $save_ok = true;
        $db->autocommit(false);

        $result = $db->prepare("DELETE FROM log4 WHERE log4_overallID IN (SELECT id FROM log4_overall WHERE userID = ? AND q_paper = ?)");
        $result->bind_param('ii', $students[$sid]['id'], $paperID);
        $result->execute();
        $result->close();

        $result = $db->prepare("DELETE FROM log4_overall WHERE userID = ? AND q_paper = ?");
        $result->bind_param('ii', $students[$sid]['id'], $paperID);
        $result->execute();
        $result->close();

        echo "<li>$sid -&gt; " . $students[$sid]['username'] . ", $question_no</li>";

        // Record overall student/station details.
        $result = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $fields[$question_no+1] = trim($fields[$question_no+1]);
        $result->bind_param('s', $fields[$question_no+1]);
        $result->execute();
        $result->bind_result($examinerID);
        $result->fetch();
        $result->close();

        echo $fields[$question_no+1] . ', ' . $examinerID . '<br />';

        if ($examinerID == '') {
          $examinerID = $userObj->get_user_ID();
        }

        // Create empty overall record
        $result = $db->prepare("INSERT INTO log4_overall VALUES(NULL, ?, ?, ?, 0, 0, '', ?, ?, 'paper', ?)");
        $result->bind_param('isisii', $students[$sid]['id'], $paper_date, $paperID, $students[$sid]['grade'], $examinerID, $students[$sid]['year']);
        $res = $result->execute();
        if ($res == false) {
          $save_ok = false;
        }
        $result->close();

        if ($save_ok) {
          $log4_overall_id = $db->insert_id;

          // Record individual questions.
          $numeric_score = 0;
          $result = $db->prepare("INSERT INTO log4 VALUES(NULL, ?, ?, NULL, ?)");
          for ($q=1; $q<=$question_no; $q++) {
            $result->bind_param('isi', $paper[$q]['id'], $fields[$q], $log4_overall_id);
            $fields[$q] = trim($fields[$q]);
            $res = $result->execute();
            if ($res == false) {
              $save_ok = false;
              break;
            }
            $numeric_score += $fields[$q];
          }
          $result->close();

          if ($save_ok) {
            switch ($marking) {
              case '3':
                $cat2no = array('clear fail'=>1,'borderline'=>2,'clear pass'=>3);
                break;
              case '4':
                $cat2no = array('fail'=>1,'borderline fail'=>2,'borderline pass'=>3,'pass'=>4,'good pass'=>5);
                break;
              case '5':
                //automatic
                $cat2no = array('unsatisfactory'=>1,'competent'=>2);
                break;
              case '6':
                $cat2no = array('clear fail'=>1,'borderline'=>2,'clear pass'=>3,'honours pass'=>4);
                break;
            }
            if (isset($cat2no[strtolower(trim($fields[$question_no+2]))])) {
              $overall_rating = $cat2no[strtolower(trim($fields[$question_no+2]))];
            } else {
              $overall_rating = 'ERROR';
            }

            if (isset($fields[$question_no+3])) {
              $feedback = trim($fields[$question_no+3]);
            } else {
              $feedback = '';
            }

            $result = $db->prepare("UPDATE log4_overall SET overall_rating = ?, numeric_score = ?, feedback = ? WHERE id = ?");
            $result->bind_param('sisi', $overall_rating, $numeric_score, $feedback, $log4_overall_id);
            $res = $result->execute();
            if ($res == false) {
              $save_ok = false;
            }
            $result->close();
          }
        }

        if ($save_ok === false) {
          // rollback
          $db->rollback();
          echo "<li style=\"color:C00000\">$sid -&gt; " . sprintf($string['saveerror'], $sid) . "</li>";
        } else {
          // commit the updates to the log tables
          $db->commit();
        }

        //turn auto commit back on so future queries function as before
        $db->autocommit(true);

      } else {
        echo "<li style=\"color:#C00000\">$sid -&gt; {$string['usernotfound']}</li>";
      }
    }
    $line_written++;
  }
  echo "</ol>\n";
}

$gradebook = new gradebook($mysqli);
$graded = $gradebook->paper_graded($paperID);
if (!$graded and isset($_POST['submit'])) {
  if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
    if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_osce_marks.csv"))  {
      echo uploadError($_FILES['csvfile']['error']);
      exit;
    } else {
      marks_from_file($notice, $userObject, $paperID, $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_osce_marks.csv', $mysqli, $string);
      unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_osce_marks.csv');
      ?>
      <!DOCTYPE html>
      <html>
      <head>
      <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
      <title><?php echo $string['importoscemarks']; ?></title>
      </head>
      <body>
      <p><?php echo $string['marksloaded']; ?></p>
      <p><input type="submit" name="submit" onclick="window.location='../paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'" value="<?php echo $string['ok']; ?>" style="width:100px" /></p>
      <?php
    }
  }
} else {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['importoscemarks']; ?></title>

  <link rel="stylesheet" href="../css/header.css" type="text/css" />
  <link rel="stylesheet" href="../css/body.css" type="text/css">
  <link rel="stylesheet" href="../css/dialog.css" type="text/css">
  <link rel="stylesheet" href="../css/submenu.css" type="text/css">
  <style>
    span.killer {float: none}
  </style>	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
	<script>
    $(function () {
		  $('html').click(function() {
			  hideMenus();
      });
		});
	</script>
</head>
<?php
  require '../include/toprightmenu.inc';
  echo draw_toprightmenu();
?>
<body>
<?php
  require '../include/paper_options.php';
?>

<div id="content">
<div class="head_title">
<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
  // Create page breadcrumbs.
  echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
  if (!is_null($moduleID)) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $moduleID . '">' . module_utils::get_moduleid_from_id($moduleID, $mysqli) . '</a>';
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/type.php?module=' . $moduleID . '&type=' . $properties->get_paper_type() . '">' . Paper_utils::type_to_name($properties->get_paper_type(), $string) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?module=' . $moduleID . '&paperID=' . $paperID . '">' . $properties->get_paper_title() . '</a>';
  // Display page title.
  echo "</div><div class=\"page_title\">" . $string['importoscemarks'] . "</div>";
?>
</div>
<br />
<br />

<table class="dialog_border" style="width:600px">
<tr>
<td class="dialog_header" style="width:52px"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /></td>
<td class="dialog_header" style="width:90%"><?php echo $string['importoscemarks']; ?></td>
</tr>
<tr>
<td colspan="2" class="dialog_body">

<p><?php echo $string['topmsg']; ?></p>

<blockquote>ID, Q1, Q2, Q3..., Examiner, Classification</blockquote>

<div style="text-align:center"><img src="../artwork/osce_import.png" width="386" height="139" style="border:1px solid black" alt="<?php echo $string['import']; ?>" /></div>

<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $paperID; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>" enctype="multipart/form-data" autocomplete="off">

<p><strong><?php echo $string['csvfile']; ?></strong> <input type="file" size="50" name="csvfile" /><br />
<input type="checkbox" name="header_row" value="1" checked />&nbsp;<?php echo $string['headerrow']; ?></p>

<p><input type="submit" class="ok" value="<?php echo $string['import']; ?>" name="submit" /><input class="cancel" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
</form>
<?php
    if ($graded and isset($_POST['submit'])) {
        echo '<p>' . $string['gradedmsg'] . '</p>';
    }
?>
</div>
</td>
</tr>
</table>

</div>

</body>
</html>
<?php
}
$mysqli->close();
?>