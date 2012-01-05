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
?>
<!DOCTYPE html
   PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['qualitativeanalysis'] . ' ' . $cfg_install_type; ?></title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; color:black; margin-top:0px; margin-left:0px; margin-right:0px}
.heading {background-color:#F1F5FB}
.breadcrumb {margin-top:2px; margin-left:10px; font-size:90%}
.breadcrumb a:link {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:visited {color:blue; text-decoration:none; cursor:pointer}
.breadcrumb a:hover {color:blue; text-decoration:underline; cursor:pointer}
</style>
</head>

<body>
<?php
  $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper);
  $result->fetch();
  $result->close();

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

  echo "<form name=\"analyse\" method=\"get\" action=\"qualitative_results.php\" target=\"results\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
  echo "<tr><td class=\"heading\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php" target="_top">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '" target="_top">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '" target="_top">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '" target="_top">' . $paper . '</a></div>';
  echo "<span style=\"font-size:200%; color:black; font-weight:bold; margin-left:10px\">" . $string['qualitativeanalysis'] . "</span></td>";
  echo "<td class=\"heading\" valign=\"top\" width=\"250\"><input type=\"text\" name=\"keywords\" size=\"20\" value=\"";
  if (isset($_GET['keywords'])) echo $_GET['keywords']; 
  echo "\" /><input type=\"submit\" name=\"submit\" value=\"" . $string['highlight'] . "\" />";
  if (isset($_GET['collapse']) and $_GET['collapse'] == '1') {
    echo "<br /><input type=\"checkbox\" name=\"collapse\" value=\"1\" checked />&nbsp;" . $string['collapse'];
  } else {
    echo "<br /><input type=\"checkbox\" name=\"collapse\" value=\"1\" />&nbsp;" . $string['collapse'];
  }
  echo '&nbsp;&nbsp;&nbsp;&nbsp;';
  if (isset($_GET['casesensitive']) and $_GET['casesensitive'] == '1') {
    echo "<br /><input type=\"checkbox\" name=\"casesensitive\" value=\"1\" checked />&nbsp;" . $string['casesensitive'];
  } else {
    echo "<br /><input type=\"checkbox\" name=\"casesensitive\" value=\"1\" />&nbsp;" . $string['casesensitive'];
  }
  echo '<input type="hidden" name="paperID" value="' . $_GET['paperID'] . '" />';
  echo '<input type="hidden" name="startdate" value="' . $_GET['startdate'] . '" />';
  echo '<input type="hidden" name="enddate" value="' . $_GET['enddate'] . '" />';
  echo '<input type="hidden" name="module" value="' . $_GET['module'] . '" />';
  echo '<input type="hidden" name="repdegree" value="' . $_GET['repdegree'] . '" />';
  echo '<input type="hidden" name="repyear" value="' . $_GET['repyear'] . '" />';
  echo "</td></tr>";
  echo "<tr><td colspan=\"2\" style=\"height: 3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
?>
</table>
</form>
</body>
</html>