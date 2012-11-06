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

if (isset($_POST['prefix']) and trim($_POST['prefix']) != '') {
  $new_folder = $_POST['prefix'] . ';' . $_POST['folder'];
} elseif (isset($_POST['folder'])) {
  $new_folder = $_POST['folder'];
}
if (isset($_POST['folderID'])) {
  $folderID = $_POST['folderID'];
} else {
  $folderID = 0;
}

if (isset($_POST['moduleID'])) {
  $moduleID = $_POST['moduleID'];
} else {
  $moduleID = 0;
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['folderproperties']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:#F1F5FB}
    td {font-size:90%}
  </style>

  <script language="JavaScript">
    function toggle(objectID) {
      if (document.getElementById(objectID).style.backgroundColor == 'white') {
        document.getElementById(objectID).style.backgroundColor = '#B3C8E8';
      } else {
        document.getElementById(objectID).style.backgroundColor = 'white';
      }
    }

    function checkForm() {
      if (edit_form.folder.value == "") {
        alert ("<?php echo $string['enteraname']; ?>");
        return false;
      }    
    }

    function closeWindow() {
      window.opener.location.href = '/folder/details.php?folder=<?php echo $folderID; ?>';
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
  $module_string = '';
  for ($i=0; $i<$_POST['module_no']; $i++) {
    if (isset($_POST['module' . $i])) {
      if ($module_string == '') {
        $module_string = $_POST['module' . $i];
      } else {
        $module_string .= ',' . $_POST['module' . $i];
      }
    }
  }
  
  if ($_POST['old_prefix'] != '') {
    $new_folder = $_POST['old_prefix'] . ';' . $new_folder;
  }
    
  if (strtolower($new_folder) != strtolower($_POST['old_folder'])) {
    $result = $mysqli->prepare("SELECT name FROM folders WHERE name=? AND ownerID=? LIMIT 1");
    $result->bind_param('si', $new_folder, $userID);
    $result->execute();
    $result->store_result();
    $result->bind_result($name);
    $result->fetch();
    if ($result->num_rows > 0) {
      $unique_name = false;
    } else {

      // Alter the name of the folder in the 'folders' table first.
      $editProperties = $mysqli->prepare("UPDATE folders SET name=?, team_name=?, color=? WHERE name=? AND ownerID=?");
      $editProperties->bind_param('ssssi', $new_folder, $module_string, $_POST['color'], $_POST['old_folder'], $userID);
      $editProperties->execute();  
      $editProperties->close();

      // Alter the prefix of any child folders.
      if (!$mysqli->query("UPDATE folders SET name=REPLACE(name,\"" . $_POST['old_folder'] . ";\",\"" . $new_folder . ";\") WHERE name LIKE \"" . $_POST['old_folder'] . ";%\" AND ownerID=$userID")) {
        echo "<p class=\"error\">Folders Edit Error 2</p>\n<p>Query: " . $editProperties . "</p>\n<p>" . mysql_error($link_id) . "</p>\n";
        echo "</body>\n</html>\n";
        exit;
      }
      
      // Next update the folder name in the 'properties' table (moves papers).
      $editProperties = $mysqli->prepare("UPDATE properties SET folder=? WHERE folder=? AND paper_ownerID=?");
      $editProperties->bind_param('ssi', $new_folder, $_POST['old_folder'], $userID);
      $editProperties->execute();  
      $editProperties->close();
    }
    $result->free_result();
    $result->close();
    
  } else {
    
    $editProperties = $mysqli->prepare("UPDATE folders SET team_name=?, color=? WHERE name=? AND ownerID=?");
    $editProperties->bind_param('sssi', $module_string, $_POST['color'], $_POST['old_folder'], $userID);
    $editProperties->execute();  
    $editProperties->close();
  }
  if ($unique_name) {
  ?>
    <body onload="closeWindow();">
    <form>
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="updateParent('<?php echo $paper; ?>');" /></div>
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
  $folder_team = $_POST['folder_team'];
  
} else {
  $result = $mysqli->prepare("SELECT team_name, name, color, DATE_FORMAT(created, '$cfg_long_date_time'), title, initials, surname FROM folders, users WHERE folders.ownerID=users.id AND folders.id=?");
  $result->bind_param('i', $_GET['folder']);
  $result->execute();
  $result->bind_result($folder_team, $full_path, $color, $created, $title, $initials, $surname);
  $result->fetch();
  $result->close();
  
  $owner = $title . ' ' . $initials . ', ' . $surname;
}

if ($unique_name) {
  echo "<body>\n";
} else {
  echo "<body onload=\"javascript:alert('" . $string['nameinuse'] . "')\">\n";
}
?>
<body>
<table border="0" cellpadding="6" cellspacing="0" width="100%">
<tr>
<td valign="middle" style="background-color:white; text-align:left"><img src="../artwork/properties.png" width="48" height="48" alt="Properties" />&nbsp;&nbsp;<span class="midblue_header" style="font-size:160%; font-weight:bold"><?php echo $string['folderproperties']; ?></span></td>
</tr>
<tr>
<td style="text-align:left">
  <form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF'] . '?folder=' . $_GET['folder']; ?>">
    <br />
    <?php

      $folder_array = explode(';',$full_path);
      $sections = substr_count($full_path,';');
      $current_folder = $folder_array[$sections];
      $prefix = substr($full_path,0,strrpos($full_path,';'));
      echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" style=\"width:100%\" >\n";
      echo "<tr><td align=\"right\"><nobr>" . $string['foldername'] . "&nbsp;</nobr></td><td colspan=\"3\"><input";
      if (!$unique_name) {
        echo ' style="color:#800000; background-color:#FFC0C0; border:1px solid #400000"';
      }
      echo " type=\"text\" size=\"50\" maxlength=\"255\" value=\"$current_folder\" name=\"folder\" onkeypress=\"if (event.keyCode == 59) illegalChar(event.keyCode);\" /><input type=\"hidden\" name=\"old_folder\" value=\"$full_path\"><input type=\"hidden\" name=\"old_prefix\" value=\"$prefix\"></td></tr>\n";
      echo "<input type=\"hidden\" name=\"folderID\" value=\"" . $_GET['folder'] . "\" />";
      echo "<tr><td align=\"right\" valign=\"middle\">" . $string['colour'] . "&nbsp;</td><td>";
      echo "<input type=\"radio\" name=\"color\" value=\"yellow\"";
      if ($color == 'yellow') echo ' checked';
      echo " /><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Yellow\" border=\"0\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"red\"";
      if ($color == 'red') echo ' checked';
      echo " /><img src=\"../artwork/red_folder.png\" width=\"48\" height=\"48\" alt=\"Red\" border=\"0\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"green\"";
      if ($color == 'green') echo ' checked';
      echo " /><img src=\"../artwork/green_folder.png\" width=\"48\" height=\"48\" alt=\"Green\" border=\"0\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"blue\"";
      if ($color == 'blue') echo ' checked';
      echo " /><img src=\"../artwork/blue_folder.png\" width=\"48\" height=\"48\" alt=\"Blue\" border=\"0\" />";
      echo "<input type=\"radio\" name=\"color\" value=\"grey\"";
      if ($color == 'grey') echo ' checked';
      echo " /><img src=\"../artwork/grey_folder.png\" width=\"48\" height=\"48\" alt=\"Grey\" border=\"0\" />";
      echo "</td></tr>\n";
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['owner'] . "&nbsp;</td><td>$owner</td></tr>\n";
      echo "<tr><td align=\"right\" valign=\"top\">" . $string['created'] . "&nbsp;</td><td>$created</td></tr>\n";
       
      echo "<tr><td align=\"right\">" . $string['teams'] . "&nbsp;</td><td><div style=\"background-color:white; display:block; height:200px; width:100%; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">";
      $modules_array = explode(',',$folder_team);
      $total_modules = array_merge($teams, $modules_array);
	    $module_sql = implode("','", $total_modules);
       
	    if ($module_sql != '') $module_sql = "'$module_sql'";
	      if (strpos($userroles,'SysAdmin') !== false) {
          $module_details = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules ORDER BY moduleID");
        } elseif (strpos($userroles,'Admin') !== false) {
          $module_details = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules, schools WHERE modules.school=schools.school AND faculty='$faculty' ORDER BY moduleID");
        } else {
          $module_details = $mysqli->prepare("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($module_sql) ORDER BY moduleID");
        }
        $module_details->execute();
        $module_details->bind_result($moduleid, $fullname);
        $module_no = 0;
        while ($module_details->fetch()) {
          $match = false;
          foreach ($modules_array as $separate_module) {
            if ($separate_module == $moduleid) $match = true;
          }
          if ($match == true) {
            if (in_array($moduleid, $teams) or strpos($userroles, 'SysAdmin') !== false) {
              echo "<div style=\"background-color:#B3C8E8\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $moduleid . "\" checked>&nbsp;" . $moduleid . ": " . substr($fullname,0,60) . "</div>\n";
            } else {
              echo "<div style=\"background-color:#B3C8E8\" id=\"divmodule$module_no\"><input type=\"checkbox\" name=\"dummymodule$module_no\" value=\"" . $moduleid . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $moduleid . "\" checked>&nbsp;" . $moduleid . ": " . substr($fullname,0,60) . "</div>\n";
            }
          } else {
            echo "<div style=\"background-color:white\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $moduleid . "\">&nbsp;" . $moduleid . ": " . substr($fullname,0,60) . "</div>\n";
          }
          $module_no++;
        }
        $module_details->close();
        echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n</td></tr>";
      ?>
    </table>
  <br />
  <div align="center"><input type="submit" style="width:100px" name="Submit" value="<?php echo $string['save']; ?>">&nbsp;&nbsp;<input type="button" name="home" style="width:100px" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></div>
<?php
  echo '<input type="hidden" name="created" value="' . $created . '" />';
  echo '<input type="hidden" name="owner" value="' . $owner . '" />';
  echo '<input type="hidden" name="folder_team" value="' . $folder_team . '" />';
?>
</form>

</td>
</tr>
</table>
<?php
$mysqli->close();
?>

</body>
</html>