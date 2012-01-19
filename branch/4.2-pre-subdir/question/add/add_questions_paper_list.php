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
?>
<html>
<head>
<title>by Paper</title>
<style>
body {margin:0px; font-family:Arial,sans-serif; color:black; background-color:white; font-size:80%}
a:link {color:black}
a:visited {color:black}
a:hover {color:black}
.f {padding-left:2px; width:20px}
.s {padding-left:6px}
</style>
</head>

<body>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; font-size:100%">
<tr style="background-color:#F1F5FB"><td colspan="5"style="font-size:160%; font-weight:bold">&nbsp;xx by Paper</td></tr>
<tr style="background-color:#F1F5FB"><td>&nbsp;</td><td><img src="../../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Title</td><td><img src="../../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Module</td><td><img src="../../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Owner</td><td><img src="../../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;Created</td></tr>
<tr style="height:4px"><td valign="top" colspan="5"><img src="../../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
<?php
  $my_teams = '';
  foreach ($teams as $individual_team) {
    $my_teams .= " OR moduleID LIKE '%$individual_team%'";
  }

  $paper_icons = array('formative_16.gif','progress_16.gif','summative_16.gif','survey_16.gif','osce_16.gif','spotter_16.gif');
  
  if (isset($_GET['paper_type'])) {
    $sql = "SELECT property_id, paper_title, paper_type, moduleID, DATE_FORMAT(created,'$cfg_short_date') AS created, title, initials, surname FROM (properties, users) WHERE paper_type='" . $_GET['paper_type'] . "' AND deleted IS NULL AND paper_ownerID=users.id AND (paper_ownerID=$userID $my_teams) ORDER BY paper_title";
  } else {
    $sql = "SELECT property_id, paper_title, paper_type, moduleID, DATE_FORMAT(created,'$cfg_short_date') AS created, title, initials, surname FROM (properties, users) WHERE moduleID LIKE '%" . $_GET['team_name'] . "%' AND deleted IS NULL AND paper_ownerID=users.id ORDER BY paper_title";
  }
  $papers = $mysqli->query($sql);
  while ($row = $papers->fetch_assoc()) {
    echo '<tr><td class="f"><a href="add_questions_by_paper.php?question_paper=' . $row['property_id'] . '"><img src="../../artwork/' . $paper_icons[$row['paper_type']] . '" width="16" height="16" alt="Folder" border="0" align="middle" /></a></td><td class="s"><a href="add_questions_by_paper.php?question_paper=' . $row['property_id'] . '">' . $row['paper_title'] . '</a></td><td class="s">' . $row['moduleID'] . '</td><td class="s">' . $row['surname'] . ', ' . $row['initials'] . '. ' . $row['title'] . '</td><td class="s">' . $row['created'] . '</td></tr>';
  }
?>
</table>
</body>
</html>