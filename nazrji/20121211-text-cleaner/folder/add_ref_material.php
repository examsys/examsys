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
require_once '../classes/searchutils.class.php';

if (isset($_POST['submit'])) {
  // Write the reference material
  $result = $mysqli->prepare("INSERT INTO reference_material VALUES (NULL, ?, ?, ?, NOW(), NULL)");
  $result->bind_param('sss', $_POST['title'], $_POST['ref_content'], $_POST['width']);
  $result->execute();
  
  $refID = $mysqli->insert_id;
  
  // Add it to the modules
  for ($i=0; $i<$_POST['module_no']; $i++) {
    if (isset($_POST['module' . $i])) {
      $result = $mysqli->prepare("INSERT INTO reference_modules VALUES (NULL, ?, ?)");
      $result->bind_param('ii', $refID, $_POST['module' . $i]);
      $result->execute();
    }
  }
  
  header("location: list_ref_material.php?module=" . $_POST['module']);
  exit;  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <title><?php echo $string['newreferencematerial'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    table {font-size:100%}
    input, textarea {line-height:140%}
    .r1 {text-indent:-23px; padding-left:23px; background-color:white}
    .r2 {text-indent:-23px; padding-left:23px; background-color:#B3C8E8}
  </style>
  <?php echo $configObject->get('cfg_js_root') ?>
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
  <div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="details.php?module=<?php echo $_GET['module']; ?>"><?php echo module_utils::get_moduleid_from_id($_GET['module'], $mysqli); ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="list_ref_material.php?module=<?php echo $_GET['module']; ?>"><?php echo $string['referencematerial']; ?></a></div>
  <div style="font-size:220%; font-weight:bold; margin-left:10px"><?php echo $string['newreferencematerial']; ?></div>
</th></tr>
<tr><th class="bevel"></th></tr>
</table>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" charset="UTF-8">
<br />
<table border="0" style="text-align:left; margin-left:auto; margin-right:auto; font-size:80%">
<tr><td><?php echo $string['name']; ?> <input type="text" name="title" size="40" />&nbsp;&nbsp;&nbsp;<?php echo $string['width']; ?> <select name="width"><?php
$width = 400;
for ($size=200; $size<850; $size+=50) {
  if ($width == $size) {
    echo "<option value=\"$size\" selected>" . $size . "px</option>\n";
  } else {
    echo "<option value=\"$size\">" . $size . "px</option>\n";
  }
}
?></select></td><td><?php echo $string['modules']; ?></td></tr>
<tr><td><textarea name="ref_content" id="ref_content" rows="40" cols="100" style="height:600px" class="mceEditor"></textarea></td><td style="vertical-align:top">
<?php
  echo "<div style=\"margin-top:1px; display:block; width:400px; height:604px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
  $modules_array = array();
  $module_array = $userObject->get_staff_accessable_modules();
  
  $module_no = 0;
  $old_school = '';
  foreach ($module_array as $modID=>$module) {
    if ($module['school'] != $old_school) {
      echo "<div style=\"padding-top:2px\"><strong>" . $module['school'] . "</strong></div>";
    }
    $match = false;
    if ($_GET['module'] == $modID) $match = true;
    
    if ($match == true) {
      echo "<div class=\"r2\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no');\" name=\"module$module_no\" id=\"module$module_no\" value=\"$modID\" checked>&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
    } else {
      echo "<div class=\"r1\" id=\"divmod$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$module_no');\" name=\"module$module_no\" id=\"module$module_no\" value=\"$modID\">&nbsp;" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</div>\n";
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
