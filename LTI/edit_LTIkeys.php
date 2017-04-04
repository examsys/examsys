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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';
require_once 'ims-lti/UoN_LTI.php';

$lti = new UoN_LTI($mysqli);
$lti->init_lti0($mysqli);
$LTIkeysid = check_var('LTIkeysid', 'GET', true, false, true);

if (!$lti->lti_key_exists($LTIkeysid)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$result = $mysqli->prepare("SELECT id, oauth_consumer_key, secret, name, context_id FROM lti_keys WHERE id = ?");
$result->bind_param('i', $LTIkeysid);
$result->execute();
$result->bind_result($ltis['id'], $ltis['oauth_consumer_key'], $ltis['secret'], $ltis['name'], $ltis['context_id']);
$result->fetch();
$result->close();

if (isset($_POST['submit'])) {
  $ltiname    = trim($_POST['ltiname']);
  $ltikey     = trim($_POST['ltikey']);
  $ltisec     = trim($_POST['ltisec']);
  $lticontext = trim($_POST['lticontext']);
  
  $insert_id = $lti->update_lti_key($LTIkeysid, $ltiname, $ltikey, $ltisec, $lticontext);
  
  // Log changes
  $logger = new Logger($mysqli);
  if ($ltis['name'] != $ltiname)              $logger->track_change('LTI Key', $LTIkeysid, $userObject->get_user_ID(), $ltis['name'], $ltiname, 'name');
  if ($ltis['oauth_consumer_key'] != $ltikey) $logger->track_change('LTI Key', $LTIkeysid, $userObject->get_user_ID(), $ltis['name'], $ltikey, 'key');
  if ($ltis['secret'] != $ltisec)             $logger->track_change('LTI Key', $LTIkeysid, $userObject->get_user_ID(), $ltis['secret'], $ltisec, 'secret');
  if ($ltis['context_id'] != $lticontext)     $logger->track_change('LTI Key', $LTIkeysid, $userObject->get_user_ID(), $ltis['context_id'], $lticontext, 'context');

  header("location: lti_keys_list.php");
  exit();
} else {
  ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

  <title>Rog&#333;: <?php echo $string['editltikeys'] . " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    td {
      text-align: left
    }
    .field {
      font-weight: bold;
      text-align: right;
      padding-right: 10px
    }
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
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
  </script>
</head>
<body>
<?php
  require '../include/lti_keys_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-"/><a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-"/><a href="lti_keys_list.php"><?php echo $string['ltikeys']; ?></a></div>
  <div class="page_title"><?php echo $string['editltikeys']; ?></div>
</div>

  <br/>
  <div align="center">
    <form id="theform" name="edit_LTIkeys" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?LTIkeysid=' . $_GET['LTIkeysid'] ?>" autocomplete="off">
      <table cellpadding="0" cellspacing="2" border="0">
        <tr>
          <td class="field"><?php echo $string['name']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltiname" id="ltiname" value="<?php echo $ltis['name']; ?>" required />
          </td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_consume_key']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltikey" id="ltikey" value="<?php echo $ltis['oauth_consumer_key']; ?>" required /></td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_secret']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="ltisec" id="ltisec" value="<?php echo $ltis['secret']; ?>" required />
          </td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_context_id']; ?></td>
          <td><input type="text" size="70" maxlength="255" name="lticontext" id="lticontext" value="<?php echo $ltis['context_id']; ?>"/></td>
        </tr>


      </table>
      <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" onclick="javascript:history.back();"/></p>
    </form>
  </div>
  <?php
}
?>
</div>
</body>
</html>