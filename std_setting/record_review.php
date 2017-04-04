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

require '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/std_set_shared_functions.inc';

$paperID    = check_var('paperID', 'POST', true, false, true);
$tmp_method = check_var('method', 'POST', true, false, true);

$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$logger           = new Logger($mysqli);
$rating           = '';
$old_leadin       = '';
$old_type         = '';
$old_score_method = '';
$question_no      = 0;
$question_part    = 0;
$log_id           = 0;
$now              = date("Y-m-d H:i:s");
$total_rating     = 0;
$total_parts      = 0;

if (isset($_GET['group']) and $_GET['group'] == 'true' and isset($_POST['review_string']) and $_POST['review_string'] != '') {
  $group_review = $_POST['review_string'];
} else {
  $group_review = 'No';
}

if (isset($_POST['std_setID']) and $_POST['std_setID'] != '') {
	if (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '3') {
		$std_query = $mysqli->prepare("UPDATE std_set SET std_set = NOW(), distinction_score = NULL WHERE id = ?");
		$std_query->bind_param('i', $_POST['std_setID']);
		$std_query->execute();
		$std_query->close();
	} elseif (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '2') {
		$std_query = $mysqli->prepare("UPDATE std_set SET std_set = NOW(), distinction_score = 0 WHERE id = ?");
		$std_query->bind_param('i', $_POST['std_setID']);
		$std_query->execute();
		$std_query->close();	} else {
		$std_query = $mysqli->prepare("UPDATE std_set SET std_set = NOW(), distinction_score = 1 WHERE id = ?");
		$std_query->bind_param('i', $_POST['std_setID']);
		$std_query->execute();
		$std_query->close();
	}

  
  $std_query = $mysqli->prepare("DELETE FROM std_set_questions WHERE std_setID = ?");
  $std_query->bind_param('i', $_POST['std_setID']);
  $std_query->execute();
  $std_query->close();
  
  $std_query = $mysqli->prepare("DELETE FROM ebel WHERE std_setID = ?");
  $std_query->bind_param('i', $_POST['std_setID']);
  $std_query->execute();
  $std_query->close();
  
  $std_setID = $_POST['std_setID'];
} else {
  $setterID = $userObject->get_user_ID();
	
	if (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '3') {
		$std_query = $mysqli->prepare("INSERT INTO std_set VALUES(NULL, ?, ?, NOW(), ?, ?, NULL, NULL)");
		$std_query->bind_param('iiss', $setterID, $paperID, $tmp_method, $group_review);
	} elseif (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '2') {
		$std_query = $mysqli->prepare("INSERT INTO std_set VALUES(NULL, ?, ?, NOW(), ?, ?, NULL, 0)");
		$std_query->bind_param('iiss', $setterID, $paperID, $tmp_method, $group_review);
	} else {
		$std_query = $mysqli->prepare("INSERT INTO std_set VALUES(NULL, ?, ?, NOW(), ?, ?, NULL, 1)");
		$std_query->bind_param('iiss', $setterID, $paperID, $tmp_method, $group_review);
	}
  $std_query->execute();
  $std_query->close();
  
  $std_setID = $mysqli->insert_id;
}

$last_question = 0;
$old_q_id = 0;

$result = $mysqli->prepare("SELECT q_id, scenario, leadin, q_type, option_text, q_media, correct, score_method, marks_correct, settings FROM papers, questions LEFT JOIN options ON questions.q_id = options.o_id WHERE paper = ? AND papers.question = questions.q_id AND q_type != 'info' ORDER BY display_pos, id_num");
$result->bind_param('i', $paperID);
$result->execute();
$result->store_result();
$result->bind_result($q_id, $scenario, $leadin, $q_type, $option_text, $q_media, $correct, $score_method, $marks_correct, $settings);
while ($result->fetch()) {
  if ($old_q_id != $q_id) {
    if ($question_no > 0) {
      if ($old_type == 'rank' and $old_score_method == 'Bonus Mark') {
        $question_part++;
        $qid = 'std' . $question_no . '_' . $question_part;
        if ($rating == '') {
          $rating = $_POST["$qid"];
        } else {
          $rating .= ',' . $_POST["$qid"];
        }
        if ($_POST["$qid"] != '') $last_question = $question_no;
        if ($tmp_method == 'Modified Angoff') {
          $total_rating += $_POST["$qid"];
        }
        $total_parts++;
      } elseif ($old_type == 'mrq' and $old_score_method == 'Mark per Question') {
        $qid = 'std' . $question_no . '_1';
        $rating = $_POST["$qid"];
        if ($_POST["$qid"] != '') $last_question = $question_no;
        if ($tmp_method == 'Modified Angoff') {
          $total_rating += $_POST["$qid"];
        }
        $total_parts++;
      }

      $std_query = $mysqli->prepare("INSERT INTO std_set_questions VALUES (NULL, ?, ?, ?)");
      $std_query->bind_param('iis', $std_setID, $log_id, $rating);
      $std_query->execute();
      $std_query->close();

      if (isset($_POST['banksave']) and $_POST['banksave'] == '1') {
        $std_query = $mysqli->prepare("UPDATE questions SET std = ? WHERE q_id = ?");
        $std_query->bind_param('si', $rating, $log_id);
        $std_query->execute();
        $std_query->close();

        if ($rating != $_POST["old$log_id"]) {
          $old_value = $_POST["old$log_id"];
          $logger->track_change('Edit Question', $log_id, $userObject->get_user_ID(), $old_value, $rating, 'Std Setting');
        }
      }
      $rating = '';
    }
    $question_no++;
    $question_part = 0;
    $old_q_id = $q_id;
    $old_leadin = $leadin;
    $old_type = $q_type;
    $old_score_method = $score_method;
  }

  $log_id = $q_id;
  $question_part++;

  if ($question_no > 0) {
    // Default format for $qid
    $qid = 'std' . $question_no;
    switch ($q_type) {
      case 'area':
      case 'enhancedcalc':
      case 'mcq':
      case 'true_false':
        if ($question_part == 1) {
          if (isset($_POST["std$question_no"])) {
            $rating = $_POST["std$question_no"];
          } else {
            $rating = '';
          }
          if ($tmp_method == 'Modified Angoff') {
            $total_rating += $_POST["$qid"];
          }
          if (isset($qid) and isset($_POST["$qid"])) $last_question = $question_no;
          $total_parts++;
        }
        break;
      case 'dichotomous':
        $qid = 'std' . $question_no . '_' . $question_part;
        if (isset($_POST["$qid"])) {
          if ($rating == '') {
            $rating = $_POST["$qid"];
          } else {
            $rating .= ',' . $_POST["$qid"];
          }
        }
        if ($tmp_method == 'Modified Angoff') {
          $total_rating += $_POST["$qid"];
        }
        if (isset($_POST["$qid"]) and $_POST["$qid"] != '') $last_question = $question_no;
        $total_parts++;
        break;
      case 'hotspot':
        $subparts = explode('|', $correct);
        $no_parts = count($subparts);
        for ($i=1; $i<=$no_parts; $i++) {
          $qid = 'std' . $question_no . '_' . $i;
          if ($tmp_method == 'Modified Angoff') {
            $total_rating += $_POST["$qid"];
          }
          if ($i == 1) {
            if (isset($_POST["$qid"])) {
              $rating = $_POST["$qid"];
            } else {
              $rating = '';
            }
          } else {
            if (isset($_POST["$qid"])) {
              $rating .= ',' . $_POST["$qid"];
            } elseif ($score_method != 'Mark per Question') {
              $rating .= ',';
            }
          }
          $total_parts++;
        }
        break;
      case 'mrq':
        if ($score_method == 'Mark per Question') {
          $qid = 'std' . $question_no . '_1';
          $rating = $_POST[$qid];
        } else {
          $qid = 'std' . $question_no . '_' . $question_part;
          if ($correct == 'y' and $score_method != 'Mark per Question') {
            if ($question_part == 1) {
              $rating = $_POST["$qid"];
            } else {
              $rating .= ',' . $_POST["$qid"];
            }
            if ($tmp_method == 'Modified Angoff') {
              $total_rating += $_POST["$qid"];
            }
            if ($_POST["$qid"] != '') $last_question = $question_no;
            $total_parts++;
          } elseif ($correct == 'n' and $score_method != 'Mark per Question') {
            if ($question_part == 1) {
              if (isset($_POST[$qid])) {
                $rating = $_POST[$qid];
              } else {
                $rating = '';
              }
            } else {
              if (isset($_POST[$qid])) {
                $rating .= ',' . $_POST[$qid];
              } else {
                $rating .= ',';
              }
            }
          }
        }
        break;
      case 'matrix':
        // Individual scenarios are separated by '|' characters.
        if ($question_part == 1) {
          $scenarios = 0;
          $matching_scenarios = explode('|', $scenario);
          for ($part_id=0; $part_id<10; $part_id++) {
            if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') $scenarios++;
          }

          for ($part_id=1; $part_id<=$scenarios; $part_id++) {
            $qid = 'std' . $question_no . '_' . $part_id;
            if (isset($_POST["$qid"])) {
              if ($rating == '') {
                $rating = $_POST["$qid"];
              } else {
                $rating .= ',' . $_POST["$qid"];
              }
              if ($tmp_method == 'Modified Angoff') {
                $total_rating += $_POST["$qid"];
              }
              if ($_POST["$qid"] != '') $last_question = $question_no;
            }
            $total_parts++;
          }
        }
        break;
      case 'extmatch':
        // Multimatching is similar to matching except that the separate
        // options are separated by '$' characters.
        if ($question_part == 1) {
          if ($score_method == 'Mark per Question') {
            $qid = 'std' . $question_no . '_1';
            $rating = $_POST["$qid"];
          } else {
            $correct_options = explode('|', $correct);
            $matching_scenarios = explode('|', $scenario);
            $text_scenarios = 0;
            for ($part_id=0; $part_id<10; $part_id++) {
              if (isset($matching_scenarios[$part_id]) and $matching_scenarios[$part_id] != '') $text_scenarios++;
            }

            $matching_media = explode('|', $q_media);
            $media_scenarios = 0;
            for ($part_id=1; $part_id<10; $part_id++) {
              if (isset($matching_media[$part_id]) and $matching_media[$part_id] != '') $media_scenarios++;
            }
            $scenarios = max($text_scenarios, $media_scenarios);
            $part_id = 1;
            $scenario_no = 0;
            for ($scenario_no=0; $scenario_no<$scenarios; $scenario_no++) {
              $correct_answers = explode('$', $correct_options[$scenario_no]);
              $answer_count = count($correct_answers);
              for ($i=1; $i<=$answer_count; $i++) {
                $qid = 'std' . $question_no . '_' . $part_id;
                if ($rating == '') {
                  $rating = $_POST["$qid"];
                } else {
                  $rating .= ',' . $_POST["$qid"];
                }
                if ($tmp_method == 'Modified Angoff') {
                  $total_rating += $_POST["$qid"];
                }
                if ($_POST["$qid"] != '') $last_question = $question_no;
                $total_parts++;
                $part_id++;
              }
            }
          }
        }
        break;
      case 'rank':
        if ($score_method == 'Mark per Question') {
          $qid = 'std' . $question_no . '_1';
          $rating = $_POST["$qid"];
        } else {          
          $qid = 'std' . $question_no . '_' . $question_part;
          $current_rating = (isset($_POST["$qid"])) ? $_POST["$qid"] : '';
          if ($question_part == 1) {
            $rating = $current_rating;
          } else {
            $rating .= ',' . $current_rating;
          }
          if ($current_rating != '') $last_question = $question_no;
          if ($tmp_method == 'Modified Angoff') {
            $total_rating += $current_rating;
          }
        }
        $total_parts++;
        break;
      case 'textbox':
        // NOTE: Cannot standards set with Ebel method.
        if ($tmp_method == 'Modified Angoff') {
          for ($mark_part = $marks_correct; $mark_part > 0; $mark_part--) {
            $qid = 'std' . $question_no . '_' . $mark_part;
            if ($rating == '') {
              $rating = $_POST[$qid];
            } else {
              $rating .= ',' . $_POST[$qid];
            }
            $total_rating += $_POST["$qid"];
            if ($_POST["$qid"] != '') $last_question = $question_no;
            $total_parts++;
          }
        }
        break;
      case 'blank':
        $blank_details = explode('[blank', $option_text);
        $no_answers = count($blank_details) - 1;
        $rating = '';
        for ($i=1; $i<=$no_answers; $i++) {
          $qid = 'std' . $question_no . '_' . $i;
          if(isset($_POST["$qid"])) {
            if ($i == 1) {
              $rating = $_POST["$qid"];
            } else {
              $rating .= ',' . $_POST["$qid"];
            }
            if ($_POST["$qid"] != '') $last_question = $question_no;
            if ($tmp_method == 'Modified Angoff') {
              $total_rating += $_POST["$qid"];
            }
          }
          $total_parts++;
        }
        break;
      case 'labelling':
        $tmp_first_split = explode(';', $correct);
        $tmp_second_split = explode('$', $tmp_first_split[11]);
        for ($label_no = 4; $label_no <= count($tmp_second_split); $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 200) {
            $qid = 'std' . $question_no . '_' . $question_part;
            if (isset($_POST["$qid"])) {
              if ($rating == '') {
                $rating = $_POST["$qid"];
              } else {
                $rating .= ',' . $_POST["$qid"];
              }
              if ($tmp_method == 'Modified Angoff') {
                $total_rating += $_POST["$qid"];
              }
              if ($_POST["$qid"] != '') $last_question = $question_no;
            }
            $total_parts++;
            $question_part++;
          }
        }
        break;
      case 'flash':
        $rating = $_POST["std$question_no"];
        if ($tmp_method == 'Modified Angoff') {
          $total_rating += $_POST["$qid"];
        }
        if ($_POST["$qid"] != '') $last_question = $question_no;
        $total_parts++;
        break;
    }
  }
}                    // End of while loop.
$result->close();

$question_part++;
if ($old_type == 'rank' and $old_score_method == 'Bonus Mark') {
  $qid = 'std' . $question_no . '_' . $question_part;
  if ($rating == '') {
    $rating = $_POST["$qid"];
  } else {
    $rating .= ',' . $_POST["$qid"];
  }
  if ($_POST["$qid"] != '') $last_question = $question_no;
  if ($tmp_method == 'Modified Angoff') {
    $total_rating += $_POST["$qid"];
  }
  $total_parts++;
} elseif ($old_type == 'mrq' and $old_score_method == 'Mark per Question') {
  $qid = 'std' . $question_no . '_1';
  $rating = $_POST["$qid"];
  if ($_POST["$qid"] != '') $last_question = $question_no;
  if ($tmp_method == 'Modified Angoff') {
    $total_rating += $_POST["$qid"];
  }
}

$std_query = $mysqli->prepare("INSERT INTO std_set_questions VALUES (NULL, ?, ?, ?)");
$std_query->bind_param('iis', $std_setID, $log_id, $rating);
$std_query->execute();
$std_query->close();

if (isset($_POST['banksave']) and $_POST['banksave'] == '1') {
  $std_query = "UPDATE questions SET std = ? WHERE q_id = ?";
  $result = $mysqli->prepare($std_query);
  $result->bind_param('si', $rating, $log_id);
  $result->execute();
  $result->close();

  if ($rating != $_POST["old$log_id"]) {
    $old_value = $_POST["old$log_id"];
    $logger->track_change('Edit Question', $log_id, $userObject->get_user_ID(), $old_value, $rating, 'Std Setting');
  }
}

if ($tmp_method == 'Ebel') {
  $id_array = array('EE', 'EI', 'EN', 'ME', 'MI', 'MN', 'HE', 'HI', 'HN', 'EE2', 'EI2', 'EN2', 'ME2', 'MI2', 'MN2', 'HE2', 'HI2', 'HN2');
  
	$std_query = $mysqli->prepare("INSERT INTO ebel VALUES (?, ?, ?)");
	foreach ($id_array as $individualID) {
    if (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '3') {
      if ($individualID == 'EE2' or $individualID == 'EI2' or $individualID == 'EN2' or $individualID == 'ME2' or $individualID == 'MI2' or $individualID == 'MN2' or $individualID == 'HE2' or $individualID == 'HI2' or $individualID == 'HN2') {
        $category = $individualID;
        $percentage = NULL;
      } else {
        $category = $individualID;
        $percentage = $_POST[$individualID];
      }
    } elseif (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '2') {
      if ($individualID == 'EE2' or $individualID == 'EI2' or $individualID == 'EN2' or $individualID == 'ME2' or $individualID == 'MI2' or $individualID == 'MN2' or $individualID == 'HE2' or $individualID == 'HI2' or $individualID == 'HN2') {
        $category = $individualID;
        $percentage = 0;
      } else {
        $category = $individualID;
        $percentage = $_POST[$individualID];
      }
    } else {
      $category = $individualID;
      $percentage = $_POST[$individualID];
    }
    
		$percentage = floatval($percentage);
    $std_query->bind_param('isd', $std_setID, $category, $percentage);
    $std_query->execute();
  }
  $std_query->close();
}

// Alter paper properties
if (isset($_POST['alterpassmark']) and $_POST['alterpassmark'] == 1) {
  if ($tmp_method == 'Angoff (Yes/No)' or $tmp_method = 'Modified Angoff') {
    $pass_mark = round($total_rating / $total_parts);
    $assessment = new assessment($mysqli, $configObject);
    $update_params = array(
        'pass_mark' => array('i', $pass_mark)
    );
    $assessment->db_update_assessment($paperID, $update_params);
  }
}

$module = (isset($_GET['module'])) ? $_GET['module'] : '';
$folder = (isset($_GET['folder'])) ? $_GET['folder'] : '';

if (isset($_POST['continue'])) {
  // Clicking continue does not leave the page, so we need to recalculate the paper marks here.
  $no_reviews = 0;
  $total_mark = $propertyObj->get_total_mark();
  $reviews = get_reviews($mysqli, 'index', $paperID, $total_mark, $no_reviews);
  foreach ($reviews as $review) {
    if ($review['std_setID'] == $std_setID) {
      if ($review['method'] != 'Hofstee') {
        updateDB($review, $mysqli);
      }
    }
  }
  $mysqli->close();
  header("location: individual_review.php?&paperID=$paperID&std_setID=$std_setID&method=" . $_GET['method'] . "&module=$module&folder=$folder#$last_question");
  exit();
} else {
  $mysqli->close();
  header("location: index.php?paperID=$paperID&module=$module&folder=$folder");
  exit();
}
?>
