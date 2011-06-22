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

  require '../include/sysadmin_auth.inc';
?>
<html>
<head>
<title>Schools</title>
<style style="text/css">
  body {font-family: Arial,sans-serif; font-size:100%; color:black; background-color:white; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px}
  td {font-family:Arial,sans-serif; font-size:80%; color: black}
  a {font-family:Arial,sans-serif; color:black}
  a:hover {color:white; background-color: #000080}
  a:hover img {background-color:#EBEADB}
  .question_no {text-align:right; vertical-align:top; cursor:pointer}
</style>

<script language="javascript">
  function move_in(img_name,img_src) {
    document[img_name].src=img_src;
  }

  function move_out(img_name,img_src) {
    document[img_name].src=img_src;
  }

  function deleteSchool(schoolID,schoolName) {
    notice=window.open("check_delete_school.php?schoolID=" + schoolID + "&schoolName=" + schoolName + "","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-210,screen.height/2-85);
    if (window.focus) {
      notice.focus();
    }
  }
</script>
</head>

<body>
<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
echo "<tr><td colspan=\"2\" style=\"background-color: #EBEADB\"><div style=\"font-size: 220%; font-weight: bold\"><a onmouseover=\"move_in('image1','../artwork/up_folder_icon_on.gif')\" onmouseout=\"move_out('image1','../artwork/up_folder_icon_off.gif')\" href=\"../index.php\" target=\"_top\"><img name=\"image1\" src=\"../artwork/up_folder_icon_off.gif\" width=\"32\" height=\"38\" alt=\"Up\" border=\"0\" /></a>&nbsp;List of Schools</div></td></tr>\n";
echo "<tr><td style=\"background-color: #EBEADB\">&nbsp;School</td>\n";
echo "<td style=\"background-color: #EBEADB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Faculty&nbsp;</td></tr>\n";
echo "<tr><td colspan=\"2\" style=\"height: 3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";

$query_string = "SELECT id, school, faculty FROM schools ORDER BY school;";
$results = mysql_query($query_string,$link_id);
while ($row = mysql_fetch_array($results)) {
  if ($_GET['action'] == 'edit') {
    echo "<tr>\n<td>&nbsp;<a href=\"edit_school.php?schoolID=" . $row['id'] . "\">" . $row['school'] . "</a></td>\n";
  } elseif ($_GET['action'] == 'delete') {
    echo "<tr>\n<td>&nbsp;<a href=\"#\" onclick=\"deleteSchool(" . $row['id'] . ",'" . $row['school'] . "'); return false;\">" . $row['school'] . "</a></td>\n";
  }
  echo "<td>&nbsp;" . $row['faculty'] . "</td></tr>\n";
}
?>
</table>
</body>
</html>