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

require '../../include/staff_auth.inc';

if (isset($_GET['paperID'])) {
  $paperID = $_GET['paperID'];
} else {
  $paperID = '';
}

if (isset($_GET['display_pos'])) {
  $display_pos = $_GET['display_pos'];
} else {
  $display_pos = '';
}

if (isset($_GET['module'])) {
  $module = $_GET['module'];
} else {
  $module = '';
}

if (isset($_GET['folder'])) {
  $folder = $_GET['folder'];
} else {
  $folder = '';
}

if (isset($_GET['scrOfY'])) {
  $scrOfY = $_GET['scrOfY'];
} else {
  $scrOfY = '';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['byteam']; ?></title>
<style>
body {margin:0px; background-color:white; color:black; font-family:Arial,sans-serif; font-size:90%}
a:link {color:black}
a:visited {color:black}
a:hover {color:black}
.foldername {float:left; width:380px; height:60px; padding-left:12px; font-size:80%}
</style>
</head>

<body>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; font-size:100%">
<tr style="background-color:#F1F5FB"><td colspan="5"style="font-size:160%; font-weight:bold">&nbsp;<?php echo $string['byteam']; ?></td></tr>
<tr style="height:4px"><td valign="top" colspan="5"><img src="../../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>
<?php
  $result = $mysqli->prepare("SELECT name, COUNT(groupID) AS count_no FROM teams WHERE name IN (SELECT name FROM teams WHERE memberID=?) GROUP BY teams.name");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($team_name, $count_no);
  while ($result->fetch()) {
    echo '<div class="foldername">';
    echo '<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%"><tr><td style="width:66px" align="center">';
    echo '  <a href="add_questions_by_team.php?team=' . $team_name . '&paperID=' . $paperID . '&display_pos=' . $display_pos . '&module=' . $module . '&folder=' . $folder . ' &scrOfY=' . $scrOfY . '"><img src="../../artwork/user_accounts_icon.png" width="48" height="48" alt="' . $team_name . '" border="0"  /></a><td>';
    echo '  <td width="290"><a href="add_questions_by_team.php?team=' . $team_name . '&paperID=' . $paperID . '&display_pos=' . $display_pos . '&module=' . $module . '&folder=' . $folder . ' &scrOfY=' . $scrOfY . '">' . $team_name . '</a><br />';
    echo '  <span style="color:#808080">' . $count_no . ' ' . $string['members'] . '</span></td></tr></table>';
    echo "</div>\n";
  }
  $result->close();
  $mysqli->close();
?>
</body>
</html>