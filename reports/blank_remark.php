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
* This script presents a list of all the unique entries (words) entered for a particular blank in
* a fill-in-the-blank question with textboxes. The interface allows staff to tick correct alternative
* spellings and have the system remark student scripts (only works with summative exams).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$q_id     = check_var('q_id', 'GET', true, false, true);
$paperID  = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$paper_type = $propertyObj->get_paper_type();

// Read whole question from database.
$result = $mysqli->prepare("SELECT option_text FROM options WHERE o_id = ?");
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($option_text);
$result->fetch();
$result->close();

// Read user properties from questions.
$result = $mysqli->prepare("SELECT score_method, marks_correct, marks_incorrect FROM questions, options WHERE questions.q_id = options.o_id AND q_id = ?");
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($score_method, $marks_correct, $marks_incorrect);
$result->fetch();
$result->close();

// Read user answers from log.
$log_answers = array();
if ($paper_type == '0') {
  $result = $mysqli->prepare("(SELECT 0 AS type, l.id, l.user_answer FROM log0 l, log_metadata lm WHERE l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%' AND student_grade NOT LIKE '%nhs%') UNION ALL (SELECT 1 AS type, l.id, l.user_answer FROM log1 l, log_metadata lm WHERE l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%' AND student_grade NOT LIKE '%nhs%')");
  $result->bind_param('iissiiss', $q_id, $paperID, $_GET['startdate'], $_GET['enddate'], $q_id, $paperID, $_GET['startdate'], $_GET['enddate']);
} else {
  $result = $mysqli->prepare("SELECT $paper_type AS type, l.id, l.user_answer FROM log$paper_type l, log_metadata lm WHERE l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? AND DATE_ADD(lm.started, INTERVAL 2 MINUTE) >= ? AND lm.started <= ? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%' AND student_grade NOT LIKE '%nhs%'");
  $result->bind_param('iiss', $q_id, $paperID, $_GET['startdate'], $_GET['enddate']);
}
$result->execute();
$result->bind_result($type, $id, $user_answer);
while ($result->fetch()) {
  // Decode user answers into an array of lowercase strings.
  $tmp_answer = json_decode($user_answer);
  foreach ($tmp_answer as &$answer) {
    $answer = strtolower(StringUtils::clean_and_trim($answer));
  }
  $log_answers[$type][$id] = $tmp_answer;
}
$result->close();

if (isset($_POST['submit'])) {
  $option_list_array = array();

  // Iterate around all words marked for correction
  for ($i=0; $i<$_POST['word_count']; $i++) {
    if (isset($_POST['word' . $i])) {
      // Encode commas.
      $word = str_replace(',', '&#44;', $_POST['word' . $i]);
      $option_list_array[] = $word;
    }
  }
  $option_list = implode(',', $option_list_array);

  $blank_details = explode('[blank', $option_text);
  for ($i=1; $i<count($blank_details); $i++) {
    $end_start_tag = strpos($blank_details[$i],']');
    $start_end_tag = strpos($blank_details[$i],'[/blank]');
    $blank_options = substr($blank_details[$i],($end_start_tag+1),($start_end_tag-1));

    $new_option_text = substr($blank_details[$i],0,($end_start_tag+1));
  }

  for ($i=1; $i<count($blank_details); $i++) {
    $tmp_parts = explode('[/blank]', $blank_details[$i]);

    if ($i == $_GET['blank']) {
      $blank_details[$i] = ']' . $option_list . '[/blank]' . $tmp_parts[1];
    }
  }

  $new_option_text = $blank_details[0];
  for ($i=1; $i<count($blank_details); $i++) {
    $new_option_text .= '[blank' . $blank_details[$i];
  }

  // Save the new option text back to the Questions table.
  $result = $mysqli->prepare("UPDATE options SET option_text = ? WHERE o_id = ?");
  $result->bind_param('si', $new_option_text, $q_id);
  $result->execute();
  $result->close();
	
  $logger = new Logger($mysqli);
  $success = $logger->track_change('Post-Exam Blank correction', $q_id, $userObject->get_user_ID(), $option_text, $new_option_text, 'Question/Stem');

  // Remark student answers
  $blank_details = explode("[blank", $new_option_text);
  $no_answers = count($blank_details) - 1;
  
  $totalpos = 0;
  for ($i = 1; $i <= $no_answers; $i++) {
    if (preg_match("|mark=\"([0-9]{1,3})\"|", $blank_details[$i], $mark_matches)) {
      $totalpos += $mark_matches[1];
      $individual_q_mark = $mark_matches[1];
    } else {
      $totalpos += $marks_correct;
      $individual_q_mark = $marks_correct;
    }
  }

  foreach ($log_answers as $log_type=>$log_data) {
    foreach ($log_data as $id => $user_parts) {
      $mark = 0;
      $have_answer = false;
      $saved_response = '';
      // Required to shift array indexes.
      $blank_details_redo = array();
      $j = 0;
      $blank_details = explode("[blank", $new_option_text);
      for ($i = 1; $i <= $no_answers; $i++) {
        // Strip out answers from $blank_details
        // n.b. First item in $blank_details not required
        // Step 1. get all contents after ]
        $blank_details_redo[$j] = substr($blank_details[$i], (strpos($blank_details[$i], ']') + 1));
        // Step 2. get all contents before [/blank]
        $blank_details_redo[$j] = substr($blank_details_redo[$j], 0, (strpos($blank_details[$i], '[/blank]') - 1));
        // $blank_details_redo is now what was between ] and [/blank]
        $answer_list = explode(',', $blank_details_redo[$j]);
        if ($user_parts[$j] != 'u' and $user_parts[$j] != '') {
          $have_answer = true;
          $is_correct = false;
          foreach ($answer_list as $individual_answer) {
            if ($user_parts[$j] == strtolower(StringUtils::clean_and_trim($individual_answer))) {
              $is_correct = true;
              break;
            }
          }
          $mark += ($is_correct) ? $individual_q_mark : $marks_incorrect;
        }
        $j++;
      }
      // Recalculate if mark per question
      if ($score_method == 'Mark per Question') {
        if ($have_answer) {
          $mark = ($mark == $totalpos) ? $marks_correct : $marks_incorrect;
        }
      }
      // Update marks in the database
      $result = $mysqli->prepare("UPDATE log$log_type SET mark = ? WHERE id = ?");
      $result->bind_param('ii', $mark, $id);
      $result->execute();
      $result->close();
    }
  }

  // Set paper to re-cache marks again after the change.
  $propertyObj->set_recache_marks(1);
  $propertyObj->save();

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['remark']); ?></title>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function() {
      window.opener.location = window.opener.location;
      self.close();
    });
  </script>
</head>
<body>
</body>
</html>

<?php
} else {
  $blank_details = explode('[blank', $option_text);
  for ($i=1; $i<count($blank_details); $i++) {
    $end_start_tag = strpos($blank_details[$i],']');
    $start_end_tag = strpos($blank_details[$i],'[/blank]');
    $blank_options = substr($blank_details[$i],($end_start_tag+1),($start_end_tag-1));
    if ($i == $_GET['blank'] && $blank_options !== '') {
      $blanks = explode(',', $blank_options);
    }
  }
  
  // Merge the same option on its own and with spaces (e.g. 'cat' and ' cat').
  $new_blanks = array();
  foreach ($blanks as $blank) {
    $new_blanks[] = strtolower(trim($blank));
  }
  $blanks = array_unique($new_blanks);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['remark']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#F1F5FB}
    th {font-weight:normal; color:#001687}
    .o {text-align:right; padding-right:10px}
    .c1 {width:65px; text-align:center}
    .c2 {width:250px}
    .r1 {background-color:white}
    .r2 {background-color:#FFBD69}
    .msg {text-align:justify; margin:5px; font-size:90%; color:#001687}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    function toggle(objectID) {
      if ($('#' + objectID).hasClass('r2')) {
        $('#' + objectID).addClass('r1');
        $('#' + objectID).removeClass('r2');
      } else {
        $('#' + objectID).addClass('r2');
        $('#' + objectID).removeClass('r1');
      }
    }

    function resizeList() {
      winH = $(window).height() - 160;

      $('#list').css('height', winH + 'px');
    }
    
    $(function() {
			resizeList();
			
			$(window).resize(function(){
				resizeList();
			});
		});	
  </script>
</head>

<body>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id'] . '&blank=' . $_GET['blank'] . '&paperID=' . $_GET['paperID'] . '&startdate=' . $_GET['startdate'] . '&enddate=' . $_GET['enddate']; ?>" autocomplete="off">
  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/dictionary.png" width="32" height="32 alt="Word List" /></td><td style="background-color:white; font-size:150%; color:#5582D2; border-bottom:1px solid #CCD9EA"><strong><?php echo $string['uniqueanswers']; ?></td></tr>
  </table>

  <div class="msg"><?php echo $string['msg']; ?></div>

  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
  <tr><th style="width:70px"><?php echo $string['correct']; ?></th><th style="width:250px"><?php echo $string['wordphrase']; ?></th><th><?php echo $string['occurrence']; ?></th></tr>
  </table>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:0px 4px 8px 4px; font-size:90%" id="list">
  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
<?php
// Make sure words that are already defined as correct appear in the list even if no users have
// given them as an answer
$unique_list = array_fill_keys($blanks, 0);

foreach ($log_answers as $log_type) {
  foreach ($log_type as $id=>$log_answer) {
    foreach ($log_answer as $word) {
      if ($word != 'u') {
        if (isset($unique_list[$word])) {
          $unique_list[$word]++;
        } else {
          $unique_list[$word] = 1;
        }
      }
    }
  }
}

$word_count = 0;
ksort($unique_list);
foreach ($unique_list as $word=>$occurrance) {
  $match = false;
  foreach ($blanks as $blank) {
    if (strtolower($word) == strtolower($blank)) {
      $match = true;
      $word = $blank;
    }
  }

  if ($match) {
    echo '<tr id="div' . $word_count . '" class="r2"><td class="c1"><input type="checkbox" onclick="toggle(\'div'. $word_count . '\')" name="word' . $word_count . '" value="' . htmlspecialchars($word) . '" checked="checked" /></td><td class="c2">' . $word . '</td><td class="o">' . $occurrance . '</td></tr>';
  } else {
    echo '<tr id="div' . $word_count . '" class="r1"><td class="c1"><input type="checkbox" onclick="toggle(\'div'. $word_count . '\')" name="word' . $word_count . '" value="' . htmlspecialchars($word) . '" /></td><td class="c2">' . $word . '</td><td class="o">' . $occurrance . '</td></tr>';
  }
  $word_count++;
}
?>
</table>
</div>

<input type="hidden" name="word_count" value="<?php echo $word_count; ?>" />
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" onclick="window.close();" /></div>

</form>
</body>
</html>
<?php
}
?>