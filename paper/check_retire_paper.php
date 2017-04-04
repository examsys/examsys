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
* Confirm with the user that they want to retire a paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);

if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['confirmretirepaper'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:90%}
  </style>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/formative_retired_icon.png" width="48" height="48" alt="<?php echo $string['paper']; ?>" /></td>

<td><p><?php echo $string['msg']; ?></p>
<br />

<div style="text-align:right">
<form action="do_retire_paper.php" method="post" autocomplete="off">
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<input class="ok" type="submit" name="paper" value="<?php echo $string['paper']; ?>" onclick="" /><input class="ok" type="submit" name="questions" value="<?php echo $string['paperquestions']; ?>" onclick="" />
</form>
</div>
</td></tr>
</table>

</body>
</html>