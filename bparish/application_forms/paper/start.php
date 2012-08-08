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
* Handles paper display and the recording of marks to the 'logX' tables. Uses functions within 'display_functions.inc' to process specific 
* types of questions. Start.php continues calling itself while there are further screens to be displayed and then calls 'finish.php'
* to end.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
require '../include/staff_student_auth.inc';
require '../include/display_functions.inc';
require '../include/media.inc';
require_once '../include/errors.inc';
require '../include/paper_security.inc';

check_var('id', 'GET', true, false);

function randomQOverwrite(&$questions, $random_q_data, $paper_type, $user_answers, $current_screen, $q_no) {
  global $mysqli, $used_questions;
 
  $selected_q_id = '';
  if(isset($user_answers[$current_screen])) {
    //match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }
  
  if ($selected_q_id == '') {
    // Generate a random question ID.
    $random_q_no = count($random_q_data['options']);
    $try = 0;
    $unique = false;
    while ($unique == false and $try < 9999) {
      $selected_no = rand(0,$random_q_no-1);
      $selected_q_id = $random_q_data['options'][$selected_no]['option_text'];
      if (!isset($used_questions[$selected_q_id])) $unique = true;
      $try++;
    }
    $used_questions[$selected_q_id] = 1;
  }
  
  // Look up selected question and overwrite data.
  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
  $question_data->bind_param('i', $selected_q_id);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
  while ($question_data->fetch()) {
    if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
      $question['theme'] = $theme;
      $question['scenario'] = $scenario;
      $question['leadin'] = $leadin;
      $question['notes'] = $notes;
      $question['q_type'] = $q_type;
      $question['q_id'] = $q_id;
      $question['display_pos'] = $q_no;
      $question['score_method'] = $score_method;
      $question['display_method'] = $display_method;
      $question['q_media'] = $q_media;
      $question['q_media_width'] = $q_media_width;
      $question['q_media_height'] = $q_media_height;
      $question['q_option_order'] = $q_option_order;
      $question['dismiss'] = '';
    }
    $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  }
  $questions[] = $question;
  echo "\n<input type=\"hidden\" name=\"q" . $q_no . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
}

function keywordQOverwrite(&$questions, $random_q_data, $paper_type, $user_answers, $current_screen, $q_no) {
  global $mysqli, $used_questions, $string;
  
  $selected_q_id = '';
  if (isset($user_answers[$current_screen])) {
    //match user's answers with random question ID.
    $question_on_screen = array_keys($user_answers[$current_screen]);
    $selected_q_id = current($question_on_screen);
    for ($i=1; $i<$q_no; $i++) {
      $selected_q_id = next($question_on_screen);
    }
  }
  
  if ($selected_q_id == '') {
    // Generate a random question ID from keywords.
    $question_ids = array();
    $question_data = $mysqli->prepare("SELECT DISTINCT q_id FROM keywords_question WHERE keywordID=?");
    $question_data->bind_param('i', $random_q_data['options'][0]['option_text']);
    $question_data->execute();
    $question_data->bind_result($q_id);
    while ($question_data->fetch()) {
      $question_ids[] = $q_id;
    }
    $question_data->close();
    shuffle($question_ids);
    
    $try = 0;
    $unique = false;
    while ($unique == false and $try < count($question_ids)) {
      $selected_q_id = $question_ids[$try];
      if (!isset($used_questions[$selected_q_id])) $unique = true;
      $try++;
    }
    $used_questions[$selected_q_id] = 1;
  }
  
  if ($unique) {
    // Look up selected question and overwrite data.
    $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, q_option_order FROM questions, options WHERE q_id=? AND questions.q_id=options.o_id ORDER BY id_num");
    $question_data->bind_param('i', $selected_q_id);
    $question_data->execute();
    $question_data->store_result();
    $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $q_option_order);
    while ($question_data->fetch()) {
      if (!isset($question['q_id']) or $question['q_id'] != $q_id) {
        $question['theme'] = $theme;
        $question['scenario'] = $scenario;
        $question['leadin'] = $leadin;
        $question['notes'] = $notes;
        $question['q_type'] = $q_type;
        $question['q_id'] = $q_id;
        $question['display_pos'] = $q_no;
        $question['score_method'] = $score_method;
        $question['display_method'] = $display_method;
        $question['q_media'] = $q_media;
        $question['q_media_width'] = $q_media_width;
        $question['q_media_height'] = $q_media_height;
        $question['q_option_order'] = $q_option_order;
        $question['dismiss'] = '';
      }
      $question['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
    }
    echo "\n<input type=\"hidden\" name=\"q" . $q_no . "_randomID\" value=\"" . $question['q_id'] ."\" />\n";
  } else {
    $question['leadin'] = '<span style="color: #f00;">' . $string['error_keywords'] . '</span>';
    $question['q_type'] = 'keyword_based';
    $question['q_id'] = -1;
    $question['display_pos'] = $q_no;
    $question['theme'] = $question['scenario'] = $question['notes'] = $question['score_method'] = $question['q_media'] = '';
    $question['q_media_width'] = $question['q_media_height'] = $question['q_option_order'] = $question['dismiss'] = '';
    $question['options'] = array();
  }
  $questions[] = $question;
}

if (isset($_POST['sessionid'])) require '../include/marking_functions.inc';

if ($special_needs == 1) {
  $stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid=?");
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
  $stmt->close();
}

// Get how many screens make up the question paper.
$screen_data = array();
$row_no = 0;
$stmt = $mysqli->prepare("SELECT property_id, labs, paper_title, paper_type, paper_prologue, marking, screen, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, bidirectional, calculator, moduleID, calendar_year, latex_needed, password, questions.q_type FROM (properties, papers, questions) WHERE properties.property_id=papers.paper AND crypt_name=? AND papers.question=questions.q_id ORDER BY screen");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($property_id, $labs, $paper_title, $paper_type, $paper_prologue, $marking, $screen, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $bidirectional, $calculator, $moduleID, $calendar_year, $latex_needed, $password, $q_type);
if ($stmt->num_rows == 0) {  // No record found, the paper can't exist
  access_denied($string['error_paper'], $output_header = false);
}
while ($stmt->fetch()) {
  $row_no++;
  $no_screens = $screen;
  $add_q = ($q_type == 'info') ? 0 : 1;
  if (!isset($screen_data[$no_screens])) { 
    $screen_data[$no_screens] = $add_q;
  } else {
    $screen_data[$no_screens] += $add_q;
  }
  if ($row_no == 1) {
    $original_paper_type = $paper_type;
    
    // If set overwrite the default colours with the current users' special settings
    if (!isset($bgcolor) or $bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
    if (!isset($fgcolor) or $fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
    if (!isset($textsize) or $textsize == 'NULL' or $textsize == '') $textsize = 90;
    if (!isset($marks_color) or $marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
    if (!isset($themecolor) or $themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
    if (!isset($labelcolor) or $labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
    if (!isset($font) or $font== 'NULL' or $font == '') $font = 'Arial';
    $attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
  
    if (stripos($userroles,'Student') !== false) {
	    // Check for additional password on the paper
      check_paper_password($password);
	  
      // Check time security
      check_datetime($start_date, $end_date);
	  
      //Check room security
      $low_bandwidth = check_labs($paper_type, $labs, $password, $mysqli);
      
      // get modules if the user is a student and the paper is not formative
      $attempt = check_modules($userID, $moduleID, $calendar_year, $mysqli);
      
      // Check for any metadata security restrictions
      check_metadata($property_id, $userID, $moduleID, $mysqli);
      
      if (time() > $end_date and ($paper_type == '1' or $paper_type == '2')) {
        $paper_type = '_late';
      }
    }
  }
}
$stmt->free_result();
$stmt->close();

// Get any Reference Material
$reference_materials = array();
$ref_no = 0;
$max_ref_width = 0;
$stmt = $mysqli->prepare("SELECT title, content, width FROM (reference_material, reference_papers) WHERE reference_material.id=reference_papers.refID AND paperID=?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$stmt->bind_result($reference_title, $reference_material, $reference_width);
while ($stmt->fetch()) {
  $reference_materials[$ref_no]['title'] = $reference_title;
  $reference_materials[$ref_no]['material'] = $reference_material;
  $reference_materials[$ref_no]['width'] = $reference_width;
  if ($reference_width > $max_ref_width) {
    $max_ref_width = $reference_width;
  }
  $ref_no++;
}
$stmt->close();

// Extract the posted variables.
$restart = 0;
if (isset($_POST['sessionid'])) {
  if ($_POST['button_pressed'] == 'next') {
    $current_screen = $_POST['current_screen'];
  } elseif ($_POST['button_pressed'] == 'prevous') {
    $current_screen = $_POST['current_screen'] - 2;
  } elseif ($_POST['button_pressed'] == 'jump_screen') {
    $current_screen = $_POST['jump_screen'];
  } elseif ($_POST['fire_alarm'] == 1) {
    $current_screen = $_POST['current_screen'];
  }
  $sessionid = $_POST['sessionid'];
} else {
  $current_screen = 1;
  if (($paper_type == '1' or $paper_type == '2' or $paper_type == '3') and !isset($_GET['mode'])) {  //Mode is used for staff preview.
    $stmt = $mysqli->prepare("SELECT DATE_FORMAT(MAX(started),\"%Y%m%d%H%i%s\") AS started, MAX(screen) AS screen FROM log$paper_type WHERE q_paper=? AND userID=? GROUP BY screen DESC LIMIT 1");
    $stmt->bind_param('ii', $property_id, $userID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($sessionid, $current_screen);
    if ($stmt->num_rows == 1) {
      $row = $stmt->fetch();
      $stmt->free_result();
      $restart = 1;
      if ($paper_type == '3') {
        $current_screen = 1;
      } elseif ($current_screen < $no_screens) {
        $current_screen++;
      }
    } else {
      $sessionid = date("YmdHis", time());
    }
    $stmt->close();
  } else {
    $sessionid = date("YmdHis", time());
  }
}

require '../config/start.inc';
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html>\n<head>\n";
if ($paper_type == '3') {
  echo "<title>" . $string['survey'] . "</title>\n";
} else {
  echo "<title>" . $string['assessment'] . "</title>\n";
}
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_page_charset ?>" />
<meta http-equiv="pragma" content="no-cache" />
<link rel="stylesheet" type="text/css" href="../css/start.css" />
<?php
$css = '';
if ($special_needs == 1 and $bgcolor != '#FFFFFF') {
  $css .= "select,input{background-color:$bgcolor;color:$fgcolor;font-family:$font,sans-serif}\n";
}
if (($bgcolor != '#FFFFFF' and $bgcolor != 'white') or ($fgcolor != '#000000' and $fgcolor != 'black') or $textsize != 90) {
  $css .= "body {background-color:$bgcolor;color:$fgcolor;font-size:$textsize%}\n";
}
if ($font != 'Arial') {
  if (strpos($font,' ') === false) {
    $css .= "body {font-family:$font,sans-serif}\n";
    $css .= "pre {font-family:$font,sans-serif}\n";
  } else {
    $css .= "body {font-family:'$font',sans-serif}\n";
    $css .= "pre {font-family:'$font',sans-serif}\n";
  }
}
if ($themecolor != '#316AC5') {
  $css .= ".theme {color:$themecolor}\n";
}
if ($marks_color != '#808080') {
  $css .= ".mk {color:$marks_color}\n";
}
if ($fgcolor != '#000000' and $fgcolor != 'black') {
  $css .= ".act {color:$fgcolor}\n";
}
if (count($reference_materials) > 0) {
  $css .= "#maincontent {position:fixed; right:" . ($max_ref_width + 1) . "px}\n";
  $css .= ".framecontent {width:" . ($max_ref_width - 12) . "px}\n";
  $css .= ".refhead {width:" . ($max_ref_width - 12) . "px;}\n";
}
if ($css != '') {
  echo "<style type=\"text/css\">\n$css\n</style>\n";
}
?>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<?php if ($latex_needed == 1) {?>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
<?php }?>
<script type="text/javascript" src="../js/start.js"></script>
<script type="text/javascript" src="../js/flash_include.js"></script>
<script type="text/javascript" src="../js/jquery.flash_q.js"></script>
<script language="javascript">
  window.history.go(1);
<?php
  if (count($reference_materials) > 0) {
    echo "\$(document).ready(function() {\n";
    if (isset($_POST['refpane'])) {
      echo "  changeRef(" . $_POST['refpane'] . ");\n";
    } else {
      echo "  resizeReference();\n";
    }
    echo "$(window).resize(resizeReference);";
    echo "});\n";
  }
?>    
  var lang = {
  <?php
  $langstrings = array('msgselectable1', 'msgselectable2', 'msgselectable3', 'msgselectable4');
  $first = true;
  foreach ($langstrings as $langstring) {
    if (!$first) {
      echo ',';
    }
    echo "'{$langstring}':'{$string[$langstring]}'";
    $first = false;
  }
  ?>
  };
  
  var getWinH = function() {
    var winH = 460;
    if (document.body && document.body.offsetWidth) {
      winH = document.body.offsetHeight;
    }
    if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth ) {
      winH = document.documentElement.offsetHeight;
    }
    if (window.innerWidth && window.innerHeight) {
      winH = window.innerHeight;
    }
    return winH;
  }
  
  var changeRef = function(refID) {
    document.getElementById('refpane').value = refID;
    winH = getWinH();
    resizeReference();
    var flag = 0;
    <?php
      if (count($reference_materials) > 0) {
        echo "    for (i=0; i<" . count($reference_materials) . "; i++) {\n";
        echo "      if (i == refID) {\n";
        echo "        document.getElementById('framecontent' + i).style.display = 'block';\n";
        echo "        document.getElementById('refhead' + i).style.top = (31 * i) + 'px';\n";
        echo "        flag = 1;\n";
        echo "      } else {\n";
        echo "        document.getElementById('framecontent' + i).style.display = 'none';\n";
        echo "        if (flag == 0) {\n";
        echo "          document.getElementById('refhead' + i).style.top = (31 * i) + 'px';\n";
        echo "        } else {\n";
        echo "          document.getElementById('refhead' + i).style.top = (winH - (" . count($reference_materials) . " - i) * 31) + 'px';\n";
        echo "        }\n";
        echo "      }\n";
        echo "    }\n";
      }
    ?>  
  }
  
  var resizeReference = function() {
    winH = getWinH();
<?php
  if (count($reference_materials) > 0) {
    $subtract = (31 * count($reference_materials)) + 11;
    echo "    for (i=0; i<" . count($reference_materials) . "; i++) {\n";
    echo "      document.getElementById('framecontent' + i).style.height = (winH - $subtract) + 'px';\n";
    echo "    }\n";
?>
    var mainWidth = $('body').outerWidth() - $('#framecontent0').outerWidth(true);
    $('#maincontent').width(mainWidth);
<?php
  }
?>  
  }

<?php
  if ($bidirectional == '0') {
?>
  var submitted = false;
  var confirmSubmit = function() {
    if (submitted == true) {
      return false;
    }
    var agree = confirm("<?php echo $string['javacheck1']; ?>");
    if (agree) {
      document.body.style.cursor = 'wait';
      submitted = true;
      return true;
    } else {
      return false;
    }
  }
<?php
  } else {
?>
  var submitted = false;
  var confirmSubmit = function() {
	  if (submitted == true) {
      return false;
    }
    if (document.questions.button_pressed.value == 'finish') {
      var agree = confirm("<?php echo $string['javacheck2']; ?>");
      if (agree) {
        document.body.style.cursor = 'wait';
        submitted = true;
        return true;
      } else {
        $('#savemsg').html("");
        document.body.style.cursor = 'default';
        return false;
      }
    } else {
      document.body.style.cursor = 'wait';
      submitted = true;
      return true;
    }
  }
  var jumpScreen = function () {
      document.questions.button_pressed.value='jump_screen';
      $('#qForm').attr('action',"start.php?id=<?php echo $_GET['id']; ?>&dont_record=true");
      return userSubmit(null);
  }
<?php
  }
?>

  //Bind save function to the screen for fault tolerant form saving
  var submitPending = false;
  var success = false;
  var usingAjax = false;
  var submitType = '';
  var autoSaveRef = '';
  $(document).ready(function () {
      //we have javascript replace the form submit buttons to enable ajax saving 
      usingAjax = true;
      $('#next').replaceWith('<?php echo "<input id=\"next\" type=\"button\" value=\"" . $string['screen'] . " " . ($current_screen + 1) . " &gt;\" />&nbsp;";?>');
      $('#next').click(userSubmit);

      $('#prevous').replaceWith('<?php echo "<input id=\"prevous\" type=\"button\" value=\"&nbsp;&lt; " . $string['screen'] . " " . ($current_screen - 1) . "&nbsp;\" />&nbsp;";?>');
      $('#prevous').click(userSubmit);

      $('#finish').replaceWith('<?php echo "<input id=\"finish\" type=\"button\" value=\"" . $string['finish'] . "\" />&nbsp;";?>');
      $('#finish').click(userSubmit);

      //attach ui events
      $('.rankselect').change(rankCheck);
      $(".calc-answer").keydown(filterKeypress);

      //setup autosave
      startAutoSave();
  });
  
  var userSubmit = function (event) {
    submitType = 'userSubmit';
    stopAutoSave();

    $('#saveError').fadeOut('slow');
    $('#savemsg').html("<img src=\"../artwork/busy.gif\" width=\"20\" height=\"20\" alt=\"Wait\" />")
    document.body.style.cursor = 'wait';

    //log which method the users submited the page via
    if (!!event) {
    $('#button_pressed').attr('value',event.target.id);
      if(event.target.id != 'finish') {
        $('#qForm').attr('action',"start.php?id=<?php echo $_GET['id']; ?>&dont_record=true");
      }
    }
    ajaxSave();
  }
  
  var startAutoSave = function () {
    autoSaveRef = setTimeout("autoSave()",<?php echo ($cfg_autosave_timeout * 1000); ?>);
  }

  var stopAutoSave = function() {
    clearTimeout(autoSaveRef);
  }

  var autoSave = function() {
    submitType = 'autoSave';
    $('#savemsg').html("<?php echo $string['auto_saving']; ?>")
    ajaxSave();
    //clear auto save message
    setTimeout("$('#savemsg').html(\"\")",5000);
    //reset the timer incase this is a long screen
    startAutoSave();
  }

  var ajaxSave = function () {
    submitPending = true;
    //hide any errors
    $('#saveError').fadeOut('fast');
    //random page ID to stop IE caching results. arrrggg
    date = new Date();
    randomPageID = date.getTime();
    $('#randomPageID').val(randomPageID);
    if(typeof(tinyMCE) != "undefined"){
      tinyMCE.triggerSave();
    }
    $.ajax({
          url: 'save_screen.php?id=<?php echo $_GET['id'] ?>&rnd=' + randomPageID,
          type: 'post',
          data: $('#qForm').serialize(),
          dataType: 'html',
          timeout: 1000,
          cache: false,
          tryCount : 0,    
          retryLimit : 5, //try 5 times b4 error
          beforeSend: function() {
              submitPending = true;
              success = false;
          },
          fail: function() {
              submitPending = false;
              success = false;
              saveFail();
          },
          error: function(xhr, textStatus, errorThrown) {
              if (textStatus == 'timeout' || textStatus == 'error') {            
                this.tryCount++;            
                if (this.tryCount <= this.retryLimit) {                
                  //try again
                  $.ajax(this);                
                  return;            
                }            
              }
              saveFail();
              submitPending = false;
              success = false;
              return;
          },
          success: function (data, jqXHR, textStatus) {
              submitPending = false
              if(data == randomPageID) {
                  success = true;
                  saveSuccess();
                  return;
              }
              saveFail();
              return;
          }
      });
    submitPending = false;
    return;
  }

  var saveSuccess = function () {
    if (submitType == 'userSubmit') {
      $('#qForm').submit();
      return true;
    }
  }

  var saveFail = function () {
    startAutoSave();
    $('#saveError').fadeIn('fast');
    $('#savemsg').html("");
    document.body.style.cursor = 'default';
    return false;
  }

  var fire = function (scrno) {
    submitType = 'userSubmit';
    document.questions.button_pressed.value='fire_exit';
    if (usingAjax) {
        document.questions.action="fire_evacuation.php?id=<?php echo $_GET['id']; ?>&dont_record=true";
    } else {
        document.questions.action="fire_evacuation.php?id=<?php echo $_GET['id']; ?>";
    }
    ajaxSave();
  }
</script>
</head>
<?php
if (stripos($userroles,'Student') !== false) {
  echo '<body oncontextmenu="return false;" onload="StartClock();" onunload="KillClock()">';
} else {
  echo '<body onload="StartClock();" onunload="KillClock()">';
}
$show_ref_material = false;
echo "<div id=\"maincontent\">\n";

if ($current_screen < $no_screens) {
  echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"" . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "\"";
} else {
  echo "<form method=\"post\" id=\"qForm\" name=\"questions\" action=\"finish.php?id=" . $_GET['id'] . "\"";
}
echo ' onsubmit="return confirmSubmit()">';   // Warning message only in linear navigation mode.
?>
  <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
  <tr><td valign="top">
<?php
  if ((isset($_POST['old_screen']) and $_POST['old_screen'] != '') and (!isset($_GET['dont_record']) or $_GET['dont_record'] != true)) {
    record_marks($property_id, $mysqli, $userID, $paper_type, $grade, $year, $attempt, $userroles);
  }

  echo $top_table_html;
  echo '<tr><td><div class="paper">' . $paper_title . '</div>';
  $question_offset = 0;
  if ($no_screens > 1) {
    echo '<table cellspacing="1" cellpadding="1" border="0" class="screens"><tr>';
    for ($i=1; $i<=$no_screens; $i++) {
      echo "<td title=\"";
      if (isset($screen_data[$i])) {
        echo $screen_data[$i];
      } else {
        echo '0';
      }
      if ($i == $current_screen) {
        if (isset($screen_data[$i]) and $screen_data[$i] == 1) {
          echo " question\" class=\"s1\">";
        } else {
          echo " questions\" class=\"s1\">";
        }
      } else {
        if (isset($screen_data[$i]) and $screen_data[$i] == 1) {
          echo " question\" class=\"s0\">";
        } else {
          echo " questions\" class=\"s0\">";
        }
        if ($i < $current_screen and isset($screen_data[$i])) $question_offset += $screen_data[$i];
      }
      echo "$i</td>\n";
    }
    echo '</tr></table>';
  }
  echo '</td>';
  echo $logo_html;
  
  $user_answers = array();
  $previous_duration = 0;
  $screen_pre_submitted = 0;
  if (isset($_POST['sessionid']) or (isset($_POST['fire_alarm']) and $_POST['fire_alarm'] == '1') or $restart == 1) {    // Get users previous answers for the current screen.
    $log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log$paper_type WHERE userID=? AND started=? and q_paper=? ORDER BY id");
    $log_data->bind_param('isi', $userID, $sessionid, $property_id);
    $log_data->execute();
    $log_data->store_result();
    $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
    if ($log_data->num_rows > 0) {
      while ($log_data->fetch()) {
        $user_answers[$log_screen][$log_q_id] = $log_user_answer;
        $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
        $user_order[$log_screen][$log_q_id] = $option_order;
        $used_questions[$log_q_id] = $log_q_id;
        if ($log_screen == $current_screen) {
          $previous_duration = $log_duration;
          $screen_pre_submitted = 1;
        }
      }
      $log_data->close();
    } else {
      $log_data->close();
      if ($paper_type == '_late') {
        $log_data = $mysqli->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log$original_paper_type WHERE userID=? AND started=? and q_paper=?");
        $log_data->bind_param('isi', $userID, $sessionid, $property_id);
        $log_data->execute();
        $log_data->store_result();
        $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
        $user_answers = array();
        $used_questions[$log_q_id] = $log_q_id;
        while ($log_data->fetch()) {
          $user_answers[$log_screen][$log_q_id] = $log_user_answer;
          $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
          $user_order[$log_screen][$log_q_id] = $option_order;
          if ($log_screen == $current_screen) $previous_duration = $log_duration;
        }
        $log_data->close();
      }
    }
  }

  $old_leadin = '';
  $old_q_type = '';
  $old_q_id = 0;
  $question_no = 0;
  $q_displayed = 0;
  $marks = 0;
  $old_theme = '';
  $previous_q_type = '';
  
  $question_data = $mysqli->prepare("SELECT q_type, q_id, score_method, display_method, marks_correct, marks_incorrect, marks_partial, theme, scenario, leadin, correct, REPLACE(option_text,'\t','') AS option_text, q_media, q_media_width, q_media_height, o_media, o_media_width, o_media_height, notes, display_pos, q_option_order FROM papers, questions, options WHERE paper=? AND screen=? AND papers.question=questions.q_id AND questions.q_id=options.o_id ORDER BY display_pos, id_num");
  $question_data->bind_param('ii', $property_id, $current_screen);
  $question_data->execute();
  $question_data->store_result();
  $question_data->bind_result($q_type, $q_id, $score_method, $display_method, $marks_correct, $marks_incorrect, $marks_partial, $theme, $scenario, $leadin, $correct, $option_text, $q_media, $q_media_width, $q_media_height, $o_media, $o_media_width, $o_media_height, $notes, $display_pos, $q_option_order);
  $num_rows = $question_data->num_rows;
  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\" style=\"table-layout:fixed\">\n";
  echo "<col width=\"40\"><col>\n";
  $q_no = 0;
  //build the questions_array
  $tmp_questions_array = array();
  while ($question_data->fetch()) {
    if ($q_no == 0 or $tmp_questions_array[$q_no]['q_id'] != $q_id or $tmp_questions_array[$q_no]['display_pos'] != $display_pos) {
      $q_no++;
      $tmp_questions_array[$q_no]['theme'] = trim($theme);
      $tmp_questions_array[$q_no]['scenario'] = trim($scenario);
      $tmp_questions_array[$q_no]['leadin'] = trim($leadin);
      $tmp_questions_array[$q_no]['notes'] = trim($notes);
      $tmp_questions_array[$q_no]['q_type'] = $q_type;
      $tmp_questions_array[$q_no]['q_id'] = $q_id;
      $tmp_questions_array[$q_no]['display_pos'] = $display_pos;
      $tmp_questions_array[$q_no]['score_method'] = $score_method;
      $tmp_questions_array[$q_no]['display_method'] = $display_method;
      $tmp_questions_array[$q_no]['q_media'] = $q_media;
      $tmp_questions_array[$q_no]['q_media_width'] = $q_media_width;
      $tmp_questions_array[$q_no]['q_media_height'] = $q_media_height;
      $tmp_questions_array[$q_no]['q_option_order'] = $q_option_order;
      $tmp_questions_array[$q_no]['dismiss'] = '';
      $used_questions[$q_id] = 1;
    }
    $tmp_questions_array[$q_no]['options'][] = array('correct'=>$correct, 'option_text'=>$option_text, 'o_media'=>$o_media, 'o_media_width'=>$o_media_width, 'o_media_height'=>$o_media_height, 'marks_correct'=>$marks_correct, 'marks_incorrect'=>$marks_incorrect, 'marks_partial'=>$marks_partial);
  } 
  $question_data->close();
  
  //look for braching and random questions and overwrite as needed
  $questions_array = array();
  $tmp_q_no = 0;
  foreach ($tmp_questions_array as &$question) {
    if ($question['q_type'] != 'info') {
      $tmp_q_no++;
    }
    if ($question['q_type'] == 'random') {
      randomQOverwrite($questions_array, $question, $paper_type, $user_answers, $current_screen, $tmp_q_no);
    } elseif ($question['q_type'] == 'keyword_based') {
      keywordQOverwrite($questions_array, $question, $paper_type, $user_answers, $current_screen, $tmp_q_no);
    } else {
      $questions_array[] = $question;
    }
  }
  unset($tmp_questions_array);
  
  $unanswered = false;
  
  //display the questions
  foreach($questions_array as &$question) {
    if ($screen_pre_submitted == 1 and $q_displayed == 0) echo "<tr style=\"display:none\" id=\"unansweredkey\"><td colspan=\"2\"><span style=\"background-color:#FFC0C0\">&nbsp;&nbsp;&nbsp;&nbsp;</span> " . $string['unansweredquestion'] . "</td></tr>\n";
    if ($q_displayed == 0 and $current_screen == 1 and $paper_prologue != '') echo '<tr><td colspan="2" style="padding:20px; text-align:justify">' . $paper_prologue . '</td></tr>';
    if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    display_question($question, $paper_type, $current_screen, $previous_q_type, $question_no, $question_offset, $user_answers, $unanswered);	
    $previous_q_type = $question['q_type'];
    $q_displayed++;
  }
  
  echo "</table></td></tr>\n<tr><td valign=\"bottom\">\n<br />\n";

  $current_screen++;
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"$current_screen\" />\n";
  echo "<input type=\"hidden\" name=\"sessionid\" value=\"$sessionid\" />\n";
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($current_screen - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"$previous_duration\" />\n";
  echo "<input type=\"hidden\" id=\"button_pressed\" name=\"button_pressed\" value=\"\" />\n";
  echo "<input type=\"hidden\" id=\"randomPageID\" name=\"randomPageID\" value=\"\" />\n";
  if ($current_screen > $no_screens) {
    echo "<br />\n<div class=\"note\" style=\"text-align:center;font-size:90%\">";
    if (isset($low_bandwidth) and $low_bandwidth == 0) echo '<img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;';
    echo $string['finishnote'];
    if ($bidirectional == 1) echo "<br />" . $string['gobackpink'];
    echo "</div>\n<br >\n";
  } elseif ($bidirectional == 0) {
    echo "<br />\n<div class=\"note\" style=\"text-align:center;font-size:90%\">";
    if (isset($low_bandwidth) and $low_bandwidth == 0) echo '<img src="../artwork/notes_icon.gif" width="14" height="14" alt="' . $string['note'] . '" />&nbsp;';
    printf($string['pleasecomplete'], $current_screen);
    echo "</div>\n<br >\n";
  }
 
  echo '<div id="saveError"><img alt="Warning" src="/artwork/no_save.png" /> <div><strong>' .  $string['savefailed'] . '</strong><br />' . $string['tryagain'] . '</div></div>';
  
  echo $bottom_html;
  echo '<input type="text" style="background-color:transparent;text-align:center;font-size:80%;color:white;border:0px" id="theTime" size="8" /></td><td align="right">';
  echo '<span id="savemsg"></span>';
  if ($bidirectional == 1 and $no_screens > 1) {
    if ($current_screen > 2) echo "<input input id=\"prevous\" type=\"submit\" name=\"prev\" onclick=\"document.questions.button_pressed.value='previous';\" value=\"&nbsp;&lt; " . $string['screen'] . " " . ($current_screen - 2) . "&nbsp;\" />&nbsp;";
    if ($original_paper_type == '0' or $original_paper_type == '1' or $original_paper_type == '2') {
      echo "<select name=\"jump_screen\" onchange=\"jumpScreen()\">";
      for ($i=1; $i<=$no_screens; $i++) {
        if ($i == ($current_screen - 1)) {
          echo "<option value=\"$i\" selected>$i</option>";
        } else {
          echo "<option value=\"$i\">$i</option>";
        }
      }
      echo "</select>&nbsp;";
    }
  }
  echo "<input type=\"hidden\" name=\"refpane\" id=\"refpane\" value=\"0\" />\n";
  if ($current_screen > $no_screens) {
    echo "<input id=\"finish\" type=\"submit\" name=\"next\" onclick=\"document.questions.button_pressed.value='finish';\" value=\"" . $string['finish'] . "\" />&nbsp;\n";
  } else {
    echo "<input id=\"next\" type=\"submit\" name=\"next\" value=\"" . $string['screen'] . " $current_screen &gt;\" />&nbsp;\n";
  }
  echo '</td></tr></table>';
?>
</td></tr></table>
</form>
</div>
<?php

if (count($reference_materials) > 0) {
  $top = 0;
  $ref_no = 0;
  foreach ($reference_materials as $reference_material) {
    echo "<div class=\"refhead\" id=\"refhead" . $ref_no . "\" onclick=\"changeRef(" . $ref_no . ")\" style=\"top:{$top}px\">" . $reference_material['title'] . "</div>\n";
    echo "<div class=\"framecontent\" id=\"framecontent" . $ref_no . "\" style=\"top:" . (31 + $top) . "px\">\n" . $reference_material['material'] . "</div>\n";
    $top+=31;
    $ref_no++;
  }
}
$mysqli->close();

if (isset($_POST['refpane'])) {
  echo "<script language=\"JavaScript\">\n";
  echo "  changeRef(" . $_POST['refpane'] . ");\n";
  echo "</script>\n";
}

if ($unanswered) {
  echo "<script language=\"JavaScript\">\n";
  echo "  document.getElementById('unansweredkey').style.display = '';\n";
  echo "</script>\n";
}
?>

</body>
</html>








