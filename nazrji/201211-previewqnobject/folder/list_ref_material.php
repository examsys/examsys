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

check_var('module', 'GET', true, false);
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $string['referencematerial'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
  .l {cursor:pointer}
  </style>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script language="javascript">
    function selRef(divID, evt) {
      tmp_ID = document.myform.oldID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
        document.getElementById(tmp_ID).style.color = 'black';
      }

      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';

      document.myform.oldID.value = divID;
      document.myform.divID.value = divID;

      document.getElementById(divID).style.backgroundColor = '#B3C8E8';
      evt.cancelBubble = true;
    }

    function deselRef() {
      tmp_ID = document.myform.oldID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
      document.myform.oldID.value = '';
      document.getElementById('menu1b').style.display = 'none';
      document.getElementById('menu1a').style.display = 'block';
    }

    function lon(lineID) {
      if (lineID != document.myform.oldID.value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function loff(lineID) {
      if (lineID != document.myform.oldID.value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }
    
    function editReference() {
      window.location="<?php echo $cfg_root_path ?>/folder/edit_ref_material.php?refID=" + document.getElementById('divID').value + "&module=<?php echo $_GET['module']; ?>";
    }
    
    function deleteReference() {
      notice=window.open("<?php echo $cfg_root_path ?>/delete/check_delete_ref_material.php?refID=" + document.getElementById('divID').value + "&module=<?php echo $_GET['module']; ?>","notice","width=420,height=170,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      notice.moveTo(screen.width/2-210,screen.height/2-85);
      if (window.focus) {
        notice.focus();
      }
    }
  </script>
</head>

<body onclick="deselRef()">
<?php
  $reference_materials = array();
  $old_id = '';

  $result = $mysqli->prepare("SELECT reference_material.id, reference_material.title, modules.moduleid FROM (reference_material, reference_modules, modules) WHERE reference_material.id=reference_modules.refID AND reference_material.deleted IS NULL AND reference_modules.idMod=modules.id AND modules.moduleid=? ORDER BY reference_material.id");
  $result->bind_param('s', $_GET['module']);
  $result->execute();
  $result->bind_result($id, $title, $moduleid);
  while ($result->fetch()) {
    if (isset($reference_materials[$id]['modules'])) {
      $reference_materials[$id]['modules'] = $reference_materials[$id]['modules'] . ', ' . $moduleid;
    } else {
      $reference_materials[$id]['modules'] = $moduleid;
    }
    $reference_materials[$id]['title'] = $title;
    $old_id = $id;
  }
  $result->close();

  require '../include/reference_material_options.inc';
?>
<div id="content" class="content">

<table class="header">
<tr>
<?php
  echo "<th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./details.php?module=" . $_GET['module'] . "\">" . $_GET['module'] . "</a></div><div style=\"margin-left:10px; font-size:200%; font-weight:bold\">" . $string['referencematerial'] . "</th>\n";
?>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(237); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>

<?php
foreach ($reference_materials as $id => $details) {
  echo "<tr id=\"$id\" onclick=\"selRef($id,event)\" ondblclick=\"editReference($id)\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td><img src=\"/artwork/ref_16.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\">&nbsp;" . $details['title'] . "</td><td>" . $details['modules'] . "</td></tr>\n";
}
echo "</table>\n";
$mysqli->close();
?>

</div>

</body>
</html>