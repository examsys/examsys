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
  
  check_var('teamID', 'GET', true, false);
  $teamID = $_GET['teamID'];
  
  if (isset($_POST['submit'])) {
    // Clear the team of all members.
    $result = $mysqli->prepare("DELETE FROM teams WHERE name=?");
    $result->bind_param('s', $teamID);
    $result->execute();  
    $result->close();
    
    // Insert a record for each team member.
    for ($i=0; $i<$_POST['staff_no']; $i++) {
      if (isset($_POST["staff$i"]) and $_POST["staff$i"] != '') {
        $result = $mysqli->prepare("INSERT INTO teams VALUES (NULL,?,?,NULL,'System')");
        $result->bind_param('si', $teamID, $_POST["staff$i"]);
        $result->execute();  
        $result->close();
      }
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['teammembers'] . ' ' . $teamID; ?></title>
<script language="JavaScript">
  function closeWindow() {
    window.opener.location.href = '../folder/details.php?module=<?php echo $teamID; ?>';
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['teammembers'] . ' ' . $_GET['teamID']; ?></title>
<style>
  body {font-family:Arial,sans-serif; font-size:90%; background-color:#F1F5FB; color:black; margin:0px}
</style>
<script language="JavaScript">
  function toggle(objectID) {
    if (document.getElementById(objectID).style.backgroundColor == 'white') {
      document.getElementById(objectID).style.backgroundColor = '#B3C8E8';
    } else {
      document.getElementById(objectID).style.backgroundColor = 'white';
    }
  }
  
  function resizeList() {
    var winW = 630, winH = 460;
    if (document.body && document.body.offsetWidth) {
      winW = document.body.offsetWidth;
      winH = document.body.offsetHeight;
    }
    if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
      winW = document.documentElement.offsetWidth;
      winH = document.documentElement.offsetHeight;
    }
    if (window.innerWidth && window.innerHeight) {
      winW = window.innerWidth;
      winH = window.innerHeight;
    }
    winH -= 105;
    document.getElementById('list').style.height = winH + 'px';
  }
</script>
</head>
<body onload="resizeList()" onresize="resizeList()">
<form name="teamform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post">

  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/team_members.png" width="32" height="32 alt="Members" /></td><td style="background-color:white; font-size:150%; color:#5582D2; border-bottom:1px solid #CCD9EA"><strong><?php echo $string['teammembers']; ?> </strong><?php echo $_GET['teamID']; ?></td></tr>
  </table>

<?php
  $team_members = array();
  $result = $mysqli->prepare("SELECT memberID FROM teams WHERE name=?");
  $result->bind_param('s', $_GET['teamID']);
  $result->execute();
  $result->bind_result($memberID);
  while ($row = $result->fetch()) {
    $team_members[] = $memberID;
  }
  $result->close();

  echo "<div style=\"height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:12px 4px 8px 4px; font-size:90%\" id=\"list\">";
  $query_string = $mysqli->query("SELECT DISTINCT id, surname, initials, first_names, title FROM users WHERE surname != '' AND roles LIKE 'Staff%' AND grade != 'left' ORDER BY surname, initials");
  $staff_no = 0;
  $old_letter = '';
  while ($row = $query_string->fetch_assoc()) {
    if ($old_letter != strtoupper(substr($row['surname'],0,1))) {
      echo "<table border=\"0\" style=\"padding-bottom:5px; width:95%; background-color:white; color:#1E3287\"><tr><td><nobr>" . strtoupper(substr($row['surname'],0,1)) . "</nobr></td><td style=\"width:95%\"><hr noshade=\"noshade\" style=\"width:100%; border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5\" /></td></tr></table>\n";
    }
  
    $match = false;
    foreach ($team_members as $member) {
      if ($member == $row['id']) $match = true;
    }
   
    if ($match == true) {
      echo "<div style=\"background-color:#B3C8E8\" id=\"divstaff$staff_no\"><input type=\"checkbox\" onclick=\"toggle('divstaff$staff_no')\" name=\"staff$staff_no\" value=\"" . $row['id'] . "\" checked />";
    } else {
      echo "<div style=\"background-color:white\" id=\"divstaff$staff_no\"><input type=\"checkbox\" onclick=\"toggle('divstaff$staff_no')\" name=\"staff$staff_no\" value=\"" . $row['id'] . "\" />";
    }
    if ($row['first_names'] != '') {
      $display_text = $row['first_names'];
    } else {
      $display_text = $row['initials'];
    }
    echo "&nbsp;" . $row['surname'] . '<span style="color:#808080">, ' . $display_text . '. ' . $row['title'] . "</span></div>\n";
    $old_letter = strtoupper(substr($row['surname'],0,1));
    $staff_no++;
  }
  $query_string->close();
  echo "<input type=\"hidden\" name=\"staff_no\" value=\"$staff_no\" /></div></td>\n</tr>\n";
?>

<div style="text-align:center"><input style="width:120px" type="submit" name="submit" value="<?php echo $string['ok']; ?>" />&nbsp;<input style="width:120px" type="submit" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="window.close()" /></div>

</form>
</body>
</html>
<?php
  }
  $mysqli->close();
?>