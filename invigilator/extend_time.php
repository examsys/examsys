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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/invigilator_auth.inc';
if (isset($_GET['userID'])) {
    $student_id = $_GET['userID'];
}

if (isset($_GET['paperID'])) {
    $paper_id = $_GET['paperID'];
}

$student = array();
$student['user_ID'] = $student_id;
$stmt = $mysqli->prepare('SELECT title, initials, surname FROM users WHERE user_deleted IS NULL AND id = ?');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($title, $initials, $surname);
$stmt->fetch();
$stmt->close();
$current_address = NetworkUtils::get_client_address();
$lab_factory = new LabFactory($mysqli);
$lab_object = $lab_factory->get_lab_based_on_client($current_address);
$propertyObj = PaperProperties::get_paper_properties_by_id($paper_id, $mysqli, $string);
$log_lab_end_time = $propertyObj->getLogLabEndTime($lab_object->get_id());
$log_extra_time = new LogExtraTime($log_lab_end_time, $student, $mysqli);
$special_needs_percentage = $log_extra_time->get_extra_time_secs();
$special_needs_percentage = $special_needs_percentage / 60;
$time_range = range(0, 30, 1);

?>
<html>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>
<head>
  <title><?php echo $string['extendtime'] ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css"/>
  <style>
    body {font-size:90%}
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src='../js/invigilatorextratimeinit.min.js'></script>
</head>
<body>
<form id="extend_time_form" method="post" action="" autocomplete="off">
    <p>&nbsp;<?php echo $title . ' ' . $initials . ' ' . $surname; ?></p>

    <div style="text-align:center">
      <?php echo $string['extendtimeby'] ?>
        <select id="extra_time" name="extra_time">
          <?php
            foreach ($time_range as $time_increment) {
                $selected = '';
                if ($time_increment === $special_needs_percentage) {
                    $selected = 'selected';
                }
                ?>
              <option value="<?php echo $time_increment; ?>" <?php echo $selected ?>><?php echo $time_increment; ?></option>
                <?php
            }
            ?>
        </select>
      <?php echo $string['minutes'] ?>
    </div>
    <div style="text-align:center; margin-top:20px;">
        <input type="submit" name="submit" value="<?php echo $string['ok'] ?>" class="ok" /><input type="button" name="close" value="<?php echo $string['cancel'] ?>" class="cancel" />
    </div>
    <input type="hidden" name="userID" value="<?php echo $student_id; ?>"/>
    <input type="hidden" name="paperID" value="<?php echo $paper_id; ?>"/>
</form>
</body>
</html>
