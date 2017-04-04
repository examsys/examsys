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

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$paper_type = $propertyObj->get_paper_type();

function load_marks($paperID, $q_id, $phase, $db) {
  $marks = array();
  
  $result = $db->prepare("SELECT answer_id, mark FROM textbox_marking WHERE paperID = ? AND q_id = ? AND phase = ?");
  $result->bind_param('iii', $paperID, $q_id, $phase);
  $result->execute();
  $result->bind_result($answer_id, $mark);
  while ($result->fetch()) {
    $marks[$answer_id] = $mark;
  }
  $result->close();

  return $marks;
}

function load_question_mark($q_id, $db) {
  $result = $db->prepare("SELECT marks_correct FROM options WHERE o_id = ? LIMIT 1");
  $result->bind_param('i', $q_id);
  $result->execute();
  $result->bind_result($marks_correct);
  $result->fetch();
  $result->close();
  
  return $marks_correct;
}

function displayMarks($id, $marks, $override, $user_mark) {
  $html = '<select name="override' . $id . '" id="override' . $id . '"><option value="NULL"></option>';
  $inc = 0.5;
  for ($i=0; $i<=$marks; $i+=$inc) {
    $display_i = $i;
    if ($i == 0.5) {
      $display_i = '&#189;';
    } elseif ($i - floor($i) > 0) {
      $display_i = floor($i) . '&#189;';
    }
    if ($override and $i === $user_mark) {
      $html .= "<option value=\"$i\" selected>$display_i</option>";
    } else {
      $html .= "<option value=\"$i\">$display_i</option>";
    }
  }
  $html .= '</select>';
  
  return $html;
}

if (isset($_POST['submit'])) {
  for ($i=1; $i <= $_POST['student_no']; $i++) {
    if (isset($_POST["override$i"]) and $_POST["override$i"] != 'NULL') {
      $tmp_mark = $_POST["override$i"];
    } elseif (isset($_POST["mark$i"])) {
      $tmp_mark = $_POST["mark$i"];
    } else {
      $tmp_mark = NULL;
    }
    $logtype = $_POST["logtype$i"];
    $log_id = $_POST["log_id$i"];
    $result = $mysqli->prepare("UPDATE log$logtype SET mark = ?, adjmark = ? WHERE id = ?");
    $result->bind_param('ddi', $tmp_mark, $tmp_mark, $log_id);
    $result->execute();
    $result->close();
  }
  header("location: ../reports/textbox_select_q.php?action=finalise&paperID=$paperID&startdate=" . $_POST['startdate'] . "&enddate=" . $_POST['enddate'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder'] . "&repcourse=" . $_GET['repcourse']);
	exit();
} else {
  $q_id       = check_var('q_id', 'GET', true, false, true);
  $startdate  = check_var('startdate', 'GET', true, false, true);
  $enddate    = check_var('enddate', 'GET', true, false, true);

  // Check the question exists.
  if (!QuestionUtils::question_exists($q_id, $mysqli)) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
  
  $primary_marks = load_marks($paperID, $q_id, 1, $mysqli);
  
  $secondary_marks = load_marks($paperID, $q_id, 2, $mysqli);
  
  $marks_correct = load_question_mark($q_id, $mysqli);

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['finalisemarks'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/textbox_finalise_marks.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    // Check select all button
    function selectall() {
      var total = 0;
      var count = 0;
      $(".primarychk").each(function() {
        if ($(this).is(':checked')) {
          count++;
          total++;
        } else {
          total++;
        }
      });
      if (count === total) {
        $("#selectallprimary").prop("checked", true);
      }
    }
    $(function() {
      // Check select all button if all primary mark radio buttons selected on load.
      $(document).ready(function(){
        selectall();
      });
      $("input:radio").click(function() {
        str = $(this).attr('id');
        
        dropdownID = str.replace('mark', 'override');
        $("#" + dropdownID).val('');        
      });
      
      $("select").click(function() {
        str = $(this).attr('id');
        
        radioID = str.replace('override', 'mark');
        
        
        $('input:radio[name=' + radioID + ']').removeAttr('checked');
      });
      // Select all primary marks radio buttons.
      $("#selectallprimary").change(function() {
        if ($("#selectallprimary").is(':checked')) {
          $(".primarychk").each(function() {
            $(this).prop("checked", true);
          });
        } else {
          $(".primarychk").each(function() {
            $(this).prop("checked", false);
          });
        }
      });
      // Check select all button if all primary mark radio buttons selected.
      $(".primarychk").click(function() {
        selectall();
      });
      // Uncheck select all button if a secondary mark has been selected.
      $(".secondarychk").click(function() {
        $("#selectallprimary").prop("checked", false);
      });
    })
  </script>
</head>

<body>
  <div id="content">
<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();

  echo "<form action=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $paperID . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder'] . "&repcourse=" . $_GET['repcourse'] . "\" method=\"post\" autocomplete=\"off\">\n";
  echo '<div class="head_title">';
  echo '<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>';
  echo '<div class="breadcrumb" style="height:20px"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and trim($_GET['folder']) != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $propertyObj->get_paper_title() . '</a></div></div>';
  echo '<table class="header"><tr><th><div class="page_title">' . $string['finalisemarks'] . ': <span style="font-weight:normal"> ' . $string['question'] . ' ' . $_GET['qNo'] . '</span></div></th><th style="text-align:center; vertical-align:bottom"><div style="width:70px; font-size:110%">'.$string['first'].'</div></th><th style="text-align:center; vertical-align:bottom"><div style="width:70px; font-size:110%">'.$string['second'].'</div></td><th style="text-align:center; vertical-align:bottom"><div style="width:70px; font-size:110%">'.$string['override'].'</div></th></tr>';

  $student_no = 0;

  // Get student answers
  if ($paper_type == '0') {
    $sql = <<< SQL
SELECT 0 AS logtype, l.id, lm.userID, l.user_answer, l.mark
  FROM (log0 l, log_metadata lm, users u)
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND (u.roles LIKE '%Student%' OR u.roles = 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
  AND lm.started <= ?
UNION ALL
SELECT 1 AS logtype, l.id, lm.userID, l.user_answer, l.mark
  FROM (log1 l, log_metadata lm, users u)
  WHERE lm.paperID = ?
  AND l.metadataID = lm.id
  AND (u.roles LIKE '%Student%' OR u.roles = 'graduate')
  AND u.id = lm.userID
  AND l.q_id = ?
  AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
  AND lm.started <= ?
SQL;

    $result = $mysqli->prepare($sql);
    $result->bind_param('iissiiss', $paperID, $q_id, $startdate, $enddate, $paperID, $q_id, $startdate, $enddate);
  } else {
    $sql = <<< SQL
SELECT $paper_type AS logtype, l.id, lm.userID, l.user_answer, l.mark
FROM (log{$paper_type} l, log_metadata lm, users u)
WHERE lm.paperID = ?
AND l.metadataID = lm.id
AND (u.roles LIKE '%Student%' OR u.roles = 'graduate')
AND u.id = lm.userID
AND l.q_id = ?
AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ?
AND lm.started <= ?;
SQL;

    $result = $mysqli->prepare($sql);
    $result->bind_param('iiss', $paperID, $q_id, $startdate, $enddate);
  }
  $result->execute();
  $result->bind_result($logtype, $log_id, $tmp_userID, $user_answer, $user_mark);
  while ($result->fetch()) {
      $student_no++;
      if (isset($primary_marks[$log_id]) and $primary_marks[$log_id] === $user_mark) {
        $primary_checked = ' checked';
        $secondary_checked = '';
        $override = false;
      } elseif (isset($secondary_marks[$log_id]) and $secondary_marks[$log_id] === $user_mark) {
        $primary_checked = '';
        $secondary_checked = ' checked';
        $override = false;
      } else {
        $primary_checked = '';
        $secondary_checked = '';
        $override = true;
      }
      if (trim($user_answer) != '') {
      echo "<tr class=\"l\"><td class=\"ans\">" . nl2br($user_answer) . "<br />&nbsp;</td>";

      if (isset($secondary_marks[$log_id]) and isset($primary_marks[$log_id]) and abs($primary_marks[$log_id] - $secondary_marks[$log_id]) > 1) {
        echo "<td class=\"primary noans\">" . $primary_marks[$log_id] . "<input class=\"primarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $primary_marks[$log_id] . "\" $primary_checked /></td><td class=\"secondary noans\">" . $secondary_marks[$log_id] . "<input type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $secondary_marks[$log_id] . "\" $secondary_checked /><input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /></td><td class=\"override noans\">" . displayMarks($student_no, $marks_correct, $override, $user_mark);
      } else {
        if (isset($primary_marks[$log_id])) {
          echo "<td class=\"primary\">" . $primary_marks[$log_id] . "<input class=\"primarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $primary_marks[$log_id] . "\" $primary_checked /></td>";
        } else {
          echo "<td class=\"unmarked\">" . $string['unmarked'] . "</td>";
        }
        if (isset($secondary_marks[$log_id])) {
          echo "<td class=\"secondary\">" . $secondary_marks[$log_id] . "<input class=\"secondarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $secondary_marks[$log_id] . "\" $secondary_checked /></td>";
        } else {
          echo "<td class=\"secondary missing\">&nbsp;</td>";
        }
        echo "<td class=\"override\">" . displayMarks($student_no, $marks_correct, $override, $user_mark);
      }
    } else {
      // User answer is blank.
      echo "<tr class=\"l\"><td class=\"ans\" style=\"color: #C00000\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"!\" />&nbsp;" . $string['noanswer'] . "<br />&nbsp;</td>";
      if (isset($primary_marks[$log_id])) {
        echo "<td class=\"primary noans\">" . $primary_marks[$log_id] . "<input class=\"primarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $primary_marks[$log_id] . "\" $primary_checked/></td>";
      } else {
        echo "<td class=\"unmarked\">" . $string['unmarked'] . "</td>";
      }
      if (isset($secondary_marks[$log_id])) {
        echo "<td class=\"secondary noans\"\">" . $secondary_marks[$log_id] . "<input class=\"secondarychk\" type=\"radio\" name=\"mark$student_no\" id=\"mark$student_no\" value=\"" . $secondary_marks[$log_id] . "\" $secondary_checked/>
            <input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /></td>";
      } else {
        echo "<td class=\"secondary noans missing\">&nbsp;</td>";
      }
      echo "<td class=\"override noans\">" . displayMarks($student_no, $marks_correct, $override, $user_mark);
    }
    echo "<input type=\"hidden\" name=\"log_id$student_no\" value=\"$log_id\" /><input type=\"hidden\" name=\"logtype$student_no\" value=\"$logtype\" /></td></tr>\n";   
  }
  $result->close();
?>
<tr><td></td><td align="right"><label for="selectallprimary"><?php echo $string['selectallprimary'] ?></label>&nbsp;<input type="checkbox" id="selectallprimary" name="selectallprimary" value="" /> </td><td></td><td></td></tr>
</table>
<br />
<div style="text-align:center">
<input type="hidden" name="student_no" value="<?php echo $student_no ?>" />
<input type="hidden" name="paperID" value="<?php echo $paperID ?>" />
<input type="hidden" name="startdate" value="<?php echo $startdate ?>" />
<input type="hidden" name="enddate" value="<?php echo $enddate ?>" />

<input type="submit" name="submit" class="ok" value="<?php echo $string['finalisemarks'] ?>" />  

</div>
</form>
</div>
</body>
</html>

<?php
}
?>