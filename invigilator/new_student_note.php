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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/invigilator_auth.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$userID = check_var('userID', 'REQUEST', true, false, true);  // User ID is the student ID.
$remote = param::optional('remote', false, param::BOOLEAN, param::FETCH_REQUEST);

// Does the paper exist?
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
// Does the student exist?
if (!UserUtils::userid_exists($userID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

    <title><?php echo $string['note'] ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/notes.css" />
  <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src="../js/invigilatornoteinit.min.js"></script>

</head>

<body>

<?php

$student_details = UserUtils::get_user_details($userID, $mysqli);
$note_details = StudentNotes::get_note($paperID, $userID, $mysqli);
?>


<form action="" method="post" id="studentnote" name="studentnote" autocomplete="off">

<?php
  echo '<div><strong>' . $string['studentname'] . ':</strong> ' . $student_details['title'] . ' ' . $student_details['surname'] . ', ' . $student_details['first_names'];
if ($student_details['student_id'] != '') {
    echo ' (' . $student_details['student_id'] . ')';
}
  echo '</div>';
  

  echo "<input type=\"hidden\" id=\"paperID\" name=\"paperID\" value=\"$paperID\" />\n";
  echo '<input type="hidden" id="remote" name="remote" value="' . $remote . "\" />\n";
  echo '<strong>' . $string['note'] . ":</strong><br />\n";
  echo '<textarea name="note" id="note" cols="60" rows="17" style="font-size:110%; width:100%" required>' . $note_details['note'] . "</textarea><br />\n";
?>

<br />
<div style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save']; ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" /></div>
<input type="hidden" name="userID" value="<?php echo $userID ?>" />
<input type="hidden" name="note_id" value="<?php echo $note_details['note_id']; ?>" />
</form>

</body>
</html>
