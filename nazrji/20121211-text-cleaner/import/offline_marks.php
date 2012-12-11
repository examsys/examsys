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
  require '../include/question_types.inc';
  require '../include/errors.inc';
  
  $summative_lock = 0; 
  
  function marks_from_file($fileName) {
    global $mysqli,  $configObject->get('cfg_tmpdir');
  
    // Get properties of the paper.
    $result = $mysqli->prepare("SELECT property_id, calendar_year, start_date FROM properties WHERE property_id=?");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($property_id, $session, $paper_date);
    $result->fetch();
    $result->close();
    
    if ($property_id == '') {   // Paper could not be found, exit.
      unlink( $configObject->get('cfg_tmpdir') . $_SERVER['PHP_AUTH_USER'] . '_spotter_marks.csv');
      exit;    
    }
    $moduleIDs=Paper_utils::get_modules($_GET['paperID'], $mysqli);
    
    // Get the questions on the paper.
    $paper = array();
    $question_no = 0;
    $result = $mysqli->prepare("SELECT question, sum(marks_correct) as sum FROM papers, options WHERE paper=? AND papers.question=options.o_id GROUP BY question ORDER BY screen, display_pos");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($question, $marks_correct);
    while ($row = $result->fetch()) {
      $question_no++;
      $paper[$question_no]['id'] = $question;
      $paper[$question_no]['marks_correct'] = $marks_correct;
    }
    $result->close();
    
    // Get student data.
    $students = array();
    $modids=implode(',',array_keys($moduleIDs));
    $result = $mysqli->prepare("SELECT users.id, student_id, username, yearofstudy, grade FROM users, sid, modules_student WHERE users.id=sid.userID AND users.id=modules_student.userID AND idMod IN ($modids) AND calendar_year=?");

    print $mysqli->error;
    $result->bind_param('s', $session);
    $result->execute();
    $result->bind_result($id, $student_id, $username, $year, $grade);
    while ($row = $result->fetch()) {
      $students[$student_id]['username'] = $username;
      $students[$student_id]['year'] = $year;
      $students[$student_id]['grade'] = $grade;
      $students[$student_id]['id'] = $id;
    }
    $result->close();

    $lines = file($fileName);
    $line_written = 0;
    if ( isset($_POST['header_row']) and $_POST['header_row'] == '1') {
      echo "<ol start=\"1\">\n";
    } else {
      echo "<ol>\n";
    }
    foreach ($lines as $separate_line) {
      if ((!isset($_POST['header_row']) or $_POST['header_row'] !=1 ) or $line_written > 0) {
        $fields = explode(',',$separate_line);
        $sid = trim($fields[0]);
        if (!isset($students[$sid]['username'])) {  // Student is not in class List.
          // Look up to see if anywhere else in Authentication database.
          $result = $mysqli->prepare("SELECT id, student_id, users.username, yearofstudy, grade FROM users, sid WHERE users.id=sid.userID AND sid.student_id=?");
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
          
          $result = $mysqli->prepare("DELETE FROM log_metadata WHERE userID=? AND paperID=? AND started=?");
          $result->bind_param('iis', $students[$sid]['id'], $_GET['paperID'], $paper_date);
          $result->execute();
          $result->close();
          
          $result = $mysqli->prepare("DELETE FROM log5 WHERE userID=? AND q_paper=? AND started=?");
          $result->bind_param('iis', $students[$sid]['id'], $_GET['paperID'], $paper_date);
          $result->execute();
          $result->close();

          echo "<li>$sid -&gt; " . $students[$sid]['username'] . "</li>";
          
          $result = $mysqli->prepare("INSERT INTO log_metadata VALUES(NULL,?,?,?,?,?,?,?)");
          $ip = '127.0.0.1';
          $attempt = 1;
          $result->bind_param('iisssii', $students[$sid]['id'], $_GET['paperID'], $paper_date, $ip, $students[$sid]['grade'], $students[$sid]['year'], $attempt);
          $result->execute();
          $result->close();
          
          for ($q=1; $q<=$question_no; $q++) {
            $result = $mysqli->prepare("INSERT INTO log5 VALUES(NULL,?,?,?,?,?,?)");
            $mark = floatval(trim($fields[$q]));
            $result->bind_param('isiidi', $students[$sid]['id'], $paper_date, $_GET['paperID'], $paper[$q]['id'], $mark, $paper[$q]['marks_correct']);
            $result->execute();
            $result->close();
          }
        } else {
          echo "<li style=\"color:C00000\">$sid -&gt; username not found!</li>";
        }
      }
      $line_written++;
    }
    echo "</ol>\n";
  }

  if (isset($_POST['submit']) and $_POST['submit']) {
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $_SERVER['PHP_AUTH_USER'] . "_spotter_marks.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        marks_from_file( $configObject->get('cfg_tmpdir') . $_SERVER['PHP_AUTH_USER'] . '_spotter_marks.csv');
        unlink( $configObject->get('cfg_tmpdir') . $_SERVER['PHP_AUTH_USER'] . '_spotter_marks.csv');
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html>
        <head>
        <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
        <title><?php echo $string['loadofflinemarks']; ?></title>
        </head>
        <body>
        <p><?php echo $string['marksloaded']; ?></p>
        <p><input type="submit" name="submit" onclick="window.location='../paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'" value="OK" style="width:100px" /></p>
        <?php
      }
    }
  } else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['loadofflinemarks']; ?></title>
  
  <link rel="stylesheet" href="../css/body.css" type="text/css">
  <link rel="stylesheet" href="../css/dialog.css" type="text/css">
  <link rel="stylesheet" href="../css/submenu.css" type="text/css">
</head>

<body onclick="hideMenus()">
<?php
  include '../include/paper_options.inc';
?>

<div id="content" class="content">
<br />
<br />

<table class="dialog_border" style="width:600px">
<tr>
<td class="dialog_header" style="width:52px"><img src="../artwork/import_48.gif" width="48" height="48" alt="Icon" /></td><td class="dialog_header" style="width:90%"><?php echo $string['loadofflinemarks']; ?></td>
</tr>
<tr>
<td class="dialog_body" colspan="2">

<p><?php echo $string['msg1']; ?></p>

<div><?php echo $string['msg2']; ?></div>


<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>" enctype="multipart/form-data">

<p><input type="file" size="50" name="csvfile" /><br />
<input type="checkbox" name="header_row" value="1" checked />&nbsp;<?php echo $string['headerrow']; ?></p>

<p><input type="submit" style="width:100px" value="<?php echo $string['loadmarks']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></p>
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