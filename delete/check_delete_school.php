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
* Confirm that it is OK to proceed deleting a school.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$schoolID = check_var('schoolID', 'GET', true, false, true);

if (!SchoolUtils::schoolid_exists($schoolID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (SchoolUtils::school_in_use($schoolID, $mysqli)) {
  $notice->display_notice($string['cannotdelete'], $string['modulesattached'], '../artwork/exclamation_64.png', 'black', true, false);
  echo '<p><button type="button" onclick="javascript:window.close();">' . $string['cancel'] .  '</button></p>';
  echo "\n</body>\n</html>";
  exit;
}

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['confirmdelete'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>

<p><?php echo $string['msg'] ?></p>

<div class="button_bar">
<form action="do_delete_school.php" method="post" autocomplete="off">
<input type="hidden" name="schoolID" value="<?php echo $schoolID ?>" />
<input class="delete" type="submit" name="submit" value="<?php echo $string['delete'] ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="javascript:window.close();" />
</form>
</div>

</body>
</html>