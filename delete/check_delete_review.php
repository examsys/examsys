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
* Confirm that it is OK to proceed deleting a standards setting review.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$std_setID = check_var('std_setID', 'GET', true, false, true);

$row_no = 0;
$result = $mysqli->prepare("SELECT id, DATE_FORMAT(std_set, '" . $configObject->get('cfg_long_date_time') . "') AS std_set_date FROM std_set WHERE id = ? LIMIT 1");
$result->bind_param('i', $std_setID);
$result->execute();  
$result->store_result();
$result->bind_result($id, $std_set_date);
$result->fetch();
$row_no = $result->num_rows;
$result->close();

if ($row_no == 0) {
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

  <title><?php echo $string['confirmdelete']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>

<p><?php printf($string['confirmmsg'], $std_set_date); ?></p>

<div class="button_bar">
<form action="do_delete_review.php" method="post" autocomplete="off">
<input type="hidden" name="std_setID" value="<?php echo $std_setID; ?>" />
<input class="delete" type="submit" name="submit" value="<?php echo $string['delete']; ?>" /><input class="cancel" type="button" name="cancel" value=" <?php echo $string['cancel']; ?> " onclick="javascript:window.close();" />
</form>
</div>

</body>
</html>