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
  <title>Properties</title>

  <style>
    body {font-family:Arial,sans-serif; color: black;margin:0px; background-color:#EEEEEE}
    td {font-size:90%}
    input, textarea {font-family:Arial,sans-serif}
  </style>

  <script language="JavaScript">
    function toggle(objectID) {
      if (document.getElementById(objectID).style.backgroundColor == 'white') {
        document.getElementById(objectID).style.backgroundColor = 'highlight';
        document.getElementById(objectID).style.color = 'white';
      } else {
        document.getElementById(objectID).style.backgroundColor = 'white';
        document.getElementById(objectID).style.color = 'black';
      }
    }

    function checkForm() {
      if (edit_form.folder.value == "") {
        alert ("Please enter a name for the Folder.");
        return false;
      }    
    }

    function closeWindow() {
      window.opener.location.href = '/touchstone/folder/details.php?folder=<?php echo $folderID; ?>';
      window.close();
    }
     
    function illegalChar(codeID) {
      if (codeID == 38) {
        alert("Character '&' illegal - please use alternative characters in folder name.");
      } else if (codeID == 59) {
        alert("Character ';' illegal - please use alternative characters in folder name.");
      } else if (codeID == 63) {
        alert("Character '?' illegal - please use alternative characters in folder name.");
      } else if (codeID == 64) {
        alert("Character '@' illegal - please use alternative characters in folder name.");
      } else if (codeID == 94) {
        alert("Character '^' illegal - please use alternative characters in folder name.");
      }
      event.returnValue = false;
    }
  </script>
</head>
<?php

if (isset($_POST['Submit'])) {
  $module_string = '';
  for ($i=0; $i<$_POST['module_no']; $i++) {
    if ($_POST['module' . $i] != '') {
      if ($module_string == '') {
        $module_string = $_POST['module' . $i];
      } else {
        $module_string .= ',' . $_POST['module' . $i];
      }
    }
  }
  
  if (strtolower($new_folder) != strtolower($_POST['old_folder'])) {
    $result = $mysqli->prepare("SELECT name FROM folders WHERE name=? AND ownerID=? LIMIT 1");
    $result->bind_param('si', $new_folder, $userID);
    $result->execute();
    $result->store_result();
    $result->bind_result($name);
    $result->fetch();
    if ($result->num_rows > 0) {
      ?>
        <html>
        <body>
        <form>
          <br />Warning folder name already used!<br />&nbsp;<div align="center"><input type="button" name="back" value="&lt; Back" onclick="javascript: history.go(-1)" /></div>
        </form>
        </body>
        </html>
      <?php
      $result->free_result();
      $result->close();
      exit;    
    } else {

      // Alter the name of the folder in the 'folders' table first.
      $editProperties = $mysqli->prepare("UPDATE folders SET name=?, team_name=?, color=? WHERE name=? AND ownerID=?");
      $editProperties->bind_param('ssssi', stripslashes($new_folder), $module_string, $_POST['color'], stripslashes($_POST['old_folder']), $userID);
      $editProperties->execute();  
      $editProperties->close();

      // Alter the prefix of any child folders.
      if (!$mysqli->query("UPDATE folders SET name=REPLACE(name,\"" . stripslashes($_POST['old_folder']) . ";\",\"" . stripslashes($new_folder) . ";\") WHERE name LIKE \"" . stripslashes($_POST['old_folder']) . ";%\" AND ownerID=$userID")) {
        echo "<p class=\"error\">Folders Edit Error 2</p>\n<p>Query: " . $editProperties . "</p>\n<p>" . mysql_error($link_id) . "</p>\n";
        echo "</body>\n</html>\n";
        exit;
      }
      
      // Next update the folder name in the 'properties' table (moves papers).
      $editProperties = $mysqli->prepare("UPDATE properties SET folder=? WHERE folder=? AND paper_ownerID=?");
      $editProperties->bind_param('ssi', stripslashes($new_folder), stripslashes($_POST['old_folder']), $userID);
      $editProperties->execute();  
      $editProperties->close();
    }
    $result->free_result();
    $result->close();
  } else {
    
    $editProperties = $mysqli->prepare("UPDATE folders SET team_name=?, color=? WHERE name=? AND ownerID=?");
    $editProperties->bind_param('sssi', $module_string, $_POST['color'], stripslashes($_POST['old_folder']), $userID);
    $editProperties->execute();  
    $editProperties->close();
  }
  ?>
    <body onload="closeWindow();">
    <form>
      <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="updateParent('<?php echo $paper; ?>');" /></div>
    </form>
  <?php
} else {
?>
<body>
<table border="0" cellpadding="6" cellspacing="0" width="100%">
<tr>
<td valign="middle" style="background-color:white; text-align:left"><img src="../artwork/properties.png" width="48" height="48" alt="Properties" />&nbsp;&nbsp;<span style="font-family:Arial,sans-serif; font-size:18pt; font-weight:bold; color:#5582D2">Folder Properties</span></td>
</tr>
<tr>
<td style="text-align: left">
  <form name="edit_form" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <br />
    <?php
      $result = $mysqli->prepare("SELECT team_name, name, color, DATE_FORMAT(created,'%d/%m/%Y %T'), title, initials, surname FROM folders, users WHERE folders.ownerID=users.id AND folders.id=?");
      $result->bind_param('i', $_GET['folder']);
      $result->execute();
      $result->bind_result($folder_team, $full_path, $color, $created, $title, $initials, $surname);
      $result->fetch();
      $result->close();
      $owner = $title . ' ' . $initials . ', ' . $surname;

      $folder_array = explode(';',stripslashes($full_path));
      $sections = substr_count(stripslashes($full_path),';');
      $current_folder = $folder_array[$sections];
      $prefix = substr(stripslashes($full_path),0,strrpos(stripslashes($full_path),';'));
      echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" style=\"width:100%\" >\n";
      echo "<tr><td align=\"right\"><strong>Folder&nbsp;Name&nbsp;</strong></td><td colspan=\"3\"><input type=\"text\" size=\"50\" maxlength=\"255\" value=\"$current_folder\" name=\"folder\" onkeypress=\"if (event.keyCode == 38 || event.keyCode == 59 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94)  illegalChar(event.keyCode);\" /><input type=\"hidden\" name=\"old_folder\" value=\"" . stripslashes($full_path) . "\"></td></tr>\n";
      echo "<input type=\"hidden\" name=\"folderID\" value=\"" . $_GET['folder'] . "\" />";
      echo "<tr><td align=\"right\" valign=\"middle\"><strong>Colour&nbsp;</strong></td><td>";
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
      echo "</td></tr>\n";
      echo "<tr><td align=\"right\" valign=\"top\"><strong>Owner&nbsp;</strong></td><td>$owner</td></tr>\n";
      echo "<tr><td align=\"right\" valign=\"top\"><strong>Created&nbsp;</strong></td><td>$created</td></tr>\n";
       
      echo "<tr><td align=\"right\"><strong>Team(s)&nbsp;</strong></td><td><div style=\"background-color:white; display:block; height:200px; width:100%; overflow-y:scroll; border:1px solid highlight; font-size:90%\">";
      $modules_array = explode(',',$folder_team);
      $total_modules = array_merge($teams, $modules_array);
	    $module_sql = implode("','", $total_modules);
       
	    if ($module_sql != '') $module_sql = "'$module_sql'";
	      if (strpos($userroles,'SysAdmin') !== false) {
          $module_details = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM modules ORDER BY moduleID");
        } elseif (strpos($userroles,'Admin') !== false) {
          $module_details = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM modules, schools WHERE modules.school=schools.school AND faculty='$faculty' ORDER BY moduleID");
        } else {
          $module_details = $mysqli->query("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($module_sql) ORDER BY moduleID");
        }
        $module_no = 0;
        while ($row = $module_details->fetch_assoc()) {
          $match = false;
          foreach ($modules_array as $separate_module) {
            if ($separate_module == $row['moduleid']) $match = true;
          }
          if ($match == true) {
            if (in_array($row['moduleid'],$teams) or strpos($userroles,'SysAdmin') !== false) {
              echo "<div style=\"background-color:highlight; color:white\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $row['moduleid'] . "\" checked>&nbsp;" . $row['moduleid'] . ": " . substr($row['fullname'],0,60) . "</div>\n";
            } else {
              echo "<div style=\"background-color:highlight; color:white\" id=\"divmodule$module_no\"><input type=\"checkbox\" name=\"dummymodule$module_no\" value=\"" . $row['moduleid'] . "\" checked disabled><input type=\"checkbox\" name=\"module$module_no\" id=\"module$module_no\" style=\"display:none\" value=\"" . $row['moduleid'] . "\" checked>&nbsp;" . $row['moduleid'] . ": " . substr($row['fullname'],0,60) . "</div>\n";
            }
          } else {
            echo "<div style=\"background-color:white; color:black\" id=\"divmodule$module_no\"><input type=\"checkbox\" onclick=\"toggle('divmodule$module_no')\" name=\"module$module_no\" id=\"module$module_no\" value=\"" . $row['moduleid'] . "\">&nbsp;" . $row['moduleid'] . ": " . substr($row['fullname'],0,60) . "</div>\n";
          }
          $module_no++;
        }
        $module_details->close();
        echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n</td></tr>";
      ?>
    </table>
  <br />
  <div align="center"><input type="submit" style="width:100px" name="Submit" value="Save">&nbsp;&nbsp;<input type="button" name="home" style="width:100px" value="Cancel" onclick="javascript:window.close();" /></div>

</form>

</td>
</tr>
</table>
<?php
}
$mysqli->close();
?>

</body>
</html>