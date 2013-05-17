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

$module = (isset($_GET['module']) and $_GET['module'] != '') ? $_GET['module'] : '';

if (isset($_POST['submit'])) {
  // Delete any previous remark records
  $result = $mysqli->prepare("DELETE FROM textbox_remark WHERE paperID=?");
  $result->bind_param('i', $_POST['paperID']);
  $result->execute();
  $result->close();

  for ($student=1; $student<$_POST['student_no']; $student++) {
    if (isset($_POST["student$student"]) and $_POST["student$student"] != '') {
      $result = $mysqli->prepare("INSERT INTO textbox_remark VALUES (NULL,?,?)");
      $result->bind_param('ii', $_POST['paperID'], $_POST["student$student"]);
      $result->execute();
      $result->close();
    }
  }
  header("location: ../paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $_GET['folder']);
} elseif (isset($_POST['submit']) and $_POST['submit'] == 'Cancel') {
  header("location: ../paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $_GET['folder']);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['secondmark']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {font-size:90%}
    .pad {padding-left:40px; width:20px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
</head>

<body>
<?php
  // Get some paper properties
  $result = $mysqli->prepare("SELECT paper_type, paper_title FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_type, $paper);
  $result->fetch();
  $result->close();

  $module_code = '';
  if ($module != '') {
    $result = $mysqli->prepare("SELECT moduleid FROM modules WHERE id=? LIMIT 1");
    $result->bind_param('i', $module);
    $result->execute();
    $result->bind_result($module_code);
    $result->fetch();
    $result->close();
  }

  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }

  echo "<form action=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $_GET['folder'] . "\" method=\"post\">\n";
  echo "<table class=\"header\" style=\"font-size:90%\">\n<tr><th colspan=\"4\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
  } elseif ($module != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $module . '">' . $module_code . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper . '</a></div><div style="margin-left:10px; font-size:220%; color:black; font-weight:bold">' . $string['secondmarkselection'] . '</div></th><th style="width:50%; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(0); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="' . $string['help'] . '" border="0" /></a></th></tr>';

  echo "<tr><th colspan=\"5\" class=\"bevel\"></th></tr>\n";

  $result = $mysqli->prepare("SELECT SUM(marks_correct), pass_mark FROM (properties, papers, questions, options) WHERE property_id=? AND properties.property_id=papers.paper AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type != 'info' GROUP BY paper");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_total, $pass_mark);
  $result->fetch();
  $result->close();

  $student_no = 1;

  $result = $mysqli->prepare("SELECT SUM(mark) AS total_mark, users.username, users.id, student_id FROM textbox_marking, users LEFT JOIN sid ON users.id=sid.userID WHERE users.id=textbox_marking.student_userID AND paperID=? AND phase=1 GROUP BY student_userID ORDER BY student_id");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($total_mark, $username, $recordID, $student_id);
  while ($result->fetch()) {
    $student_id = ($student_id == '') ? '&lt;student ID unknown&gt;' : $student_id;
    if (round(($total_mark/$paper_total)*100) < $pass_mark) {
      echo "<tr style=\"color:red\"><td class=\"pad\"><input type=\"checkbox\" name=\"student$student_no\" value=\"$recordID\" checked /></td><td>$username</td><td>$student_id</td><td style=\"text-align:right\">$total_mark</td><td class=\"pad\">" . round(($total_mark/$paper_total)*100) . "%</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"pad\"><input type=\"checkbox\" name=\"student$student_no\" value=\"$recordID\" /></td><td>$username</td><td>$student_id</td><td style=\"text-align:right\">$total_mark</td><td class=\"pad\">" . round(($total_mark/$paper_total)*100) . "%</td><td>&nbsp;</td></tr>\n";
    }
    $student_no++;
  }
  $result->close();
?>

<tr><td colspan="5">&nbsp;</td></tr>
<tr><td colspan="4" style="text-align:center">
<input type="hidden" name="student_no" value="<?php echo $student_no; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="submit" name="submit" value="<?php echo $string['secondmark']; ?>" style="width:120px" />&nbsp;<input type="submit" name="submit" value="<?php echo $string['cancel']; ?>" style="width:120px" />
</td><td>&nbsp;</td></tr>
</table>
<br />

</form>
</body>
</html>

<?php
}
?>