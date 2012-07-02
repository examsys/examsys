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
* Completes final log of the last screen to the 'logX' table and then will display feedback if the paper is in 'formative' 
* mode or will display a confirmation notice to the examinee stating all answers and marks have been successfully recorded.
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_student_auth.inc';
  require '../include/calculate_marks.inc';
  require '../include/errors.inc';
  require '../include/mapping.inc';
  require '../include/finish_functions.inc';
  require '../include/paper_security.inc';
  
  check_var('id', 'GET', true, false);

  getSpecialSettings($userID, $mysqli);
    
  if ($paper_properties = $mysqli->prepare("SELECT property_id, labs, moduleID, calendar_year, display_correct_answer, display_question_mark, display_students_response, display_feedback, hide_if_unanswered, paper_title, paper_type, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), bgcolor, fgcolor, themecolor, labelcolor, marking, paper_postscript, pass_mark, latex_needed, password FROM properties WHERE crypt_name=?")) {
    $paper_properties->bind_param('s', $_GET['id']);
    $paper_properties->execute();
    $paper_properties->store_result();
    $paper_properties->bind_result($paperID, $labs, $moduleID, $calendar_year, $display_correct_answer, $display_question_mark, $display_students_response, $display_feedback, $hide_if_unanswered, $paper_title, $paper_type, $start_date, $end_date, $paper_bgcolor, $paper_fgcolor, $paper_themecolor, $paper_labelcolor, $marking, $paper_postscript, $pass_mark, $latex_needed, $password);
    while ($paper_properties->fetch()) {
      // If set overwrite the default colours with the current users' special settings
      if (!isset($bgcolor) or $bgcolor == 'NULL' or $bgcolor == '') $bgcolor = $paper_bgcolor;
      if (!isset($fgcolor) or $fgcolor == 'NULL' or $fgcolor == '') $fgcolor = $paper_fgcolor;
      if (!isset($textsize) or $textsize == 'NULL' or $textsize == '') $textsize = 90;
      if (!isset($marks_color) or $marks_color == 'NULL' or $marks_color == '') $marks_color = '#808080';
      if (!isset($themecolor) or $themecolor == 'NULL' or $themecolor == '') $themecolor = $paper_themecolor;
      if (!isset($labelcolor) or $labelcolor == 'NULL' or $labelcolor == '') $labelcolor = $paper_labelcolor;
      if (!isset($font) or $font== 'NULL' or $font == '') $font = 'Arial';
      
      $log_type = $paper_type;
      $low_bandwidth = 0;
      $sessionid = '';
      
      if ($userroles == 'Student') {
        // Check for additional password on the paper
        check_paper_password($password);
      
        $display_correct_answer = 1;
        $display_question_mark = 1;
        $display_students_response = 1;
        $display_feedback = 1;
 
        // Check if paper can be released date wise
        $stmt = $mysqli->prepare("SELECT UNIX_TIMESTAMP(date) FROM feedback_release WHERE paper_id=? AND type='questions'");
        $stmt->bind_param('i', $paperID);
        $stmt->execute();
        $stmt->bind_result($access_date);
        $stmt->store_result();
        $stmt->fetch();
        if ($stmt->num_rows == 0) {
          access_denied($string['nofeedback'], false);
        }
        $stmt->close();
        
        // Check to see if the student has sat the paper
        $stmt = $mysqli->prepare("SELECT started FROM log$paper_type WHERE q_paper=? AND userID=?");
        $stmt->bind_param('ii', $paperID, $userID);
        $stmt->execute();
        $stmt->bind_result($sessionid);
        $stmt->store_result();
        $stmt->fetch();
        if ($stmt->num_rows == 0) {
          access_denied($string['nottaken'], false);
        }
        $stmt->close();
        
      }
      if (isset($_GET['type'])) $log_type = $_GET['type'];
    }
    $paper_properties->close();
  } else {
    display_error("Properties Query Error", $mysqli->error);
  }
  require '../config/finish.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['examscript'] . ' ' . $cfg_install_type; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="imagetoolbar" content="false">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_page_charset ?>" />
<link rel="stylesheet" type="text/css" href="../css/start.css" />
<link rel="stylesheet" type="text/css" href="../css/finish.css" />
<?php
  $css = '';
  if ($special_needs == 1 and $bgcolor != '#FFFFFF') {
    $css .= "select,input{background-color:$bgcolor;color:$fgcolor;font-family:$font,sans-serif}\n";
  }
  if (($bgcolor != '#FFFFFF' and $bgcolor != 'white') or ($fgcolor != '#000000' and $fgcolor != 'black') or $textsize != 90) {
    $css .= "body {background-color:$bgcolor;color:$fgcolor;font-size:$textsize%}\n";
    $css .= ".staffview {\nbackground: -moz-linear-gradient(top, #FF8282, $bgcolor);\nbackground: -webkit-linear-gradient(top, #FF8282, $bgcolor);\nfilter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FF8282', endColorstr='$bgcolor');\n}\n";
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
    $css .= ".objH {color:$themecolor}\n";
  }
  if ($labelcolor != '#316AC5') {
    $css .= ".fback {color:$labelcolor}\n";
    $css .= ".label {color:$labelcolor}\n";
  }
  if ($css != '') {
    echo "<style type=\"text/css\">\n$css\n</style>\n";
  }
  
  if ($latex_needed == 1) {
    echo "<script type=\"text/javascript\" src=\"../js/jquery-1.6.1.min.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"../tools/mee/mee/js/mee_src.js\"></script>";
  }
?>
<script type="text/javascript" src="../js/ie_fix.js"></script>
<script type="text/javascript" src="../js/ie_fix.js"></script>
<script type="text/javascript" src="../js/flash_include.js"></script>
<script language="JavaScript">
  window.history.go(1);

  function launchHelp(pageID) {
    helpwin=window.open("/help/student/index.php?id=" + pageID + "","help","width="+(screen.width-30)+",height="+(screen.height-100)+",scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
    helpwin.moveTo(10,10);
    if (window.focus) {
      helpwin.focus();
    }
  }
</script>
</head>
<body>
<?php
  $current_screen = 1;

  if (isset($_GET['userid'])) {
    $temp_userID = $_GET['userid'];
  } else {
    $temp_userID = $userID;
  }
  $old_q_id = 0;
  $old_screen = 0;
  
  echo $top_table_html;
  echo '<tr><td><div class="paper">' . $paper_title . '</div></td>';
  echo $logo_html;
  echo '</table>';

  display_feedback($sessionid, $temp_userID, $paperID, $paper_type, $log_type, $paper_title, $paper_postscript, $marking, $userroles, $mysqli);

  echo "</body>\n</html>";
  $mysqli->close();
?>