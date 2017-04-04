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
* Class total reports, included functions used in:
*
*    class_totals.php
*    class_totals_csv.php
*    class_totals_xml.php
*    assessment_marks.php
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/calculate_marks.inc';
require_once '../include/demo_replace.inc';
require_once '../include/sort.inc';

class ClassTotals {

  private $db;
  private $demo;
  private $user_results;
  private $paper_buffer;
  private $stats;
  private $cohort_size;
  private $absent;
  private $studentsonly;
  private $paperID;
  private $paper_type;
  private $calendar_year;
  private $ss_pass;
  private $ss_hon;
  private $student_cohort;
  private $startdate;
  private $enddate;
  private $exclusions;
  private $moduleID_in;
  private $user_modules;
  private $metadata_array;
  private $late_cohort;
  private $marking;
  private $distinction_score;
  private $pass_mark;
  private $distinction_mark;
  private $question_no;
  private $log_late;
  private $repcourse;
  private $repmodule;
  private $total_marks;
  private $orig_total_marks;
  private $total_random_mark;
  private $display_excluded;
  private $display_experimental;
  private $q_medians;
  private $random_q_ids;
  private $config;
  private $propertyObj;
  private $user_no;
  private $recache;
  private $question_statuses;
  private $marking_overrides;
  private $string;
  private $percent_decimals;
  private $unmarked_enhancedcalc = false;
  private $unmarked_textbox = false;
  private $gradebook_enabled;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  public function __construct($studentsonly, $percent, $ordering, $absent, $sortby, $userObject, $propertyObj, $startdate, $enddate, $repcourse, $repmodule, $db, $string) {
    $this->db                 = $db;
    $this->demo               = is_demo($userObject);
    $this->paperID            = $propertyObj->get_property_id();
    $this->paper_type         = $propertyObj->get_paper_type();
    $this->calendar_year      = $propertyObj->get_calendar_year();
    $this->startdate          = $startdate;
    $this->enddate            = $enddate;
    $this->absent             = $absent;
    $this->studentsonly       = $studentsonly;
    $this->marking            = $propertyObj->get_marking();
    $this->percent            = $percent;
    $this->ordering           = $ordering;
    $this->sortby             = $sortby;
    $this->repcourse          = $repcourse;
    $this->repmodule          = $repmodule;
    $this->pass_mark          = $propertyObj->get_pass_mark();
    $this->distinction_mark   = $propertyObj->get_distinction_mark();
    $this->log_late           = array();
    $this->q_medians          = array();
    $this->random_q_ids       = array();
    $this->config             = Config::get_instance();
    $this->propertyObj        = $propertyObj;
    $this->exclusions         = new Exclusion($this->paperID, $this->db);
    $this->display_excluded   = '';
    $this->user_no            = 0;
    $this->marking_overrides  = array();
    $this->string             = $string;
    $this->percent_decimals   = $this->config->get('percent_decimals');
    $this->gradebook_enabled  = $this->config->get_setting('core', 'cfg_gradebook_enabled');
    $this->question_statuses = QuestionStatus::get_all_statuses($db, array(), true);
  }

  function error_handling($context = null) {
    return error_handling($this);
  }

  public function get_user_results() {
    return $this->user_results;
  }

  public function set_user_results($user_results) {
    $this->user_results = $user_results;
  }

  public function get_paper_buffer() {
    return $this->paper_buffer;
  }

  public function get_stats() {
    return $this->stats;
  }

  public function get_cohort_size() {
    return $this->cohort_size;
  }

  public function get_ss_pass() {
    return $this->ss_pass;
  }

  public function get_ss_hon() {
    return $this->ss_hon;
  }

  public function get_student_cohort() {
    return $this->student_cohort;
  }

  public function get_question_no() {
    return $this->question_no;
  }

  public function get_log_late() {
    return $this->log_late;
  }

  public function get_total_marks() {
    return $this->total_marks;
  }

  public function get_orig_total_marks() {
    return $this->orig_total_marks;
  }

  public function get_total_random_mark() {
    return $this->total_random_mark;
  }

  public function get_display_excluded() {
    return $this->display_excluded;
  }

  public function get_display_experimental() {
    $rval = '';

    foreach ($this->display_experimental as $status => $questions) {
      $rval .= $status . ': ';
      $rval .= implode(', ', $questions) . '<br />';
    }

    return $rval;
  }

  public function get_exclusions() {
    return $this->exclusions;
  }

  public function get_user_no() {
    return $this->user_no;
  }

	/**
	 * Initiates the building of the main Class Totals report.
	 * @param bool $recache - True = will force paper caches to be updated.
     * @param bool $review - If true we are reviewing so should not cache.
	 */
  public function compile_report($recache, $review = false) {
    $results_cache = new ResultsCache($this->db);
    if (!$review and ($recache or $results_cache->should_cache($this->propertyObj, $this->percent, $this->absent))) {
      $this->recache = true;
    } else {
      $this->recache = false;
    }

    $moduleID = Paper_utils::get_modules($this->paperID, $this->db);
    $this->moduleID_in = implode(',', array_keys($moduleID));

    $this->exclusions->load();                                                      // Get any questions to exclude.

    $this->load_answers();

    $this->set_log_late();

    $this->load_absent();

    $this->find_users();                                                            // Get all the users on the module(s) the paper is on.

    $this->load_metadata();                                                         // Query for metadata

    $this->load_overrides();                                                        // Load marking overrides (e.g. Calculation question).

    $this->load_results();                                                          // Load the student data

    $this->adjust_marks();                                                          // Scale marks (random marks or standards setting)

    $this->add_rank();                                                              // Add in rank data.

    $this->convert_moduleIDs();                                                     // Convert Module IDs into codes

    $this->flag_subpart();                                                          // Used to flag subsets of the cohort (i.e. top 33%)

    $this->add_absent_students();                                                   // Add any absent students into main dataset

    $this->generate_stats();                                                        // Generate the main statistics

    $this->add_deciles();                                                           // Add in deciles per student

    $this->sort_results();                                                          // Sort the whole array by the right column

    $this->load_special_needs();                                                    // Load which users have special needs

    if ($this->recache) {
      $results_cache->save_paper_cache($this->paperID, $this->stats);                 // Cache general paper stats

      $results_cache->save_student_mark_cache($this->paperID, $this->user_results);   // Cache student/paper marks

      $results_cache->save_median_question_marks($this->paperID, $this->q_medians);   // Cache the question/paper medians

			// Unset the re-caching flag now we have just cached the marks.
			$this->propertyObj->set_recache_marks(0);
			$this->propertyObj->save();
    }
  }

	/**
	 * Converts a time/date from 20140301103059 into 01/03/2014 10:30.
	 * @param string $original - The date that needs to be convered.
	 */
  public function nicedate($original) {
    return substr($original, 6, 2) . '/' . substr($original, 4, 2) . '/' . substr($original, 0, 4) . ' ' . substr($original, 8, 2) . ':' . substr($original, 10, 2);
  }

    /**
    * Works out if marks need scaling (monkey mark or standards setting) and will apply necessary conversions.
    */
    private function adjust_marks() {
        $user_no = count($this->user_results);

        if ($this->marking == '1') {                              // Monkey mark
            for ($i=0; $i<$user_no; $i++) {
                $this->user_results[$i]['percent'] = (($this->user_results[$i]['mark'] - $this->total_random_mark) / ($this->total_marks - $this->total_random_mark)) * 100;
            }
        } elseif ($this->marking{0} == '2') {                     // Standards Setting
            $this->set_ss_pass();

            for ($i=0; $i<$user_no; $i++) {
                $this->user_results[$i]['percent'] = $this->crankMark($this->user_results[$i]['percent']);
            }
        }
        
        for ($i=0; $i<$user_no; $i++) {
            if (round($this->user_results[$i]['percent'], $this->percent_decimals) < $this->pass_mark) {
                $this->user_results[$i]['classification'] = 'Fail';
            } elseif (round($this->user_results[$i]['percent'], $this->percent_decimals) >= $this->distinction_mark) {
                $this->user_results[$i]['classification'] = 'Distinction';
            } else {
                $this->user_results[$i]['classification'] = 'Pass';
            }
        }
    }

	/**
	 * Load question data for a random question.
	 * @param int $questionID - Question ID of the random question to be loaded.
	 * @return int						- Question ID of the 'original' or parent random question.
	 */
  private function getRandomDetails($questionID) {
    $this->random_q_ids[] = $questionID;

    $this->question_no  = 0;
    $random_questions   = array();
    $old_q_id           = '';
    $old_score_method   = '';
    $old_q_media_width  = '';
    $old_q_media_height = '';
    $old_correct        = array();
    $old_option_text    = array();
    $old_scenario       = '';
    $old_leadin         = '';
    $old_media          = '';
    $old_type           = '';
    $old_score_method   = '';

    $result = $this->db->prepare("SELECT options1.q_id, leadin, scenario, q_media_width, q_media_height, options2.correct, options2.marks_correct, options2.marks_incorrect, options2.option_text, q_type, display_method, score_method, status, settings FROM random_link AS options1, questions LEFT JOIN options AS options2 ON questions.q_id = options2.o_id WHERE options1.q_id = questions.q_id AND options1.id = ?");
    $result->bind_param('i', $questionID);
    $result->execute();
    $result->store_result();
    if ($result->num_rows > 0) {
      $result->bind_result($q_id, $leadin, $scenario, $q_media_width, $q_media_height, $correct, $marks_correct, $marks_incorrect, $option_text, $q_type, $display_method, $score_method, $status, $settings);
      while ($result->fetch()) {
        if ($old_q_id != $q_id and $old_q_id != '') {
          $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
          $random_questions[$old_q_id]['q_id']            = $old_q_id;
          $random_questions[$old_q_id]['q_type']          = $old_q_type;
          $random_questions[$old_q_id]['leadin']          = $old_leadin;
          $random_questions[$old_q_id]['scenario']        = $old_scenario;
          $random_questions[$old_q_id]['correct']         = $old_correct;
          $random_questions[$old_q_id]['display_method']  = $old_display_method;
          $random_questions[$old_q_id]['score_method']    = $old_score_method;
          $random_questions[$old_q_id]['status']          = $old_status;
          $random_questions[$old_q_id]['settings']        = $old_settings;
          $random_questions[$old_q_id]['marks_correct']   = $old_marks_correct;
          $random_questions[$old_q_id]['marks_incorrect'] = $old_marks_incorrect;
          $random_questions[$old_q_id]['random_mark']     = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
          $random_questions[$old_q_id]['option_text']     = $old_option_text;
          $old_correct     = array();
          $old_option_text = array();
          $this->question_no++;
        }
        $old_q_id             = $q_id;
        $old_q_type           = $q_type;
        $old_leadin           = $leadin;
        $old_scenario         = $scenario;
        $old_status           = $status;
        $old_settings         = $settings;
        $old_marks_correct    = $marks_correct;
        $old_marks_incorrect  = $marks_incorrect;
        $old_correct[]        = $correct;
        $old_option_text[]    = $option_text;
        $old_display_method   = $display_method;
        $old_score_method     = $score_method;
        $old_q_media_width    = $q_media_width;
        $old_q_media_height   = $q_media_height;
      }

      // Write out the last question.
      $old_leadin = trim(str_replace('&nbsp;',' ',(strip_tags($old_leadin))));
      $random_questions[$old_q_id]['q_id']            = $old_q_id;
      $random_questions[$old_q_id]['q_type']          = $old_q_type;
      $random_questions[$old_q_id]['leadin']          = $old_leadin;
      $random_questions[$old_q_id]['scenario']        = $old_scenario;
      $random_questions[$old_q_id]['correct']         = $old_correct;
      $random_questions[$old_q_id]['display_method']  = $old_display_method;
      $random_questions[$old_q_id]['score_method']    = $old_score_method;
      $random_questions[$old_q_id]['status']          = $old_status;
      $random_questions[$old_q_id]['settings']        = $old_settings;
      $random_questions[$old_q_id]['marks_correct']   = $old_marks_correct;
      $random_questions[$old_q_id]['marks_incorrect'] = $old_marks_incorrect;
      $random_questions[$old_q_id]['random_mark']     = qRandomMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);
      $random_questions[$old_q_id]['option_text']     = $old_option_text;
    }
    $result->close();

    $this->paper_buffer[$questionID]['random_questions'] = $random_questions;

    return $old_q_id;
  }

  private function random_qMarks($random_questions) {
    $min = 999;
    $max = 0;

    foreach ($random_questions as $individual_question) {
      if ($individual_question['marks'] > $max) $max = $individual_question['marks'];
      if ($individual_question['marks'] < $min) $min = $individual_question['marks'];
    }

    if ($min == $max) {
      return $min;
    } else {
      return 'ERR';
    }
  }

	/**
	 * Builds up a list of excluded questions for display at the bottom of the report.
	 * @param array $exclude - Array of which questions on the paper are excluded.
	 * @param int $q_no			 - ID of the question to be checked.
	 * @param string $q_type - Type of the question to be checked.
	 */
	 private function checkDisplayExcluded($exclude, $q_no, $q_type) {
    $subpart = '';
    $numerals = array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii');

    if ($q_type != 'mrq' and $q_type != 'rank' and strlen($exclude) > 1) {
      for ($i=0; $i<strlen($exclude); $i++) {
        if ($exclude{$i} == '1') {
          if ($subpart == '') {
            if ($q_type == 'extmatch') {
              $subpart = $numerals[$i];
            } else {
              $subpart = chr($i+97);
            }
          } else {
            $subpart .= ', Q' . $q_no . chr($i+97);
          }
        }
      }
    }

    if (strpos($exclude, '1') !== false) {
      if ($this->display_excluded == '') {
        $this->display_excluded = $q_no . $subpart;
      } else {
        $this->display_excluded .= ', ' . $q_no . $subpart;
      }
    }
  }

	/**
	 * Builds up a list of questions with non-marked (i.e. experimental) status for display at the bottom of the report.
	 * @param int $q_no			 - ID of the question to be checked.
	 * @param string $status - Name of the status to be checked.
	 */
	 private function displayExperimental($q_no, $status) {
    if (!isset($this->display_experimental[$status])) {
      $this->display_experimental[$status] = array('Q' . $q_no);
    } else {
      $this->display_experimental[$status][] = 'Q' . $q_no;
    }
  }

	/**
	 * Calculates what the Standard Setting pass mark is.
	 */
  private function set_ss_pass() {
    $mark_parts = explode(',', $this->marking);

    $standard_setting = new StandardSetting($this->db);
    $percents = $standard_setting->get_pass_distinction($mark_parts[1]);

    $this->ss_pass = $percents['pass_score'];

    if (is_null($percents['distinction_score'])) {
			$this->ss_hon = 100;
		} elseif ($percents['distinction_score'] == '0.000000') {   // If zero set to top 20% of cohort performance.
      $this->set_ss_hon();
    } else {
			$this->ss_hon = $percents['distinction_score'];
    }
  }

	/**
	 * Sets the Standard Setting honours mark to the top 20 percentile of student performance on the paper.
	 */
  private function set_ss_hon() {
    $user_no = count($this->user_results);

    $marks_data = array();
    for ($i=0; $i<$user_no; $i++) {
      if (isset($this->user_results[$i]['percent'])) $marks_data[] = $this->user_results[$i]['percent'];
    }

    $this->ss_hon = MathsUtils::percentile($marks_data, 0.2);
  }

	/**
	 * Converts 'cranks' a percentage using either a pass and honours mark or just a pass mark.
	 * @param int $percent	 - The percentage to be converted.
	 * @return int           - The cranked value.
	 */
	 private function crankMark($percent) {
    if ($this->ss_hon > 0 and $this->ss_hon < 100) {    // Two point cranking
      if ($percent < $this->ss_pass) {
        $cranked = $this->pass_mark  - ($this->pass_mark - 0) * (($this->ss_pass - $percent) / ($this->ss_pass - 0));
      } elseif ($percent >= $this->ss_pass and $percent < $this->ss_hon) {
        $cranked = $this->distinction_mark - ($this->distinction_mark - $this->pass_mark) * (($this->ss_hon - $percent) / ($this->ss_hon - $this->ss_pass));
      } else {
        $cranked = 100 - (100 - $this->distinction_mark) * ((100 - $percent) / (100 - $this->ss_hon));
      }
    } else {
      if ($percent < $this->ss_pass) {                 // One point cranking
        $cranked = $this->pass_mark  - ($this->pass_mark - 0) * (($this->ss_pass - $percent) / ($this->ss_pass - 0));
      } else {
        $cranked = 100 - (100 - $this->pass_mark) * ((100 - $percent) / (100 - $this->ss_pass));
      }
    }
    return $cranked;
  }

	/**
	 * Builds up a master array 'user_results' of data about users.
	 * @param int $user_number					- ID from log_metadata table
	 * @param int $tmp_user_mark	 			- Mark of the user
	 * @param int $tmp_user_mark_array	- Array of individual question marks
	 * @param int $tmp_user_duration	 	- Total paper duration in seconds
	 * @param int $marking_comp					- 1 = marking is complete, 0 = marking incomplete (i.e. waiting on textbox or calculation question)
	 */
  private function writeUserResults($user_number, $tmp_user_mark, $tmp_user_mark_array, $tmp_user_duration, $marking_comp) {
    $this->user_results[$user_number]['mark'] = round($tmp_user_mark, 1);
    $this->user_results[$user_number]['mark_array'] = $tmp_user_mark_array;
    if ($this->total_marks == 0) {
      $this->user_results[$user_number]['percent'] = 0;
    } else {
      $this->user_results[$user_number]['percent'] = ($tmp_user_mark / $this->total_marks) * 100;
    }
    $this->user_results[$user_number]['duration']         = $tmp_user_duration;
    $this->user_results[$user_number]['marking_complete'] = $marking_comp;
    $this->user_results[$user_number]['visible']          = true;    // Default to visible unless switched off below.

    // Add metadata
    $userID = $this->user_results[$user_number]['userID'];
    if (!empty($this->metadata_array['types'])) {
      foreach ($this->metadata_array['types'] as $type) {
        if (isset($this->metadata_array['students'][$userID][$type]) ) {
          $this->user_results[$user_number]['meta_' . $type] = $this->metadata_array['students'][$userID][$type];
        } else {
          $this->user_results[$user_number]['meta_' . $type] = '';
        }
      }
    }

    // Check metadata exclusions
    $i = 1;
    while (isset($_GET["meta$i"])) {
      $meta_parts = explode('=', $_GET["meta$i"]);
      if ($meta_parts[1] != '%') {
        if ($this->user_results[$user_number][$meta_parts[0]] != $meta_parts[1]) {
          $this->user_results[$user_number]['visible'] = false;
        }
      }
      $i++;
    }

    if (isset($this->student_cohort)) {
      $this->check_and_clear_cohort($this->user_results[$user_number]['username']);
    }
  }

  private function find_random_question($q_id, &$tmp_q) {
    $tmp_q_id = -1;

    $randomIDs = $this->random_q_ids;
    foreach ($randomIDs as $rnd_id) {
      if (isset($this->paper_buffer[$rnd_id]) and isset($this->paper_buffer[$rnd_id]['random_questions']) and count($this->paper_buffer[$rnd_id]['random_questions']) > 0) {
        if (in_array($q_id, array_keys($this->paper_buffer[$rnd_id]['random_questions']))) {
          $tmp_q_id = $q_id;
          $tmp_q = $this->paper_buffer[$rnd_id]['random_questions'][$q_id];
        }
      }
    }

    return $tmp_q_id;
  }

	/**
	 * Parses a Fill-in-th-Blank question and returns an array of correct answers.
	 * @param string $option_text	- Original text of the question with all the blanks in.
	 * @return array - correct answers.
	 */
  private function extract_blank_correct($option_text) {
    $correct = array();

    $not_used = preg_match('/mark="([0-9]{1,3})"/', $option_text, $results);
    $blank_details = explode("[blank", $option_text);
    $no_answers = count($blank_details) - 1;
    for ($i=1; $i<=$no_answers; $i++) {
      $blank_details[$i] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$blank_details[$i]);
      $blank_details[$i] = preg_replace("| size=\"([0-9]{1,3})\"|","",$blank_details[$i]);
      $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
      $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));

      $answer_list = explode(',', $blank_details[$i]);
      $answer_list[0] = str_replace("[/blank]", '', $answer_list[0]);
      foreach ($answer_list as $individual_answer) {
        $correct[$i-1][] = html_entity_decode(trim($individual_answer));
      }
    }

    return $correct;
  }

	/**
	 * Return the marks for a user/answer taking into account question exclusions.
	 * @param int $q_id									- ID of the question being marked.
	 * @param int $userID								- ID of the user (student)
	 * @param int $tmp_user_answer			- Answer of the user.
	 * @param int $tmp_user_mark				- The 'original' mark as stored in logX.
	 * @param int $tmp_user_mark_array	- An array of marks for all the questions on the paper.
	 * @return int - The mark the user got for the question.
	 */
  private function getUserMark($q_id, $userID, $tmp_user_answer, $tmp_user_mark, &$tmp_user_mark_array) {
    $tmp_mark = 0;

    $tmp_exclude = $this->exclusions->get_exclusions_by_qid($q_id);

    $multi_part_qns = array('extmatch'=>1, 'matrix'=>1, 'blank'=>1, 'dichotomous'=>1, 'enhancedcalc'=>1, 'labelling'=>1, 'hotspot'=>1);

    $skip_random = false;
    if (!isset($this->paper_buffer[$q_id])) {
      $tmp_q = array();
      $tmp_q_id = $this->find_random_question($q_id, $tmp_q);
      if ($tmp_q_id == -1) {
        $skip_random = true;
      } else {
        $question = $tmp_q;
        if ($question['q_type'] == 'blank') {
          $question['correct'] = $this->extract_blank_correct($question['option_text'][0]);
        }
      }
    } else {
      $question = $this->paper_buffer[$q_id];
    }

		if (isset($question['status'])) {
			$curr_status = $this->question_statuses[$question['status']];
			if (!is_null($curr_status) and $curr_status->get_exclude_marking()) {
				$tmp_exclude = '1111111111111111111111111111111111111111';
			}
		}

    if (!$skip_random and $question['score_method'] != 'Mark per Question' and isset($multi_part_qns[$question['q_type']])) {
      if ($question['q_type'] == 'extmatch' or $question['q_type'] == 'matrix') {
        if (!isset($tmp_user_mark_array[$q_id])) $tmp_user_mark_array[$q_id] = array();
        $paper_answers = explode('|', $question['correct'][0]);
        $user_answers = explode('|', $tmp_user_answer);
        $section = 0;
        $count_paper_answers = count($paper_answers);
        for ($a=0; $a<$count_paper_answers; $a++) {
          if ($paper_answers[$a] != '') {
            $answers_correct = 0;
            $answers_incorrect = 0;
            if ($question['q_type'] == 'extmatch') {
              $sub_paper_answers = explode('$', $paper_answers[$a]);
              if (isset($user_answers[$a])) {
                $sub_user_answers = explode('$', $user_answers[$a]);
              } else {
                $sub_user_answers = array();
              }
              $exclude_on = true;
              $count_sub_paper_answers = count($sub_paper_answers);
              for ($c = 0; $c < $count_sub_paper_answers; $c++) {
                if (isset($tmp_exclude{$section}) and $tmp_exclude{$section} == '0') {
                  if (!isset($sub_user_answers[$c]) or $sub_user_answers[$c] == '' or $sub_user_answers[$c] == 'u') {
                    // Do nothing
                  } elseif (in_array($sub_user_answers[$c], $sub_paper_answers)) {
                    $answers_correct++;
                  } else {
                    $answers_incorrect++;
                  }
                  $exclude_on = false;
                }
                $section++;
              }

              $section_mark = ($answers_correct * $question['marks_correct']) + ($answers_incorrect * $question['marks_incorrect']);
              $tmp_mark += $section_mark;
              // This is excluding whole question parts (not answers/marks).
              if ($exclude_on == false) $tmp_user_mark_array[$q_id][] = $section_mark;
            } else {   // Matrix
              if ($tmp_exclude{$a} == '0') {
                if ($paper_answers[$a] == $user_answers[$a]) {
                  $tmp_mark += $question['marks_correct'];
                  $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
                } elseif ($user_answers[$a] != 'u' and $user_answers[$a] != '') {
                  $tmp_mark += $question['marks_incorrect'];
                  $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
                } else {
                  $tmp_user_mark_array[$q_id][] = 0;
                }
              }
            }
          }
        }
      } elseif ($question['q_type'] == 'blank') {
        $user_answers = json_decode($tmp_user_answer);
        $count_user_answer = count($user_answers);
        for ($a=0; $a<$count_user_answer; $a++) {
          if ($tmp_exclude{$a} == '0') {
            if ($question['display_method'] == 'dropdown') {
              $student_answer = html_entity_decode(trim(str_replace('&nbsp;', ' ', $question['correct'][$a][0])));
              $correct_answer = html_entity_decode(trim(str_replace('&nbsp;', ' ', $user_answers[$a])));

              if ($student_answer == $correct_answer) {
                $tmp_mark += $question['marks_correct'];
                $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
              } elseif ($user_answers[$a] != 'u' and $user_answers[$a] != '') {
                $tmp_mark += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
              } else {
                $tmp_user_mark_array[$q_id][] = 0;
              }
            } else {
              $match = false;
              if (isset($question['correct'][$a])) {
                foreach ($question['correct'][$a] as $correct_alternative) {
                  if (strtolower(trim($user_answers[$a])) == strtolower(trim($correct_alternative))) {
                    $match = true;
                  }
                }
              }

              if ($match == true) {
                $tmp_mark += $question['marks_correct'];
                $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
              } elseif ($user_answers[$a] != 'u' and $user_answers[$a] != '') {
                $tmp_mark += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
              } else {
                $tmp_user_mark_array[$q_id][] = 0;
              }
            }
          }
        }
      } elseif ($question['q_type'] == 'dichotomous') {
        $count_question_correct = count($question['correct']);
        for ($a=0; $a<$count_question_correct; $a++) {
          if ($tmp_exclude{$a} == '0') {
            if ($question['correct'][$a] == $tmp_user_answer{$a}) {
              $tmp_mark += $question['marks_correct'];
              $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
            } else {
              if ($tmp_user_answer{$a} == 'a' or $tmp_user_answer{$a} == 'u') {
                $tmp_user_mark_array[$q_id][] = 0;
              } else {
                $tmp_mark += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
              }
            }
          }
        }
      } elseif ($question['q_type'] == 'enhancedcalc') {
        if ($tmp_exclude{0} == '0') {
          $settings = json_decode($question['settings'], true);

          if (isset($this->marking_overrides[$q_id][$userID])) {
            $new_mark_type = $this->marking_overrides[$q_id][$userID];

            if ($new_mark_type == 'correct') {
              $tmp_mark += $settings['marks_correct'];
              $tmp_user_mark_array[$q_id][] = $settings['marks_correct'];
            } elseif ($new_mark_type == 'partial') {
              $tmp_mark += $settings['marks_partial'];
              $tmp_user_mark_array[$q_id][] = $settings['marks_partial'];
            } else {
              $tmp_mark += $settings['marks_incorrect'];
              $tmp_user_mark_array[$q_id][] = $settings['marks_incorrect'];
            }
          } else {
            $tmp_mark += $tmp_user_mark;
            $tmp_user_mark_array[$q_id][] = $tmp_user_mark;
          }
        }
      } elseif ($question['q_type'] == 'hotspot') {
        $question_parts = explode('|', $question['correct'][0]);
        $user_answers = explode('|', $tmp_user_answer);
        $count_question_parts = count($question_parts);
        for ($i=0; $i<$count_question_parts; $i++) {
          if ($tmp_exclude{$i} == '0') {
            if (isset($user_answers[$i]) and substr($user_answers[$i], 0, 1) == '1') {
              $tmp_mark += $question['marks_correct'];
              $tmp_user_mark_array[$q_id][] = $question['marks_correct'];
            } elseif (isset($user_answers[$i]) and substr($user_answers[$i], 0, 1) == '0') {
              $tmp_mark += $question['marks_incorrect'];
              $tmp_user_mark_array[$q_id][] = $question['marks_incorrect'];
            } else {
              $tmp_user_mark_array[$q_id][] = 0;
            }
          }
        }
      } elseif ($question['q_type'] == 'labelling') {
        $correct_labels = array();

        $tmp_first_split = explode(';', $question['correct'][0]);
        $tmp_second_split = explode('$', $tmp_first_split[11]);

        $label_count = 0;
        $placeholders = 0;
        $i = 0;
        $excluded_no = 0;
        $count_tmp_second_split = count($tmp_second_split);
        for ($label_no = 4; $label_no <= $count_tmp_second_split; $label_no += 4) {
          if (substr($tmp_second_split[$label_no],0,1) != '|') $label_count++;
          if (substr($tmp_second_split[$label_no],0,1) != '|' and $tmp_second_split[$label_no-2] > 219) {
            $x = round($tmp_second_split[$label_no-2]);
            $y = round($tmp_second_split[$label_no-1]) - 25;
            $correct_labels[$x . 'x' . $y] = substr($tmp_second_split[$label_no], 0, strpos($tmp_second_split[$label_no],'|'));
            if ($tmp_exclude{$i} == '0') {
              $placeholders++;
            } else {
              $excluded_no++;
            }
            $i++;
          }
        }

        // Strip out width and height for graphical labels.
        $i = 0;
        foreach ($correct_labels as $key=>$value) {
          $tmp_parts = explode('~', $value);
          $correct_labels[$key] = $tmp_parts[0];
          $correct_labels_pos[$key] = $i++;
        }

        if ($tmp_user_answer != '') {
          $user_split1 = explode(';', $tmp_user_answer);
          $user_split2 = explode('$', $user_split1[1]);

          $i = 0;
          $correct = 0;
          $count_user_split2 = count($user_split2)-3;
          for ($a=0; $a<$count_user_split2; $a+=4) {
            $x = round($user_split2[$a]);
            $y = round($user_split2[$a+1]);
            $index = 0;
            if (isset($correct_labels[$x . 'x' . $y])) $index = $correct_labels_pos[$x . 'x' . $y];
            if ($tmp_exclude{$index} == '0') {
              if (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] == $user_split2[$a+2]) {
                $tmp_mark += $question['marks_correct'];
                $correct += $question['marks_correct'];
                $tmp_user_mark_array[$q_id][$index] = $question['marks_correct'];
              } elseif (isset($correct_labels[$x . 'x' . $y]) and $correct_labels[$x . 'x' . $y] != $user_split2[$a+2]) {
                $tmp_mark += $question['marks_incorrect'];
                $correct += $question['marks_incorrect'];
                $tmp_user_mark_array[$q_id][$index] = $question['marks_incorrect'];
              } else {
                if (isset($index) && $index!='') $tmp_user_mark_array[$q_id][$index] = 0;
              }
              $i++;
            }
          }
        }

        if (isset($tmp_user_mark_array[$q_id])) ksort($tmp_user_mark_array[$q_id]);
        //create shortened array
        $i = 0;
        $correct_labels_exc = array();
        foreach ($correct_labels as $cli => $clv) {
          if ($tmp_exclude{$i++} == '0') $correct_labels_exc[$cli] = $clv;
        }
        // If the user has not answered the question pad out with zero marks.
        if (isset($tmp_user_mark_array[$q_id])) {
          $user_answer_no = count($tmp_user_mark_array[$q_id]);
        } else {
          $user_answer_no = 0;
        }
        if ($user_answer_no < count($correct_labels_exc)) {
          for ($i=$user_answer_no; $i<count($correct_labels_exc); $i++) {
            $tmp_user_mark_array[$q_id][] = 0;
          }
        }

        $this->paper_buffer[$q_id]['correct_labels'] = $correct_labels_exc;
      }

    } else {
      // Marking per Question, or all other question types, simply return the original mark.
      if ($tmp_exclude{0} == '0') {
        $round_tmp_user_mark = round($tmp_user_mark, 2);
        $tmp_mark += $round_tmp_user_mark;
        $tmp_user_mark_array[$q_id] = $round_tmp_user_mark;
      }

    }

    $this->q_medians[$q_id][] = $tmp_mark;

    return $tmp_mark;
  }

	/**
	 * Formats a number of seconds as hours, minutes and seconds.
	 * @param int $seconds - Number of seconds to be converted.
	 * @return string  	 	 - Seconds formatted into hour, minutes and seconds.
	 */
	public function formatsec($seconds) {
    $diff_hour = ($seconds / 60) / 60;
    $tmp_position = strpos($diff_hour, '.');
    if ($tmp_position > 0) $diff_hour = substr($diff_hour, 0, $tmp_position);
    if ($diff_hour > 0) $seconds -= ($diff_hour * 60) * 60;
    $diff_min = $seconds / 60;
    $tmp_position = strpos($diff_min, '.');
    if ($tmp_position > 0) $diff_min = substr($diff_min, 0, $tmp_position);
    if ($diff_min > 0) $seconds -= $diff_min * 60;
    $diff_sec = $seconds;
    $timestring = '';
    if ($diff_hour < 10) $timestring = '0';
    $timestring .= "$diff_hour:";
    if ($diff_min < 10) $timestring .= '0';
    $timestring .= "$diff_min:";
    if ($diff_sec < 10) $timestring .= '0';
    $timestring .= $diff_sec;

    return $timestring;
  }

  /**
   * Check if a record exists in the student cohort array created when showing absent candidates. If found, remove the entry
   * @param array $student_cohort - All (remaining) students in the cohort
   * @param string $username      - Username to look for
   * @return array                - Cohort array with current user removed if found
   */
  private function check_and_clear_cohort($username) {
    $tmp_cohort_size = count($this->student_cohort);
    for ($i=0; $i < $tmp_cohort_size; $i++) {
      if ($this->student_cohort[$i]['username'] == $username) {
        unset($this->student_cohort[$i]);
        break;
      }
    }
    $this->student_cohort = array_values($this->student_cohort);
  }

  /**
   * Creates an array called 'paper_buffer' which contains all the questions on the paper.
   * @return bool - If no questions are found will return FALSE.
   */
  public function load_answers() {
    $question_no      = 0;
    $old_q_id         = 0;
    $old_correct      = array();
    $old_option_text  = array();

    $this->total_marks = 0;
    $this->orig_total_marks = 0;
    $this->total_random_mark = 0;
    $this->display_excluded = '';
    $this->display_experimental = array();

    // Load the correct answers into 'paper_buffer' array.
    $result = $this->db->prepare("SELECT q_id, marks_correct, marks_incorrect, display_method, score_method, q_media_height, q_media_width, q_type, correct, score_method, option_text, status, display_pos, settings FROM (papers, questions) LEFT JOIN options ON questions.q_id = options.o_id WHERE papers.question = questions.q_id AND papers.paper = ? AND q_type != 'info' ORDER BY screen, display_pos, id_num");
    $result->bind_param('i', $this->paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id, $marks_correct, $marks_incorrect, $display_method, $score_method, $q_media_height, $q_media_width, $q_type, $correct, $score_method, $option_text, $status, $display_pos, $settings);
    while ($result->fetch()) {
      if ($q_id != $old_q_id or $old_display_pos != $display_pos) {
        if ($old_q_id != 0) {
          $old_status_obj = $this->question_statuses[$old_status];

          if (!$old_status_obj->get_exclude_marking()) {
            $tmp_exclude = $this->exclusions->get_exclusions_by_qid($old_q_id);

						if ($old_q_type == 'random') {
              $tmp_id = $this->getRandomDetails($old_q_id);

              $last_random = $this->paper_buffer[$old_q_id]['random_questions'][$tmp_id];

              $old_q_type 				= $last_random['q_type'];
              $old_marks_correct 	= $this->get_marks_correct($last_random['q_type'], $last_random['marks_correct'], $last_random['settings']);
              $old_option_text 		= $last_random['option_text'];
              $old_correct 				= $last_random['correct'];
              $old_display_method = $last_random['display_method'];
              $old_score_method 	= $last_random['score_method'];

							$this->total_marks				+= qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
              $this->orig_total_marks		+= qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
            } else {
              $this->total_marks				+= qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
              $this->orig_total_marks		+= qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
              $this->total_random_mark	+= qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);

							$this->checkDisplayExcluded($tmp_exclude, $question_no, $old_q_type);
            }
          } else {
            $this->displayExperimental($question_no, $old_status_obj->get_name());
          }
          $old_marks_correct = 0;
        }
        $question_no++;
        $option_no        = 0;
        $old_correct      = array();
        $old_option_text  = array();
        $question_id      = $q_id;
        $this->paper_buffer[$question_id]['q_type']           = $q_type;
        $this->paper_buffer[$question_id]['score_method']     = $score_method;
        $this->paper_buffer[$question_id]['display_method']   = $display_method;
        $this->paper_buffer[$question_id]['marks_correct']    = $this->get_marks_correct($q_type, $marks_correct, $settings);
        $this->paper_buffer[$question_id]['marks_incorrect']  = $marks_incorrect;
        $this->paper_buffer[$question_id]['status']           = $status;
        $this->paper_buffer[$question_id]['settings']         = $settings;

        if ($q_type == 'blank') {
          $this->paper_buffer[$question_id]['correct'] = $this->extract_blank_correct($option_text);
        } else {
          $this->paper_buffer[$question_id]['correct'][$option_no] = $correct;
        }
      } else {
        $this->paper_buffer[$question_id]['correct'][$option_no] = $correct;
      }
      $option_no++;

      $old_q_id             = $q_id;
      $old_display_pos      = $display_pos;
      $old_q_type           = $q_type;
      $old_display_method   = $display_method;
      $old_score_method     = $score_method;
      $old_correct[]        = $correct;
      $old_q_media_width    = $q_media_width;
      $old_q_media_height   = $q_media_height;
      $old_option_text[]    = $option_text;
      $old_marks_correct    = $this->get_marks_correct($q_type, $marks_correct, $settings);
      $old_marks_incorrect  = $marks_incorrect;
      $old_status           = $status;
      $old_settings         = $settings;
    }
    $result->close();

		if ($this->paper_buffer === null) {   // There are no questions on the paper.
		  return false;
		}

    $old_status_obj = $this->question_statuses[$old_status];
    if (!$old_status_obj->get_exclude_marking()) {
      $tmp_exclude = $this->exclusions->get_exclusions_by_qid($old_q_id);

      if ($old_q_type == 'random') {
        $tmp_id = $this->getRandomDetails($old_q_id);

        $last_random = $this->paper_buffer[$old_q_id]['random_questions'][$tmp_id];

        $old_q_type					= $last_random['q_type'];
        $old_marks_correct	= $this->get_marks_correct($last_random['q_type'], $last_random['marks_correct'], $last_random['settings']);
        $old_option_text		= $last_random['option_text'];
        $old_correct				= $last_random['correct'];
        $old_display_method = $last_random['display_method'];
        $old_score_method		= $last_random['score_method'];

				$this->total_marks				+= qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $this->orig_total_marks		+= qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
      } else {
        $this->total_marks				+= qMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $this->orig_total_marks		+= qMarks($old_q_type, '', $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method);
        $this->total_random_mark	+= qRandomMarks($old_q_type, $tmp_exclude, $old_marks_correct, $old_option_text, $old_correct, $old_display_method, $old_score_method, $old_q_media_width, $old_q_media_height);

				$this->checkDisplayExcluded($tmp_exclude, $question_no, $old_q_type);
      }
    } else {
      $this->displayExperimental($question_no, $old_status_obj->get_name());
    }

    $this->question_no = $question_no;
  }

  /**
   * Build up an array of any users with records in log_late. Used later to display warnings to staff through the UI.
   */
  private function set_log_late() {
    // Check log_late for any records
    $late_ts = strtotime($this->enddate) + 7200;
    $late_end = date('Y-m-d H:i:s', $late_ts);

    $result = $this->db->prepare("SELECT DISTINCT metadataID, userID, title, surname, first_names, DATE_FORMAT(started, '" . $this->config->get('cfg_long_date_time') . "') AS display_started, started FROM log_late, log_metadata, users WHERE log_late.metadataID = log_metadata.id AND log_metadata.userID = users.id AND paperID = ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? ORDER BY surname, initials");
    $result->bind_param('iss', $this->paperID, $this->startdate, $late_end);
    $result->execute();
    $result->bind_result($metadataID, $userID, $title, $surname, $first_names, $display_started, $started);
    while ($result->fetch()) {
      $this->log_late[$metadataID] = $title . ' ' .  $surname . ', ' . $first_names;
    }
    $result->close();
  }

  /**
   * Work out which students are on the module of the paper but who have not take it.
   */
	 private function load_absent() {
    if ($this->absent == 1) {
      // Get students in the cohort.

      $this->student_cohort = array();
      $i = 0;

      $sql = "SELECT DISTINCT
                             users.id
                           , users.username
                           , title
                           , surname
                           , first_names
                           , initials
                           , student_id
                           , grade
                           , moduleid
                           , gender
              FROM
                    (modules_student, modules, users, sid)
              WHERE
                    modules_student.userID = sid.userID
              AND
                    modules_student.userID = users.id
              AND
                    modules_student.idMod = modules.id
              AND
                    users.roles
              LIKE
                    '%Student%'
              AND
                    idMod
              IN
                    ($this->moduleID_in)
              AND
                    calendar_year = ?";

      $result = $this->db->prepare($sql);
      $result->bind_param('s', $this->calendar_year);
      $result->execute();
      $result->bind_result($userID, $tmp_username, $title, $surname, $first_names, $initials, $student_id, $grade, $moduleid, $gender);
      while ($result->fetch()) {
        $this->student_cohort[$i]['username']      = $tmp_username;
        $this->student_cohort[$i]['userID']        = $userID;
        $this->student_cohort[$i]['name']          = trim(str_replace("'","",$surname) . ',' . $first_names);
        $this->student_cohort[$i]['title']         = $title;
        $this->student_cohort[$i]['surname']       = demo_replace($surname, $this->demo);
        $this->student_cohort[$i]['first_names']   = demo_replace($first_names, $this->demo);
        $this->student_cohort[$i]['initials']      = demo_replace($initials, $this->demo);
        $this->student_cohort[$i]['student_id']    = demo_replace_number($student_id, $this->demo);
        $this->student_cohort[$i]['student_grade'] = $grade;
        $this->student_cohort[$i]['module']        = $moduleid;
        $this->student_cohort[$i]['gender']        = $gender;

        $i++;
      }
      $result->close();
    }
  }

  /**
   * Get all the users on the module(s) the paper is on.
   */
  private function find_users() {
    $this->user_modules = array();
    if ($this->repmodule != '') {
      $tmp_moduleID_in = $this->repmodule;
    } else {
      $tmp_moduleID_in = $this->moduleID_in;
    }

    if ($this->calendar_year == '') {
      $mod_query = $this->db->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in)");
    } else {
      $mod_query = $this->db->prepare("SELECT modules_student.idMod, userID, moduleID FROM modules_student, modules WHERE modules_student.idMod = modules.id AND idMod IN ($tmp_moduleID_in) AND calendar_year = ?");
      $mod_query->bind_param('s', $this->calendar_year);
    }
    $mod_query->execute();
    $mod_query->bind_result($idMod, $userID, $tmp_moduleid);
    $mod_query->store_result();
    while ($mod_query->fetch()) {
      $this->user_modules[$userID]['idMod'] = $idMod;
    }
    $mod_query->close();
  }

  /**
   * Load user (student) metadata which will be output on-screen later.
   */
  private function load_metadata() {
    $this->metadata_array = array();

    if ($this->calendar_year == '') {
      $stmt = $this->db->prepare("SELECT userID, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN ($this->moduleID_in)");
    } else {
      $stmt = $this->db->prepare("SELECT userID, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN ($this->moduleID_in) AND calendar_year = ?");
      $stmt->bind_param('s', $this->calendar_year);
    }
    $stmt->execute();
    $stmt->bind_result($student_userID, $type, $value);
    while ($stmt->fetch()) {
      $this->metadata_array['students'][$student_userID][$type] = $value;
      $this->metadata_array['types'][$type] = $type;
    }
    $stmt->close();
  }

  /**
   * Load marking overrides (for example, Calculation question).
   */
  private function load_overrides() {
    $result = $this->db->prepare("SELECT q_id, user_id, new_mark_type FROM marking_override WHERE paper_id = ?");
    $result->bind_param('i', $this->paperID);
    $result->execute();
    $result->bind_result($q_id, $user_id, $new_mark_type);
    while ($result->fetch()) {
      $this->marking_overrides[$q_id][$user_id] = $new_mark_type;
    }
    $result->close();
  }

  /**
   * Load user data from the log tables and store.
	 * Calls 'writeUserResults' to write into an array.
   */
  private function load_results() {
    if ($this->studentsonly == 0) {
      $roles_sql = '';
    } else {
      $roles_sql = " AND (users.roles = 'Student' OR users.roles = 'graduate')";
    }

    $data_array = array();
    $metadataids = array();

    // Load started records from 'log_metadata'.
    if ($this->paper_type == '2') {
      $time_int = 2;
    } else {
      $time_int = 0;
    }
    $result = $this->db->prepare("SELECT
                                    log_metadata.id,
                                    users.id,
                                    username,
                                    roles,
                                    year,
                                    title,
                                    surname,
                                    initials,
                                    first_names,
                                    email,
                                    gender,
                                    ipaddress,
                                    lab_name,
                                    student_id,
                                    attempt,
                                    DATE_FORMAT(started, '{$this->config->get('cfg_long_date_time')}') AS display_started,
                                    started,
                                    student_grade
                                  FROM
                                    log_metadata,
                                    users
                                  LEFT JOIN
                                    sid
                                  ON
                                    users.id = sid.userID
                                  WHERE
                                    log_metadata.userID = users.id AND
                                    paperID = ? AND
                                    grade LIKE ? $roles_sql AND
                                    DATE_ADD(started, INTERVAL $time_int MINUTE) >= ?
                                    AND started <= ?");
    $result->bind_param('isss', $this->paperID, $this->repcourse, $this->startdate, $this->enddate);
    $result->execute();
    $result->bind_result($metadataID, $userID, $username, $roles, $year, $title, $surname, $initials, $first_names, $email, $gender, $ipaddress, $lab_name, $student_id, $attempt, $display_started, $started, $student_grade);
    while ($result->fetch()) {
      $tmp_name = trim(str_replace("'","",$surname) . ',' . $first_names);
      if ($lab_name == '') {
        $room = '<span style="color:#808080">&lt;unknown&gt;</span>';
      } else {
        $room = $lab_name;
      }

      if ($this->demo) {
        $surname     = demo_replace($surname, true, true, $surname{0});
        $initials    = demo_replace($initials, true, true, $initials{0});
        $first_names = demo_replace($first_names, true, true, $first_names{0});
        $email       = demo_replace($email);
        $student_id  = demo_replace_number($student_id);
      }

      $this->user_results[$metadataID] = array(
                                                'metadataID'=>$metadataID,
                                                'userID'=>$userID,
                                                'username'=>$username,
                                                'roles'=>$roles,
                                                'year'=>$year,
                                                'title'=>$title,
                                                'surname'=>$surname,
                                                'initials'=>$initials,
                                                'first_names'=>$first_names,
                                                'name'=>$tmp_name,
                                                'email'=>$email,
                                                'gender'=>$gender,
                                                'ipaddress'=>$ipaddress,
                                                'room'=>$room,
                                                'student_id'=>$student_id,
                                                'attempt'=>$attempt,
                                                'visible'=>false, // We must assume the user should not be displayed.
                                                'display_started'=>$display_started,
                                                'started'=>$started,
                                                'student_grade'=>$student_grade,
                                                'mark'=>0,
                                                'percent'=>0,
                                                'questions'=>0,
                                                'duration'=>0,
                                                'marking_complete'=>true,
                                                'module'=>'',
                                                'paper_type'=>$this->paper_type
                                               );
      $metadataids[] = $metadataID;
    }
    $result->close();

    if (count($metadataids) == 0) {
      $this->user_results = array();
      return false;
    }

    $i                    = 0;
    $old_screen           = 0;
    $old_duration         = 0;
    $old_metadataID       = 0;
    $user_duration        = 0;
    $marking_complete     = 1;
    $tmp_mark             = 0;
    $tmp_user_mark_array  = array();
    $log_data             = array();
    $tmp_array            = array();

    // Load 'logX' data.
    if ($this->paper_type == '0' or $this->paper_type == '1') {
      $result = $this->db->prepare("(SELECT log0.id, metadataID, 0 AS paper_type, questions.q_id, screen, duration, user_answer, q_type, mark FROM log0, questions WHERE log0.q_id = questions.q_id AND metadataID IN (" . implode(',', $metadataids) . ")) UNION ALL (SELECT log1.id, metadataID, 1 AS paper_type, questions.q_id, screen, duration, user_answer, q_type, mark FROM log1, questions WHERE log1.q_id = questions.q_id AND metadataID IN (" . implode(',', $metadataids) . ")) ORDER BY metadataID, screen");
    } elseif ($this->paper_type == '5') {
      $result = $this->db->prepare("SELECT log$this->paper_type.id, metadataID, $this->paper_type AS paper_type, questions.q_id, 1 AS screen, 0 AS duration, NULL AS user_answer, q_type, mark FROM log$this->paper_type, questions WHERE log$this->paper_type.q_id = questions.q_id AND metadataID IN (" . implode(',', $metadataids) . ") ORDER BY metadataID");
    } else {
      $result = $this->db->prepare("SELECT log$this->paper_type.id, metadataID, $this->paper_type AS paper_type, questions.q_id, screen, duration, user_answer, q_type, mark FROM log$this->paper_type, questions WHERE log$this->paper_type.q_id = questions.q_id AND metadataID IN (" . implode(',', $metadataids) . ") ORDER BY metadataID, screen");
    }
    $result->execute();
    $result->bind_result($log_id, $metadataID, $paper_type, $q_id, $screen, $duration, $user_answer, $q_type, $mark);

    while ($result->fetch()) {
      $userID = $this->user_results[$metadataID]['userID'];
      if ($this->repmodule != '' and !isset($this->user_modules[$userID]['idMod'])) {
        continue;      // This user is not on the module set in repmodule so don't put them in the array.
      }

      // We have passed the check this students should be displayed.
      $this->user_results[$metadataID]['visible'] =  true;

      if ($old_screen != $screen or $old_metadataID != $metadataID) {
        $user_duration += $old_duration;
      }
      if ($old_metadataID != $metadataID and $old_metadataID != 0) {
        if (isset($this->user_modules[$userID]['idMod'])) {
          $this->user_results[$metadataID]['module'] = $this->user_modules[$userID]['idMod'];
        } else {
          // No module details set for this user.  Perhaps it is an unassigned guest user account.
          $this->user_results[$metadataID]['module'] = '';
        }

        // Write the user results for the user that was iterated over previously using $old_metadataID
        $this->writeUserResults($old_metadataID, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete);
        $tmp_mark = 0;
        $tmp_user_mark_array = array();
        $user_duration = 0;
        $marking_complete = 1;
      } else if (!$old_metadataID) {
        // This is the first record being iterated over so $old_metadataID is set to 0.
        if (isset($this->user_modules[$userID]['idMod'])) {
          $this->user_results[$metadataID]['module'] = $this->user_modules[$userID]['idMod'];
        } else {
          // No module details set for this user.  Perhaps it is an unassigned guest user account.
          $this->user_results[$metadataID]['module'] = '';
        }
      }

      $this->user_results[$metadataID]['questions']++;
      $this->user_results[$metadataID]['paper_type'] = $paper_type;

			$single_mark = $this->getUserMark($q_id, $userID, $user_answer, $mark, $tmp_user_mark_array);
			$tmp_mark += $single_mark;

      if (($q_type == 'textbox') and !is_numeric($mark)) {
			  $this->unmarked_textbox = true;
        $marking_complete = 0;
      }
      if ($q_type == 'enhancedcalc' and !is_numeric($mark)) {
			  $this->unmarked_enhancedcalc = true;
        $marking_complete = 0;
      }
      $old_duration   = $duration;
      $old_screen     = $screen;
      $old_metadataID = $metadataID;

      $log_data[$i]['paper_type'] = $paper_type;
      $log_data[$i]['adjmark']    = $single_mark;
      $log_data[$i]['id']         = $log_id;
      $i++;
    }
    $result->close();

    if ($old_metadataID != 0) {
      if ($this->repmodule == '' or (isset($this->user_modules[$userID]['idMod']) and $this->user_modules[$userID]['idMod'] == $this->repmodule)) {
        $user_duration += $old_duration;
        $this->writeUserResults($old_metadataID, $tmp_mark, $tmp_user_mark_array, $user_duration, $marking_complete);
      }
    }

    // Re-index the array.
    $tmp_array = $this->user_results;
    unset($this->user_results);
    $i = 0;
    foreach ($tmp_array as $metID=>$row) {
      $this->user_results[$i] = $row;
      $i++;
    }

   if ($this->recache and count($log_data) > 0) {
      $this->db->autocommit(false);

      $log_query = $this->db->prepare("UPDATE log$paper_type SET adjmark = ? WHERE id = ?");
      foreach ($log_data as $individual_log_data) {
        $paper_type = $individual_log_data['paper_type'];
        $adjmark = $individual_log_data['adjmark'];
        $log_id = $individual_log_data['id'];

        $log_query->bind_param('di', $adjmark, $log_id);
        $log_query->execute();
      }
      $log_query->close();

      $this->db->commit();
      $this->db->autocommit(true);
    }

  }

  /**
   * Add rank (position in cohort) to the 'user_results' array.
   */
  private function add_rank() {
    $result_no = count($this->user_results);
    if ($result_no == 0) return;

    // Put the whole array in marks order.
    $sortby = 'mark';
    $ordering = 'desc';
    $this->user_results = array_csort($this->user_results, $sortby, $ordering, SORT_NUMERIC);

    $display_rank = 1;
    $global_rank  = 1;
    $old_mark     = 0;

    for ($i=0; $i<$result_no; $i++) {
      if ($this->user_results[$i]['mark'] != $old_mark) {
        $display_rank = $global_rank;
      }
      $this->user_results[$i]['rank'] = $display_rank;
      $old_mark = $this->user_results[$i]['mark'];
      $global_rank++;
    }
  }

  /**
   * When dealing with certain percentages of the cohort, for example top 33%,
	 * then this function will set 'visible' flags on the 'user_results' array
	 * to control which elements are displayed in the final report.
   */
  private function flag_subpart() {
    $user_no = count($this->user_results);

    if ($user_no == 0) return;

    if ($this->percent < 100) {
      // Sort by mark order.
      $sortby = 'mark';
      $this->user_results = array_csort($this->user_results, $sortby, $this->ordering, SORT_NUMERIC);
      $this->cohort_size = round(($user_no/100) * $this->percent);

      // Set visible/invisible flag where necessary.
      for ($i=0; $i<$user_no; $i++) {
        if ($i >= $this->cohort_size) {
          $this->user_results[$i]['visible'] = false;
        }
      }
    } else {
      $this->cohort_size = $user_no;
    }
  }

  /**
   * Add in students in cohort who haven't taken the exam.
   */
  private function add_absent_students() {
    if ($this->absent == 1) {
			$user_no = count($this->user_results);

      $count_student_cohort = count($this->student_cohort);

      for ($i=0; $i < $count_student_cohort; $i++) {
        $this->user_results[$user_no]['name']             = $this->student_cohort[$i]['name'];
        $this->user_results[$user_no]['mark']             = 0;
        $this->user_results[$user_no]['percent']          = 0;
        $this->user_results[$user_no]['started']          = '';
        $this->user_results[$user_no]['username']         = $this->student_cohort[$i]['username'];
        $this->user_results[$user_no]['userID']           = $this->student_cohort[$i]['userID'];
        $this->user_results[$user_no]['student_grade']    = $this->student_cohort[$i]['student_grade'];
        $this->user_results[$user_no]['module']           = $this->student_cohort[$i]['module'];
        $this->user_results[$user_no]['display_started']  = '';
        $this->user_results[$user_no]['started']  				= '';
        $this->user_results[$user_no]['title']            = $this->student_cohort[$i]['title'];
        $this->user_results[$user_no]['surname']          = $this->student_cohort[$i]['surname'];
        $this->user_results[$user_no]['initials']         = $this->student_cohort[$i]['initials'];
        $this->user_results[$user_no]['first_names']      = $this->student_cohort[$i]['first_names'];
        $this->user_results[$user_no]['student_id']       = $this->student_cohort[$i]['student_id'];
        $this->user_results[$user_no]['gender']           = $this->student_cohort[$i]['gender'];
        $this->user_results[$user_no]['ipaddress']        = '';
        $this->user_results[$user_no]['duration']         = 0;
        $this->user_results[$user_no]['questions']        = '';
        $this->user_results[$user_no]['paper_type']       = '';
        $this->user_results[$user_no]['room']             = '';
        $this->user_results[$user_no]['visible']          = true;    // Default to visible unless switched off below.
        $this->user_results[$user_no]['roles']            = 'Student';
        $this->user_results[$user_no]['late']             = true;
        $this->user_results[$user_no]['rank']             = 99999999999;
        $this->user_results[$user_no]['classification']   = '';
        $user_no++;
      }
    }
  }

  /**
   * Translates from internal numerical module IDs to institution module codes for display.
   */
  private function convert_moduleIDs() {
    $result_no = count($this->user_results);
    $moduleIDs = array();

    // Build up an array of IDs to module codes.
    for ($i=0; $i<$result_no; $i++) {
      $id = $this->user_results[$i]['module'];
      if ($id != '') {
        if (!isset($moduleIDs[$id])) {
          $moduleIDs[$id] = module_utils::get_moduleid_from_id($id, $this->db);
        }
      }
    }

    // Loop around the results array and convert to codes.
    for ($i=0; $i<$result_no; $i++) {
      if (isset($moduleIDs[$this->user_results[$i]['module']])) {
        $this->user_results[$i]['module'] = $moduleIDs[$this->user_results[$i]['module']];
      }
    }
  }

  /**
   * Count how many user elements we have.
   */
  private function set_user_no() {
    $this->user_no = count($this->user_results);
  }

  /**
   * Returns true of false whether there are any unmarked Textbox question answers on the paper.
   */
	public function unmarked_textbox() {
	  return $this->unmarked_textbox;
	}

  /**
   * Returns true of false whether there are any unmarked Calculation question answers on the paper.
   */
	public function unmarked_enhancedcalc() {
	  return $this->unmarked_enhancedcalc;
	}

  /**
   * Creates an array of basic statistics on the cohort performance.
   */
  public function generate_stats() {
    // Generate summary statistics.
    $this->set_user_no();
    $mark_total    = 0;
    $percent_total = 0;

    $this->stats['sum_of_marks']  = 0;
    $this->stats['out_of_range']  = 0;
    $this->stats['completed_no']  = 0;
    $this->stats['failures']      = 0;
    $this->stats['passes']        = 0;
    $this->stats['honours']       = 0;
    $this->stats['total_time']    = 0;
    $this->stats['max_mark']      = 0;
    $this->stats['min_mark']      = 9999;
    $this->stats['max_percent']   = 0;
    $this->stats['min_percent']   = 100;

    $median_mark_array    = array();
    $median_percent_array = array();
    $marks_data           = array();

    for ($i=0; $i<$this->user_no; $i++) {
      if (isset($this->user_results[$i]['percent']) and $this->user_results[$i]['questions'] >= $this->question_no and $this->user_results[$i]['visible']) {
        $this->stats['completed_no']++;
        $median_mark_array[] = round($this->user_results[$i]['mark'], $this->percent_decimals);
        $median_percent_array[] = round($this->user_results[$i]['percent'], $this->percent_decimals);

        $mark_total += round($this->user_results[$i]['mark'], $this->percent_decimals);
        $percent_total += round($this->user_results[$i]['percent'], $this->percent_decimals);  // Round to the precision being displayed on screen.
      }
      if (isset($this->user_results[$i]['mark']) and $this->user_results[$i]['visible']) {
			  $tmp_mark = round($this->user_results[$i]['mark'], $this->percent_decimals);
				$tmp_percent = round($this->user_results[$i]['percent'], $this->percent_decimals);

				$marks_data[] = $tmp_percent;
				if ($tmp_percent < $this->pass_mark) $this->stats['failures']++;
				if ($tmp_percent < $this->stats['min_percent']) $this->stats['min_percent'] = $tmp_percent;
				if ($tmp_percent > $this->stats['max_percent']) $this->stats['max_percent'] = $tmp_percent;

        if ($tmp_percent >= $this->pass_mark and $tmp_percent < $this->distinction_mark) $this->stats['passes']++;
        if ($tmp_percent >= $this->distinction_mark) $this->stats['honours']++;
        if ($tmp_mark < $this->stats['min_mark']) $this->stats['min_mark'] = $tmp_mark;
        if ($tmp_mark > $this->stats['max_mark']) $this->stats['max_mark'] = $tmp_mark;
        $this->stats['sum_of_marks'] += $tmp_mark;
      }

      if ($this->user_results[$i]['visible']) {
        $this->stats['total_time'] += $this->user_results[$i]['duration'];
      } else {
        $this->stats['out_of_range']++;
      }
    }

    if ($this->stats['min_mark'] == 9999) $this->stats['min_mark'] = 0;    // Reset back to zero if still on default.
    $this->stats['range'] = $this->stats['max_mark'] - $this->stats['min_mark'];
    $this->stats['range_percent'] = $this->stats['max_percent'] - $this->stats['min_percent'];


		// Calculate StdDev.
    $xmean_total = 0;
    $xmean_percent_total = 0;
    for ($i=0; $i<$this->user_no; $i++) {
      if (isset($this->user_results[$i]['questions']) and $this->user_results[$i]['questions'] >= $this->question_no and $this->user_results[$i]['visible'] and $this->stats['completed_no'] > 0) {
			  $tmp_percent = round($this->user_results[$i]['percent'], $this->percent_decimals);

        $xmean_total += (($this->user_results[$i]['mark'] - ($mark_total / $this->stats['completed_no'])) * ($this->user_results[$i]['mark'] - ($mark_total / $this->stats['completed_no'])));
        $xmean_percent_total += (($tmp_percent - ($percent_total / $this->stats['completed_no'])) * ($tmp_percent - ($percent_total / $this->stats['completed_no'])));
      }
    }

    if ($this->stats['completed_no'] > 1) {
      $this->stats['stddev_mark']    = sqrt($xmean_total / ($this->stats['completed_no'] - 1));
      $this->stats['stddev_percent'] = sqrt($xmean_percent_total / ($this->stats['completed_no'] - 1));
    } else {
      $this->stats['stddev_mark']    = 0;
      $this->stats['stddev_percent'] = 0;
    }

    if ($this->stats['completed_no'] > 0) {
      $this->stats['mean_mark']      = $mark_total / $this->stats['completed_no'];
      $this->stats['mean_percent']   = $percent_total / $this->stats['completed_no'];
      $this->stats['median_mark']    = MathsUtils::median($median_mark_array);
      $this->stats['median_percent'] = MathsUtils::median($median_percent_array);
    } else {
      $this->stats['mean_mark']      = 0;
      $this->stats['mean_percent']   = 0;
      $this->stats['median_mark']    = 0;
      $this->stats['median_percent'] = 0;
    }

    $this->stats['q1'] = MathsUtils::percentile($marks_data, 0.75);
    $this->stats['q2'] = MathsUtils::percentile($marks_data, 0.50);
    $this->stats['q3'] = MathsUtils::percentile($marks_data, 0.25);

    for ($i=1; $i<10; $i++) {
      $this->stats["decile$i"] = MathsUtils::percentile($marks_data, ($i / 10));
    }
  }

  /**
   * Adds decile information to each student in the 'user_results' array.
   */
  private function add_deciles() {
    for ($student=0; $student<$this->user_no; $student++) {
      $this->user_results[$student]['decile'] = 10;  // Set all to 10 as a baseline

      for ($i=9; $i>0; $i--) {
        if ($this->user_results[$student]['percent'] >= $this->stats["decile$i"]) {
          $this->user_results[$student]['decile'] = $i;
        }
      }
    }
  }

  /**
   * Used to sort the main 'user_results' array by various columns.
   */
  public function sort_results() {
    if (count($this->user_results) == 0) return;

    $tmp_sort = ($this->sortby != 'result' and $this->sortby != 'percent') ? $this->sortby : 'percent';
    if ($tmp_sort == 'adj_percent') $tmp_sort = 'mark';
    if ($tmp_sort == 'classification') $tmp_sort = 'mark';
    if ($tmp_sort == 'mark' or $tmp_sort == 'rank' or $tmp_sort == 'decile') {
      $method = SORT_NUMERIC;
    } else {
      $method = SORT_STRING;
    }
    $this->user_results = array_csort($this->user_results, $tmp_sort, $this->ordering, $method);
  }

  /**
   * Get possible marks for the question (marks correct). May come from options or settings.
   * @param string $q_type					- Type of question
   * @param integer $marks_correct	- Marks correct if available in an option
   * @param string $settings				- JSON encoding settings for the question. Assumed to contain the correct marks if not available in an option
   * @return mixed
   */
  protected function get_marks_correct($q_type, $marks_correct, $settings) {
    switch ($q_type) {
      case 'enhancedcalc':
        require_once "../plugins/questions/{$q_type}/{$q_type}.class.php";
        $q_class = new $q_type($this->config);
        $q_class->set_settings($settings);
        $mc = $q_class->calculate_question_mark();
        break;
      default:
        $mc = $marks_correct;
    }

    return $mc;
  }

  /**
   * Loads special needs for all users in the current cohort.
   */
  private function load_special_needs() {
    // Query any student special needs for the current paper
    $this->special_needs = array();
    $users_in = array();
    foreach($this->user_results as $u) {
      $users_in[] = $u['userID'];
    }
    $users_in = implode(',', $users_in);
    if ($users_in != '') {
      $result = $this->db->prepare("SELECT userID FROM special_needs where userID IN ($users_in)");
      $result->execute();
      $result->bind_result($special_userID);
      while ($result->fetch()) {
        $this->special_needs[$special_userID] = 'y';
      }
      $result->close();
    }
  }
  
  /**
   * @brief Store exam grades
   * @return bool true on success
   */
  public function create_gradebook() {
    if ($this->gradebook_enabled) {
      $gradebook = new gradebook($this->db);
      return $gradebook->create_gradebook($this->paperID);
    }
    return false;
  }
  
  /**
   * @brief Store exam grades
   * @return void
   */
  public function store_grades() {
    if ($this->gradebook_enabled) {
      $gradebook = new gradebook($this->db);
      for ($student = 0; $student < $this->user_no; $student++) {
        $userid = $this->user_results[$student]['userID'];
        $mark = $this->user_results[$student]['mark'];
        $adjusted = MathsUtils::formatNumber($this->user_results[$student]['percent'], $this->percent_decimals);
        $classification = $this->user_results[$student]['classification'];
        $gradebook->store_grade($userid, $this->paperID, $mark, $adjusted, $classification);
      }
    }
  }
  
  /**
   * @brief Check if paper already graded
   * @return bool true if graded
   */
  public function paper_graded() {
    $gradebook = new gradebook($this->db);
    return $gradebook->paper_graded($this->paperID);
  }

  /**
   * Returns true or false if a given user has special needs.
   */
  public function has_special_need($userID) {
    if (isset($this->special_needs[$userID]) and $this->special_needs[$userID] == 'y') {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Checks and displays if necessary a late submissions warning banner at the top of the screen.
   */
  public function check_late_submission_warnings() {
    if (count($this->log_late) > 0) {
    ?>
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
        <tr>
          <td class="redwarn" style="width:40px; line-height:0; padding-left:0"><img src="../artwork/late_warning_icon.png" width="32" height="32" alt="<?php echo strip_tags($this->string['latesubmissionsmsg']) ?>" /></td>
          <td class="redwarn"><?php echo $this->string['latesubmissionsmsg'] . ' (<a style="color:black" href="#" onclick="launchHelp(221); return false;">' . $this->string['moredetails'] . '</a>)'; ?></td>
        </tr>
      </table>
    <?php
    }
  }

  /**
   * Checks and displays if necessary an unmark textbox warning banner at the top of the screen.
   */
  public function check_unmarked_textbox_warnings() {
    if ($this->unmarked_textbox()) {
    ?>
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
        <tr>
          <td class="redwarn" style="width:40px; line-height:0; padding-left:0"><img src="../artwork/unmarked_questions_warning.png" width="32" height="32" alt="<?php echo $this->string['warning'] ?>" /></td>
          <td class="redwarn"><?php echo $this->string['unmarkedtextbox'] ?></td>
        </tr>
      </table>
    <?php
    }
  }

  /**
   * Checks and displays if necessary a numarked calculation question warning banner at the top of the screen.
   */
  public function check_unmarked_enhancedcalc_warnings() {
    if ($this->unmarked_enhancedcalc()) {
    ?>
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
        <tr>
          <td class="redwarn" style="width:40px; line-height:0; padding-left:0"><img src="../artwork/unmarked_questions_warning.png" width="32" height="32" alt="<?php echo $this->string['warning'] ?>" /></td>
          <td class="redwarn"><?php echo $this->string['unmarkedenhancedcalc'] ?></td>
        </tr>
      </table>
    <?php
    }
  }

  /**
   * Checks and displays if necessary a temporary account warning banner at the top of the screen.
   */
  public function check_temp_account_warnings() {
    // Check for any temporary accounts and if so display warning banner
    $temp_user_no = 0;
    $user_no = count($this->user_results);
    for ($i=0; $i<$user_no; $i++) {
      if (strpos($this->user_results[$i]['username'], 'user') === 0) {
        $temp_user_no++;
      }
    }
    if ($temp_user_no > 0) {
    ?>
      <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
        <tr>
          <td class="redwarn" style="width:40px; line-height:0; padding-left:0"><img src="../artwork/temp_account_warning.png" width="32" height="32" alt="<?php echo $this->string['warning'] ?>" /></td>
          <td class="redwarn"><?php echo $this->string['temporaryaccountswarning'] ?></td>
        </tr>
      </table>
    <?php
    }
  }

}
?>
