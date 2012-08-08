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
require '../include/errors.inc';
require_once '../classes/searchutils.class.php';

check_var('refID', 'GET', true, false);

if (isset($_POST['submit'])) {
  // Write the reference material
  $result = $mysqli->prepare("UPDATE reference_material SET title=?, content=?, width=? WHERE id=?");
  $result->bind_param('sssi', $_POST['title'], $_POST['ref_content'], $_POST['width'], $_GET['refID']);
  $result->execute();
  
  // Add it to the modules
  $result = $mysqli->prepare("DELETE FROM reference_modules WHERE refID=?");
  $result->bind_param('i', $_GET['refID']);
  $result->execute();

  for ($i=0; $i<$_POST['module_no']; $i++) {
    if (isset($_POST['module' . $i])) {
      $result = $mysqli->prepare("INSERT INTO reference_modules VALUES (NULL, ?, ?)");
      $result->bind_param('ii', $_GET['refID'], $_POST['module' . $i]);
      $result->execute();
    }
  }
  
  header("location: list_ref_material.php?module=" . $_POST['module']);
  exit;
}

$result = $mysqli->prepare("SELECT title, content, width FROM reference_material WHERE id=?");
$result->bind_param('i', $_GET['refID']);
$result->execute();
$result->bind_result($title, $content, $width);
$result->fetch();
$result->close();

$ref_modules = array();

$result = $mysqli->prepare("SELECT moduleID FROM reference_modules WHERE refID=?");
$result->bind_param('i', $_GET['refID']);
$result->execute();
$result->bind_result($moduleID);
while ($result->fetch()) {
  $ref_modules[] = $moduleID;
}
$result->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>New Reference Material</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
body {font-size:100%; font-family:Arial,sans-serif; margin:0px}
table {font-size:100%}
input, textarea {font-family:Arial,sans-serif; line-height:140%}
.r1 {text-indent:-23px; padding-left:23px; background-color:white}
.r2 {text-indent:-23px; padding-left:23px; background-color:#B3C8E8}
</style>
<?php echo $cfg_js_root ?>
<script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config.js"></script>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script language="JavaScript">
  function toggle(objectID) {
    if (document.getElementById(objectID).className == 'r2') {
      document.getElementById(objectID).className = 'r1';
    } else {
      document.getElementById(objectID).className = 'r2';
    }
  }
</script>
</head>

<body>

<table class="header" cellspacing="0" cellpadding="0" border="0" style="font-size:80%">
<tr><th>
  <div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="details.php?module=<?php echo $_GET['module']; ?>"><?php echo $_GET['module']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="list_ref_material.php?module=<?php echo $_GET['module']; ?>"><?php echo $string['referencematerial']; ?></a></div>
  <div style="font-size:220%; font-weight:bold; margin-left:10px">Reference Material</div>
</th></tr>
<tr><th class="bevel"></th></tr>
</table>

<form action="<?php echo $_SERVER['PHP_SELF'] . '?refID=' . $_GET['refID']; ?>" method="post" charset="UTF-8">
<br />
<table border="0" style="text-align:left; margin-left:auto; margin-right:auto; font-size:80%">
<tr><td><?php echo $string['name']; ?> <input type="text" name="title" size="40" value="<?php echo $title; ?>" />&nbsp;&nbsp;&nbsp;<?php echo $string['width']; ?> <select name="width"><?php
for ($size=200; $size<850; $size+=50) {
  if ($width == $size) {
    echo "<option value=\"$size\" selected>" . $size . "px</option>\n";
  } else {
    echo "<option value=\"$size\">" . $size . "px</option>\n";
  }
}
?></select></td><td><?php echo $string['modules']; ?></td></tr>
<tr><td><textarea name="ref_content" id="ref_content" rows="40" cols="100" style="height:600px" class="mceEditor"><?php echo $content; ?></textarea></td><td style="vertical-align:top">
<?php
  echo "<div style=\"margin-top:1px; display:block; width:400px; height:604px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
  $modules_array = array();
    
  $module_array = search_utils::get_teams($teams, $userroles, $userID, $mysqli);
  $module_no = 0;
  $old_school = '';
  foreach ($module_array as $module) {
    if ($module['school'] != $old_school) {
      echo "<div style=\"padding-top:2px\"><strong>" . $module['school'] . "</strong></div>";
    }
    $match = false;
    foreach ($ref_modules as $separate_module) {
      if ($separate_module == $module['recordid']) $match = true;
    }
    if ($match == true) {
      if (in_array($module['id'],$teams) or strpos($userroles,'SysAdmin') !== false) {
        echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no');\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['recordid'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
      } else {
        echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['id'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $module['recordid'] . "\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
      }
    } else {
      echo "<div class=\"r1\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no');\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['recordid'] . "\">&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
    }
    $module_no++;  
    $old_school = $module['school'];        
  }
  echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
?>
</td>
</tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok']; ?>" style="width:100px; font-size:90%" />&nbsp;&nbsp;<input onclick="history.back();" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px; font-size:90%" /></td></tr>
</table>
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
</form>

</body>
</html>
