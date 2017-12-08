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
*
* @author Joseph Baxter
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once dirname(dirname(__DIR__)) . '/include/finish_functions.inc';
require_once dirname(dirname(__DIR__)) . '/plugins/questions/enhancedcalc/helpers/enhancedcalc_helper.php';

/**
 * Class for class_totals functions used in summative exam check test.
 */
class class_totals {

  /**
   * Function to parse marks in display_feedback
   *
   * @param string $data - html from function
   * @return float $mark
   */
  function parseScript($data) {
    if (empty($data)) {
      return false;
    }

    $mark = 0;
    $main_data = explode('<div class="key">', $data);
    $data_line = explode('<tr', $main_data[2]);

    foreach ($data_line as $row) {
      $found = strpos($row, 'Your mark');
      if ($found !== false) {
        $cols = explode('>', $row);

        $parts = explode(' out of', $cols[4]);
        $mark = round($parts[0],1);  // Round it to 1 decimal because this is what Class Totals does.
      }
    }
    return $mark;
  }

  /**
   * Function to get all papers completed in time frame to scrape for marks, and then compare the class_totals and finish reports.
   *
   * @param type $mysqli - database object
   * @param type $username - user for db access
   * @param type $password - the users password
   * @param type $rootpath - root path of site
   * @param type $userid - the user running the script
   * @param type $start_dateSQL - start date range of papers checked
   * @param type $end_dateSQL - end date range of papers checked
   * @param type $server - the server we are checking
   * @param array $string - translation strings
   * @param object $userObject logged in user object
   * @param type $paperid - the papers we want to check (optional, all if not supplied)
   */
  public function process_papers($mysqli, $username, $password, $rootpath, $userid, $start_dateSQL, $end_dateSQL, $server, $string, $userObject, $paperid = '') {
    global $display_correct_answer, $display_students_response, $display_feedback, $display_question_mark;
    $papers = array();

    if ($paperid != '') {
      $result = $mysqli->prepare("SELECT crypt_name, property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE property_id = ?");
      $result->bind_param('i', $paperid);
    } else {
      $result = $mysqli->prepare("SELECT crypt_name, property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE paper_type = '2' AND start_date > $start_dateSQL AND end_date < $end_dateSQL AND deleted IS NULL ORDER BY start_date");
    }
    $result->execute();
    $result->bind_result($crypt_name, $paperID, $title, $display_start_date, $start_date, $end_date);
    while ($result->fetch()) {
      $papers[] = array('crypt_name'=>$crypt_name, 'paperID'=>$paperID, 'title'=>$title, 'display_start_date'=>$display_start_date, 'start_date'=>$start_date, 'end_date'=>$end_date);
    }
    $result->close();

    $paper_no = count($papers);
    $current_no = 0;

    $result = $mysqli->prepare("DELETE FROM class_totals_test_local WHERE user_id = ?");
    $result->bind_param('i', $userid);
    $result->execute();

    $result = $mysqli->prepare("SELECT surname, first_names, username FROM users WHERE id = ? LIMIT 1");
    
    $status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
    $paper_utils = Paper_utils::get_instance();
    // Turn on all feedback if staff and a student exam script is being reviewed.
    $display_correct_answer     = 1;
    $display_question_mark      = 1;
    $display_students_response  = 1;
    $display_feedback           = 1;
    foreach ($papers as $paper) {
      $propertyObj = PaperProperties::get_paper_properties_by_crypt_name($paper['crypt_name'], $mysqli, $string, true);
      // Mark calculation questions.
      if ($propertyObj->unmarked_enhancedcalc(1)) {
        $qids = $propertyObj->get_enhancedcalc_questions(1);
        foreach ($qids as $qid) {
          enhancedcalc_remark('2', $paper['paperID'], $qid, QuestionUtils::get_settings($qid), $mysqli, 'all');
        }
      }
      $report = new ClassTotals(1, 100, 'asc', 0, 'name', $userObject, $propertyObj, $paper['start_date'], $paper['end_date'], '%', '', $mysqli, $string);
      $report->compile_report(false);
      $marks_set = $report->get_user_results();

      $current_no++;

      $insert = $mysqli->prepare("INSERT INTO class_totals_test_local(user_id, paper_id, status) VALUES(?, ?, 'in_progress')");
      $insert->bind_param('ii', $userid, $paper['paperID']);
      $insert->execute();
      $insert->close();

      $errors = '';
      if ($marks_set === false) {
        $marks_set = array();
        $errors = "<ul><li>Couldn't access class totals</li>\n";
      }
      
      foreach ($marks_set as $mark) {
        $overrides = $paper_utils->get_marking_overrides('2', $mark['userID'], $paper['paperID']);
        $log_metadata = new LogMetadata($mark['userID'], $paper['paperID'], $mysqli);
        $log_metadata->get_record();
        ob_start(); // Start output buffering
        display_feedback($propertyObj, $mark['userID'], '2', $userObject, $log_metadata, $mysqli, $status_array, $overrides, null);
        $output = ob_get_contents(); // Store buffer in variable
        ob_end_clean(); // End buffering and clean up

        $script_mark = $this->parseScript($output);

        if ($script_mark === false) {
          if ($errors == '') {
            $errors = '<ul>';
          }
          $errors .= "<li>Couldn't access feedback</li>\n";
        }

        if ($script_mark != $mark['mark']) {
          $result->bind_param('i', $mark['userID']);
          $result->execute();
          $result->store_result();
          $result->bind_result($tmp_surname, $tmp_first_names, $tmp_username);
          $result->fetch();

          if ($errors == '') {
            $errors = '<ul>';
          }
          $errors .= "<li>Problem with " . $mark['userID'] . " $tmp_surname, $tmp_first_names ($tmp_username) - $script_mark / " . $mark['mark'] . "</li>";
        }
      }

        if ($errors != '') {
          $errors .= '</ul>';
          $status = 'failure';
        } else {
          $status = 'success';
        }

        $update = $mysqli->prepare("UPDATE class_totals_test_local SET status = ?, errors = ? WHERE user_id = ? AND paper_id = ?");
        $update->bind_param('ssii', $status, $errors, $userid, $paper['paperID']);
        $update->execute();
        $update->close();
      }
      $result->close();
  }
}
?>
