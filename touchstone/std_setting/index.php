<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  $paperID = $_GET['paperID'];

  function displayReview($group_review) {
    global $review_no, $old_setterID, $old_date, $review_no, $old_method, $old_title, $old_initials, $old_surname, $old_display_date, $pass_score, $distinction_score, $review_total, $total_marks, $old_method, $userroles, $userID;

    if ($review_total == $total_marks) {
      $icon = '../artwork/std_set_icon_16.gif';
      $text_color  = 'black';
      $background = 'white';
    } else {
      $icon = '../artwork/std_set_icon_problem.gif';
      $text_color  = '#800000';
      $background = '#FFC0C0';
    }
    if ($group_review != 'No') {
      $icon = '../artwork/small_users_icon.png';
      $old_setterID = $old_setterID . ',' . $old_date . ';' . $group_review;
    }
    
    $html = '';
    if ($old_setterID == $userID or strpos($userroles,'SysAdmin') !== false) {
      $html .= "<tr id=\"review$review_no\" style=\"cursor:hand\" onmouseover=\"highlight($review_no)\" onmouseout=\"unhighlight($review_no)\" onclick=\"selReview('$old_setterID','$old_date',$review_no,'$old_method','menu2b',event); return false;\" ondblclick=\"editReview('$old_setterID','$old_date','$review_no','$old_method','$group_review'); return false;\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" border=\"0\" /></td><td>&nbsp;";
    } else {
      $html .= "<tr id=\"review$review_no\" style=\"cursor:hand\" onmouseover=\"highlight($review_no)\" onmouseout=\"unhighlight($review_no)\" onclick=\"selReview('$old_setterID','$old_date',$review_no,'$old_method','menu2c',event); return false;\" ondblclick=\"editReview('$old_setterID','$old_date','$review_no','$old_method','$group_review'); return false;\"><td align=\"center\"><img src=\"$icon\" width=\"16\" height=\"16\" alt=\"icon\" border=\"0\" /></td><td>&nbsp;";
    }
    if ($distinction_score != 'n/a') $distinction_score .= '%';
    if ($group_review != 'No') {
      $html .= "&lt;group review&gt;</a>";
    } else {
      $html .= "$old_title $old_initials $old_surname</a>";
    }
    if ($review_total == $total_marks) {
      $html .= "</td><td>&nbsp;$old_display_date</td><td style=\"text-align:right\">$pass_score%&nbsp;</td><td style=\"text-align:right\">$distinction_score&nbsp;</td><td style=\"text-align:right\">$review_total&nbsp;</td><td style=\"text-align:right\">$total_marks&nbsp;</td><td>&nbsp;$old_method</td><td></td></tr>\n";
    } else {
      $html .= "</td><td>&nbsp;$old_display_date</td><td style=\"text-align:right\">$pass_score%&nbsp;</td><td style=\"text-align:right\">$distinction_score&nbsp;</td><td style=\"text-align:right; color:$text_color; background-color:$background\">$review_total&nbsp;</td><td style=\"text-align:right; color:$text_color; background-color:$background\">$total_marks&nbsp;</td><td>&nbsp;$old_method</td><td></td></tr>\n";
    }
    return $html;
  }

  // Get any questions to exclude.
  $exclude = array();
  $exclude_query = $mysqli->query("SELECT q_id, parts FROM question_exclude WHERE q_paper=$paperID");
  while ($row = $exclude_query->fetch_assoc()) {
    $exclude[$row['q_id']] = $row['parts'];
  }
  $exclude_query->close();

  // Calculate marks for the current paper.
  $marks_array = array();
  $partID = 0;
  $total_marks = 0;
  $old_q_id = 0;
  $stems = 0;
  $correct_no = 0;
  $old_score_method = '';
  $question_data = $mysqli->query("SELECT q_type, q_id, marks, correct, score_method, option_text FROM (papers, questions, options) WHERE papers.paper=" . $_GET['paperID'] . " AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type != 'info' ORDER BY q_id, o_id, id_num");
  while ($row = $question_data->fetch_assoc()) {
    if ($old_q_id != $row['q_id'] and $old_q_id != 0) {
      if ($old_q_type == 'rank' and $old_score_method == 'BonusMark') {
        if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id],'1') !== false) {
          $marks_array[$old_q_id][$partID] = 0;
        } else {
          $marks_array[$old_q_id][$partID] = 1;
        }
      }
      $stems = 0;
      $correct_no = 0;
      $temp_marks = 0;
      $partID = 0;
    }
    
    $old_q_id = $row['q_id'];
    if (!isset($exclude[$old_q_id])) {  // If no record exists set to zero (i.e. included)
      $exclude[$old_q_id] = '00000000000000000000000000000';
    }

    switch ($row['q_type']) {
      case 'textbox':
        if (substr($exclude[$old_q_id],$partID,1) == '0') {
          $marks_array[$old_q_id][$partID] = $row['marks'];
        }
        break;
      case 'dichotomous':
        if (substr($exclude[$old_q_id],$partID,1) == '0') {
          $marks_array[$old_q_id][$partID] = 1;
        } else {
          $marks_array[$old_q_id][$partID] = 0;
        }
        $partID++;
        break;
      case 'calculation':
      case 'timedate':
        if (substr($exclude[$old_q_id],$partID,1) == '0') {
          $marks_array[$old_q_id][$partID] = 1;
        }
        break;
      case 'hotspot':
        $tmp_first_split = explode('|', $row['correct']);
        for ($i=0; $i<count($tmp_first_split); $i++) {
          if (substr($exclude[$old_q_id],$i,1) == '0') {
            $marks_array[$old_q_id][$i] = 1;
          } else {
            $marks_array[$old_q_id][$i] = 0;
          }
        }
        break;
      case 'blank':
        $no_blanks = substr_count($row['option_text'],'[blank');
        for ($i=0; $i<$no_blanks; $i++) {
          if (substr($exclude[$old_q_id],$i,1) == '0') {
            $marks_array[$old_q_id][$i] = 1;
          }
        }
        break;
      case 'matrix':
        $tmp_part = 0;
        $matching_correct = explode('|', $row['correct']);
        for ($part_id=0; $part_id<count($matching_correct); $part_id++) {
          if (trim($matching_correct[$part_id]) != '') {
            if (substr($exclude[$old_q_id],$part_id,1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = 1;
            }
            $tmp_part++;
          }
        }
        break;
      case 'extmatch':
        $tmp_part = 0;
        $matching_correct = explode('|', $row['correct']);
        for ($part_id=0; $part_id<count($matching_correct); $part_id++) {
          $sub_array = explode('$',$matching_correct[$part_id]);
          for ($sub_part_id=0; $sub_part_id<count($sub_array); $sub_part_id++) {
            if (substr($exclude[$old_q_id],$tmp_part,1) == '0') {
              $marks_array[$old_q_id][$tmp_part] = 1;
            } else {
              $marks_array[$old_q_id][$tmp_part] = 0;
            }
            $tmp_part++;
          }
        }
        break;
      case 'mcq':
        if (substr($exclude[$old_q_id],$partID,1) == '0') {
          $marks_array[$old_q_id][$partID] = $row['marks'];
          $partID++;
        }
        break;
      case 'mrq':
        if (substr($exclude[$old_q_id],$partID,1) == '0') {
          if ($row['score_method'] == 'SelectedPositive') {
            if ($row['correct'] == 'y') {
              $marks_array[$old_q_id][$partID] = 1;
            }
            $partID++;
          } elseif ($row['score_method'] == 'AllItemsCorrect') {
            $marks_array[$old_q_id][0] = 1;
          } else {
            $marks_array[$old_q_id][$partID] = 1;
            $partID++;
          }
        }
        break;
      case 'rank':
        if ($row['correct'] < 9990) {
          if (substr($exclude[$old_q_id],$partID,1) == '0') {
            $marks_array[$old_q_id][$partID] = 1;
          } else {
            $marks_array[$old_q_id][$partID] = 0;
          }
          $partID++;
        }
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $row['correct']);
        $tmp_second_split = explode('$', $tmp_first_split[8]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
            if (substr($exclude[$old_q_id],$partID,1) == '0') {
              $marks_array[$old_q_id][$partID] = 1;
            } else {
              $marks_array[$old_q_id][$partID] = 0;
            }
            $partID++;
          }
        }
        break;
    }

    $old_q_type = $row['q_type'];
    $old_score_method = $row['score_method'];
    $old_correct = $row['correct'];
    $old_option_text = $row['option_text'];
    $temp_marks = $row['marks'];
    if ($row['q_type'] == 'mrq') {
      if ($row['correct'] == 'y') $correct_no++;
    }
    if ($row['q_type'] == 'rank') {
      if ($row['correct'] < 9990) $correct_no++;
    }
    $stems++;
  }

  $question_data->close();

  if ($old_q_type == 'rank' and $old_score_method == 'BonusMark') {
    if (isset($exclude[$old_q_id]) and strpos($exclude[$old_q_id],'1') !== false) {
      $marks_array[$old_q_id][$partID] = 0;
    } else {
      $marks_array[$old_q_id][$partID] = 1;
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>TouchStone: List Settings<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<script src="../javascript/staff_help.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
  function editReview(tmp_setter, tmp_date, tmp_review_no, tmp_method, tmp_group_review) {
    if (tmp_method == 'Modified Angoff') {
      tmp_method = 'modified_angoff';
    } else if (tmp_method == 'Ebel') {
      tmp_method = 'ebel';
    }
    if (tmp_group_review == 'No') {
      window.location.href = "individual_review.php?setterID=" + tmp_setter + "&paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&dateID=" + tmp_date + "&method=" + tmp_method;
    } else {
      window.location.href = "group_set_angoff.php?reviewers=" + tmp_setter + "&paperID=<?php echo $_GET['paperID']; ?>&module=<?php echo $_GET['module']; ?>&dateID=" + tmp_date + "&method=" + tmp_method;
    }
  }

  function selReview(setterID, dateID, reviewID, methodType, menuID, evt) {
    tmp_ID = document.StdSetMenu.oldReviewID.value;
    if (tmp_ID != '') {
      document.getElementById('review' + tmp_ID).style.backgroundColor = 'white';
      document.getElementById('review' + tmp_ID).style.color = 'black';
    }
    document.getElementById('menu2a').style.display = 'none';
    document.getElementById('menu2b').style.display = 'none';
    document.getElementById('menu2c').style.display = 'none';
    document.getElementById(menuID).style.display = 'block';

    document.StdSetMenu.setterID.value = setterID;
    document.StdSetMenu.dateID.value = dateID;
    document.StdSetMenu.method.value = methodType;

    document.getElementById('review' + reviewID).style.backgroundColor = '#316AC5';
    document.getElementById('review' + reviewID).style.color = 'white';
    document.StdSetMenu.oldReviewID.value = reviewID;
    evt.cancelBubble = true;
  }

  function reviewOff() {
    parent.frames['menu'].document.getElementById('menu2a').style.display = 'block';
    parent.frames['menu'].document.getElementById('menu2b').style.display = 'none';
    parent.frames['menu'].document.getElementById('menu2c').style.display = 'none';
    tmp_ID = document.StdSetMenu.oldReviewID.value;
    if (tmp_ID != '') {
      document.getElementById('review' + tmp_ID).style.backgroundColor = 'white';
      document.getElementById('review' + tmp_ID).style.color = 'black';
    }
  }

  function highlight(lineID) {
    if (lineID != parent.frames['menu'].document.StdSetMenu.oldReviewID.value) {
      document.getElementById('review' + lineID).style.backgroundColor = '#ECE9D8';
    }
  }

  function unhighlight(lineID) {
    if (lineID != parent.frames['menu'].document.StdSetMenu.oldReviewID.value) {
      document.getElementById('review' + lineID).style.backgroundColor = '';
    }
  }

  function roundNumber(num, dec) {
    var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
    return result;
  }
</script>
</head>

<body onclick="reviewOff()">

<?php
  $old_date = '';
  $old_title = '';
  $old_initials = '';
  $old_surname = '';
  $old_questionID = 0;
  $question_no = 0;
  $std_total = 0;
  $review_no = 0;
  $ebel_percents = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
  $ebel_marks = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
  $angoff_review_marks = 0;

  $reviews_html = '';
  
  $results = $mysqli->query("SELECT paper_title, total_mark FROM properties WHERE property_id=$paperID LIMIT 1");
  while ($row = $results->fetch_assoc()) {
    $reviews_html .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
    $reviews_html .= "<tr><td style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../index.php\">Home</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../paper/details.php?paperID=" . $_GET['paperID'] . "&folder=" . $_GET['folder'] . "&module=" . $_GET['module'] . "\">" . $row['paper_title'] . "</a></div><div style=\"font-size:220%; color:black; font-weight:bold; margin-left:10px\">Standards Setting</div></td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(97); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
    $reviews_html .= "</table>\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
    $reviews_html .= "<tr><td style=\"width:18px; background-color:#F1F5FB\">&nbsp;</td><td style=\"background-color:#F1F5FB; width:150px\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Standard Setter&nbsp;</td><td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Date&nbsp;</td><td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Pass Score</td><td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Distinction</td><td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Review Marks</td><td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Paper Total</td><td style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;Method</td><td width=\"25%\" style=\"background-color:#F1F5FB\"><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp</td></tr>\n";
    $reviews_html .= "<tr style=\"height:4px\"><td valign=\"top\" colspan=\"9\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
    $total_marks = $row['total_mark'];
  }
  $results->close();
  
  $results = $mysqli->query("SELECT q_type, questionID, category, percentage, group_review, DATE_FORMAT(std_set,'%Y%m%d%H%i%s') AS std_set, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, rating, standards_setting.setterID, method, title, initials, surname, paper_title FROM (standards_setting, properties, questions, users) LEFT JOIN ebel ON (standards_setting.setterID=ebel.setterID AND standards_setting.std_set=ebel.date_set) WHERE standards_setting.questionID=questions.q_id AND standards_setting.paperID=properties.property_id AND standards_setting.setterID=users.id AND paperID=$paperID ORDER BY standards_setting.std_set DESC, standards_setting.setterID, standards_setting.id");
  $no_reviews = 0;
  if ($results->num_rows > 0) {
    while ($row = $results->fetch_assoc()) {
      $paper_title = $row['paper_title'];
      if ($old_date != $row['std_set'] and $old_date != '') {     // New review date
        $review_no++;
        if ($old_method == 'Modified Angoff') {
          // $question_no can be 0 in some cases if questions have been excluded
          $pass_score = ($question_no > 0) ? round($std_total/$question_no) : 0;
          $review_total = $angoff_review_marks;
          $distinction_score = 'n/a';
        } elseif ($old_method == 'Ebel') {
          $cut_marks = 0.0;
          $cut_marks += $ebel_marks['EE'] * $ebel_percents['EE'] * 100;
          $cut_marks += $ebel_marks['EI'] * $ebel_percents['EI'] * 100;
          $cut_marks += $ebel_marks['EN'] * $ebel_percents['EN'] * 100;
          $cut_marks += $ebel_marks['ME'] * $ebel_percents['ME'] * 100;
          $cut_marks += $ebel_marks['MI'] * $ebel_percents['MI'] * 100;
          $cut_marks += $ebel_marks['MN'] * $ebel_percents['MN'] * 100;
          $cut_marks += $ebel_marks['HE'] * $ebel_percents['HE'] * 100;
          $cut_marks += $ebel_marks['HI'] * $ebel_percents['HI'] * 100;
          $cut_marks += $ebel_marks['HN'] * $ebel_percents['HN'] * 100;
          $review_total = $ebel_marks['EE'] + $ebel_marks['EI'] + $ebel_marks['EN'] + $ebel_marks['ME'] + $ebel_marks['MI'] + $ebel_marks['MN'] + $ebel_marks['HE'] + $ebel_marks['HI'] + $ebel_marks['HN'];
         
          $pass_score = ($cut_marks / ($total_marks  * 100)) * 100;
          $pass_score = round($pass_score,1);

          $cut_marks2 = 0.0;
          $cut_marks2 += $ebel_marks['EE'] * $ebel_percents['EE2'] * 100;
          $cut_marks2 += $ebel_marks['EI'] * $ebel_percents['EI2'] * 100;
          $cut_marks2 += $ebel_marks['EN'] * $ebel_percents['EN2'] * 100;
          $cut_marks2 += $ebel_marks['ME'] * $ebel_percents['ME2'] * 100;
          $cut_marks2 += $ebel_marks['MI'] * $ebel_percents['MI2'] * 100;
          $cut_marks2 += $ebel_marks['MN'] * $ebel_percents['MN2'] * 100;
          $cut_marks2 += $ebel_marks['HE'] * $ebel_percents['HE2'] * 100;
          $cut_marks2 += $ebel_marks['HI'] * $ebel_percents['HI2'] * 100;
          $cut_marks2 += $ebel_marks['HN'] * $ebel_percents['HN2'] * 100;
          $distinction_score = (($cut_marks2 / ($total_marks * 100)) * 100);
          $distinction_score = round($distinction_score,1);
        }
        if($old_group_review == 'No') $no_reviews++;
        $reviews_html .= displayReview($old_group_review);
        $ebel_percents = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
        $ebel_marks = array('EE'=>0,'EI'=>0,'EN'=>0,'ME'=>0,'MI'=>0,'MN'=>0,'HE'=>0,'HI'=>0,'HN'=>0);
        $question_no = 0;
        $std_total = 0;
        $angoff_review_marks = 0;
      }
      $category = $row['category'];
      $ebel_percents[$category] = $row['percentage'];
      if ($old_questionID != $row['questionID']) {
        $questionID = $row['questionID'];
        if (array_key_exists($questionID,$exclude)) {
          $tmp_exclude = $exclude[$questionID];
        } else {
          $tmp_exclude = '0000000000000000000000000000000000000000';
        }
        $partID = 0;
        
        if ($row['rating'] != '') {
          $rating_array = explode(',',$row['rating']);
          foreach ($rating_array as $individual_rating) {
            if (isset($marks_array[$questionID][$partID]) and $individual_rating != '') {
              if (isset($ebel_marks[$individual_rating])) {
                $ebel_marks[$individual_rating] += $marks_array[$questionID][$partID];
              } else {
                $ebel_marks[$individual_rating] = $marks_array[$questionID][$partID];
              }
              $angoff_review_marks += $marks_array[$questionID][$partID];
            }
            $partID++;
          }
        }
      }

      if ($row['rating'] != '') {
        $q_sections = explode(',',$row['rating']);
        $tmp_part = 0;
        foreach ($q_sections as $part) {
          if (substr($tmp_exclude,$tmp_part,1) == '0') {
            if ($row['q_type'] == 'textbox') {
              $std_total += $part * (count($q_sections) - $tmp_part);
            } else {
              $std_total += $part;
            }
            $question_no++;
          }
          $tmp_part++;
        }
      }
      $old_date = $row['std_set'];
      $old_display_date = $row['display_date'];
      $old_method = $row['method'];
      $old_title = $row['title'];
      $old_initials = $row['initials'];
      $old_surname = $row['surname'];
      $old_setterID = $row['setterID'];
      $old_group_review = $row['group_review'];
      $old_questionID = $row['questionID'];
    }  // End while loop

    $review_no++;
    if ($old_method == 'Modified Angoff') {
      if ($question_no > 0) {
        $pass_score = round($std_total/$question_no);
      } else {
        $pass_score = 0;
      }
      $review_total = $angoff_review_marks;
      $distinction_score = 'n/a';
    } elseif ($old_method == 'Ebel') {
      $cut_marks = 0.0;
      $cut_marks += $ebel_marks['EE'] * $ebel_percents['EE'] * 100;
      $cut_marks += $ebel_marks['EI'] * $ebel_percents['EI'] * 100;
      $cut_marks += $ebel_marks['EN'] * $ebel_percents['EN'] * 100;
      $cut_marks += $ebel_marks['ME'] * $ebel_percents['ME'] * 100;
      $cut_marks += $ebel_marks['MI'] * $ebel_percents['MI'] * 100;
      $cut_marks += $ebel_marks['MN'] * $ebel_percents['MN'] * 100;
      $cut_marks += $ebel_marks['HE'] * $ebel_percents['HE'] * 100;
      $cut_marks += $ebel_marks['HI'] * $ebel_percents['HI'] * 100;
      $cut_marks += $ebel_marks['HN'] * $ebel_percents['HN'] * 100;
      $review_total = $ebel_marks['EE'] + $ebel_marks['EI'] + $ebel_marks['EN'] + $ebel_marks['ME'] + $ebel_marks['MI'] + $ebel_marks['MN'] + $ebel_marks['HE'] + $ebel_marks['HI'] + $ebel_marks['HN'];
      $pass_score = (($cut_marks / ($total_marks * 100)) * 100);
      $pass_score = round($pass_score,1);

      $cut_marks2 = 0.0;
      $cut_marks2 += $ebel_marks['EE'] * $ebel_percents['EE2'] * 100;
      $cut_marks2 += $ebel_marks['EI'] * $ebel_percents['EI2'] * 100;
      $cut_marks2 += $ebel_marks['EN'] * $ebel_percents['EN2'] * 100;
      $cut_marks2 += $ebel_marks['ME'] * $ebel_percents['ME2'] * 100;
      $cut_marks2 += $ebel_marks['MI'] * $ebel_percents['MI2'] * 100;
      $cut_marks2 += $ebel_marks['MN'] * $ebel_percents['MN2'] * 100;
      $cut_marks2 += $ebel_marks['HE'] * $ebel_percents['HE2'] * 100;
      $cut_marks2 += $ebel_marks['HI'] * $ebel_percents['HI2'] * 100;
      $cut_marks2 += $ebel_marks['HN'] * $ebel_percents['HN2'] * 100;
      $distinction_score = (($cut_marks2 / ($total_marks * 100)) * 100);
      $distinction_score = round($distinction_score,1);
    }
    if($old_group_review == 'No') $no_reviews++;
    $reviews_html .= displayReview($old_group_review);
  }
  require '../include/std_set_menu.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
  echo $reviews_html;
  echo "</table>\n";
  $results->close();
  $mysqli->close();
?>
</body>
</html>
