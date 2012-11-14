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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['qualitativeanalysis'] . ' ' . $cfg_install_type; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {font-size:90%}
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

  echo "<form name=\"analyse\" method=\"get\" action=\"qualitative_results.php\" target=\"results\"><table class=\"header\" style=\"font-size:90%\">\n";
  echo "<tr><th style=\"width:75%\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php" target="_top">' . $string['home'] . '</a>';
  if ($folder != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '" target="_top">' . $folder_name . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '" target="_top">' . $_GET['module'] . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '" target="_top">' . $paper . '</a></div>';
  echo "<span style=\"font-size:220%; color:black; font-weight:bold; margin-left:10px\">" . $string['qualitativeanalysis'] . "</span></td>";
  echo "<th valign=\"top\" style=\"width:25%\"><input type=\"text\" name=\"keywords\" size=\"20\" value=\"";
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
  echo '<input type="hidden" name="repcourse" value="' . $_GET['repcourse'] . '" />';
  echo '<input type="hidden" name="repyear" value="' . $_GET['repyear'] . '" />';
  echo "</th></tr>";
  echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n";
?>
</table>
</form>
</body>
</html>