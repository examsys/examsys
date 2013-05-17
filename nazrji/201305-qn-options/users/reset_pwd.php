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
require_once '../classes/userutils.class.php';
require_once '../include/errors.inc';

$userid = check_var('userID', 'GET', true, false, true);

if (!UserUtils::userid_exists($userid, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$username     = UserUtils::get_username($userid, $mysqli);
$new_password = gen_password();

$success = UserUtils::update_password($username, $new_password, $userid, $mysqli);
if (!$success) {
  display_error($string['resetfailed'], $string['failuremsg'], $configObject->get('cfg_root_path') . '/artwork/bomb.png', '#C00000', true, true, true);
}
$mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['passwordreset'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <style type="text/css">
    body {font-size:80%}
    .header_line {border-bottom:1px solid #CCD9EA}
    .passwd {font-weight:bold; font-family:'Courier New'}
    .msg {padding:10px; font-size:110%}
  </style>
</head>

<body class="dialog_body">
<table cellpadding="2" cellspacing="0" style="width:100%">
<tr><td class="dialog_header header_line" style="width:54px"><img src="../artwork/user_info_48.png" width="48" height="48" /></td><td class="dialog_header header_line" style="width:90%"><?php echo $string['passwordreset']; ?></td></tr>
<tr><td colspan="2">&nbsp;</tr>
<tr><td colspan="2" class="msg"><?php echo $string['msg']; ?> <span class="passwd"><?php echo $new_password; ?></span></tr>
<tr><td colspan="2">&nbsp;</tr>
<tr><td colspan="2" style="text-align:center"><input type="button" name="ok" value="<?php echo $string['ok']; ?>" style="width:100px" onclick="window.close();" /></tr>
</table>
</body>
</html>
