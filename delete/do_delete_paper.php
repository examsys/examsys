<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

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
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
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

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/deletepaperinit.min.js"></script>
</head>

<body>

<p><?php echo $string['msg']; ?></p>

<div class="button_bar">
<form action="" method="get" autocomplete="off">
<input type="button" name="cancel" value="OK" class="ok" />
</form>
</div>
<?php
// Dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['module'] = $_POST['module'];
$miscdataset['attributes']['folder'] = $_POST['folder'];
$render->render($miscdataset, array(), 'dataset.html');
?>
</body>
</html>
