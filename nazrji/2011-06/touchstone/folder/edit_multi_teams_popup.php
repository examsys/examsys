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
  require '../include/errors.inc';
  
  if (isset($_POST['submit'])) {
    // Clear the team of all members.
    $result = $mysqli->prepare("DELETE FROM teams WHERE memberID=?");
    $result->bind_param('i', $_POST['userID']);
    $result->execute();  
    $result->close();
    
    // Insert a record for each team member.
    for ($i=0; $i<$_POST['module_no']; $i++) {
      if (isset($_POST["mod$i"]) and $_POST["mod$i"] != '') {
        $result = $mysqli->prepare("INSERT INTO teams VALUES (NULL, ?, ?, NULL, 'System')");
        $result->bind_param('si', $_POST["mod$i"], $_POST['userID']);
        $result->execute();  
        $result->close();
      }
    }
?>
<html>
<head>
<title>Manage Teams</title>
<script language="JavaScript">
  function closeWindow() {
    window.opener.location.href = '../users/details.php?userID=<?php echo $_POST['userID']; ?>&tab=teams';
    self.close();
  }
</script>
</head>
<body onload="closeWindow()">
</body>
</html>
<?php
  } else {
?>
<html>
<head>
<title>Manage Teams</title>
<style>
  body {font-family:Arial,sans-serif; font-size:90%; background-color:#F1F5FB; color:black; margin:8px 4px 4px 4px}
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
</script>
</head>
<body>
<form name="teamform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post">
<div style="font-weight:bold; color:#1E3287">Teams:</div>
<?php
  $user_teams = array();
  $result = $mysqli->prepare("SELECT name FROM teams WHERE type='System' AND memberID=?");
  $result->bind_param('i', $_GET['userID']);
  $result->execute();
  $result->bind_result($name);
  while ($result->fetch()) {
    $user_teams[] = $name;
  }
  $result->close();

  $old_school = '';
  $mod_no = 0;
  echo "<div style=\"width:100%; height:660px; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%; background-color:white\">";

  $result = $mysqli->prepare("SELECT school, moduleid, fullname FROM modules, schools WHERE modules.schoolid=schools.id AND active=1 ORDER BY school, moduleid");
  $result->execute();
  $result->bind_result($school, $moduleid, $fullname);
  while ($result->fetch()) {
    if ($old_school != $school) {
      echo "<table border=\"0\" style=\"margin-top:10px; width:100%; background-color:white; color:#1E3287\"><tr><td><nobr>$school</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    }
  
    $match = false;
    foreach ($user_teams as $individual_team) {
      if ($individual_team == $moduleid) $match = true;
    }
   
    if ($match == true) {
      echo "<div style=\"background-color:#B3C8E8\" id=\"divmod$mod_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$mod_no')\" name=\"mod$mod_no\" value=\"$moduleid\" checked />";
    } else {
      echo "<div style=\"background-color:white\" id=\"divmod$mod_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$mod_no')\" name=\"mod$mod_no\" value=\"$moduleid\" />";
    }
    echo "&nbsp;$moduleid: $fullname</span></div>\n";
    $old_school = $school;
    $mod_no++;
  }
  $result->close();
  echo "<input type=\"hidden\" name=\"module_no\" value=\"$mod_no\" /><input type=\"hidden\" name=\"userID\" value=\"" . $_GET['userID'] . "\" /></div></td>\n</tr>\n";
?>
<br />
<div align="center"><input style="width:120px" type="submit" name="submit" value="OK" />&nbsp;<input style="width:120px" type="submit" name="cancel" value="Cancel" onclick="window.close()" /></div>

</form>
</body>
</html>
<?php
  }
  $mysqli->close();
?>