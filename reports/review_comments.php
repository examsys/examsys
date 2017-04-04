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
* View internal and external reviewers comments
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/media.inc';
require_once '../include/errors.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

$type = check_var('type', 'GET', true, false, true);
$paperID = check_var('paperID', 'GET', true, false, true);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];

function list_externals($reviewer_data, $string) {
  $html = '<table class="reviewer_list" cellspacing="0" cellpadding="4">';
  $html .= '<thead><tr><th>' . $string['reviewers'] . '</th><th>' . $string['started'] . '</th><th>' . $string['completed'] . '</th><th style="width:60%">' . $string['generalpapercomments'] . '</th></tr></thead>';
  foreach ($reviewer_data as $reviewer) {
    if ($reviewer['started'] == '') $reviewer['started'] = '<span style="color:#C0C0C0">' . $string['na'] . '</span>';
    if ($reviewer['complete'] == '') $reviewer['complete'] = '<span style="color:#C0C0C0">' . $string['na'] . '</span>';
    
    $html .= '<tr>';
    $html .= '<td>' . $reviewer['title'] . ' ' . $reviewer['initials'] . ' ' . $reviewer['surname'] . '</td>';
    $html .= '<td>' . $reviewer['started'] . '</td>';
    $html .= '<td>' . $reviewer['complete'] . '</td>';
    $html .= '<td>' . $reviewer['paper_comment'] . '</td>';
    $html .= '</tr>';
  }
  $html .= '</table>';
  
  return $html;
}

function displayRank($rank_position, $string) {
  if ($rank_position == 1) {
    $html = '1st';
  } elseif ($rank_position == 2) {
    $html = '2nd';
  } elseif ($rank_position == 3) {
    $html = '3rd';
  } elseif ($rank_position == 9990) {
    $html = $string['na'];
  } else {
    $html = $rank_position . 'th';
  }
  return $html;
}

function displayComments($questionID, $comments_data, $qtype, $qno, $reviewer_data, $type, $string, $language) {

  $html = "<tr><td></td><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:98%\">\n";
  $html .= "<tr><td colspan=\"5\"><strong>" . $string[$type . 'comments'] . "$qno</strong>&nbsp;<img onclick=\"editQ($questionID, $qno)\" class=\"pencil\" src=\"../artwork/pencil_16.png\" alt=\"" . $string['editquestion'] . "\" /></td></tr>\n";
  $html .= "<tr><td style=\"width:20px\"><div class=\"reviewbar\">&nbsp;</div></td><td style=\"width:20%\"><div class=\"reviewbar\">" . $string['reviewer'] . "</div></td><td style=\"width:35%\"><div class=\"reviewbar\">" . $string['comment'] . "</div></td><td style=\"width:10%\"><div class=\"reviewbar\">" . $string['action'] . "</div></td><td style=\"width:35%\"><div class=\"reviewbar\">" . $string['response'] . "</div></td></tr>\n";
  
  foreach ($reviewer_data as $reviewerID=>$rev_data) {
    $image = '';
    $reviewer_name = $rev_data['title'] . ' ' . $rev_data['initials'] .  ' ' . $rev_data['surname'];
    $comment = '';
    
    $comment = nl2br($comments_data[$reviewerID]->get_comment($questionID));
    
    if ($comments_data[$reviewerID]->get_category($questionID) === null) {
      $image = '';
      $status = '';
    } else {
      switch($comments_data[$reviewerID]->get_category($questionID)) {
        case 1:
          $image = 'ok_comment.png';
          $status = 'OK';
          break;
        case 2:
          $image = 'minor_comment.png';
          $status = 'Minor';
          break;
        case 3:
          $image = 'major_comment.png';
          $status = 'Major';
          break;
        case 4:
          $image = 'cannot_comment.png';
          $status = 'Cannot Comment';
          break;
      }
    }
    
    if (trim($comment) == '') {
      if ($comments_data[$reviewerID]->get_category($questionID) == 4) {
        $comment = '<span style="color:#808080">' . $string['cannotcomment'] . '</span>';
      } else {
        $comment = '<span style="color:#808080">' . $string['nocomment'] . '</span>';
      }
      $action = '<span style="color:#808080">' . $string['nocomment'] . '</span>';
      $response = '<span style="color:#808080">' . $string['na'] . '</span>';
    } else {
      $action = $comments_data[$reviewerID]->get_action($questionID);
      $response = nl2br($comments_data[$reviewerID]->get_response($questionID));
    }
    $extra = '';
    if ($image != '') {
      $image = "<img src=\"../artwork/$image\" class=\"status\" alt=\"$status\" />";
    } else {
      $image = '';
    }    
    
    $html .= "<tr><td class=\"reviewline$extra\">$image</td><td class=\"reviewline$extra\">$reviewer_name</td><td class=\"reviewline$extra\">$comment</td><td class=\"reviewline$extra\">$action</td><td class=\"reviewline$extra\">$response</td></tr>\n";
  }
  
  $html .= "</table></td></tr>\n";

  return $html;
}

function displayQuestion($q_no, $q_id, $theme, $scenario, $leadin, $q_type, $correct, $settings, $q_media, $q_media_width, $q_media_height, $options, $comments, $correct_buf, $display_method, $score_method, $labelcolor, $themecolor, $std, $reviewer_data, $type, $string, $language) {
  $configObject = Config::get_instance();

  $cfg_root_path = $configObject->get('cfg_root_path');
  $mediadirectory = rogo_directory::get_directory('media');

  if ($theme != '') echo "<tr><td colspan=\"2\"><h1 style=\"color:$themecolor\">$theme</h1></td></tr>\n";
  echo "<tr>\n";

  if ($q_type != 'extmatch' and $q_type != 'matrix') {
    if ($q_type == 'info') {
      echo "<td colspan=\"2\" style=\"padding-left:10px; padding-right:10px\">$leadin\n";
    } else {
      if ($scenario != '') {
        echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$scenario<br />\n";
        echo $leadin;
        if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'area') {
          echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height, '') . "</p>\n";
        }
        if ($q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'blank') echo "<p>\n<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"margin-left:30px\">\n";
      } else {
        echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin\n";
        if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'area') {
          echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height, '') . "</p>\n";
        }
        if ($q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'blank') echo "<p>\n<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"margin-left:30px\">\n";
      }
    }
    switch ($q_type) {
      case 'area':
      ?>
      <br />
			<?php
    			//<!-- ======================== HTML5 part include find ================= -->
    			echo "<canvas id='canvas" . $q_no . "' width='" . ($q_media_width + 2) . "' height='" . ($q_media_height + 1) . "'></canvas>\n";
    			echo "<div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
    			echo "<script>\n";
    			echo "setUpQuestion(" . $q_no . ", 'q" . $q_no . "','" . $language . "', '" . $q_media . "', '" . $correct . "', '','','#FFC0C0','area','script');\n";
    			echo "</script>\n";
    			//<!-- ==================================================== -->
			?>
      <input type="hidden" name="q<?php echo $q_no; ?>" id="q<?php echo $q_no; ?>" />
      <?php
        break;
      case 'blank':
        $options[0] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$options[0]);
        $options[0] = preg_replace("| size=\"([0-9]{1,3})\"|","",$options[0]);
        $blank_details = array();
        $blank_details = explode('[blank',$options[0]);
        $array_size = count($blank_details);
        $blank_count = 0;
        while ($blank_count < $array_size) {
          if (strpos($blank_details[$blank_count],'[/blank]') === false) {
            echo $blank_details[$blank_count];
          } else {
            $end_start_tag = strpos($blank_details[$blank_count],']');
            $start_end_tag = strpos($blank_details[$blank_count],'[/blank]');
            $blank_options = substr($blank_details[$blank_count],($end_start_tag+1),($start_end_tag-1));
            $remainder = substr($blank_details[$blank_count], ($start_end_tag+8));

            if ($display_method == 'dropdown') {
              echo '<select>';
              $options_array = array();
              $options_array = explode(',',$blank_options);
              $i = 0;
              foreach ($options_array as $individual_blank_option) {
                $individual_blank_option = trim($individual_blank_option);
                if ($i == 0) {
                  echo '<option value="" selected="selected">' . $individual_blank_option . '</option>';
                } else {
                  echo '<option value="">' . $individual_blank_option . '</option>';
                }
                $i++;
              }
              echo '</select>';
            } else {
              // Correct answer.
              $correct_options = explode(',' , $blank_options);
              echo '<input type="text" size="10" value="' . $correct_options[0] . '" />';
            }
            echo $remainder;
          }
          $blank_count++;
        }
        break;
      case 'calculation':
        break;
      case 'dichotomous':
        $tmp_std_array = explode(',', $std);
        $std_part = 0;
        if ($score_method == 'YN_Positive') {
          $true_label = 'Yes';
          $false_label = 'No';
        } else {
          $true_label = 'True';
          $false_label = 'False';
        }
        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          if ($correct_buf[$i-1] == 't') {
            echo "<tr><td style=\"font-weight:bold\">$true_label</td><td>$individual_option</td></tr>\n";
          } else {
            echo "<tr><td style=\"font-weight:bold\">$false_label</td><td>$individual_option</td></tr>\n";
          }
        }
        break;
      case 'labelling':
        $tmp_std_array = explode(',',$std);
        $std_part = 0;
        $tmp_std_array = explode(',',$std);
        $std_part = 0;
        $max_col1 = 0;
        $max_col2 = 0;
        $tmp_first_split = explode(';', $correct);
        $tmp_second_split = explode('|', $tmp_first_split[11]);
        foreach ($tmp_second_split as $ind_label) {
          $label_parts = explode('$', $ind_label);
          if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
            if ($label_parts[0] < 10) {
              $max_col1 = $label_parts[0];
            } else {
              $max_col2 = $label_parts[0];
            }
          }
        }
        $max_col2-=10;

        $max_label = max($max_col1, $max_col2);

        $tmp_height = $q_media_height;
        if ($tmp_height < ($max_label * 55)) $tmp_height = ($max_label * 55);
        $correct = str_replace('"', '&#034;', $correct);
        $correct = str_replace("'", '&#039;', $correct);
?>
  <div align="center">
	<?php
	$configObject          = Config::get_instance();
	//<!-- ======================== HTML5 part rep disc ================= -->
	echo "<canvas id='canvas" . $q_no . "' width='" . ($q_media_width + 220) . "' height='" . $tmp_height . "'></canvas>\n";
	echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
	echo "<script>\n";
	echo "setUpQuestion(" . $q_no . ", 'flash" . $q_no . "', '" . $language . "', '" . $q_media . "', '" . trim($correct) . "', '', '','#FFC0C0','labelling','analysis');\n";
	echo "</script>\n";
	//<!-- ==================================================== -->
	?>
	</div>
  <br />
<?php
        break;
      case 'hotspot':
        $tmp_width = ($q_media_width + 301);
        if ($tmp_width < 375) $tmp_width = 375;
        $tmp_height = $q_media_height + 30;
        ?>
        <div>
        <?php
        //"<!-- ======================== HTML5 part include finf ================= -->
          echo "<canvas id='canvas" . $q_no . "' width='" . $tmp_width . "' height='" . $tmp_height . "'></canvas>\n";
          echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
          echo "<script>\n";
          echo "setUpQuestion(" . $q_no . ", 'flash" . $q_no . "', '" . $language . "', '" . $q_media . "', '" . str_replace('&nbsp;', ' ', $correct) . "', '', '0,0,0000000000000','#FFC0C0','hotspot','script');\n";
          echo "</script>\n";
          //<!-- ==================================================== -->
        ?>
        </div>
        <?php
        break;
      case 'mcq':
        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          if ($correct == $i) {
            echo "<tr><td><input type=\"radio\" checked=\"checked\" /></td><td>$individual_option</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"radio\" /></td><td>$individual_option</td></tr>\n";
          }
        }
        break;
      case 'true_false':
        if ($correct == 't') {
          echo "<tr><td><input type=\"radio\" checked=\"checked\" /></td><td>True</td></tr>\n";
          echo "<tr><td><input type=\"radio\" /></td><td>False</td></tr>\n";
        } else {
          echo "<tr><td><input type=\"radio\" /></td><td>True</td></tr>\n";
          echo "<tr><td><input type=\"radio\" checked=\"checked\" /></td><td>False</td></tr>\n";
        }
        break;
      case 'mrq':
        $tmp_std_array = explode(',',$std);
        $i = 0;
        $correct_stems = 0;
        foreach ($options as $individual_option) {
          $i++;
          if ($correct_buf[$i-1] == 'y') {
            echo "<tr><td><input type=\"checkbox\" checked=\"checked\" /></td><td>$individual_option</td></tr>\n";
          } else {
            echo "<tr><td><input type=\"checkbox\" /></td><td>$individual_option</td></tr>\n";
          }
        }
        break;
      case 'rank':
        $tmp_std_array = explode(',', $std);
        $std_part = 0;
        $rank_no = 0;
        foreach ($correct_buf as $individual_correct) {
          if ($individual_correct > $rank_no and $individual_correct < 9990) $rank_no = $individual_correct;
        }

        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          echo "<tr><td><select><option value=\"\"></option>";
          for ($a=1; $a<=$rank_no; $a++) {
            if ($correct_buf[$i-1] == $a) {
              echo '<option value="" selected="selected">' . displayRank($a, $string) . '</option>';
            } else {
              echo '<option value="">' . displayRank($a, $string) . '</option>';
            }
          }
          echo "</select></td><td>$individual_option</td></tr>\n";
        }
        break;
      case 'textbox':
        $settings = json_decode($settings, true);
        if (isset($settings['terms'])) {
          $correct_answers = explode(';', $settings['terms']);
          foreach ($correct_answers as $single_answer) {
            $answer_count[$single_answer] = 0;
          }
        }
        break;
    }
    if ($q_type != 'info' and $q_type != 'blank' and $q_type != 'labelling' and $q_type != 'hotspot') echo "</table></p>\n";
  } elseif ($q_type == 'matrix') {
    $matching_scenarios = explode('|', $scenario);
    $correct_answers = explode('|', $correct);
    echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin\n";
    echo '<ol type="i">';
    $i = 0;
    echo '<table cellpadding="2" cellspacing="0" border="1" class="matrix">';
    echo "<tr>\n<td colspan=\"2\">&nbsp;</td>";
    foreach ($options as $single_option) {
      echo '<td>' . $single_option . '</td>';
    }

    echo "<tr>\n";

    $row_no = 0;
    foreach ($matching_scenarios as $single_scenario) {
      if (trim($single_scenario) != '') {
        echo "<tr>\n";
        echo '<td align="right">' . chr(65 + $row_no) . '.</td><td>' . $single_scenario . '</td>';
        $answer_no = 1;
        $col_no = 1;
        foreach ($options as $single_option) {
          if ($correct_answers[$row_no] == $col_no) {
            echo '<td><div align="center"><input type="radio" name="q' . $q_no . '_' . $row_no . '" value="' . $answer_no . '" checked /></div></td>';
          } else {
            echo '<td><div align="center"><input type="radio" name="q' . $q_no . '_' . $row_no . '" value="' . $answer_no . '" /></div></td>';
          }
          $answer_no++;
          $col_no++;
        }
        echo "</tr>\n";
        $row_no++;
      }
    }
    echo '</table>';
    echo "</ol>\n</td></tr>\n";
  } elseif ($q_type == 'extmatch') {
    $matching_scenarios = explode('|', $scenario);
    $matching_media = explode('|', $q_media);
    $tmp_media_width_array = explode('|',$q_media_width);
    $tmp_media_height_array = explode('|',$q_media_height);
    $tmp_answers_array = explode('|',$correct_buf[0]);
    $tmp_std_array = explode(',',$std);
    $std_part = 0;

    array_unshift($matching_scenarios, '');
    $max_scenarios = max(count($matching_scenarios), count($matching_media));
    $scenario_no = 0;
    for ($part_id = 1; $part_id < $max_scenarios; $part_id++) {
      if ((isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id],'<img>')) != '')
              or (isset($matching_media[$part_id]) and $matching_media[$part_id] != '')) {
        $scenario_no++;
      }
    }

    echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td>$leadin\n<ol type=\"A\">";
    if ($matching_media[0] != '') {
      echo "<div align=\"center\">" . display_media($matching_media[0], $tmp_media_width_array[0], $tmp_media_height_array[0], '') . "</div>\n";
    }
    for ($i=1; $i<=$scenario_no; $i++) {
      echo "<li>\n";
      if (isset($matching_media[$i]) and $matching_media[$i] != '') {
        echo "<div>" . display_media($matching_media[$i], $tmp_media_width_array[$i], $tmp_media_height_array[$i], '') . "</div>\n";
      }
      if ($matching_scenarios[$i]) echo $matching_scenarios[$i] . '<br />';
      $option_no = 1;
      $specific_answers = array();
      $specific_answers = explode('$', $tmp_answers_array[$i-1]);
      if (count($specific_answers) > 1) {
        echo '<select multiple="multiple" size="10">';
      } else {
        echo '<select>';
      }
      foreach ($options as $individual_option) {
        $answer_match = false;
        for ($x=0; $x<count($specific_answers); $x++) {
          if ($option_no == $specific_answers[$x]) $answer_match = true;
        }
        if ($answer_match == true) {
          echo "<option value=\"\" selected=\"selected\">$individual_option</option>\n";
        } else {
          echo "<option value=\"\">$individual_option</option>\n";
        }
        $option_no++;
      }
      echo "</select><br />&nbsp;</li>\n";
    }
    echo "</ol>\n";
  }
  echo "</td></tr>\n";

  // Display comments here.
  if ($q_type != 'info') echo displayComments($q_id, $comments, $q_type, $q_no, $reviewer_data, $type, $string, $language);
  echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
}

// Get some paper properties
$paper      = $propertyObj->get_paper_title();
$marking    = $propertyObj->get_marking();
$paper_type = $propertyObj->get_paper_type();
$labelcolor = $propertyObj->get_labelcolor();
$themecolor = $propertyObj->get_themecolor();
        
if (!isset($paper)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$reviewer_data = array();
$result = $mysqli->prepare("SELECT users.id, title, initials, surname, DATE_FORMAT(started,'%d/%m/%Y %T') AS started, DATE_FORMAT(complete,'%d/%m/%Y %T') AS complete, paper_comment FROM (properties_reviewers, users) LEFT JOIN review_metadata ON properties_reviewers.reviewerID = review_metadata.reviewerID AND properties_reviewers.paperID = review_metadata.paperID WHERE properties_reviewers.reviewerID = users.id AND type = ? AND properties_reviewers.paperID = ? ORDER BY surname, initials");
$result->bind_param('si', $type, $paperID);
$result->execute();
$result->bind_result($id, $title, $initials, $surname, $started, $complete, $paper_comment);
while ($result->fetch()) {
  $reviewer_data[$id]['title'] = $title;
  $reviewer_data[$id]['initials'] = $initials;
  $reviewer_data[$id]['surname'] = $surname;
  $reviewer_data[$id]['started'] = $started;
  $reviewer_data[$id]['complete'] = $complete;
  $reviewer_data[$id]['paper_comment'] = $paper_comment;
}
$result->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo ucfirst($type); ?> Comments Report</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <style type="text/css">
    body {font-size:90%}
    table {font-size:100%;table-layout:auto}
    h1 {margin-left:15px; font-size:18pt}
    p {margin-left:0px; margin-right:15px; margin-top:0px; padding-top:0px}
    .figures {text-align:right}
    .q_no {text-align:right; vertical-align:top; width:50px}
    .grey {color:#808080}
    .OK {}
    .Minor {}
    .Major {}
    .notreviewed {color:#C00000}
    .pencil {cursor:pointer; width:16px; height:16px}
    .status {width:16px; height:16px}
    .screenbrk {
      padding-top: 3px;
      color: #808080;
      font-size: 90%;
      height: 70px;
      width: 100%;
      border-top: 2px solid #808080;
    }
    .reviewbar {
		  color:white;
			background-color:#295AAD;
			width:100%;
      padding:2px;
		}
    .reviewline {
      padding:2px;
      border-bottom:solid 1px #C0C0C0;
    }
    .reviewer_list {width: 95%; margin-left:auto; margin-right:auto; margin-bottom: 20px; border: 2px solid #FCE699; background-color: #FFFFEE}
    .reviewer_list th {background-color: #FCE699}
    .reviewer_list td {vertical-align:top; text-align:justify}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/page_scroll.js"></script>
	<?php
  if ($propertyObj->get_latex_needed() == 1) {
    echo "<script type=\"text/javascript\" src=\"../js/jquery-migrate-1.2.1.min.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"../tools/mee/mee/js/mee_src.js\"></script>\n";
  }
  $render = new render($configObject);
  $render->render_html5_js(json_encode($jstring));
  ?>

	
  <script>
    function editQ(qid, qno) {
      location.href='../question/edit/index.php?q_id=' + qid + '&qNo=' + qno + '&paperID=<?php echo $paperID; ?>&folder=<?php echo $_GET['folder']; ?>&module=<?php echo $_GET['module']; ?>&calling=<?php echo $type; ?>_comments&scrOfY=' + $('#scrOfY').val() + '&tab=comments';
    }
    <?php
    if (isset($_GET['scrOfY'])) {
    ?>
    $(function () {
      window.scrollTo(0,<?php echo $_GET['scrOfY'] ?>);
    });
    <?php
    }
    ?>
  </script>
</head>

<body>
<div id="maincontent">
<form name="theform" autocomplete="off">

<div class="head_title" style="font-size:90%">
<div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '">' . $paper . '</a></div>';

  echo "<div class=\"page_title\">" . $string[$type . 'report'] . "</div></div>";

  if (count($reviewer_data) == 0) {
    echo $notice->info_strip($string['noreviewers'], 100) . "\n</body>\n</html>\n";
    exit;
  }
 
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(30);
?>
  
<br />

<?php
  echo list_externals($reviewer_data, $string);
?>
<br />
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<?php

  // Capture reviewer comments data first.
  $comments_array = array();
  foreach ($reviewer_data as $reviewerID=>$reviewer_detail) {
    // Only loads reviews if they exist.
    if (!empty($reviewer_detail['started'])) {
        $comments_array[$reviewerID] = new Review($paperID, $reviewerID, $type, $mysqli);
        $comments_array[$reviewerID]->load_reviews();
    } else {
        // Un-set the data as the exeternal has not revied yet.
        unset($reviewer_data[$reviewerID]);
    }
  }
  
  // Capture the paper makeup.
  $question_no = 0;
  $old_q_id = 0;
  $old_screen = 1;
  $options_buffer = array();
  $correct_buffer = array();

  $result = $mysqli->prepare("SELECT paper_title, labelcolor, themecolor, screen, q_id, q_type, theme, scenario, leadin, option_text, display_method, score_method, q_media, q_media_width, q_media_height, correct, std, questions.settings FROM (properties, papers, questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE papers.paper = properties.property_id AND papers.question = questions.q_id AND papers.paper = ? ORDER BY screen, display_pos, id_num");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($paper_title, $labelcolor, $themecolor, $screen, $q_id, $q_type, $theme, $scenario, $leadin, $option_text, $display_method, $score_method, $q_media, $q_media_width, $q_media_height, $correct, $std, $settings);
  while ($result->fetch()) {
    if ($old_q_id != $q_id and $old_q_id > 0) {   // New question.
      $question_no++;
      if ($old_q_type == 'info') $question_no--;
      displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_settings, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $comments_array, $correct_buffer, $old_display_method, $old_score_method, $labelcolor, $themecolor, $old_std, $reviewer_data, $type, $string, $language);
      $options_buffer = array();
      $correct_buffer = array();
      if ($old_screen != $screen) {
        echo '<tr><td colspan="2"><br /><div class="screenbrk">&nbsp;&nbsp;&nbsp;&nbsp;' . $string['screen'] . '&nbsp;' . $screen . '</div></td></tr>';
      }
    }
    if ($q_type == 'labelling') {
      $tmp_first_split = explode(';', $correct);
      $tmp_second_split = explode('$', $tmp_first_split[11]);
      for ($label_no = 4; $label_no <= 43; $label_no += 4) {
        if (array_key_exists($label_no, $tmp_second_split) and substr($tmp_second_split[$label_no],0,1) != '|') {
          $options_buffer[] = trim(substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'))) . '|' . $tmp_second_split[$label_no-2] . '|' . ($tmp_second_split[$label_no-1] - 25);
          if ($tmp_second_split[$label_no-2] > 150) {
            $correct_buffer[] = $tmp_second_split[$label_no-2] . 'x' . ($tmp_second_split[$label_no-1] - 25);
          }
        }
      }
    } elseif ($q_type == 'blank') {
      $blank_details = explode('[blank',$option_text);
      $no_answers = count($blank_details) - 1;
      for ($i=1; $i<=$no_answers; $i++) {
        $blank_details[$i] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$blank_details[$i]);
        $blank_details[$i] = preg_replace("| size=\"([0-9]{1,3})\"|","",$blank_details[$i]);

        $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
        $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
        $answer_list = explode(',',$blank_details[$i]);
        $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
        if ($score_method == 'textboxes') {
          foreach ($answer_list as $individual_answer) {
            $correct_buffer[] = html_entity_decode(trim($individual_answer));
          }
        } else {
          $correct_buffer[] = html_entity_decode(trim($answer_list[0]));
        }
      }
      $options_buffer[] = $option_text;
    } else {
      $options_buffer[] = $option_text;
      $correct_buffer[] = $correct;
    }
    $old_q_id = $q_id;
    $old_theme = $theme;
    $old_scenario = $scenario;
    $old_leadin = $leadin;
    $old_q_type = $q_type;
    $old_q_media = $q_media;
    $old_q_media_width = $q_media_width;
    $old_q_media_height = $q_media_height;
    $old_correct = $correct;
    $old_settings = $settings;
    $old_display_method = $display_method;
    $old_score_method = $score_method;
    $old_std = $std;
    $old_screen = $screen;
  }
  $result->close();
  $question_no++;
  if ($old_q_type == 'info') $question_no--;
  displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_settings, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $comments_array, $correct_buffer, $old_display_method, $old_score_method, $labelcolor, $themecolor, $old_std, $reviewer_data, $type, $string, $language);
  $mysqli->close();
?>
</table>
<input type="hidden" name="scrOfY" id="scrOfY" value="0" /><br />
</form>
</div>
</body>
</html>
