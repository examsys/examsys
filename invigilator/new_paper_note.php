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

// Does the paper exist?
if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['submit'])) {
	if ($_POST['note_id'] == '' or $_POST['note_id'] == '0') {
		PaperNotes::add_note($_POST['note'], $paperID, $userObject->get_user_ID(), $mysqli);
	} else {
		PaperNotes::update_note($_POST['note'], $_POST['note_id'], $mysqli);
	}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['note']; ?></title>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.location.reload(true);
      window.close();
    });
  </script>
</head>
<body>
  <form autocomplete="off">
    <br />&nbsp;<div align="center"><input type="button" name="home" value="<?php echo $string['ok'] ?>" class="ok" onclick="closeWindow();" /></div>
  </form>
  <?php
  } else {
    $current_address = NetworkUtils::get_client_address();

		$note_details = PaperNotes::get_note($paperID, $current_address, $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

	<title><?php echo $string['note']; ?></title>

	<link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/notes.css" />
	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script>
    $(function () {
	    var noteHeight = $(document).height() - 90;
	    $("#note").css('height', noteHeight + 'px')
      $("#note").focus();
			
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
    });
	 
	  $(window).resize(function() {
	    var noteHeight = $(document).height() - 90;
	    $("#note").css('height', noteHeight + 'px')
	  });
  </script>
</head>

<body>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="theform" id="theform" autocomplete="off">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<td>
<?php
  echo "<input type=\"hidden\" name=\"paperID\" value=\"" . $paperID . "\" />\n";
  echo "<strong>" . $string['note'] . ":</strong><br />\n";
  echo "<textarea name=\"note\" id=\"note\" cols=\"60\" rows=\"17\" style=\"font-size:110%; width:99%\" required autofocus>" . $note_details['note'] . "</textarea><br />\n";
?>
</td>
</table>
<br />
<div style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="javascript:window.close();" /></div>
<input type="hidden" name="note_id" value="<?php echo $note_details['note_id'] ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>