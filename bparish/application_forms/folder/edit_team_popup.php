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

require_once '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/userutils.class.php';

check_var('teamID', 'GET', true, false);
$teamID = $_GET['teamID'];

if (isset($_POST['submit'])) {
  // Clear the team of all members.
  UserUtils::clear_team_by_team_name($teamID, $mysqli);
  
  // Insert a record for each team member.
  for ($i=0; $i<$_POST['staff_no']; $i++) {
    if (isset($_POST["staff$i"]) and $_POST["staff$i"] != '') {
      UserUtils::add_staff_to_team($_POST["staff$i"], $teamID, $mysqli);
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
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
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset; ?>" />
<title><?php echo $string['teammembers'] . ' ' . $_GET['teamID'] . ' ' . $cfg_install_type; ?></title>
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
  body {font-family:Arial,sans-serif; font-size:90%; background-color:#F1F5FB; color:black; margin:0px}
  hr {width:100%; border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5}
  .r1 {background-color:white}
  .r2 {background-color:#B3C8E8}
  .g {color:#808080}
  .letter {padding-bottom:5px; width:95%; background-color:white; color:#1E3287}
</style>
<script language="JavaScript">
  function toggle(objectID) {
    if (document.getElementById(objectID).className == 'r2') {
      document.getElementById(objectID).className = 'r1';
    } else {
      document.getElementById(objectID).className = 'r2';
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
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/team_members.png" width="32" height="32 alt="Members" /></td><td class="dkblue_header" style="background-color:white; font-size:150%; border-bottom:1px solid #CCD9EA"><strong><?php echo $string['teammembers']; ?> </strong><?php echo $_GET['teamID']; ?></td></tr>
  </table>

<?php
  $team_members = UserUtils::get_team_list_by_name($_GET['teamID'], $mysqli);

  echo "<div style=\"height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:12px 4px 8px 4px; font-size:90%\" id=\"list\">";
  $staff_no = 0;
  $old_letter = '';

  $tmp_role = 'Staff%';
  
  $result = $mysqli->prepare("SELECT DISTINCT id, surname, initials, first_names, title FROM users WHERE surname != '' AND roles LIKE ? AND grade != 'left' ORDER BY surname, initials");
  $result->bind_param('s', $tmp_role);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title);
  while ($result->fetch()) {
    if ($old_letter != strtoupper(substr($tmp_surname, 0, 1))) {
      echo "<table border=\"0\" class=\"subsect\" style=\"width:100%\"><tr><td><nobr>" . strtoupper(substr($tmp_surname, 0, 1)) . "</nobr></td><td style=\"width:95%\"><hr noshade=\"noshade\" /></td></tr></table>\n";
    }
  
    $match = false;
    foreach ($team_members as $member) {
      if ($member == $tmp_id) $match = true;
    }
   
    if ($match == true) {
      echo "<div class=\"r2\" id=\"div$staff_no\"><input type=\"checkbox\" onclick=\"toggle('div$staff_no')\" name=\"staff$staff_no\" value=\"" . $tmp_id . "\" checked=\"checked\" />";
    } else {
      echo "<div class=\"r1\" id=\"div$staff_no\"><input type=\"checkbox\" onclick=\"toggle('div$staff_no')\" name=\"staff$staff_no\" value=\"" . $tmp_id . "\" />";
    }
    if ($tmp_first_names != '') {
      $display_text = $tmp_first_names;
    } else {
      $display_text = $tmp_initials;
    }
    echo " " . $tmp_surname . '<span class="g">, ' . $display_text . '. ' . $tmp_title . "</span></div>\n";
    $old_letter = strtoupper(substr($tmp_surname, 0, 1));
    $staff_no++;
  }
  $result->close();
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