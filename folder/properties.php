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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$folderID = check_var('folder', 'GET', true, false, true);

$ownerID = 0;

$result = $mysqli->prepare("SELECT ownerID FROM folders WHERE id = ?");
$result->bind_param('i', $folderID);
$result->execute();
$result->bind_result($ownerID);
$result->fetch();
$result->close();

if ($ownerID != $userObject->get_user_ID()) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['prefix']) and trim($_POST['prefix']) != '') {
  $new_folder = $_POST['prefix'] . ';' . $_POST['folder'];
} elseif (isset($_POST['folder'])) {
  $new_folder = $_POST['folder'];
}

if (isset($_POST['moduleID'])) {
  $moduleID = $_POST['moduleID'];
} else {
  $moduleID = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['folderproperties']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:90%}
    input[type=checkbox] {margin-left:20px; margin-right:8px}
    .r1 {background-color:white}
    .r2 {background-color:#FFBD69}
		.school {margin-top:10px; width:100%; background-color:white; color:#1E3287}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
    });
    
    function toggle(objectID) {
      if ($('#' + objectID).hasClass('r2')) {
        $('#' + objectID).addClass('r1');
        $('#' + objectID).removeClass('r2');
      } else {
        $('#' + objectID).addClass('r2');
        $('#' + objectID).removeClass('r1');
      }
    }
		
    function checkForm() {
      if ($('#folder').val() == "") {
        alert ("<?php echo $string['enteraname']; ?>");
        return false;
      }    
    }

    function closeWindow() {
      window.opener.location.href = '/folder/index.php?folder=<?php echo $folderID; ?>';
      window.close();
    }
     
    function illegalChar(codeID) {
      if (codeID == 59) {
        alert("Character ';' illegal - please use alternative characters in folder name.");
      }
      event.returnValue = false;
    }
  </script>
</head>
<?php
$unique_name = true;

if (isset($_POST['Submit'])) {
  $module_array = array();
  for ($i=0; $i<$_POST['module_no']; $i++) {
    if (isset($_POST['module' . $i])) {
      $module_array[] = $_POST['module' . $i];
    }
  }
  
  if ($_POST['old_prefix'] != '') {
    $new_folder = $_POST['old_prefix'] . ';' . $new_folder;
  }
    
  if (strtolower($new_folder) != strtolower($_POST['old_folder'])) {
    $result = $mysqli->prepare("SELECT name FROM folders WHERE name = ? AND ownerID = ? LIMIT 1");
    $result->bind_param('si', $new_folder, $userObject->get_user_ID());
    $result->execute();
    $result->store_result();
    $result->bind_result($name);
    $result->fetch();
    if ($result->num_rows > 0) {
      $unique_name = false;
    } else {
      // Alter the name of the folder in the 'folders' table first.
      $editProperties = $mysqli->prepare("UPDATE folders SET name = ?, color = ? WHERE id = ? AND ownerID = ?");
      $editProperties->bind_param('sssi', $new_folder, $_POST['color'], $folderID, $userObject->get_user_ID());
      $editProperties->execute();  
      $editProperties->close();

      $result2 = $mysqli->prepare("UPDATE folders SET name = REPLACE(name, ? , ?) WHERE name LIKE ? AND ownerID = ?");
      $t0 = $_POST['old_folder'] . ';';
      $t1 = $new_folder . ';';
      $t2 = $_POST['old_folder'] . ';%';
      $result2->bind_param('sssi', $t0, $t1, $t2, $userObject->get_user_ID());
      $result2->execute();
      $result2->close();

      // Alter the prefix of any child folders.
      if ($mysqli->error) {
        echo "<p class=\"error\">Folders Edit Error</p>\n<p>" . $string['showerror'] . "</p>\n";
        echo "</body>\n</html>\n";
        exit;
      }
      
      // Next update the folder name in the 'properties' table (moves papers).
      $editProperties = $mysqli->prepare("UPDATE properties SET folder = ? WHERE folder = ? AND paper_ownerID = ?");
      $editProperties->bind_param('ssi', $new_folder, $_POST['old_folder'], $userObject->get_user_ID());
      $editProperties->execute();  
      $editProperties->close();
    }
    $result->free_result();
    $result->close();
  } else {
    $editProperties = $mysqli->prepare("UPDATE folders SET color = ? WHERE id = ? AND ownerID = ?");
    $editProperties->bind_param('sii', $_POST['color'], $folderID, $userObject->get_user_ID());
    $editProperties->execute();  
    $editProperties->close();
  }

  if (count($module_array) > 0 ) {
    //set the folder staff_modules
    $editProperties = $mysqli->prepare("DELETE FROM folders_modules_staff WHERE folders_id = ?");
    $editProperties->bind_param('i', $folderID);
    $editProperties->execute();  
    $editProperties->close();

    $editProperties = $mysqli->prepare("INSERT INTO folders_modules_staff VALUES(?, ?)");
    foreach ($module_array as $idMod) {
      $editProperties->bind_param('ii', $folderID,  $idMod);
      $editProperties->execute();  
    }
    $editProperties->close();

  }

  if ($unique_name) {
  ?>
    <body onload="closeWindow();">
    <form autocomplete="off">
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="closeWindow();" /></div>
    </form>
    </body>
    </html>
  <?php
    exit;
  }
  $color = $_POST['color'];
  $created = $_POST['created'];
  $owner = $_POST['owner'];
  $full_path = $_POST['folder'];
  
} else {
  $result = $mysqli->prepare("SELECT name, color, DATE_FORMAT(created, '{$configObject->get('cfg_long_date_time')}'), title, initials, surname FROM folders, users WHERE folders.ownerID = users.id AND folders.id = ?");
  $result->bind_param('i', $folderID);
  $result->execute();
  $result->bind_result($full_path, $color, $created, $title, $initials, $surname);
  $result->fetch();
  $result->close();
  
  $owner = $title . ' ' . $initials . ', ' . $surname;

  $folder_staff_modules = array();
  $result = $mysqli->prepare("SELECT idMod, moduleID FROM folders, folders_modules_staff, modules WHERE folders.id = folders_modules_staff.folders_id AND modules.id = folders_modules_staff.idMod AND folders.id = ?");
  $result->bind_param('i', $folderID);
  $result->execute();
  $result->bind_result($idMod, $moduleID);
  while ($result->fetch()) {
    $folder_staff_modules[$idMod] = $moduleID;
  }
  $result->close();

}

if ($unique_name) {
  echo "<body>\n";
} else {
  echo "<body onload=\"javascript:alert('" . $string['nameinuse'] . "')\">\n";
}
?>
<body>
<form id="theform" name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?folder=' . $_GET['folder']; ?>" autocomplete="off">
<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr>
<td style="width:48px; background-color:white; text-align:left"><img src="../artwork/properties.png" width="48" height="48" alt="Properties" /></td><td style="background-color:white; text-align:left">&nbsp;&nbsp;<span class="midblue_header" style="font-size:160%; font-weight:bold"><?php echo $string['folderproperties']; ?></span></td>
</tr>
<tr>
<td style="text-align:left" colspan="2">
    <br />
    <?php

      $folder_array = explode(';',$full_path);
      $sections = substr_count($full_path,';');
      $current_folder = $folder_array[$sections];
      $prefix = substr($full_path,0,strrpos($full_path,';'));
      echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" style=\"width:100%\" >\n";
      echo "<tr><td style=\"text-align:right\"><nobr>" . $string['foldername'] . "&nbsp;</nobr></td><td colspan=\"3\"><input";
      if (!$unique_name) {
        echo ' style="color:#800000; background-color:#FFC0C0; border:1px solid #400000"';
      }
      echo " type=\"text\" size=\"50\" maxlength=\"255\" value=\"$current_folder\" id=\"folder\" name=\"folder\" required onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><input type=\"hidden\" name=\"old_folder\" value=\"$full_path\"><input type=\"hidden\" name=\"old_prefix\" value=\"$prefix\"></td></tr>\n";
      echo "<input type=\"hidden\" name=\"folderID\" value=\"" . $_GET['folder'] . "\" />";
      echo "<tr><td align=\"right\" valign=\"middle\">" . $string['colour'] . "&nbsp;</td><td>";
      echo "<input type=\"radio\" name=\"color\" value=\"yellow\"";
      if ($color == 'yellow') echo ' checked';
      echo " /><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Yellow\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"red\"";
      if ($color == 'red') echo ' checked';
      echo " /><img src=\"../artwork/red_folder.png\" width=\"48\" height=\"48\" alt=\"Red\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"green\"";
      if ($color == 'green') echo ' checked';
      echo " /><img src=\"../artwork/green_folder.png\" width=\"48\" height=\"48\" alt=\"Green\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"blue\"";
      if ($color == 'blue') echo ' checked';
      echo " /><img src=\"../artwork/blue_folder.png\" width=\"48\" height=\"48\" alt=\"Blue\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"grey\"";
      if ($color == 'grey') echo ' checked';
      echo " /><img src=\"../artwork/grey_folder.png\" width=\"48\" height=\"48\" alt=\"Grey\" />";
      echo "</td></tr>\n";
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['owner'] . "&nbsp;</td><td>$owner</td></tr>\n";
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['created'] . "&nbsp;</td><td>$created</td></tr>\n";
       
      echo "<tr><td align=\"right\">" . $string['teams'] . "&nbsp;</td><td><div style=\"background-color:white; display:block; height:320px; width:100%; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";

      $module_no = 0;
      $old_school = '';
      $old_schoolcode = '';
      
      foreach ($userObject->get_staff_accessable_modules() as $IdMod => $module) {
        if (is_null($module['schoolcode'])) {
          if ($module['school'] != $old_school or !is_null($old_schoolcode)) {
            echo "<table border=\"0\" class=\"school\"><tr><td><nobr>" . $module['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
          }
        } else {
          if ($module['schoolcode'] != $old_schoolcode) {
            echo "<table border=\"0\" class=\"school\"><tr><td><nobr>" . $module['schoolcode'] . ' ' . $module['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
          }
        }
        if (isset($folder_staff_modules[$IdMod])) {
          if ($userObject->is_staff_user_on_module($IdMod) or $userObject->has_role('SysAdmin')) {
            echo "<div class=\"r2\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['idMod'] . "\" checked>&nbsp;<label for=\"module$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
          } else {
            echo "<div class=\"r2\" id=\"divmodule$module_no\"><input type=\"checkbox\" name=\"dummymodule$module_no\" value=\"" . $module['id'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $module['idMod'] . "\" checked>&nbsp;<label for=\"module$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
          }
        } else {
          echo "<div class=\"r1\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $module['idMod'] . "\">&nbsp;<label for=\"module$module_no\">" . $module['id'] . ": " . substr($module['fullname'],0,60) . "</label></div>\n";
        }
        $module_no++;
        $old_school = $module['school'];
        $old_schoolcode = $module['schoolcode'];
      }

      echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n</td></tr>";
      ?>
    </table>
  <div style="text-align:center; padding-top:10px"><input type="submit" class="ok" name="Submit" value="<?php echo $string['save']; ?>"><input type="button" name="home" class="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></div>
</td>
</tr>
</table>

<?php
  echo '<input type="hidden" name="created" value="' . $created . '" />';
  echo '<input type="hidden" name="owner" value="' . $owner . '" />';
?>
</form>
<?php
$mysqli->close();
?>

</body>
</html>