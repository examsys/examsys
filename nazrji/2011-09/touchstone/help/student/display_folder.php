<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  if (strpos($_SERVER['PHP_SELF'],'student_help') !== false) {
    $help_type = 'student';
    $require_file = '../include/staff_student_auth.inc';
  } else {
    $help_type = 'staff';
    $require_file = '../include/staff_auth.inc';
  }

  require $require_file;
?>
<html>
<head>
<title>Help and Support Center</title>
<style>
body {background-color:white; color:black; margin:0px; font-family:Arial,sans-serif; font-size:85%; line-height:150%}
table {font-size:100%}
a:link {color:#0560A6}
a:visited {color:#0560A6}
.row {height:28px; border-bottom: 1px solid #A6CBEB}
</style>
<script language="JavaScript">
  function updateToolbar(pageID) {
    parent.frames['toolbar'].document.myform.pageid.value=pageID;
  }
</script>
</head>

<?php
  if ($_GET['id'] != '1' and strpos($userroles,'SysAdmin') === false) {   // Don't record the homepage or SysAdmin activities.
    $query = "INSERT INTO help_log VALUES (NULL, '$help_type', $userID, NOW(), " . $_GET['id'] . ")";
    if (!$mysqli->query($query)) {
      echo "<p>" . $mysqli->errno . " Error writing to log: $query.</p>";
    }
  }
  
  echo "<body onload=\"updateToolbar(0)\">\n";
  
  $search_results = $mysqli->query("SELECT id, title, type FROM " . $help_type . "_help WHERE title LIKE '" . $_GET['title'] . "/%' ORDER BY title");
  echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#7598C4; font-family:Verdana,sans-serif\">" . $_GET['title'] . "</div>\n";
  
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n<tr><td style=\"width:20px\">&nbsp;</td><td>";
  
  echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
  echo "<tr><td style=\"border-top: 1px solid #6B82B2; border-bottom:1px solid #6B82B2; border-left:1px solid #6B82B2; background-image:url(search_bar_background.png); background-repeat:repeat-x; height:23px; color:white; font-weight:bold\">&nbsp;&nbsp;Topics</td><td style=\"border-top: 1px solid #6B82B2; border-bottom: 1px solid #6B82B2; border-right: 1px solid #6B82B2; background-image:url(search_bar_background.png); background-repeat:repeat-x; height:23px; color:white; text-align:right\">" . mysql_num_rows($search_results) . "&nbsp;items&nbsp;</td></tr>";
  echo "</table>\n";

  echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
  $row_no = 0;
  while ($row = $search_results->fetch_assoc()) {
    $row_no++;
    if ($row_no % 2) {
      echo "<tr><td style=\"width:24px\" class=\"row\"><img src=\"./images/single_page.png\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></td><td class=\"row\"><a href=\"index.php?id=" . $row['id'] . "\" target=\"_top\">" . str_replace($_GET['title'] . '/','',$row['title']) . "</a></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:24px; background-color:#F2F2F2\" class=\"row\"><img src=\"./images/single_page.png\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></td><td style=\"background-color:#F2F2F2\" class=\"row\"><a href=\"index.php?id=" . $row['id'] . "\" target=\"_top\">" . str_replace($_GET['title'] . '/','',$row['title']) . "</a></td></tr>\n";
    }

  }
  echo "</table>\n</td><td style=\"width:20px\">&nbsp;</td></tr>\n</table>\n";
  $mysqli->close();
?>
</div>
</body>
</html>