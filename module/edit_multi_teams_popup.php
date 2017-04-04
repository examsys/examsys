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

require_once '../include/admin_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'REQUEST', true, false, true);

if (isset($_POST['submit'])) {
  // Clear the team of all members.
  UserUtils::clear_staff_modules_by_userID($_POST['userID'], $mysqli);
  
  // Insert a record for each team member.
  for ($i=0; $i<$_POST['module_no']; $i++) {
    if (isset($_POST["mod$i"]) and $_POST["mod$i"] != '') {
      UserUtils::add_staff_to_module($userID, $_POST["mod$i"], $mysqli);
    }
  }
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['manageteams'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.location.href = '../users/details.php?userID=<?php echo $userID; ?>&tab=teams';
      self.close();
    });
  </script>
</head>
<body>
</body>
</html>
<?php
  } else {
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title><?php echo $string['manageteams'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    html {
      position: absolute;
      height: 100%;
      width: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      height: 100%;
      margin: 0;
      padding: 0;
      font-size: 90%;
      background-color: #F1F5FB;
    }
    .header {
      height: 56px;
      width: 100%;
      position: absolute;
      border-bottom: 1px solid #CCD9EA;
      background-color: white;
    }
    .content {
      position: absolute;
      overflow-y: scroll;
      top: 70px;
      bottom: 40px;
      width: 98%;
      border: 1px solid #CCD9EA;
      font-size: 90%;
      background-color: white;
      margin-left: 1%;
      margin-right: 1%;
      margin-bottom: 4px;
    }
    .footer {
      height: 40px;
      width: 100%;
      position: absolute;
      bottom: 0;
    }
    input[type=checkbox] {margin-left:20px}
    .r1 {background-color:white}
    .r2 {background-color:#FFBD69}
		.school {margin-top:10px; width:100%; background-color:white; color:#1E3287}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    function toggle(objectID) {
      if ($('#' + objectID).hasClass('r2')) {
        $('#' + objectID).addClass('r1');
        $('#' + objectID).removeClass('r2');
      } else {
        $('#' + objectID).addClass('r2');
        $('#' + objectID).removeClass('r1');
      }
    }
  </script>
</head>

<body>
<form name="teamform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post" autocomplete="off">

  <table cellpadding="6" cellspacing="0" border="0" width="100%" class="header">
  <tr><td style="width:48px"><img src="../artwork/user_accounts_icon.png" width="48" height="48" alt="Members" /></td><td class="dkblue_header" style="font-size:150%"><strong><?php echo $string['teams']; ?></strong></td></tr>
  </table>

<?php
  $user_teams = array();
  $result = $mysqli->prepare("SELECT moduleID, idMod FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID = ?");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($moduleID, $idMod);
  while ($result->fetch()) {
    $user_modules[$idMod] = $moduleID;
  }
  $result->close();

  $old_school = '';
  $mod_no = 0;
  echo "<div class=\"content\" id=\"list\">";
  
  $result = $mysqli->prepare("SELECT school, moduleid, fullname, modules.id FROM modules, schools WHERE modules.schoolid = schools.id AND active = 1 AND mod_deleted IS NULL ORDER BY school, moduleid");
  $result->execute();
  $result->bind_result($school, $moduleid, $fullname, $idMod);
  while ($result->fetch()) {
    if ($old_school != $school) {
      echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>$school</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\"/></div></div>\n";
    }
   
    if (isset($user_modules[$idMod])) {
      echo "<div class=\"r2\" id=\"divmod$mod_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$mod_no')\" name=\"mod$mod_no\" id=\"mod$mod_no\" value=\"$idMod\" checked />";
    } else {
      echo "<div class=\"r1\" id=\"divmod$mod_no\"><input type=\"checkbox\" onclick=\"toggle('divmod$mod_no')\" name=\"mod$mod_no\" id=\"mod$mod_no\" value=\"$idMod\" />";
    }
    echo "<label for=\"mod$mod_no\">$moduleid: $fullname</label></div>\n";
    $old_school = $school;
    $mod_no++;
  }
  $result->close();
  echo "<input type=\"hidden\" name=\"module_no\" value=\"$mod_no\" /><input type=\"hidden\" name=\"userID\" value=\"" . $userID . "\" /></div></td>\n</tr>\n</table>\n";
?>

<div class="footer" align="center"><input class="ok" type="submit" name="submit" value="<?php echo $string['ok'] ?>" /><input class="cancel" type="submit" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="window.close()" /></div>

</form>
</body>
</html>
<?php
  }
  $mysqli->close();
?>