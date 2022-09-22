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
 * Shows information on the currently selected user: name, username, email, etc
 * plus the details of any taken assessment or survey. SysAdmin users also have the ability
 * to edit personal details such as name, username, password, etc.
 *
 * @author Simon Wilkinson, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'GET', true, false, true);

$user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if ($user_details['gender'] == 'Male') {
    $generic_icon = '../artwork/user_male_48.png';
} elseif ($user_details['gender'] == 'Female') {
    $generic_icon = '../artwork/user_female_48.png';
} else {
    $generic_icon = '../artwork/user_mx_48.png';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['usermanagement'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/edituserdetails.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src="../js/require.js"></script>
  <script src="../js/main.min.js"></script>
  <script src="../js/edituserdetailsinit.min.js"></script>
</head>

<body>
  <table cellspacing="0" cellpadding="5" border="0" style="width:100%; background-color:white">
    <tr><td style="border-bottom:1px solid #CCD9EA; width:50px"><img src="<?php echo $generic_icon ?>" width="48" height="48" /></td><td class="dkblue_header" style="background-color:white; font-size:160%; border-bottom:1px solid #CCD9EA; font-weight:bold"><?php echo $string['edituserdetails'] ?></td></tr>
  </table>
  <br />

  <form id="myform" name="myform" action="" method="post" enctype="multipart/form-data" autocomplete="off">
  <table cellspacing="0" cellpadding="2" border="0" style="width:100%; border:12px solid #EEF4FF">
<?php
  echo '<tr><td>' . $string['name'] . '</td><td>';
  $title_array = explode(',', $string['title_types']);
  echo '<select name="title">';
foreach ($title_array as $individual_title) {
    if ($individual_title == $user_details['title']) {
        echo '<option value="' . $individual_title . '" selected>' . $individual_title . '</option>';
    } else {
        echo '<option value="' . $individual_title . '">' . $individual_title . '</option>';
    }
}
  echo '</select> <input type="text" value="' . $user_details['first_names'] . '" name="first_names" required /> <input type="text" value="' . $user_details['surname'] . "\" name=\"surname\" required /></td></tr>\n";
  echo '<tr><td>' . $string['studentid'] . '</td><td><input type="text" value="' . $user_details['student_id'] . "\" name=\"sid\" /></td></tr>\n";
  echo '<tr><td>' . $string['username'] . '</td><td><input type="text" value="' . $user_details['username'] . "\" id=\"username\" name=\"username\" required /><span id=\"usernameerror\"></span></td></tr>\n";
  echo '<tr><td>' . $string['email'] . '</td><td><input type="text" value="' . $user_details['email'] . "\" name=\"email\" style=\"width:340px\" required /></td></tr>\n";
  echo '<tr><td>' . $string['course'] . '</td><td><select name="grade" style="width:300px">';
  $found = 0;
  $course_details = $mysqli->prepare('SELECT DISTINCT name, description FROM courses ORDER BY name');
  $course_details->execute();
  $course_details->bind_result($name, $description);
while ($course_details->fetch()) {
    if ($name == $user_details['grade']) {
        $found = 1;
        echo "<option value=\"$name\" selected>$name: $description</option>\n";
    } else {
        echo "<option value=\"$name\">$name: $description</option>\n";
    }
}
if ($found == 0) {
    echo '<option value="' . $user_details['grade'] . '" selected>' . $user_details['grade'] . ': ' . $string['unknown'] . "</option>\n";
}
  $course_details->close();

  echo "</select></td></tr>\n";
  echo '<tr><td>' . $string['yearofstudy'] . '</td><td><select name="year">';
for ($i = 1; $i <= 6; $i++) {
    if ($i == $user_details['yearofstudy']) {
        echo "<option value=\"$i\" selected>$i</option>";
    } else {
        echo "<option value=\"$i\">$i</option>";
    }
}
  echo '</select></td></tr>';

  echo '<tr><td>' . $string['gender'] . '</td><td><select name="gender">';
if ($user_details['gender'] == 'Male') {
    echo '<option value="Male" selected>' . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n<option value=\"Other\">" . $string['other'] . '</option>';
} elseif ($user_details['gender'] == 'Female') {
    echo '<option value="Male">' . $string['male'] . "</option>\n<option value=\"Female\" selected>" . $string['female'] . "</option>\n<option value=\"Other\">" . $string['other'] . '</option>';
} elseif ($user_details['gender'] == 'Other') {
    echo '<option value="Male">' . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n<option value=\"Other\" selected>" . $string['other'] . '</option>';
} else {
    echo "<option value=\"\"></option>\n<option value=\"Male\">" . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n<option value=\"Other\">" . $string['other'] . "</option>\n";
}
  echo '</select></td></tr>';

  echo '<tr><td><input type="hidden" name="roles" value="' . $user_details['roles'] . '" />';
  echo '<input type="hidden" name="prev_username" value="' . $user_details['username'] . '" />';
  echo '<input type="hidden" name="userID" value="' . $userID . '" />';
  echo '' . $string['photo'] . '</td><td><input type="file" name="photofile" /></td></tr>';
?>
  </table>

    <div style="margin-top:24px; text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok'] ?>" class="ok" /><input class="cancel" type="button" id="cancel" name="cancel" value="<?php echo $string['cancel'] ?>" /></div>
  </form>
  <?php
    // JS utils dataset.
    $render = new render($configObject);
    $jsdataset['name'] = 'jsutils';
    $jsdataset['attributes']['xls'] = json_encode($string);
    $render->render($jsdataset, array(), 'dataset.html');
    ?>
</body>
</html>
