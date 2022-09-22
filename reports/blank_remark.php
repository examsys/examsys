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
$result = $mysqli->prepare('SELECT option_text FROM options WHERE o_id = ?');
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($option_text);
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
        $answer = mb_strtolower(StringUtils::clean_and_trim($answer));
    }
    $log_answers[$type][$id] = $tmp_answer;
}
$result->close();
$blank_details = explode('[blank', $option_text);
for (
    $i = 1; $i < count($blank_details); $i++
) {
    $end_start_tag = mb_strpos($blank_details[$i], ']');
    $start_end_tag = mb_strpos($blank_details[$i], '[/blank]');
    $blank_options = mb_substr($blank_details[$i], ($end_start_tag + 1), ($start_end_tag - 1));
    if ($i == $_GET['blank'] && $blank_options !== '') {
        $blanks = explode(',', $blank_options);
    }
}

// Merge the same option on its own and with spaces (e.g. 'cat' and ' cat').
$new_blanks = array();
foreach ($blanks as $blank) {
    $new_blanks[] = mb_strtolower(trim($blank));
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

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src='../js/blankremarkinit.min.js'></script>


</head>

<body>

<form id="remarkform" method="post" action="" autocomplete="off">
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
    foreach ($log_type as $id => $log_answer) {
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
foreach ($unique_list as $word => $occurrance) {
    $match = false;
    foreach ($blanks as $blank) {
        if (mb_strtolower($word) == mb_strtolower($blank)) {
            $match = true;
            $word = $blank;
        }
    }

    if ($match) {
        echo '<tr id="div' . $word_count . '" class="r2"><td class="c1"><input type="checkbox" data-div="' . $word_count . '" id="word' . $word_count . '" name="word' . $word_count . '" value="' . htmlspecialchars($word) . '" checked="checked" /></td><td class="c2">' . $word . '</td><td class="o">' . $occurrance . '</td></tr>';
    } else {
        echo '<tr id="div' . $word_count . '" class="r1"><td class="c1"><input type="checkbox" data-div="' . $word_count . '" id="word' . $word_count . '" name="word' . $word_count . '" value="' . htmlspecialchars($word) . '" /></td><td class="c2">' . $word . '</td><td class="o">' . $occurrance . '</td></tr>';
    }
    $word_count++;
}
?>
</table>
</div>

<input type="hidden" name="word_count" value="<?php echo $word_count; ?>" />
<input type="hidden" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input type="hidden" name="blank" value="<?php echo $_GET['blank']; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="hidden" name="startdate" value="<?php echo $_GET['startdate']; ?>" />
<input type="hidden" name="enddate" value="<?php echo $_GET['enddate']; ?>" />
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" /></div>

</form>
<?php
$render = new render($configObject);
// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render = new render($configObject);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
