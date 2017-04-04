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
 * Authorise oauth access token.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';

function error($error, $string, $mysqli, $configObject, $notice) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $error, '../artwork/page_not_found.png', '#C00000', true, true);
}

// Exit if api not enabled.
if (!$configObject->get_setting('core', 'cfg_api_enabled')) {
    error($string['pagenotfound'], $string, $mysqli, $configObject, $notice);
}

if (isset($_POST['submit'])) {
    $authorised = check_var('authorised', 'POST', true, false, true);
    $client_id = check_var('client_id', 'POST', true, false, true);
    $userid = $userObject->get_user_ID();
    $oauth = new oauth($configObject);
    if ($userid != $oauth->get_client_user($client_id)) {
        error('User id of logged in user does not match that of the client.', $string, $mysqli, $configObject, $notice);
    }
    // Set the request token to be authorized or not authorized
    if ($authorised == "yes") {
        $resp = $oauth->authorise(true, $userid);
    } else {
        $resp = $oauth->authorise(false, $userid);
    }
    if (!$resp[0]) {
        error($resp[1], $string, $mysqli, $configObject, $notice);
    }
} else {
    $client_id = check_var('client_id', 'GET', true, false, true);
	if (!isset($_GET['state'])) {
        error('State not supplied.', $string, $mysqli, $configObject, $notice);
    } else {
    	$state = $_GET['state'];
    }
}
?>

<!DOCTYPE html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333; - <?php echo $string['authorise'] ?></title>

  <link rel="stylesheet" type="text/css" href="<?php echo $configObject->get('cfg_root_path') ?>/css/body.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $configObject->get('cfg_root_path') ?>/css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $configObject->get('cfg_root_path') ?>/css/login_form.css" />
</head>
<body>
<form method="post" id="theform" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
    <div class="mainbox">

        <img src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/r_logo.gif" alt="logo" class="logo_img" />

        <div class="logo_lrg_txt">Rog&#333;</div>
        <div class="logo_small_txt"><?php echo $string['eassessmentmanagementsystem']; ?></div>
        <div style="margin-left:65px">
            <table>
            <tr>
                <td><?php echo $string['authoriseaccess']; ?></td>
            </tr>
            <tr>
                <td><input type="radio" name="authorised" value="yes"> <?php echo $string['yes']; ?><br>
                <input type="radio" name="authorised" value="no" checked> <?php echo $string['no']; ?></td>
            </tr>
            <tr>
                <td><p id="authlabel" name="authlabel"></p></td>
            </tr>
            <tr><td><input type="submit" class="ok" name="submit" value="<?php echo $string['save']; ?>"></td></tr>
            </table>
        <div class="versionno">Rog&#333; <?php echo $configObject->get('rogo_version'); ?></div>
    </div>
    <input type="hidden" id="client_id" name="client_id" value ="<?php echo $client_id; ?>">
    <input type="hidden" id="response_type" name="response_type" value ="code">
    <input type="hidden" id="state" name="state" value ="<?php echo $state; ?>">
</form>
</body>
</html>
