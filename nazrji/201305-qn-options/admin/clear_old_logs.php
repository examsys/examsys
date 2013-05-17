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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
require_once '../classes/logger.class.php';

set_time_limit(0);
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['clearoldlogs']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <script language="JavaScript" src="../js/staff_help.js"></script>
  <?php echo $configObject->get('cfg_js_root') ?>
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

  $logger = new Logger($mysqli);

  $my_id = $userObject->get_user_ID();

  $log0_deleted_overall = 0;
  $log1_deleted_overall = 0;
  $lti_user_deleted_overall = 0;

  $stmt = $mysqli->prepare("SELECT id FROM users WHERE roles='left' OR roles='graduate'");
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($user_to_delete);
  while ($stmt->fetch()) {
    $log0_deleted = 0;
    $log1_deleted = 0;
    $lti_user_deleted = 0;

    $lm_check = $mysqli->prepare("SELECT count(lm.id) FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
    $lm_check->bind_param('i', $user_to_delete);
    $lm_check->execute();
    $lm_check->bind_result($lm_count);
    $lm_check->fetch();
    $lm_check->close();

    if (isset($lm_count) and $lm_count > 0) {
      $logquery = $mysqli->prepare("INSERT INTO log0_deleted SELECT l.* FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
      $logquery->bind_param('s', $user_to_delete);
      $logquery->execute();
      $logquery->close();

      $logquery = $mysqli->prepare("INSERT INTO log_metadata_deleted SELECT DISTINCT lm.* FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
      $logquery->bind_param('s', $user_to_delete);
      $logquery->execute();
      $logquery->close();

      // Delete from formative log.
      $deletequery = $mysqli->prepare("DELETE l, lm FROM log0 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
      $deletequery->bind_param('i', $user_to_delete);
      $deletequery->execute();
      $log0_deleted = $deletequery->affected_rows;
      $log0_deleted_overall += $log0_deleted;
      $deletequery->close();

      // Record the delete in audit trail
      $logger->track_change(sprintf($string['trackchangemsg'], '0'), $user_to_delete, $my_id, $log0_deleted, 0, $string['trackchangescope']);
    }

    $lm_check = $mysqli->prepare("SELECT count(lm.id) FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
    $lm_check->bind_param('i', $user_to_delete);
    $lm_check->execute();
    $lm_check->bind_result($lm_count);
    $lm_check->fetch();
    $lm_check->close();

    if (isset($lm_count) and $lm_count > 0) {
      $logquery = $mysqli->prepare("INSERT INTO log1_deleted SELECT l.* FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
      $logquery->bind_param('s', $user_to_delete);
      $logquery->execute();
      $logquery->close();

      $logquery = $mysqli->prepare("INSERT INTO log_metadata_deleted SELECT DISTINCT lm.* FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
      $logquery->bind_param('s', $user_to_delete);
      $logquery->execute();
      $logquery->close();

      // Delete from formative log.
      $deletequery = $mysqli->prepare("DELETE l, lm FROM log1 l INNER JOIN log_metadata lm ON l.metadataID = lm.id WHERE lm.userID = ?");
      $deletequery->bind_param('i', $user_to_delete);
      $deletequery->execute();
      $log1_deleted = $deletequery->affected_rows;
      $log1_deleted_overall += $log1_deleted;
      $deletequery->close();

      // Record the delete in audit trail
      $logger->track_change(sprintf($string['trackchangemsg'], '1'), $user_to_delete, $my_id, $log1_deleted, 0, $string['trackchangescope']);
    }


    // Delete from lti_user table.
    $deletequery = $mysqli->prepare("DELETE FROM lti_user WHERE lti_user_equ = ?");
    $deletequery->bind_param('i', $user_to_delete);
    $deletequery->execute();
    $lti_user_deleted = $deletequery->affected_rows;
    $lti_user_deleted_overall += $lti_user_deleted;
    $deletequery->close();

    if ($lti_user_deleted > 0) {
      $logger->track_change($string['trackchangeltimsg'], $user_to_delete, $my_id, 1, 0, $string['trackchangescope']);
    }
  }
  $stmt->close();

  // Reset passwords
  if ($authentication->has_plugin_type('ldap')) {
    $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('Student', 'graduate', 'left')");
    $roles_string = 'Student, graduate and left';
  } else {
    $updatequery = $mysqli->prepare("UPDATE users SET password='' WHERE roles IN('graduate', 'left')");
    $roles_string = 'graduate and left';
  }
  $updatequery->execute();
  if ($updatequery->affected_rows > 0) {
    $logger->track_change(sprintf($string['trackchangepwdmsg'], $roles_string), $my_id, $my_id, 1, 0, $string['trackchangescope']);
  }
  $updatequery->close();

  echo "<blockquote>\n<div>" . $string['log0deleted'] . " $log0_deleted_overall</div>";
  echo "<div>" . $string['log1deleted'] . " $log1_deleted_overall</div>\n</blockquote>";
?>
</div>

</body>
</html>
<?php
  ob_end_flush();
?>