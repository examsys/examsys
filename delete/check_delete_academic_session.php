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
* Confirm that it is OK to proceed deleting an academic sessions.
*
* @author Dr Joseph Baxter
* @copyright Copyright (c) 2015 The University of Nottingham
*/

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$year = check_var('year', 'GET', true, false, true);
$yearutils = new yearutils($mysqli);

if (!$yearutils->check_calendar_year($year)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

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

<?php

// Check if in use
if ($yearutils->check_calendar_year_in_use($year)) {
    echo "<p>" . $string['warning1'] . "</p>\n";
} else if ($yearutils->count_active_academic_session() < 2) {
    echo "<p>" . $string['warning2'] . "</p>\n";
} else {

?>


<p><?php echo $string['msg'] ?></p>

<div class="button_bar">
<form action="do_delete_academic_session.php" method="post" autocomplete="off">
<input type="hidden" name="year" value="<?php echo $year ?>" />
<input class="delete" type="submit" name="submit" value="<?php echo $string['delete'] ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="javascript:window.close();" />
</form>
</div>

<?php
}
?>

</body>
</html>