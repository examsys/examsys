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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

  function marks_from_file($fileName) {
    global $mysqli;
  
    // Get properties of the paper.
    $result = $mysqli->prepare("SELECT property_id, moduleID, calendar_year, start_date, marking FROM properties WHERE property_id=?");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($property_id, $moduleID, $session, $paper_date, $marking);
    $result->fetch();
    $result->close();
    
    if ($property_id == '') {   // Paper could not be found, exit.
      unlink('/tmp/' . $userID . '_spotter_marks.csv');
      exit;    
    }
    
    // Get the questions on the paper.
    $paper = array();
    $question_no = 0;
    $result = $mysqli->prepare("SELECT question, marks FROM papers, options WHERE paper=? AND papers.question=options.o_id ORDER BY screen, display_pos");
    $result->bind_param('i', $_GET['paperID']);
    $result->execute();
    $result->bind_result($question, $marks);
    while ($row = $result->fetch()) {
      $question_no++;
      $paper[$question_no]['id'] = $question;
    }
    $result->close();
    
    // Get student data.
    $students = array();
    $result = $mysqli->prepare("SELECT users.id, student_id, username, yearofstudy, grade FROM users, sid, student_modules WHERE users.id=sid.userID AND users.id=student_modules.userID AND moduleid=? AND calendar_year=?");
    echo $mysqli->error;
    $result->bind_param('ss', $moduleID, $session);
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
          $result = $mysqli->prepare("SELECT id, student_id, username, yearofstudy, grade FROM users, sid WHERE users.id=sid.userID AND sid.student_id=?");
          echo $mysqli->error;
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
          $result = $mysqli->prepare("DELETE FROM log4 WHERE userID=? AND q_paper=?");
          $result->bind_param('ii', $students[$sid]['id'], $_GET['paperID']);
          $result->execute();
          $result->close();

          $result = $mysqli->prepare("DELETE FROM log4_overall WHERE userID=? AND q_paper=?");
          $result->bind_param('ii', $students[$sid]['id'], $_GET['paperID']);
          $result->execute();
          $result->close();

          echo "<li>$sid -&gt; " . $students[$sid]['username'] . "</li>";
          // Record individual questions.
          $numeric_score = 0;
          for ($q=1; $q<=$question_no; $q++) {
            $result = $mysqli->prepare("INSERT INTO log4 VALUES(NULL,?,?,?,?,?,NULL)");
            $fields[$q] = trim($fields[$q]);
            $result->bind_param('isiis', $students[$sid]['id'], $paper_date, $_GET['paperID'], $paper[$q]['id'], $fields[$q]);
            $result->execute();
            $result->close();
            $numeric_score += trim($fields[$q]);
          }
          // Record overall student/station details.
          $result = $mysqli->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
          $fields[$question_no+1] = trim($fields[$question_no+1]);
          $result->bind_param('s', $fields[$question_no+1]);
          $result->execute();
          $result->bind_result($examinerID);
          $result->fetch();
          $result->close();
          
          if ($examinerID == '') $examinerID = 1217;    // Fall back on Simon Wilkinson.
          
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
          if(isset($cat2no[strtolower(trim($fields[$question_no+2]))])) {
            $overall_rating = $cat2no[strtolower(trim($fields[$question_no+2]))];
          } else {
            $overall_rating = 'ERROR';
          }
          if (isset($fields[$question_no+3])) {
            $feedback = trim($fields[$question_no+3]);
          } else {
            $feedback = '';
          }
          $result = $mysqli->prepare("INSERT INTO log4_overall VALUES(NULL,?,?,?,?,?,?,?,?,?,'paper')");
          $result->bind_param('isisissss', $students[$sid]['id'], $paper_date, $_GET['paperID'], $overall_rating, $numeric_score, $feedback, $students[$sid]['grade'], $students[$sid]['year'], $examinerID);
          $result->execute();
          $result->close();
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
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'], "/tmp/" . $userID . "_spotter_marks.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        marks_from_file('/tmp/' . $userID . '_spotter_marks.csv');
        unlink('/tmp/' . $userID . '_spotter_marks.csv');
        ?>
        <html>
        <head>
        <title>Load OSCE Marks</title>
        </head>
        <body style="font-family:Arial,sans-serif">
        <p>Marks loaded.</p>
        <p><input type="submit" name="submit" onclick="window.location='../paper/details.php?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>'" value="OK" style="width:100px" /></p>
        <?php
      }
    }
  } else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Load OSCE Marks</title>
<link rel="stylesheet" href="../css/submenu.css" type="text/css">
<style>
  body, p {color:#003366; font-family:Arial,sans-serif}
</style>

</head>

<body onclick="hideMenus()">
<?php
  require '../include/paper_options.inc';
?>

<div id="content" class="content">
<br />
<br />
<table border="0" width="100%" height="100%">
<tr><td valign="middle">
<div align="center">

<table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #5582D2; width:85%">
<tr>
<td valign="middle" align="left" style="background-color:white"><img src="../artwork/import_csv_32.gif" width="32" height="32" alt="Icon" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:16pt; font-weight:bold; color:#5582D2">Load OSCE Station Marks (CSV) File</span></td>
</tr>
<tr>
<td align="left" style="background-color:#DFE8FF">

<p>CSV file should contain the columns in the following order: Student ID, Question 1, Question 2, Question 3..., Examiner Username, Overall Result, Feedback</p>

<div>Please select the CSV file you wish to load:</div>


<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?paperID=<?php echo $_GET['paperID']; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>" enctype="multipart/form-data">

<p><input type="file" size="50" name="csvfile" /><br />
<input type="checkbox" name="header_row" value="1" checked />&nbsp;File contains header row</p>

<p><input type="submit" style="width:100px" value="Load Marks" name="submit" />&nbsp;<input style="width:100px" type="button" value="Cancel" name="cancel" onclick="history.go(-1)" /></p>
</form>
</div>
</td>
</tr>
</table>

</div>
</td></tr>
</table>
</div>

</body>
</html>
<?php
  }
  $mysqli->close();
?>