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

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';

set_time_limit(0);
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['clearoldlogs']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <script language="JavaScript" src="../js/staff_help.js"></script>
  <?php echo $cfg_js_root ?>
  <script language="JavaScript" src="../js/sidebar.js"></script>
</head>

<body>

<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content">
<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['clearoldlogs']; ?></div></th><th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(239); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>

<?php
  ob_flush();
  flush();

  $log0_deleted = 0;
  $log1_deleted = 0;
  $lti_user_deleted = 0;

  $stmt = $mysqli->prepare("SELECT id FROM users WHERE roles='left' OR roles='graduate'");
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($userObject->get_user_ID());
  while ($stmt->fetch()) {
    // Delete from formative log.
    $deletequery = $mysqli->prepare("DELETE log0, log_metadata FROM log0 INNER JOIN log_metadata WHERE log0.userID=log_metadata.userID AND log0.q_paper=log_metadata.paperID AND log0.started=log_metadata.started AND log0.userID=?");
    $deletequery->bind_param('s', $userObject->get_user_ID());
    $deletequery->execute();
    $log0_deleted += $deletequery->affected_rows;
    $deletequery->close();
    
    // Delete from progress test log.
    $deletequery = $mysqli->prepare("DELETE log1, log_metadata FROM log1 INNER JOIN log_metadata WHERE log1.userID=log_metadata.userID AND log1.q_paper=log_metadata.paperID AND log1.started=log_metadata.started AND log1.userID=?");
    $deletequery->bind_param('s', $userObject->get_user_ID());
    $deletequery->execute();
    $log1_deleted += $deletequery->affected_rows;
    $deletequery->close();
    
    // Delete from lti_user table.
    $deletequery = $mysqli->prepare("DELETE lti_user WHERE user_id=?");
    $deletequery->bind_param('s', $userObject->get_user_ID());
    $deletequery->execute();
    $lti_user_deleted += $deletequery->affected_rows;
    $deletequery->close();
  }  
  $stmt->close();
  
  // Reset passwords
  if ($cfg_use_ldap) {
    $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('Student', 'graduate', 'left')");
  } else {
    $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('graduate', 'left')");
  }
  $updatequery->execute();
  $updatequery->close();

  echo "<blockquote>\n<div>" . $string['log0deleted'] . " $log0_deleted</div>";
  echo "<div>" . $string['log1deleted'] . " $log1_deleted</div>\n</blockquote>";
?>
</div>

</body>
</html>
<?php
  $mysqli->close();
  ob_end_flush();
?>