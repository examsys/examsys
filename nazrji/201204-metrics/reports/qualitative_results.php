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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Qualitative Analysis<?php echo " $cfg_install_type"; ?></title>
  <style type="text/css">
  body {font-family:Arial,sans-serif; font-size:90%; color:black; margin-top:0px; margin-left:0px; margin-right:0px}
  h1 {margin-left:15px; font-size:150%; color:#3A70A4}
  li {margin-right:10px}
  .heading {background-color:#EBEADB; border-left: solid white 1px; border-right: solid #D8D2BD 1px; border-top: solid white 1px; border-bottom: solid #D8D2BD 1px; color:black}
  .comments {margin-left:10px; color:#808080}
  .scr_no {margin-left:25px}
  .screenbrk {
    color:#15428B;
    font-weight:bold;
    font-size:90%;
    height:70px;
    width:100%;
    border-top: 1px solid #B5C4DF;
    background: -moz-linear-gradient(top, #E4EEFC, #FFFFFF);
    background: -webkit-linear-gradient(top, #E4EEFC, #FFFFFF);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#E4EEFC', endColorstr='#FFFFFF');
  }
  </style>
</head>

<body>
<?php
  $result = $mysqli->prepare("SELECT question FROM papers, questions WHERE papers.question=questions.q_id AND q_type!='info' AND paper=? ORDER BY screen, display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($question);
  while ($row = $result->fetch()) {
    $paper_structure[] = $question;
  }
  $result->close();

  $occurrence_comments = 0;
  $occurrence_words = 0;
  $old_leadin = '';
  $old_theme = '';
  $old_screen = 1;
  $old_q_id = 0;
  $comment_flag = 1;
  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];
  $list_on = 0;
  $q_no = 0;

  $result = $mysqli->prepare("SELECT DISTINCT log3.screen, theme, log_metadata.started, users.username AS username, users.surname AS surname, log3.q_id AS q_id, leadin, user_answer FROM (log3, log_metadata, papers, questions, users) WHERE log3.userID=log_metadata.userID AND log3.q_paper=log_metadata.paperID AND log3.started=log_metadata.started AND users.id=log3.userID AND papers.question=log3.q_id AND papers.screen=log3.screen AND paper=? AND q_paper=? AND log_metadata.student_grade LIKE ? AND log_metadata.year LIKE ? AND log3.q_id=questions.q_id AND q_type='textbox' AND log3.started>=? AND log3.started<=? AND (roles='Student' OR roles='graduate') ORDER BY log3.screen, display_pos");
  $result->bind_param('iissss', $_GET['paperID'], $_GET['paperID'], $_GET['repcourse'], $_GET['repyear'], $startdate, $enddate);
  $result->execute();
  $result->bind_result($screen, $theme, $started, $tmp_username, $surname, $q_id, $leadin, $user_answer);
  
  while ($row = $result->fetch()) {
    if ($theme != '') $old_theme = $theme;
    if ($old_q_id != $q_id or $old_screen < $screen) {
      if ($comment_flag == 0) echo "<div class=\"comments\">&lt;No Comments&gt;</div>\n";
      if ($old_q_id != 0) {
        if ($list_on == 1) echo "</ul>\n";
        $list_on = 0;
        if (isset($_GET['keywords'])) {
          echo "<div class=\"comments\">$occurrence_words - occurrences of <strong>" . $_GET['keywords'] . "</strong> in $occurrence_comments comments.</div>\n";
        } else {
          echo "<div class=\"comments\">$occurrence_comments comments.</div>\n";
        }
      }
      $comment_flag = 0;
      if ($old_screen < $screen) {
        if ($list_on == 1) echo "</ul>\n";
        $list_on = 0;
        echo '<br /><div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div>';
      }

      if ($old_theme != '') {
        echo "<h1>$old_theme</h1>\n";
      }
      do {
        $q_no++;
      } while ($q_id != $paper_structure[$q_no-1] and $q_no < 9999);
      if ($list_on == 1) echo "</ul>\n";
      echo "<p style=\"font-weight:bold; margin-left:10px; margin-right:10px\">$q_no. $leadin</p>\n<ul>\n";
      $occurrence_words = 0;
      $occurrence_comments = 0;
      $list_on = 1;
    }
    $response = trim(strtolower($user_answer));
    $match = false;
    if ($response != NULL and $response != 'n/a' and strlen($response) > 1) {
      // Count keywords
      if (isset($_GET['keywords'])) {
        $content = $_GET['keywords'];
      } else {
        $content = '';
      }
      if (isset($_GET['keywords'])) {
        if (substr_count($content,'and') > 0 and $content != 'and') {
          $keywords = explode('and',$content);
          $match = true;
          $tmp_occurrence_comments = $occurrence_comments;
          $tmp_occurrence_words = $occurrence_words;
          foreach ($keywords as $individual_keyword) {
            $individual_keyword = trim($individual_keyword);
            if ($_GET['casesensitive'] == '1') {
              $tmp_occur = substr_count($response, $individual_keyword);
            } else {
              $tmp_occur = substr_count(strtolower($response), strtolower($individual_keyword));
            }
            if ($tmp_occur == 0) {
              $match = false;
            }
            $occurrence_words += $tmp_occur;
          }
          if ($match == true) {
            $occurrence_comments++;
          } else {
            $occurrence_comments = $tmp_occurrence_comments;
            $occurrence_words = $tmp_occurrence_words;
          }
        } elseif (substr_count($content,'or') > 0 and $content != 'or') {
          $keywords = explode('or',$content);
          foreach ($keywords as $individual_keyword) {
            $individual_keyword = trim($individual_keyword);
            if ($_GET['casesensitive'] == '1') {
              $tmp_occur = substr_count($response, $individual_keyword);
            } else {
              $tmp_occur = substr_count(strtolower($response), strtolower($individual_keyword));
            }
            if ($tmp_occur > 0) {
              $occurrence_comments++;
              $match = true;
            }
            $occurrence_words += $tmp_occur;
          }
        } else {
          $keywords = array(trim($content));
          $individual_keyword = trim($content);
          if (isset($_GET['casesensitive']) and $_GET['casesensitive'] == '1') {
            $tmp_occur = substr_count($response, $individual_keyword);
          } else {
            $tmp_occur = substr_count(strtolower($response), strtolower($individual_keyword));
          }
          if ($tmp_occur > 0) {
            $occurrence_comments++;
            $match = true;
          }
          $occurrence_words += $tmp_occur;
        }
      } else {
        $occurrence_comments++;
        $tmp_occur = 0;
      }
      // Highlight keywords
      $display_string = $user_answer;
      if ($match == true) {
        foreach ($keywords as $individual_keyword) {
          $individual_keyword = trim($individual_keyword);
          if (isset($_GET['collapse']) and $_GET['collapse'] == '1') {
            if (isset($_GET['casesensitive']) and $_GET['casesensitive'] == '1') {
              $display_string = preg_replace("/($individual_keyword)/","<span style=\"background-color:yellow\">\\1</span>",$display_string);
            } else {
              $display_string = preg_replace("/($individual_keyword)/i","<span style=\"background-color:yellow\">\\1</span>",$display_string);
            }
          } else {
            if (isset($_GET['casesensitive']) and $_GET['casesensitive'] == '1') {
              $display_string = preg_replace("/($individual_keyword)/","<span style=\"background-color:yellow\">\\1</span>",$display_string);
            } else {
              $display_string = preg_replace("/($individual_keyword)/i","<span style=\"background-color:yellow\">\\1</span>",$display_string);
            }
          }
        }
      }
      if ((isset($_GET['collapse']) and $_GET['collapse'] == '1' and $match == true) or !isset($_GET['collapse'])) {
        echo "<li>$display_string</li>\n";
      }
      $comment_flag = 1;
    }
    $old_leadin = $leadin;
    $old_screen = $screen;
    $old_q_id = $q_id;
  }
  $result->close();
  echo "</ul>\n";

  if ($comment_flag == 0) {
    echo "<div class=\"comments\">&lt;No Comments&gt;</div>\n";
  } else {
    if (isset($_GET['keywords'])) {
      echo "<div class=\"comments\">$occurrence_words - occurrences of <strong>" . $_GET['keywords'] . "</strong> in $occurrence_comments comments.</div>\n";
    } else {
      echo "<div class=\"comments\">$occurrence_comments comments.</div>\n";
    }
  }
  $mysqli->close();
?>
</body>
</html>