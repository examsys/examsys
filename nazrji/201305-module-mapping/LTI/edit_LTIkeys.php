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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.inc';
require_once 'ims-lti/UoN_LTI.php';
require_once '../classes/logger.class.php';

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
  exit;
} else {
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title><?php echo $string['editltikeys'] . " " . $configObject->get('cfg_install_type') ?></title>

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
      .mandatory {
        font-family:'Courier New';
        font-weight:bold;
        color:#C00000
      }
    </style>

    <script language="JavaScript">
      function checkForm() {
        if (document.getElementById('ltiname').value == "" || document.getElementById('ltiname').value == "<?php echo $string['prompt1']; ?>" || document.getElementById('ltikey').value == "" || document.getElementById('ltikey').value == "<?php echo $string['prompt2']; ?>" || document.getElementById('ltisec').value == "" || document.getElementById('ltisec').value == "<?php echo  $string['prompt3']; ?>") {
          alert('<?php echo $string['missingfields'] ?>');
          return false;
        }
      }
    </script>
  </head>
<body>
<?php
  require '../include/lti_keys_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table class="header">
  <tr>
    <th>
      <div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home'] ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-"/>&nbsp;&nbsp;<a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-"/>&nbsp;&nbsp;<a href="lti_keys_list.php"><?php echo $string['ltikeys']; ?></a></div>
      <div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['editltikeys']; ?></th>
    <th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#"
                                                                                            onclick="launchHelp(233); return false;"><img
      src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0"/></a></th>
  </tr>
  <tr>
    <th colspan="2" class="bevel"></th>
  </tr>
</table>
  <div style="text-align:center">
    <p style="font-size: 110%">
      <span class="mandatory">*</span> <?php echo $string['mandatory'] ?>
    </p>
  </div>
  <br/>
  <div align="center">
    <form name="edit_LTIkeys" method="post" onsubmit="return checkForm()"
          action="<?php echo $_SERVER['PHP_SELF'] . '?LTIkeysid=' . $_GET['LTIkeysid']; ?>">
      <table cellpadding="0" cellspacing="2" border="0">
        <tr>
          <td class="field"><span class="mandatory">*</span> <?php echo $string['name']; ?></td>
          <td><input type="text" size="70" name="ltiname" id="ltiname" value="<?php echo $ltis['name']; ?>"/>
          </td>
        </tr>
        <tr>
          <td class="field"><span class="mandatory">*</span> <?php echo $string['oauth_consume_key']; ?></td>
          <td><input type="text" size="70" name="ltikey" id="ltikey"
                     value="<?php echo $ltis['oauth_consumer_key']; ?>"/></td>
        </tr>
        <tr>
          <td class="field"><span class="mandatory">*</span> <?php echo $string['oauth_secret']; ?></td>
          <td><input type="text" size="70" name="ltisec" id="ltisec" value="<?php echo $ltis['secret']; ?>"/>
          </td>
        </tr>
        <tr>
          <td class="field"><?php echo $string['oauth_context_id']; ?></td>
          <td><input type="text" size="70" name="lticontext" id="lticontext" value="<?php echo $ltis['context_id']; ?>"/></td>
        </tr>


      </table>
      <p><input type="submit" style="width:100px" name="submit" value="<?php echo $string['save'] ?>">&nbsp;&nbsp;<input
        style="width:100px" type="button" name="home" value="<?php echo $string['cancel'] ?>"
        onclick="javascript:history.back();"/></p>
    </form>
  </div>
  <?php
}
?>
</div>
</body>
</html>