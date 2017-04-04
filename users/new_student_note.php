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
* Add a note to a students file
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'REQUEST', true, false, true);
$paperID = check_var('paperID', 'REQUEST', false, false, true);

// Does the paper exist?
if (!is_null($paperID) and !Paper_utils::paper_exists($paperID, $mysqli)) {
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
		StudentNotes::add_note($userID, $_POST['note'], $paperID, $userObject->get_user_ID(), $mysqli);
	} else {
		StudentNotes::update_note($_POST['note'], $_POST['note_id'], $mysqli);
	}
?>
<!DOCTYPE html>
  <html>
  <head><title><?php echo $string['note']; ?></title>
  <?php
    if ($_POST['calling'] == 'class_totals') {
  ?>
  <script>
    function closeWindow() {
      window.opener.location.reload();
      window.close();
    }
  </script></head>
  <body onload="window.opener.location.reload(); closeWindow();">
  <?php
    } else {
  ?>
  <script>
    function closeWindow() {
      window.opener.location = "details.php?userID=<?php echo $userID ?>&tab=notes";
      window.close();
    }
  </script></head>
  <body onload="closeWindow();">
  <?php
    }
  ?>
  <form autocomplete="off">
    <br />&nbsp;<div align="center"><input type="button" name="home" value="   OK   " onclick="closeWindow();" /></div>
  </form>
  <?php
  } else {
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
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
      
      resizeTextbox();      
      $("#note").focus();
    });
	 
	  $(window).resize(function() {
	    resizeTextbox();
	  });
    
    function resizeTextbox() {
	    var noteHeight = $(window).height() - 100;
	    $("#note").css('height', noteHeight + 'px');     
    }
   
    $('#theform').submit(function() {
      if ($("#paperID").val() == '') {
        alert("<?php echo $string['namecheck']; ?>");
        return false;
      }
   
      if ($("#note").val() == '') {
        alert("<?php echo $string['notecheck']; ?>");
        return false;
      }
     
      return true;
    });
  </script>
</head>

<body>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="theform" id="theform" autocomplete="off">
<?php
	$disabled = '';
	$note_details = array('note_id'=>0, 'note'=>'');
	
	$student_details = UserUtils::get_user_details($userID, $mysqli);
  
	if (isset($_GET['paperID'])) {
    echo "<input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />\n";
		
		$note_details = StudentNotes::get_note($_GET['paperID'], $userID, $mysqli);
		
    echo '<strong>' . $student_details['title'] . ' ' . $student_details['surname'] . ', ' . $student_details['initials'] . '</strong><br />';
  } else {
		$student_modules = UserUtils::load_student_modules($userID, $mysqli);
		$yearutils = new yearutils($mysqli);
		$current_year = $yearutils->get_current_session();
		$module_IDs = array();
		if (isset($student_modules[$current_year])) {
			foreach ($student_modules[$current_year] as $moduleID=>$module_code) {
				$module_IDs[] = $moduleID;
			}
		}
		
    echo $string['papername'] . " <select name=\"paperID\" id=\"paperID\" required>\n<option value=\"\"></option>\n";
    if (count($module_IDs) > 0) {
			// Look up papers that have been live in the last 28 days.
			$result = $mysqli->prepare("SELECT DISTINCT properties.property_id, paper_title FROM properties, properties_modules WHERE properties.property_id = properties_modules.property_id AND idMod IN (" . implode(',', $module_IDs) . ") AND end_date > DATE_SUB(NOW(), INTERVAL 28 DAY) AND deleted IS NULL ORDER BY paper_title");
			$result->execute();
			$result->bind_result($property_id, $paper_title);
			while ($result->fetch()) {
				echo "<option value=\"$property_id\">$paper_title</option>\n";
			}
			echo "</select>\n<br />\n";
			$result->close();
		} else {
			$disabled = ' disabled="disabled"';
		}
  }
  
  echo "<br />" . $string['note'] . "<br />\n";
  echo "<div style=\"text-align:center\"><textarea name=\"note\" id=\"note\" required>" . $note_details['note'] . "</textarea></div>\n";
?>
<div style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"<?php echo $disabled ?> /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></div>
<input type="hidden" name="userID" value="<?php echo $userID ?>" />
<input type="hidden" name="calling" value="<?php if (isset($_GET['calling'])) echo $_GET['calling'] ?>" />
<input type="hidden" name="note_id" value="<?php echo $note_details['note_id'] ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>