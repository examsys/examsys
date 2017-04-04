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

$paperID    = check_var('paperID', 'GET', true, false, true);

$module = (isset($_GET['module']) and $_GET['module'] != '') ? $_GET['module'] : '';

if (isset($_POST['submit'])) {
  // Delete any previous remark records
  $result = $mysqli->prepare("DELETE FROM textbox_remark WHERE paperID = ?");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->close();

  for ($student=1; $student<=$_POST['student_no']; $student++) {
    if (isset($_POST["student$student"]) and $_POST["student$student"] != '') {
      $result = $mysqli->prepare("INSERT INTO textbox_remark VALUES (NULL, ?, ?)");
      $result->bind_param('ii', $paperID, $_POST["student$student"]);
      $result->execute();
      $result->close();
    }
  }
  header("location: ../paper/details.php?paperID=" . $paperID . "&module=" . $module . "&folder=" . $_GET['folder']);
	exit();
} elseif (isset($_POST['submit']) and $_POST['submit'] == 'Cancel') {
  header("location: ../paper/details.php?paperID=" . $paperID . "&module=" . $module . "&folder=" . $_GET['folder']);
	exit();
} else {
	$startdate  = check_var('startdate', 'GET', true, false, true);
	$enddate    = check_var('enddate', 'GET', true, false, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['secondmark'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    .pad {padding-left:40px; width:20px}
    body {margin-bottom:10px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(function () {
      $("#selectall").click(function() {
        if (this.checked) {
          $(".check").each(function() {
            this.checked = true;
          });
        } else {
          $(".check").each(function() {
            this.checked = false;
          });
        }
      });
    });
  </script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();

  // Get some paper properties
	$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
	
	$paper_total = $properties->get_total_mark();
	$pass_mark = $properties->get_pass_mark();
	$paper_type = $properties->get_paper_type();
?>
  <div id="content">
    
<?php
  echo "<form action=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $paperID . "&module=" . $module . "&folder=" . $_GET['folder'] . "\" method=\"post\" autocomplete=\"off\">\n";
  echo "<div class=\"head_title\">";
  echo '<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>';
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and trim($_GET['folder']) != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $properties->get_paper_title() . '</a></div>';
  ?>
  <div class="page_title"><?php echo $string['secondmarkselection'] ?></div>
  <div style="padding-left:42px"><input type="checkbox" name="selectall" id="selectall" /> <strong><?php echo $string['selectall'] ?></strong></div>
  </div>
  
  <table>
  <?php

	if ($paper_type == '2') {
		$time_int = 2;
	} else {
		$time_int = 0;
	}
	
	// Get any previous remark settings.
	$remark_array = textbox_marking_utils::get_remark_users($paperID, $mysqli);
  
	if (count($remark_array) > 0) {
		$prev_remark = true;
	} else {
		$prev_remark = false;
	}
	
	// Get back total marks for the paper excluding all textboxes.
	$marks_array = array();
  if ($paper_type == '0') {
		$sql = <<< SQL
			SELECT SUM(adjmark) AS adjmark_total, userID, username
				FROM log0, log_metadata, questions, users
				WHERE log0.metadataID = log_metadata.id
				AND paperID = ?
				AND log0.q_id = questions.q_id
				AND q_type NOT IN ('textbox','info')
				AND log_metadata.userID = users.id
				AND (roles LIKE '%Student%' OR roles = 'Graduate')
				AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ?
				AND started <= ?
			UNION ALL
			SELECT SUM(adjmark) AS adjmark_total, userID, username
				FROM log1, log_metadata, questions, users
				WHERE log1.metadataID = log_metadata.id
				AND paperID = ?
				AND log1.q_id = questions.q_id
				AND q_type NOT IN ('textbox','info')
				AND log_metadata.userID = users.id
				AND (roles LIKE '%Student%' OR roles = 'Graduate')
				AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ?
				AND started <= ?
			GROUP BY metadataID
SQL;
		$result = $mysqli->prepare($sql);
		$result->bind_param('ississ', $paperID, $startdate, $enddate, $paperID, $startdate, $enddate);
	} else {
		$sql = <<< SQL
			SELECT SUM(adjmark) AS adjmark_total, userID, username
				FROM log$paper_type, log_metadata, questions, users
				WHERE log$paper_type.metadataID = log_metadata.id
				AND paperID = ?
				AND log$paper_type.q_id = questions.q_id
				AND q_type NOT IN ('textbox','info')
				AND log_metadata.userID = users.id
				AND (roles LIKE '%Student%' OR roles = 'Graduate')
				AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ?
				AND started <= ?
				GROUP BY metadataID
SQL;
		$result = $mysqli->prepare($sql);
		$result->bind_param('iss', $paperID, $startdate, $enddate);
	}
  $result->execute();
  $result->bind_result($adjmark_total, $userID, $username);
  while ($result->fetch()) {
	  if ($userID != '') {
			$marks_array[$userID]['total'] = $adjmark_total;
			$marks_array[$userID]['userID'] = $userID;
			$marks_array[$userID]['username'] = $username;
			$marks_array[$userID]['student_id'] = '';
		}
  }
	$result->close();

	// Add in total marks for the textbox questions (primary mark).
	$result = $mysqli->prepare("SELECT SUM(mark) AS sum_mark, users.username, users.id, student_id FROM textbox_marking, users LEFT JOIN sid ON users.id = sid.userID WHERE users.id = textbox_marking.student_userID AND paperID = ? AND phase = 1 GROUP BY student_userID ORDER BY student_id");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($sum_mark, $username, $userID, $student_id);
	while ($result->fetch()) {
	  if (isset($marks_array[$userID]['total'])) {
			$marks_array[$userID]['total'] += $sum_mark;
	  } else {
			$marks_array[$userID]['total'] = $sum_mark;
		}
		$marks_array[$userID]['userID'] = $userID;
	  $marks_array[$userID]['username'] = $username;
	  $marks_array[$userID]['student_id'] = $student_id;
	}
	$result->close();

	$percent_decimals = $configObject->get('percent_decimals');
		
  $student_no = 0;
	foreach ($marks_array as $userID=>$user_data) {
    $student_no++;
    $student_id = ($user_data['student_id'] == '') ? '&lt;student ID unknown&gt;' : $user_data['student_id'];
		$username = $user_data['username'];
		$total_mark = $user_data['total'];
		$recordID = $user_data['userID'];
	
		$checked = '';
		if ($prev_remark) {
		  if (isset($remark_array[$recordID])) $checked = ' checked';
		} else {
			if (round(($total_mark/$paper_total)*100) < $pass_mark) $checked = ' checked';
		}
		
    if (round(($total_mark/$paper_total)*100) < $pass_mark) {
      echo "<tr style=\"color:#C00000\"><td class=\"pad\"><input type=\"checkbox\" class=\"check\" name=\"student$student_no\" value=\"$recordID\"$checked /></td><td>$username</td><td>$student_id</td><td style=\"text-align:right\">$total_mark</td><td class=\"pad\">" . MathsUtils::formatNumber(($total_mark/$paper_total)*100, $percent_decimals) . "%</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"pad\"><input type=\"checkbox\" class=\"check\" name=\"student$student_no\" value=\"$recordID\"$checked /></td><td>$username</td><td>$student_id</td><td style=\"text-align:right\">$total_mark</td><td class=\"pad\">" . MathsUtils::formatNumber(($total_mark/$paper_total)*100, $percent_decimals) . "%</td><td>&nbsp;</td></tr>\n";
    }
  }
?>

</table>
<?php
if ($student_no == 0) {
  $msg = sprintf($string['noattempts'], textbox_marking_utils::nicedate($startdate), textbox_marking_utils::nicedate($enddate));
	echo $notice->info_strip($msg, 100);
} else {
?>
<br />
<input type="hidden" name="student_no" value="<?php echo $student_no ?>" />
<input type="submit" name="submit" value="<?php echo $string['secondmark'] ?>" class="ok" style="margin-left:40px" /><input type="submit" name="submit" value="<?php echo $string['cancel'] ?>" class="cancel" />
<?php
}
?>
</form>
</div>
</body>
</html>

<?php
}
?>