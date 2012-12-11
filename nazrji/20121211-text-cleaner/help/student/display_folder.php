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
 
require '../../include/staff_student_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Help and Support Center</title>
  
  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    body {font-size:85%; line-height:150%}
    table {font-size:100%}
    a:link {color:#0560A6}
    a:visited {color:#0560A6}
    .row {height:28px; border-bottom: 1px solid #A6CBEB}
  </style>
  
  <script type="text/javascript">
    function updateToolbar(pageID) {
      parent.frames['toolbar'].document.myform.pageid.value=pageID;
    }
  </script>
</head>

<?php
  if ((isset($_GET['id']) and $_GET['id'] != '1') or $userObject->has_role('SysAdmin')) {   // Don't record the homepage or SysAdmin activities.
    $query = "INSERT INTO help_log VALUES (NULL, 'student', $userObject->get_user_ID(), NOW(), " . $_GET['id'] . ")";
    if (!$mysqli->query($query)) {
      echo "<p>" . $mysqli->errno . " Error writing to log: $query.</p>";
    }
  }
  
  echo "<body onload=\"updateToolbar(0)\">\n";
  
  $search_results = $mysqli->prepare("SELECT id, title, type FROM student_help WHERE title LIKE '" . $_GET['title'] . "/%' ORDER BY title");
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($id, $title, $type);

  echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#7598C4\">" . $_GET['title'] . "</div>\n";
  
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n<tr><td style=\"width:20px\">&nbsp;</td><td>";
  
  echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
  echo "<tr><td style=\"border-top: 1px solid #6B82B2; border-bottom:1px solid #6B82B2; border-left:1px solid #6B82B2; background-image:url(../search_bar_background.png); background-repeat:repeat-x; height:23px; color:white; font-weight:bold\">&nbsp;&nbsp;" . $string['topics'] . "Topics</td><td style=\"border-top: 1px solid #6B82B2; border-bottom: 1px solid #6B82B2; border-right: 1px solid #6B82B2; background-image:url(../search_bar_background.png); background-repeat:repeat-x; height:23px; color:white; text-align:right\">" . $search_results->num_rows . "&nbsp;" . $string['items'] . "&nbsp;</td></tr>";
  echo "</table>\n";

  echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\">\n";
  $row_no = 0;
  while ($search_results->fetch()) {
    $row_no++;
    if ($row_no % 2) {
      echo "<tr><td style=\"width:24px\" class=\"row\"><img src=\"../single_page.png\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></td><td class=\"row\"><a href=\"index.php?id=$id\" target=\"_top\">" . str_replace($_GET['title'] . '/','',$title) . "</a></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:24px; background-color:#F2F2F2\" class=\"row\"><img src=\"../single_page.png\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" /></td><td style=\"background-color:#F2F2F2\" class=\"row\"><a href=\"index.php?id=$id\" target=\"_top\">" . str_replace($_GET['title'] . '/','',$title) . "</a></td></tr>\n";
    }

  }
  $search_results->close();
  $mysqli->close();
  echo "</table>\n</td><td style=\"width:20px\">&nbsp;</td></tr>\n</table>\n";
?>
</div>
</body>
</html>