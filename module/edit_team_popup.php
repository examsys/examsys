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

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

$moduleID = check_var('module', 'GET', true, false, true);

$module_details = module_utils::get_full_details_by_ID($moduleID, $mysqli);

if (!$module_details) {
   $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
   $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (!$userObject->has_role(array('SysAdmin', 'Admin'))) {
  if ($module_details['add_team_members'] == 0) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);  
  }
}

if (isset($_POST['submit'])) {
  // Clear the team of all members.
  UserUtils::clear_staff_modules_by_moduleID($moduleID, $mysqli);
  
  // Insert a record for each team member.
  for ($i=0; $i<$_POST['staff_no']; $i++) {
    if (isset($_POST["staff$i"]) and $_POST["staff$i"] != '') {
      UserUtils::add_staff_to_module($_POST["staff$i"], $moduleID, $mysqli);
    }
  }
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['teammembers'] . ' ' . $module_details['moduleid']; ?></title>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.location.href = '../module/index.php?module=<?php echo $moduleID; ?>';
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
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset'); ?>" />
  <title><?php echo $string['teammembers'] . ' ' . $module_details['moduleid'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
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
      height: 60px;
      width: 100%;
      position: absolute;
    }
    .content {
      position: absolute;
      overflow-y: scroll;
      top: 64px;
      bottom: 50px;
      width: 98%;
      border: 1px solid #CCD9EA;
      font-size: 90%;
      background-color: white;
      margin-left: 1%;
      margin-right: 1%;
    }
    .footer {
      height: 40px;
      width: 100%;
      position: absolute;
      bottom: 0;
    }
    hr {width:100%; border:0; height:1px; color:#E5E5E5; background-color:whit}
    .r1 {background-color:white}
    .r2 {background-color:#FFBD69}
    .g {color:#808080}
    .letter {padding-bottom:5px; width:95%; background-color:white; color:#1E3287}
    input[type="checkbox"] {margin-left: 25px}
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

  <table cellpadding="0" cellspacing="0" border="0" width="100%" class="header">
  <tr><td style="width:66px; height:55px; background-color:white; border-bottom:1px solid #CCD9EA; text-align:center"><img src="../artwork/user_accounts_icon.png" width="48" height="48 alt="Members" /></td><td class="dkblue_header" style="background-color:white; font-size:150%; border-bottom:1px solid #CCD9EA"><strong><?php echo $string['teammembers']; ?> </strong><?php echo $module_details['moduleid']; ?></td></tr>
  </table>

<?php
  $team_members = UserUtils::get_staff_modules_list_by_modID($_GET['module'], $mysqli);

  echo "<div class=\"content\" id=\"list\">";
  $staff_no = 0;
  $old_letter = '';

  $tmp_role = 'Staff%';
  
  $result = $mysqli->prepare("SELECT DISTINCT id, surname, initials, first_names, title FROM users WHERE surname != '' AND roles LIKE ? AND grade != 'left' AND user_deleted IS NULL ORDER BY surname, initials");
  $result->bind_param('s', $tmp_role);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title);
  while ($result->fetch()) {
    if ($old_letter != strtoupper(substr($tmp_surname, 0, 1))) {
      echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>" . strtoupper(substr($tmp_surname, 0, 1)) . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>";
    }
  
    $match = false;
    foreach ($team_members as $member) {
      if ($member == $tmp_id) $match = true;
    }
   
    if ($match == true) {
      echo "<div class=\"r2\" id=\"div$staff_no\"><input type=\"checkbox\" onclick=\"toggle('div$staff_no')\" name=\"staff$staff_no\" id=\"staff$staff_no\" value=\"" . $tmp_id . "\" checked=\"checked\" />";
    } else {
      echo "<div class=\"r1\" id=\"div$staff_no\"><input type=\"checkbox\" onclick=\"toggle('div$staff_no')\" name=\"staff$staff_no\" id=\"staff$staff_no\" value=\"" . $tmp_id . "\" />";
    }
    echo "<label for=\"staff$staff_no\">";
    if ($tmp_first_names != '') {
      $display_text = $tmp_first_names;
    } else {
      $display_text = $tmp_initials;
    }
    echo $tmp_surname . '<span class="g">, ' . $display_text . '. ' . $tmp_title . "</span></label></div>\n";
    $old_letter = strtoupper(substr($tmp_surname, 0, 1));
    $staff_no++;
  }
  $result->close();
  echo "<input type=\"hidden\" name=\"staff_no\" value=\"$staff_no\" /></div></td>\n</tr>\n";
?>

<div class="footer" style="text-align:center"><input class="ok" type="submit" name="submit" value="<?php echo $string['ok']; ?>" /><input class="cancel" type="submit" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="window.close()" /></div>

</form>
</body>
</html>
<?php
  }
  $mysqli->close();
?>