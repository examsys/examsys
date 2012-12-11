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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
require '../classes/logger.class.php';

check_var('q_id', 'GET', true, false);
check_var('paperID', 'GET', true, false);

// Read whole question from database.
$result = $mysqli->prepare("SELECT option_text FROM options WHERE o_id=?");
$result->bind_param('i', $_GET['q_id']);
$result->execute();
$result->bind_result($option_text);
$result->fetch();
$result->close();
  
// Read user properties from questions.
$result = $mysqli->prepare("SELECT score_method, marks_correct, marks_incorrect FROM questions, options WHERE questions.q_id=options.o_id AND q_id=?");
$result->bind_param('i', $_GET['q_id']);
$result->execute();
$result->bind_result($score_method, $marks_correct, $marks_incorrect);
$result->fetch();
$result->close();
  
// Read user answers from log.
$log_answers = array();
$result = $mysqli->prepare("SELECT id, user_answer FROM log2 WHERE q_id=? AND q_paper=? AND log2.started>=? AND log2.started<=?");
$result->bind_param('iiss', $_GET['q_id'], $_GET['paperID'], $_GET['startdate'], $_GET['enddate']);
$result->execute();
$result->bind_result($id, $user_answer);
while ($result->fetch()) {
  $log_answers[$id] = $user_answer;
}
$result->close();
  
if (isset($_POST['submit'])) {
  $option_list = '';

  // Iterate around all words marked for correction
  for ($i=0; $i<$_POST['word_count']; $i++) {
    if (isset($_POST['word' . $i])) {
      if ($option_list == '') {
        $option_list = $_POST['word' . $i];
      } else {
        $option_list .= ',' . $_POST['word' . $i];
      }
    }
  }
 
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
  $result = $mysqli->prepare("UPDATE options SET option_text=? WHERE o_id=?");
  $result->bind_param('si', $new_option_text, $_GET['q_id']);
  $result->execute();  
  $result->close();
  
  $logger = new Logger($mysqli);
  $success = $logger->track_change('Post-Exam Blank correction', $_GET['q_id'], $userObject->get_user_ID(), $option_text, $new_option_text, 'Question/Stem');
  
  // Remark student answers
  $blank_details = explode("[blank", $new_option_text);
  $no_answers = count($blank_details) - 1;
  $have_answer = false;
  for ($i=1; $i<=$no_answers; $i++) {
    $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
    $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
    $answer_list[] = explode(',',$blank_details[$i]);
  }
    
  foreach ($log_answers as $id=>$log_answer) {
    $mark = 0;
    $user_parts = explode('|', $log_answer);
    
    for ($i=1; $i<=$no_answers; $i++) {
      $match = false;
      if ($user_parts[$i] != 'u') {
        foreach ($answer_list[$i-1] as $alternative) {
          if (trim($user_parts[$i]) == trim($alternative)) {
            $match = true;
          }
        }
        if ($match) {
          $mark += $marks_correct;
        } else {
          $mark -= $marks_incorrect;
        }
      }
    }
    // Update log2 with new student marks.
    $result = $mysqli->prepare("UPDATE log2 SET mark=? WHERE id=?");
    $result->bind_param('ii', $mark, $id);
    $result->execute();
    $result->close();
  }  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Remark</title>
  <script type="text/javascript">
    function reload() {
      window.opener.location = window.opener.location;
      self.close();
    }
  </script>
</head>
<body onload="reload()">
</body>
</html>

<?php  
} else {
  $blank_details = explode('[blank',$option_text);
  for ($i=1; $i<count($blank_details); $i++) {
    $end_start_tag = strpos($blank_details[$i],']');
    $start_end_tag = strpos($blank_details[$i],'[/blank]');
    $blank_options = substr($blank_details[$i],($end_start_tag+1),($start_end_tag-1));
    if ($i == $_GET['blank']) {
      $blanks = explode(',', $blank_options);
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['remark'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#F1F5FB}
    th {font-weight:normal; color:#001687}
    .o {text-align:right; padding-right:10px}
    .c1 {width:65px; text-align:center}
    .c2 {width:250px}
    .r1 {background-color:white}
    .r2 {background-color:#B3C8E8}
    .msg {text-align:justify; margin:5px; font-size:90%; color:#001687}
  </style>
  
  <script language="JavaScript">
    function toggle(objectID) {
      if (document.getElementById(objectID).className == 'r2') {
        document.getElementById(objectID).className = 'r1';
      } else {
        document.getElementById(objectID).className = 'r2';
      }
    }
    
    function resizeList() {
      var winW = 630, winH = 460;
      if (document.body && document.body.offsetWidth) {
        winW = document.body.offsetWidth;
        winH = document.body.offsetHeight;
      }
      if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
        winW = document.documentElement.offsetWidth;
        winH = document.documentElement.offsetHeight;
      }
      if (window.innerWidth && window.innerHeight) {
        winW = window.innerWidth;
        winH = window.innerHeight;
      }
      winH -= 160;
      document.getElementById('list').style.height = winH + 'px';
    }
  </script>
</head>

<body onload="resizeList()" onresize="resizeList()">

<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?q_id=' . $_GET['q_id'] . '&blank=' . $_GET['blank'] . '&paperID=' . $_GET['paperID']; ?>">
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
$unique_list = array();

foreach ($log_answers as $id=>$log_answer) {
  $parts = explode('|', $log_answer);
  
  $word = strtolower(trim($parts[$_GET['blank']]));
  
  if ($word != 'u') {
    if (isset($unique_list[$word])) {
      $unique_list[$word]++;
    } else {
      $unique_list[$word] = 1;
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
    echo '<tr id="div' . $word_count . '" class="r2"><td class="c1"><input type="checkbox" onclick="toggle(\'div'. $word_count . '\')" name="word' . $word_count . '" value="' . $word . '" checked="checked" /></td><td class="c2">' . $word . '</td><td class="o">' . $occurrance . '</td></tr>';
  } else {
    echo '<tr id="div' . $word_count . '" class="r1"><td class="c1"><input type="checkbox" onclick="toggle(\'div'. $word_count . '\')" name="word' . $word_count . '" value="' . $word . '" /></td><td class="c2">' . $word . '</td><td class="o">' . $occurrance . '</td></tr>';
  }
  $word_count++;
}
?>
</table>
</div>

<input type="hidden" name="word_count" value="<?php echo $word_count; ?>" />
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" style="width:100px" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" style="width:100px" onclick="window.close();" /></div>

</form>
</body>
</html>
<?php
}
?>