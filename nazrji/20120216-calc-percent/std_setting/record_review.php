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
  require '../include/errors.inc';
  
  if (isset($_POST['banksave'])) {
    setcookie('caabanksave', $_POST['banksave'], time()+60*60*24*30);
  }
  $paperID = $_POST['paperID'];

  $rating = '';
  $old_leadin = '';
  $old_type = '';
  $old_score_method = '';
  $question_no = 0;
  $question_part = 0;
  $log_id = 0;
  $now = date("Y-m-d H:i:s");
  $total_rating = 0;
  $total_parts = 0;
  $tmp_method = $_POST['method'];

  if (isset($_GET['group']) and $_GET['group'] == 'true' and isset($_POST['review_string']) and $_POST['review_string'] != '') {
    $group_review = $_POST['review_string'];
  } else {
    $group_review = 'No';
  }

  if ($_POST['setterID'] != '') {
    $std_query = "DELETE FROM standards_setting WHERE paperID=$paperID AND setterID=" . $_POST['setterID'] . " AND std_set='" . $_POST['dateID'] . "'";
    if (!$mysqli->query($std_query)) {
      display_error('Error deleting previous settings', $mysqli->error, true, true);
      $mysqli->close();
      exit;
    }
    $std_query = "DELETE FROM ebel WHERE setterID=" . $_POST['setterID'] . " AND date_set='" . $_POST['dateID'] . "'";
    if (!$mysqli->query($std_query)) {
      display_error('Error deleting previous settings', $mysqli->error, true, true);
      $mysqli->close();
      exit;
    }
  }

  $last_question = 0;
  $old_q_id = 0;

  $result = $mysqli->prepare("SELECT q_id, scenario, leadin, q_type, option_text, q_media, correct, score_method, marks_correct, correct FROM papers, questions, options WHERE paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type != 'info' ORDER BY display_pos, id_num");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $scenario, $leadin, $q_type, $option_text, $q_media, $correct, $score_method, $marks_correct, $correct);

  //$questions = $mysqli->query("SELECT q_id, scenario, leadin, q_type, option_text, q_media, correct, score_method, marks_correct, correct FROM papers, questions, options WHERE paper=$paperID AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type != 'info' ORDER BY display_pos, id_num");
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
          if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
          $total_parts++;
        } elseif ($old_type == 'mrq' and $old_score_method == 'Mark per Question') {
          $qid = 'std' . $question_no . '_1';
          $rating = $_POST["$qid"];
          if ($_POST["$qid"] != '') $last_question = $question_no;
          if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
          $total_parts++;
        }

        $std_query = "INSERT INTO standards_setting VALUES (NULL, $userID, $log_id, '$now', '$rating', $paperID, '$tmp_method', '$group_review')";
        if (!$mysqli->query($std_query)) {
          display_error('Error writing to standards_setting table', $mysqli->error, true, true);
          $mysqli->close();
          exit;
        }
        if (isset($_POST['banksave']) and $_POST['banksave'] == '1') {
          $std_query = "UPDATE questions SET std='$rating' WHERE q_id=$log_id";
          if (!$mysqli->query($std_query)) {
            display_error('Error writing to questions table', $mysqli->error, true, true);
            $mysqli->close();
            exit;
          }
          if ($rating != $_POST["old$log_id"]) {
            $std_query = "INSERT INTO track_changes VALUES (NULL, 'Edit Question', $log_id, $userID, '" . $_POST["old$log_id"] . "', '$rating', '$now', 'Std Setting')";
            if (!$mysqli->query($std_query)) {
              display_error('Error writing to track_changes table', $mysqli->error, true, true);
              $mysqli->close();
              exit;
            }
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
        case 'calculation':
        case 'mcq':
        case 'true_false':
          if (isset($_POST["std$question_no"])) {
            $rating = $_POST["std$question_no"];
          } else {
            $rating = '';
          }
          if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
          if (isset($qid) and isset($_POST["$qid"])) $last_question = $question_no;
          $total_parts++;
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
          if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
          if (isset($_POST["$qid"]) and $_POST["$qid"] != '') $last_question = $question_no;
          $total_parts++;
          break;
        case 'hotspot':
          $subparts = explode('|', $correct);
          $no_parts = count($subparts);
          for ($i=1; $i<=$no_parts; $i++) {
            $qid = 'std' . $question_no . '_' . $i;
            if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
            if ($i == 1) {
              if (isset($_POST["$qid"])) {
                $rating = $_POST["$qid"];
              } else {
                $rating = '';
              }
            } else {
              if (isset($_POST["$qid"])) {
                $rating .= ',' . $_POST["$qid"];
              } else {
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
              if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
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
              if(isset($_POST["$qid"])) {
                if ($rating == '') {
                  $rating = $_POST["$qid"];
                } else {
                  $rating .= ',' . $_POST["$qid"];
                }
                if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
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
              $scenarios = max($text_scenarios,$media_scenarios);
              $part_id = 1;
              $scenario_no = 0;
              for ($scenario_no=0; $scenario_no<$scenarios; $scenario_no++) {
                $correct_answers = explode('$',$correct_options[$scenario_no]);
                $answer_count = count($correct_answers);
                for ($i=1; $i<=$answer_count; $i++) {
                  $qid = 'std' . $question_no . '_' . $part_id;
                  if ($rating == '') {
                    $rating = $_POST["$qid"];
                  } else {
                    $rating .= ',' . $_POST["$qid"];
                  }
                  if ($_POST["$qid"] != '') $last_question = $question_no;
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
            if ($tmp_method == 'Modified Angoff') $total_rating += $current_rating;
          }
          $total_parts++;
          break;
        case 'textbox':
          for ($mark_part = $marks_correct; $mark_part > 0; $mark_part--) {
            $qid = 'std' . $question_no . '_' . $mark_part;
            if ($rating == '') {
              $rating = $_POST[$qid];
            } else {
              $rating .= ',' . $_POST[$qid];
            }
            if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
            if ($_POST["$qid"] != '') $last_question = $question_no;
            $total_parts++;
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
              if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
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
              if(isset($_POST["$qid"])) {
                if ($rating == '') {
                  $rating = $_POST["$qid"];
                } else {
                  $rating .= ',' . $_POST["$qid"];
                }
                if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
                if ($_POST["$qid"] != '') $last_question = $question_no;
              }
              $total_parts++;
              $question_part++;
            }
          }
          break;
        case 'flash':
          $rating = $_POST["std$question_no"];
          if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
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
    if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
    $total_parts++;
  } elseif ($old_type == 'mrq' and $old_score_method == 'Mark per Question') {
    $qid = 'std' . $question_no . '_1';
    $rating = $_POST["$qid"];
    if ($_POST["$qid"] != '') $last_question = $question_no;
    if ($tmp_method == 'Modified Angoff') $total_rating += $_POST["$qid"];
  }

  $std_query = "INSERT INTO standards_setting VALUES (NULL,$userID,$log_id,'$now','$rating',$paperID,'$tmp_method','$group_review')";
  if (!$mysqli->query($std_query)) {
    echo "<p>Error writing to standards_setting table: ". $mysqli->error . "</p>\n";
    echo "<p>Query: $std_query</p>\n";
    $mysqli->close();
    exit;
  }
  if (isset($_POST['banksave']) and $_POST['banksave'] == '1') {
    $std_query = "UPDATE questions SET std='$rating' WHERE q_id=$log_id";
    if (!$mysqli->query($std_query)) {
      echo "<p>Error writing to questions table: ". $mysqli->error . "</p>\n";
      echo "<p>Query: $std_query</p>\n";
      $mysqli->close();
      exit;
    }
    if ($rating != $_POST["old$log_id"]) {
      $std_query = "INSERT INTO track_changes VALUES (NULL,'Edit Question',$log_id,$userID,'" . $_POST["old$log_id"] . "','$rating','$now','Std Setting')";
      if (!$mysqli->query($std_query)) {
        echo "<p>Error writing to track_changes: ". $mysqli->error . "</p>\n";
        echo "<p>Query: $std_query</p>\n";
        $mysqli->close();
        exit;
      }
    }
  }

  if ($tmp_method == "Ebel") {
    $id_array = array('EE','EI','EN','ME','MI','MN','HE','HI','HN','EE2','EI2','EN2','ME2','MI2','MN2','HE2','HI2','HN2');
    foreach($id_array as $individualID) {
      if (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '3') {
        if ($individualID == 'EE2' or $individualID == 'EI2' or $individualID == 'EN2' or $individualID == 'ME2' or $individualID == 'MI2' or $individualID == 'MN2' or $individualID == 'HE2' or $individualID == 'HI2' or $individualID == 'HN2') {
          $std_query = "INSERT INTO ebel VALUES (NULL,$userID,'$now','$individualID',NULL)";
        } else {
          $std_query = "INSERT INTO ebel VALUES (NULL,$userID,'$now','$individualID'," . $_POST[$individualID] . ")";
        }
      } elseif (isset($_POST['distinction_type']) and $_POST['distinction_type'] == '2') {
        if ($individualID == 'EE2' or $individualID == 'EI2' or $individualID == 'EN2' or $individualID == 'ME2' or $individualID == 'MI2' or $individualID == 'MN2' or $individualID == 'HE2' or $individualID == 'HI2' or $individualID == 'HN2') {
          $std_query = "INSERT INTO ebel VALUES (NULL,$userID,'$now','$individualID',0)";
        } else {
          $std_query = "INSERT INTO ebel VALUES (NULL,$userID,'$now','$individualID'," . $_POST[$individualID] . ")";
        }
      } else {
        $std_query = "INSERT INTO ebel VALUES (NULL,$userID,'$now','$individualID'," . $_POST[$individualID] . ")";
      }
      if (!$mysqli->query($std_query)) {
        echo "<p>Error writing to ebel table: ". $mysqli->error . "</p>\n";
        echo "<p>Query: $std_query</p>\n";
        $mysqli->close();
        exit;
      }
    }
  }

  // Alter paper properties
  if (isset($_POST['alterpassmark']) and $_POST['alterpassmark'] == 1) {
    if ($tmp_method == 'Angoff (Yes/No)' or $tmp_method = 'Modified Angoff') {
      $pass_mark = round($total_rating/$total_parts);

      $std_query = "UPDATE properties SET pass_mark=$pass_mark WHERE property_id=$paperID";
      if (!$mysqli->query($std_query)) {
        echo "<p>Error updating paper properties: ". $mysqli->error . "</p>\n";
        echo "<p>Query: $std_query</p>\n";
        $mysqli->close();
        exit;
      }
    }
  }

  // Update std set part of paper properties.
  $now = str_replace(' ','',$now);
  $now = str_replace('-','',$now);
  $now = str_replace(':','',$now);
  $new_marking = '2,' . $userID . ',' . $now;
  $old_marking = '2,' . $_POST['setterID'] . ',' . $_POST['dateID'];
  $std_query = $mysqli->prepare("UPDATE properties SET marking=? WHERE property_id=? AND marking=?");
  $std_query->bind_param('sis', $new_marking, $paperID, $old_marking);
  $std_query->execute();
  $std_query->close();

  $module = (isset($_GET['module'])) ? $_GET['module'] : '';
  $folder = (isset($_GET['folder'])) ? $_GET['folder'] : '';
  if (isset($_POST['continue'])) {
    header("location: individual_review.php?&paperID=$paperID&method=" . $_GET['method'] . "&setterID=$userID&dateID=$now&module=$module&folder=$folder#$last_question");
  } else {
    header("location: index.php?paperID=$paperID&module=$module&folder=$folder");
  }
  $mysqli->close();
?>
