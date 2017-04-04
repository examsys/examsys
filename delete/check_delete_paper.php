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
* Confirm that it is OK to proceed deleting a paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string, true);
if ($properties->get_summative_lock() == 1) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['paperlocked'], $msg, $string['paperlocked'], '../artwork/padlock_48.png', '#C00000', true, true);
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

<p><?php echo $string['msg'] ?><p>

<div class="button_bar">
<form action="do_delete_paper.php" method="post" autocomplete="off">
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input type="hidden" name="folder" value="<?php echo $_GET['folder']; ?>" />
<input class="delete" type="submit" name="submit" value="<?php echo $string['delete'] ; ?>" /><input class="cancel" type="button" name="cancel" value=" <?php echo $string['cancel']; ?> " onclick="window.close();" />
</form>
</div>

</body>
</html>