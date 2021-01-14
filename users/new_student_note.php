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
$calling = param::optional('calling', '', param::TEXT, param::FETCH_GET);

// Does the paper exist?
if (!is_null($paperID) and !Paper_utils::paper_exists($paperID, $mysqli)) {
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
  
  <title><?php echo $string['note']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/notes.css" />

  <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src="../js/require.js"></script>
  <script src="../js/main.min.js"></script>
  <script src="../js/studentnoteinit.min.js"></script>

</head>

<body>
<form action="" method="post" name="theform" id="theform" autocomplete="off">
<?php
    $disabled = '';
$note_details = array('note_id' => 0, 'note' => '');
  
    $student_details = UserUtils::get_user_details($userID, $mysqli);
if (isset($_GET['paperID'])) {
    echo '<input type="hidden" name="paperID" value="' . $_GET['paperID'] . "\" />\n";
        
    $note_details = StudentNotes::get_note($_GET['paperID'], $userID, $mysqli);
    if ($calling === 'class_totals') {
        echo '<strong>' . $student_details['title'] . ' ' . $student_details['surname'] . ', ' . $student_details['initials'] . '</strong><br />';
    }
} else {
    $student_modules = UserUtils::load_student_modules($userID, $mysqli);
    $yearutils = new yearutils($mysqli);
    $current_year = $yearutils->get_current_session();
    $module_IDs = array();
    if (isset($student_modules[$current_year])) {
        foreach ($student_modules[$current_year] as $moduleID => $module_code) {
            $module_IDs[] = $moduleID;
        }
    }
     
      echo $string['papername'] . " <select name=\"paperID\" id=\"paperID\" required>\n<option value=\"\"></option>\n";
    if (count($module_IDs) > 0) {
        // Look up papers that have been live in the last 28 days.
            $result = $mysqli->prepare('SELECT DISTINCT properties.property_id, paper_title FROM properties, properties_modules WHERE properties.property_id = properties_modules.property_id AND idMod IN (' . implode(',', $module_IDs) . ') AND end_date > DATE_SUB(NOW(), INTERVAL 28 DAY) AND deleted IS NULL ORDER BY paper_title');
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
  
  echo '<br />' . $string['note'] . "<br />\n";
echo '<div style="text-align:center"><textarea name="note" id="note" required>' . $note_details['note'] . "</textarea></div>\n";
?>
<div style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"<?php echo $disabled ?> /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" /></div>
<input type="hidden" id="userID" name="userID" value="<?php echo $userID ?>" />
<input type="hidden" name="calling" value="<?php echo $calling; ?>" />
<input type="hidden" name="note_id" value="<?php echo $note_details['note_id'] ?>" />
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
<?php
$mysqli->close();
?>
