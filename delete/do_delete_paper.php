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
* Delete a paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'POST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
if ($properties->get_summative_lock() == 1) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['paperlocked'], $msg, $string['paperlocked'], '../artwork/padlock_48.png', '#C00000', true, true);
}

$new_title = $properties->get_paper_title() . ' [deleted ' .  date($configObject->get('cfg_short_date_php')) . ']';
$properties->set_paper_title($new_title);

$delete_date = date('YmdHis');
$properties->set_deleted($delete_date);

$properties->save();

// Record the deletion.
$logger = new Logger($mysqli);
$logger->track_change('Paper', $paperID, $userObject->get_user_ID(), '', '', 'Paper Deleted');

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['questiondeleted']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      <?php
        if ($_POST['module'] != '') {
          echo "self.opener.location.href = '../module/index.php?module=" . $_POST['module'] . "';\n";
        } elseif ($_POST['folder'] != '') {
          echo "self.opener.location.href = '../folder/index.php?folder=" . $_POST['folder'] . "';\n";
        } else {
          echo "self.opener.location.href = '../index.php';\n";
        }
      ?>
      self.close();
    });
  </script>
</head>

<body>

<p><?php echo $string['msg']; ?></p>

<div class="button_bar">
<form action="" method="get" autocomplete="off">
<input type="button" name="cancel" value="OK" class="ok" onclick="self.opener.location.href='../module/index.php?module=<?php echo $_POST['module']; ?>&folder=<?php echo $_POST['folder']; ?>'; window.close();" />
</form>
</div>

</body>
</html>