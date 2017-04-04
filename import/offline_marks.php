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
require_once '../include/question_types.php';
require_once '../include/errors.php';

ini_set("auto_detect_line_endings", true);

$paperID = check_var('paperID', 'GET', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

function marks_from_file($fileName, $paperID, $string, $properties, $db) {
  $configObject = Config::get_instance();
  $configObject->get('cfg_tmpdir');
  
  $userObject = UserObject::get_instance();
  
  // Get properties of the paper.
  $session = $properties->get_calendar_year();
  $paper_date = $properties->get_raw_start_date();

  $moduleIDs = Paper_utils::get_modules($paperID, $db);

  // Get the questions on the paper.
  $paper = array();
  $question_no = 0;
  $result = $db->prepare("SELECT question, sum(marks_correct) AS sum FROM papers, options WHERE paper = ? AND papers.question = options.o_id GROUP BY question ORDER BY screen, display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($question, $marks_correct);
  while ($result->fetch()) {
    $question_no++;
    $paper[$question_no]['id'] = $question;
    $paper[$question_no]['marks_correct'] = $marks_correct;
  }
  $result->close();

  // Get student data.
  $students = array();
  $modids = implode(',', array_keys($moduleIDs));
  $result = $db->prepare("SELECT users.id, student_id, username, yearofstudy, grade, title, surname, first_names FROM users, sid, modules_student WHERE users.id = sid.userID AND users.id = modules_student.userID AND idMod IN ($modids) AND calendar_year = ?");
  $result->bind_param('s', $session);
  $result->execute();
  $result->bind_result($id, $student_id, $username, $year, $grade, $title, $surname, $first_names);
  while ($result->fetch()) {
    $students[$student_id]['username']    = $username;
    $students[$student_id]['title']       = $title;
    $students[$student_id]['surname']     = $surname;
    $students[$student_id]['first_names'] = $first_names;
    $students[$student_id]['year']        = $year;
    $students[$student_id]['grade']       = $grade;
    $students[$student_id]['id']          = $id;
  }
  $result->close();

  $lines = file($fileName);
  $line_written = 0;
  echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"margin-left:10px; border-collapse:collapse\">\n";

  foreach ($lines as $separate_line) {
    $error = '';

    if ($line_written == 0 and isset($_POST['header_row']) and $_POST['header_row'] == 1) {  // Write out the header line.
      $fields = explode(',', $separate_line);
      echo "<tr><th></th><th colspan=\"3\">Student Name</th>";
      foreach ($fields as $field) {
        if (trim($field) != '') {
          echo "<th>$field</th>";
        }
      }
      echo "</tr>\n";
    }
    
    if ((!isset($_POST['header_row']) or $_POST['header_row'] != 1 ) or $line_written > 0) {
      $fields = explode(',', $separate_line);
      $sid = trim($fields[0]);
      if (!isset($students[$sid]['username'])) {  // Student is not in class List.
        // Look up to see if anywhere else in Authentication database.
        $result = $db->prepare("SELECT id, student_id, users.username, yearofstudy, grade, title, surname, first_names FROM users, sid WHERE users.id = sid.userID AND sid.student_id = ?");
        $result->bind_param('s', $sid);
        $result->execute();
        $result->store_result();
        $result->bind_result($id, $student_id, $username, $year, $grade, $title, $surname, $first_names);
        if ($result->num_rows > 0) {
          $result->fetch();
          $students[$student_id]['username']    = $username;
          $students[$student_id]['title']       = $title;
          $students[$student_id]['surname']     = $surname;
          $students[$student_id]['first_names'] = $first_names;
          $students[$student_id]['year']        = $year;
          $students[$student_id]['grade']       = $grade;
          $students[$student_id]['id']          = $id;
        }
        $result->close();
      }
      
      if (isset($students[$sid]) and $students[$sid]['username'] != '') {  // Student is in class List.
      
        $save_ok = true;
        $db->autocommit(false);

        $result = $db->prepare("SELECT id FROM log_metadata WHERE userID = ? AND paperID = ? AND started = ?");
        $result->bind_param('iis', $students[$sid]['id'], $paperID, $paper_date);
        $result->execute();
        $result->store_result();
        $result->bind_result($lmd_id);
        if ($result->num_rows > 0) {
          $result->fetch();
          $delete1 = $db->prepare("DELETE FROM log5 WHERE metadataID = ?");
          $delete1->bind_param('i', $lmd_id);
          $res = $delete1->execute();
          if ($res == false) {
            $save_ok = false;
          }
          $delete1->close();

          if ($save_ok) {
            $delete2 = $db->prepare("DELETE FROM log_metadata WHERE id = ?");
            $delete2->bind_param('i', $lmd_id);
            $res = $delete2->execute();
            if ($res == false) {
              $save_ok = false;
            }
            $delete2->close();
          }
        }
        $result->close();

        //
        // did the all the save to log operations succeed?
        //
        if ($save_ok === false) {
          //NO - rollback
          $db->rollback();
          $error = $string['errorsaving'];
          break;
        } else {
          //YES - commit the updates to the log tables
          $db->commit();
        }

        $result = $db->prepare("INSERT INTO log_metadata (userID, paperID, started, ipaddress, student_grade, year, attempt) "
          . "VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ip = '127.0.0.1';
        $attempt = 1;
        $result->bind_param('iisssii', $students[$sid]['id'], $paperID, $paper_date, $ip, $students[$sid]['grade'],
          $students[$sid]['year'], $attempt);
        $res = $result->execute();
        if ($res == false) {
          $save_ok = false;
        } else {
          $lmd_id = $db->insert_id;
        }
        $result->close();

        if ($save_ok) {
          echo "<tr><td><img src=\"../artwork/green_plus_16.png\" wodth=\"16\" height=\"16\" alt=\"Add\" /></td><td>" . $students[$sid]['title'] . "</td><td>" . $students[$sid]['surname'] . "</td><td>" . $students[$sid]['first_names'] . "</td><td>$sid</td>";
          for ($q=1; $q<=$question_no; $q++) {
            $result = $db->prepare("INSERT INTO log5 (q_id, mark, adjmark, totalpos, metadataID) VALUES (?, ?, ?, ?, ?)");
            $mark = trim($fields[$q]);
            if ($mark > $paper[$q]['marks_correct']) {
              $save_mark = NULL;
            } else {
              $save_mark = floatval($mark);
            }
            $result->bind_param('iddii', $paper[$q]['id'], $save_mark, $save_mark, $paper[$q]['marks_correct'], $lmd_id);
            $res = $result->execute();
            if ($res == false) {
              echo "<td>error</td>";
              $save_ok = false;
              break;
            } else {
              if ($mark > $paper[$q]['marks_correct']) {
                echo '<td class="failed">too high</td>';
              } elseif ($mark === '') {
                echo '<td class="failed">missing</td>';
              } else {
                echo "<td class=\"num\">$mark</td>";
              }
            }
            $result->close();
          }
          echo "</tr>\n";
        }

        //
        // did the all the save to log operations succeed?
        //
        if ($save_ok === false) {
          //NO - rollback
          $db->rollback();
          $error = $string['errorsaving'];
          break;
        } else {
          //YES - commit the updates to the log tables
          $db->commit();
        }
      } else {
        echo "<tr><td><img src=\"../artwork/red_cross_16.png\" wodth=\"16\" height=\"16\" alt=\"Failed\" /></td><td colspan=\"3\" class=\"failed\">Student not found.</td><td>$sid</td><td colspan=\"" . $question_no . "\" class=\"failed\">&nbsp;</td></tr>";
      }
    }

    $line_written++;
  }
  //if ($error != '') {
  //  echo "<li style=\"color:C00000\">$error</li>";
  //}

  echo "</table>\n";
  
  //turn auto commit back on so future queries function as before
  $db->autocommit(true);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['uploadmarks']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/screen.css" />
  <style type="text/css">
    <?php
    if (isset($_POST['submit'])) echo 'td {border:1px solid #C0C0C0}';
    ?>
    .num {text-align:right}
    .failed {background-color:#FFC0C0; color:#C00000; font-weight:bold}
    #content * {margin:auto; padding:auto};
  </style></head>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<body onclick="hideMenus()">
<?php
  require '../include/paper_options.php';
  require '../include/toprightmenu.inc';

  echo draw_toprightmenu();
?>
<div id="content">
<?php
echo "<div class=\"head_title\">\n";
echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
echo "<div class=\"breadcrumb\">";
$modutils = module_utils::get_instance();
echo '<a href="../index.php">' . $string['home'] . '</a>';
if ($module != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $module . '">' . $modutils->get_moduleid_from_id($module, $mysqli) . '</a>';
} elseif ($folder != '') {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $folder . '">' . $folder_name . '</a>';
}
echo "</div><div class=\"page_title\">" . $string['importmarks'] . "</div>";
echo "</div>";
if (isset($_POST['submit']) and $_POST['submit']) {
  if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
    if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_username() . "_spotter_marks.csv"))  {
      echo uploadError($_FILES['csvfile']['error']);
      exit;
    } else {
      marks_from_file( $configObject->get('cfg_tmpdir') . $userObject->get_username() . '_spotter_marks.csv', $paperID, $string, $properties, $mysqli);
      unlink( $configObject->get('cfg_tmpdir') . $userObject->get_username() . '_spotter_marks.csv');
      ?>
      <!DOCTYPE html>
      <html>
      <head>
      <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
      <title><?php echo $string['uploadmarks']; ?></title>
      </head>
      <body>
      <p><?php echo $string['marksloaded']; ?></p>
      <p><input type="submit" name="submit" onclick="window.location='../paper/details.php?paperID=<?php echo $paperID; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'" value="OK" style="width:100px" /></p>
      <?php
    }
  }
} else {
?>

<br />
<br />

<table class="dialog_border" style="width:600px">
<tr>
<td class="dialog_header" style="width:52px"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header" style="width:90%"><?php echo $string['uploadmarks']; ?></td>
</tr>
<tr>
<td class="dialog_body" colspan="2">

<p><?php echo $string['msg1']; ?></p>

<div><?php echo $string['msg2']; ?></div>


<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $paperID; ?>&amp;folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>" enctype="multipart/form-data" autocomplete="off">

<p><input type="file" size="50" name="csvfile" /><br />
<input type="checkbox" name="header_row" value="1" checked />&nbsp;<?php echo $string['headerrow']; ?></p>

<p><input type="submit" style="width:150px" value="<?php echo $string['uploadmarks']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
</form>
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