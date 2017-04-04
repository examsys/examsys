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

require_once '../include/load_config.php';
require_once '../include/media.inc';
require_once '../include/errors.php';
require_once '../include/sct_review.inc';

// Connect to the database as the SCT user.
$mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host') , $configObject->get('cfg_db_sct_user'), $configObject->get('cfg_db_sct_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

// Check for key parameters
$form_fields = $_SESSION['authenticationObj']['loginformfields'];
$reviewer_name = check_var('reviewer_name', $form_fields, true, false, true);
$reviewer_email = check_var('reviewer_email', $form_fields, true, false, true);

function display_question($question, &$question_no, $answers, $string) {
  $question_no++;

  if ($question['scenario'] != '') {
    echo "<tr><td class=\"q_no\">" . $question_no . ".&nbsp;</td><td style=\"background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $string['clinicalvignette'] . "</td></tr>\n";
    echo '<tr><td style="vertical-align:top; text-align:right"></td><td>';
    if ($question['notes'] != '') echo '<p class="note"><img src="../artwork/notes_icon.gif" width="16" height="16" alt="Note" />&nbsp;<strong>' . $string['note'] . '</strong>&nbsp;' . $question['notes'] . '</p>';
    echo $question['scenario'] . "<br />\n<br />";
    $li_set = 1;
  }
  if ($question['q_media'] != '') {
    if ($li_set == 0) {
      echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
    }
    echo '<p style="text-align:center">' . display_media($question['q_media'], $question['q_media_width'], $question['q_media_height'], '') . "</p>\n";
    $li_set = 1;
  }

  $sct_parts = explode('~',$question['leadin']);
  $sct_titles = array(1=>'hypothesis', 2=>'investigation', 3=>'prescription', 4=>'intervention', 5=>'treatment');
  
  echo '<table cellpadding="2" cellspacing="0" border="0" style="width:100%">';
  echo "<tr><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $string[$sct_titles[$question['display_method']]] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $string['newinformation'] . "</td></tr>\n";
  echo "<tr><td style=\"width:49%; vertical-align:top\">" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\">" . $sct_parts[1] . "</td></tr>\n";
  echo "</table>\n";

  echo '<p><strong>' . $string['sct_msg' . $question['display_method']] . '</strong></p>';
  echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';

  $part_id = 0;
  foreach ($question['options'] as $option_text) {
    $part_id++;
    if (isset($answers[$question['q_id']]['answer']) and $part_id == $answers[$question['q_id']]['answer']) {
      echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" checked /></td><td>$option_text</td></tr>\n";
    } else {
      echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>$option_text</td></tr>\n";
    }
  }
  echo "</table>\n</blockquote>\n";

  echo "<span style=\"color:#808080\">" . $string['briefreasonwhy'] . "</span><br /><textarea name=\"reason$question_no\" cols=\"100\" rows=\"3\" />";
  if (isset($answers[$question['q_id']]['reason'])) {
    echo $answers[$question['q_id']]['reason'];
  }
  echo "</textarea>\n";
  echo "</td></tr>\n";
  echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
}

if (isset($_POST['submit'])) {
  $question_no = 1;

  // Clear previous ratings for current reviewer and current paper
  $stmt = $mysqli->prepare("DELETE FROM sct_reviews WHERE paperID=? AND reviewer_name = ? AND reviewer_email = ?");
  $stmt->bind_param('iss', $paperID, $reviewer_name, $reviewer_email);
  $stmt->execute();
  $stmt->close();

  // Loop through the structure of the paper
  $stmt = $mysqli->prepare("SELECT q_id FROM (papers, questions) WHERE papers.paper=? AND papers.question = questions.q_id AND q_type = 'sct' ORDER BY display_pos");
  $stmt->bind_param('i', $paperID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($q_id);
  while ($stmt->fetch()) {
    // Store experts' reviews in sct_reviews table
    $update = $mysqli->prepare("INSERT INTO sct_reviews VALUES (NULL, ?, ?, ?, ?, ?, ?)");
    $update->bind_param('ssiiis', $reviewer_name, $reviewer_email, $paperID, $q_id, $_POST['q' . $question_no], $_POST['reason' . $question_no]);
    $update->execute();
    $update->close();

    $question_no++;
  }
  $stmt->close();
}
require '../config/start.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['sctreview'] ?></title>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <?php
    if (isset($_POST['submit'])) {
  ?>
  <script>
     $(function() {
       alert('<?php echo $string['saved_msg'] ?>');
     });
   </script>
   <?php
    }
  ?>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/start.css" />
  <link rel="stylesheet" type="text/css" href="../css/key.css" />
</head>

<body>
<div id="maincontent">

<?php
  // Get any previous answers for the current reviewer.
  $saved_data = array();
  $stmt = $mysqli->prepare("SELECT q_id, answer, reason FROM sct_reviews WHERE paperID = ? AND reviewer_name = ? AND reviewer_email = ?");
  $stmt->bind_param('iss', $paperID, $reviewer_name, $reviewer_email);
  $stmt->execute();
  $stmt->bind_result($q_id, $answer, $reason);
  while ($stmt->fetch()) {
    $saved_data[$q_id]['answer'] = $answer;
    $saved_data[$q_id]['reason'] = $reason;
  }
  $stmt->close();
  
  // Output the top logo banner.
  echo $top_table_html;
  echo '<tr><td><div style="margin-left:0;font-size:180%;color:white;font-weight:bold">' . $propertyObj->get_paper_title() . '</div></td>';
  echo $logo_html;
  
  echo "<form name=\"myform\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" autocomplete=\"off\">\n";
  echo "<br />\n";
  
  echo "<div class=\"key\">" . $string['top_msg'] . "</div>\n<br />\n";
  
  echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"width:99%; font-size:100%\">\n<col width=\"40\"><col>\n";

  //build the questions_array
  $old_q_id = '';
  $q_no = 0;
  $question_no = 0;
  $questions_array = array();

  $stmt = $mysqli->prepare("SELECT q_id, theme, leadin, scenario, notes, display_method, q_media, q_media_width, q_media_height, q_option_order, option_text FROM (papers, questions, options) WHERE papers.paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type='sct' ORDER BY display_pos, id_num");
  $stmt->bind_param('i', $paperID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($q_id, $theme, $leadin, $scenario, $notes, $display_method, $q_media, $q_media_width, $q_media_height, $q_option_order, $option_text);
  while ($stmt->fetch()) {
    if ($old_q_id != $q_id) {
      $q_no++;
      $questions_array[$q_no]['theme'] = trim($theme);
      $questions_array[$q_no]['scenario'] = trim($scenario);
      $questions_array[$q_no]['leadin'] = trim($leadin);
      $questions_array[$q_no]['notes'] = trim($notes);
      $questions_array[$q_no]['q_id'] = $q_id;
      $questions_array[$q_no]['display_method'] = $display_method;
      $questions_array[$q_no]['q_media'] = $q_media;
      $questions_array[$q_no]['q_media_width'] = $q_media_width;
      $questions_array[$q_no]['q_media_height'] = $q_media_height;
      $questions_array[$q_no]['q_option_order'] = $q_option_order;
    }
    $questions_array[$q_no]['options'][] = $option_text;
    
    $old_q_id = $q_id;
  }
  $stmt->close();
  
  // Display the questions
  if (count($questions_array) > 0) {
    foreach ($questions_array as &$question) {
      if ($question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
      display_question($question, $question_no, $saved_data, $string);
    }
  } else {
    echo "<tr><td>&nbsp;</td><td>{$string['nosctquestions']}</td></tr>\n";
  }

?>
</table>

<?php
  if (count($questions_array) > 0) {
?>
  <div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save'] ?>" class="ok" /></div>
<?php
  }
?>
<input type="hidden" name="id" value="<?php echo $crypt_name ?>" />
<input type="hidden" name="reviewer_name" value="<?php echo $reviewer_name ?>" />
<input type="hidden" name="reviewer_email" value="<?php echo $reviewer_email ?>" />
<br />
</form>

</div>

</body>
</html>