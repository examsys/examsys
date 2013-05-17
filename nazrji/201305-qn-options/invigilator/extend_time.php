<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require '../include/invigilator_auth.inc';
require '../classes/paperproperties.class.php';
require '../classes/lab_factory.class.php';
require '../classes/lab.class.php';
require '../classes/log_extra_time.class.php';
require '../classes/log_lab_end_time.class.php';

if (isset($_GET['userID'])) {
  $student_id = $_GET['userID'];
}

if (isset($_POST['userID'])) {
  $student_id = $_POST['userID'];
}

if (isset($_GET['paperID'])) {
  $paper_id = $_GET['paperID'];
}

if (isset($_POST['paperID'])) {
  $paper_id = $_POST['paperID'];
}


$student = array();
$student['user_ID']=$student_id;

$stmt = $mysqli->prepare('SELECT title, initials, surname FROM users WHERE user_deleted IS NULL AND id = ?');
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($student['title'], $student['initials'], $student['surname']);
$stmt->fetch();

$title = $student['title'];
$initials = $student['initials'];
$surname = $student['surname'];

$current_ip_address = NetworkUtils::get_ipaddress();

$lab_factory = new LabFactory($mysqli);
$lab_object = $lab_factory->get_lab_based_on_ip($current_ip_address);

$propertyObj = PaperProperties::get_paper_properties_by_id($paper_id, $mysqli);
$log_lab_end_time = new LogLabEndTime($lab_object->get_id(), $propertyObj, $mysqli);
$log_extra_time = new LogExtraTime($log_lab_end_time, $student, $mysqli);

$onload = '';

if (isset($_POST['submit'])) {
  $invigilator_id = $userObject->get_user_ID();

  if ((int)$_POST['extra_time'] == 0) {
    $log_extra_time->delete($invigilator_id);
  } elseif ((int)$_POST['extra_time'] > 0) {
    $special_needs_percentage = $_POST['extra_time'];

    $log_extra_time->save($invigilator_id, $special_needs_percentage);
  }
  $onload = 'closeWindow();';
}

$special_needs_percentage = $log_extra_time->get_extra_time_secs();
$special_needs_percentage = $special_needs_percentage / 60;

$time_range = range(0, 30, 5);

?>
<html>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>
<head>
    <title><?php echo $string['extendtime'] ?></title>
    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <script type="text/javascript">
        function closeWindow() {
            window.opener.location = window.opener.location.href;
            window.close();
        }
    </script>
</head>
<body onload="<?php echo $onload; ?>">
<form id="extend_time_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
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
        <input type="submit" style="width:100px" name="submit" value="<?php echo $string['submit']; ?>"/>&nbsp;&nbsp;
        <input style="width:100px" type="button" name="close" value="<?php echo $string['close']; ?>"
               onclick="window.close();"/>
    </div>
    <input type="hidden" name="userID" value="<?php echo $student_id; ?>"/>
    <input type="hidden" name="paperID" value="<?php echo $paper_id; ?>"/>
</form>
</body>
</html>
