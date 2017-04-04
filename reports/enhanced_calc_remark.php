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
* This script allows staff to manually override the marks for Calculation type questions.
*
* @author Rob Ingram, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';

$q_id  = check_var('q_id', 'GET', true, false, true);
$paperID  = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

if (!$propertyObj) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$paper_type = $propertyObj->get_paper_type();

// Read question from database.
$result = $mysqli->prepare("SELECT leadin, settings FROM questions WHERE q_id = ?");
$result->bind_param('i', $q_id);
$result->execute();
$result->bind_result($leadin, $settings);
$result->fetch();
$result->close();

// Read user answers from log.
$log_answers = array();
if ($paper_type == '0') {
  $result = $mysqli->prepare("(SELECT 0 AS type, l.id, l.mark, l.user_answer, lm.userID FROM log0 l, log_metadata lm, users u WHERE lm.userID = u.id AND (u.roles LIKE '%Student%' OR u.roles = 'graduate') AND l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ?) UNION ALL (SELECT 1 AS type, l.id, l.mark, l.user_answer, lm.userID FROM log1 l, log_metadata lm, users u WHERE lm.userID = u.id AND (u.roles LIKE '%Student%' OR u.roles = 'graduate') AND l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ?)");
  $result->bind_param('iissiiss', $q_id, $paperID, $_GET['startdate'], $_GET['enddate'], $q_id, $paperID, $_GET['startdate'], $_GET['enddate']);
} else {
  $result = $mysqli->prepare("SELECT $paper_type AS type, l.id, l.mark, l.user_answer, lm.userID FROM log$paper_type l, log_metadata lm, users u WHERE lm.userID = u.id AND (u.roles LIKE '%Student%' OR u.roles = 'graduate') AND l.metadataID = lm.id AND l.q_id = ? AND lm.paperID = ? AND lm.started >= ? AND lm.started <= ?");
  $result->bind_param('iiss', $q_id, $paperID, $_GET['startdate'], $_GET['enddate']);
}
$result->execute();
$result->bind_result($type, $id, $mark, $user_answer, $user_id);
while ($result->fetch()) {
  if ($mark !== '') {
    $answer_obj = new enhancedcalc($configObject);
    $answer_obj->set_useranswer($user_answer);
    $answer_obj->set_settings($settings);
    $dist = $answer_obj->get_answer_distance();
    if ($dist === false) {
      $dist = 9999999;
    }

    // Don't include absolutely correct answers in the list
    if ($dist !== '0') { //'0.00%'
      $log_answers[] = array('paper_type' => $type, 'id' => $id, 'answer_obj' => $answer_obj, 'mark' => strval($mark), 'user_id' => $user_id, 'distance' => $dist);
    }
  }
}
$result->close();

// Get any existing overrides
$overrides = array();
$sql = 'SELECT log_id, new_mark_type, reason FROM marking_override WHERE q_id = ? AND paper_id = ?';
$result = $mysqli->prepare($sql);
$result->bind_param('ii', $q_id, $paperID);
$result->execute();
$result->bind_result($log_id, $new_mark_type, $reason);
while ($result->fetch()) {
  $overrides[$log_id] = array('type' => $new_mark_type, 'reason' => $reason);
}

$question_obj = new enhancedcalc($configObject);
$question_obj->set_settings($settings);

$q_vars = $question_obj->get_question_vars();
$marks_arr = $question_obj->get_question_marks();
if ($marks_arr == false) {
  $marks_arr = array();
}
$q_marks = array_flip($marks_arr);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['remark'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#F1F5FB}
    th {text-align: center; font-weight:normal; color:white; background-color: #295AAD}
    td {text-align: center}
    .separate {border-bottom: 1px solid #CCD9EA}
    .o {text-align:right; padding-right: 10px}
    .c1 {width: 65px; text-align:center}
    .c2 {width: 250px}
    .r1 {background-color: white}
    .r2 {background-color: #B3C8E8}
    .msg {margin-left: 5px; font-size: 90%; color: #001687}
    .overridden {background-color: #B3C8E8}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui-1.10.4.min.js"></script>
  <script type="text/javascript" src="../js/jquery.enhancedcalc_override.js"></script>
  <script>
    langStrings = {'saveerror': '<?php echo $string['saveerror'] ?>', 'nomarkmsg' : '<?php echo $string['nomarkmsg'] ?>'};
  </script>
</head>

<body>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id'] . '&paperID=' . $paperID; ?>" autocomplete="off">
  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/enhancedcalc_override.gif" width="32" height="32" alt="Correct" /></td><td style="background-color:white; font-size:150%; color:#5582D2; border-bottom:1px solid #CCD9EA; text-align: left"><strong><?php echo $string['useranswers']; ?></strong></td></tr>
  </table>

  <p class="msg"><?php echo $string['msg'] ?></p>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #295AAD; margin:0 4px 8px 4px; font-size:90%" id="list">
  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
    <thead>
      <tr>
        <th colspan="<?php echo count($q_vars) ?>"><?php echo $string['variables'] ?></th>
        <th colspan="2"><?php echo $string['answers'] ?></th>
        <th>&nbsp;</th>
        <th colspan="3"><?php echo $string['marks'] ?></th>
        <th colspan="2">&nbsp;</th>
      </tr>
      <tr class="separate">
<?php
foreach ($q_vars as $var => $dummy) {
?>
        <th class="shortcolumn separate"><?php echo $var ?></th>
<?php
}
?>
        <th class="longcolumn separate"><?php echo $string['useranswer'] ?></th>
        <!-- <th class="shortcolumn"><?php echo $string['units'] ?></th> -->
        <th class="longcolumn separate"><?php echo $string['correctans'] ?></th>
        <th class="longcolumn separate"><?php echo $string['distance'] ?></th>
        <th class="shortcolumn separate"><?php echo $string['fullmarks'] ?></th>
        <th class="shortcolumn separate"><?php echo $string['partialmarks'] ?></th>
        <th class="shortcolumn separate"><?php echo $string['incorrect'] ?></th>
        <th class="separate"><?php echo $string['reason'] ?></th>
        <th class="separate">&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
$mark_types = array('correct', 'partial', 'incorrect');
foreach ($log_answers as $id => $ans) {
  $dist = $ans['distance'];
  $log_answers2[$dist][] = $id;
}
krsort($log_answers2, SORT_NUMERIC);

foreach ($log_answers2 as $innerans) {
  foreach ($innerans as $answerin2) {
    $answer = $log_answers[$answerin2];
    if (!isset($answer['distance'])) {
      $answer['distance'] =$string['na'];
    }

    if ($answer['distance'] == 9999999) {
      $distance = $string['na'];
    } else {
      if($answer['distance'] == 'Inf' or $answer['distance'] == '-Inf' ) {
        $answer['distance'] = 0;
      }
      $distance  = number_format($answer['distance'], 2) . '%';
    }

    $new_type = '';
    $reason = '';
    $or_class = '';
    if (isset($overrides[$answer['id']])) {
      $new_type = $overrides[$answer['id']]['type'];
      $reason = $overrides[$answer['id']]['reason'];
      $or_class = ' class="overridden"';
    } else {
      // Populate with existing mark type
      if (isset($q_marks[$answer['mark']])) {
        $new_type = $q_marks[$answer['mark']];
      }
    }
    echo "<tr{$or_class}>";
    $u_vars = $answer['answer_obj']->get_user_vars();
    foreach ($u_vars as $label => $value) {
      echo "<td class=\"shortcolumn\">$value</td>\n";
    }
    echo "<td class=\"longcolumn\">" . $answer['answer_obj']->get_user_answer_full();

    echo "</td>\n";
    echo "<td class=\"longcolumn\">" . $answer['answer_obj']->get_real_answer();
    if ($answer['answer_obj']->get_show_units()) {
      echo ' ' . $answer['answer_obj']->get_user_answer_units_used();
    }
    echo "</td>\n";
    echo '<td class="longcolumn">' . $distance . "</td>\n";

    foreach ($mark_types as $mt) {
      $checked = ($mt == $new_type) ? ' checked="checked"' : '';
  ?>
    <td class="shortcolumn"><input type="radio" name="mark_<?php echo $answer['id'] ?>" value="<?php echo $mt ?>"<?php echo $checked ?> /></td>
  <?php
    }
  ?>
    <td><input type="textbox" id="reason_<?php echo $answer['id'] ?>" name="reason_<?php echo $answer['id'] ?>" size="30" maxlength="255" value="<?php echo $reason ?>" /></td>
    <td>
      <button id="save_<?php echo $answer['id'] ?>" type="button" data-logid="<?php echo $answer['id'] ?>" class="save-row"><?php echo $string['save'] ?></button>
      <input type="hidden" id="log_type_<?php echo $answer['id'] ?>" name="log_type_<?php echo $answer['id'] ?>" value="<?php echo $answer['paper_type'] ?>" />
      <input type="hidden" id="user_id_<?php echo $answer['id'] ?>" name="user_id_<?php echo $answer['id'] ?>" value="<?php echo $answer['user_id'] ?>" />
    </td>
    </tr>
  <?php
  }
}
?>
    </tbody>
  </table>
</div>
<div style="text-align:center"><input type="button" name="cancel" value="<?php echo $string['done'] ?>" style="width:100px" onclick="window.close();" /></div>

  <input type="hidden" id="q_id" name="q_id" value="<?php echo $q_id ?>" />
  <input type="hidden" id="paper_id" name="paper_id" value="<?php echo $paperID ?>" />
  <input type="hidden" id="marker_id" name="marker_id" value="<?php echo $userObject->get_user_ID() ?>" />
</form>
</body>
</html>
