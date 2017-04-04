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

// Does the paper exist?
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
// Does the student exist?
if (!UserUtils::userid_exists($userID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['submit'])) {
	if ($_POST['note_id'] == '' or $_POST['note_id'] == '0') {
	  $note_msg = trim($_POST['note']);
		if ($note_msg != '') {  // Check we are not saving nothing.
			StudentNotes::add_note($userID, $note_msg, $paperID, $userObject->get_user_ID(), $mysqli);
		}
	} else {
		StudentNotes::update_note($_POST['note'], $_POST['note_id'], $mysqli);
	}

?>
<!DOCTYPE html>
  <html>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <head><title><?php echo $string['note'] ?></title>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>

  <script>
    $(function () {
      window.opener.location.href='./index.php?tab=<?php echo $paperID ?>';
      window.close();
    });
  </script>
  </head>
  <body>
  <form autocomplete="off">
    <br />&nbsp;<div align="center"><input type="button" name="home" value="<?php echo $string['ok'] ?>" onclick="window.close();" /></div>
  </form>
  <?php
  } else {
    $student_details = UserUtils::get_user_details($userID, $mysqli);
		    
		$note_details = StudentNotes::get_note($paperID, $userID, $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

	<title><?php echo $string['note'] ?></title>

	<link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/notes.css" />
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
	    var noteHeight = $(document).height() - 110;
	    $("#note").css('height', noteHeight + 'px')
      $("#note").focus();
    });
	 
	  $(window).resize(function() {
	    var noteHeight = $(document).height() - 110;
	    $("#note").css('height', noteHeight + 'px')
	  });
	</script>
</head>

<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="myform" autocomplete="off">

<?php
  echo '<div><strong>' . $string['studentname'] . ':</strong> ' . $student_details['title'] . ' ' . $student_details['surname'] . ', ' . $student_details['first_names'];
  if ($student_details['student_id'] != '') echo ' (' . $student_details['student_id'] . ')';
  echo '</div>';
  

  echo "<input type=\"hidden\" name=\"paperID\" value=\"$paperID\" />\n";
  echo "<strong>" . $string['note'] . ":</strong><br />\n";
  echo "<textarea name=\"note\" id=\"note\" cols=\"60\" rows=\"17\" style=\"font-size:110%; width:100%\" required>" . $note_details['note'] . "</textarea><br />\n";
?>

<br />
<div style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save']; ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></div>
<input type="hidden" name="userID" value="<?php echo $userID ?>" />
<input type="hidden" name="note_id" value="<?php echo $note_details['note_id']; ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>