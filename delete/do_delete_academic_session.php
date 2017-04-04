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
* Delete an academic session.
*
* @author Dr Joseph Baxter
* @copyright Copyright (c) 2015 The University of Nottingham
*/

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$year = check_var('year', 'REQUEST', true, false, true);
$yearutils = new yearutils($mysqli);

if (!$yearutils->check_calendar_year($year)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$success = $yearutils->delete_year($year, $userObject->get_user_ID());

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['title'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.location.reload();
      self.close();
    });
  </script>
</head>

<body>
<p>
    <?php
    if ($success == true) {
        echo $string['success'];
    } else {
        echo $string['failure'];
    }
    ?>
<p>

<div class="button_bar">
<form action="" method="get" autocomplete="off">
<input type="button" name="cancel" value="<?php echo $string['ok'] ?>" class="ok" onclick="javascript:window.close();" />
</form>
</div>

</body>
</html>
