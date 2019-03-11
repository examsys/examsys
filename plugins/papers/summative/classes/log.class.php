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
* Summative package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2018 onwards The University of Nottingham
*/

namespace plugins\papers\summative;

/**
 * Summative helper class.
 */
class log extends \log {

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    $this->papertype = '2';
    $this->late = true;
  }

  /**
   * Get summative logs
   * @return array
   */
  public function get_log() {
    $user_answers = array();
    $user_dismiss = array();
    $user_order = array();
    $used_questions = array();
    $log_data = $this->db->prepare("SELECT id, q_id, user_answer, duration, screen, dismiss, option_order FROM log2 WHERE metadataID = ? ORDER BY id");
    $log_data->bind_param('i', $this->metadataid);
    $log_data->execute();
    $log_data->store_result();
    $log_data->bind_result($log_id, $log_q_id, $log_user_answer, $log_duration, $log_screen, $current_dismiss, $option_order);
    while ($log_data->fetch()) {
      $user_answers[$log_screen][$log_q_id] = $log_user_answer;
      $user_dismiss[$log_screen][$log_q_id] = $current_dismiss;
      $user_order[$log_screen][$log_q_id] = $option_order;
      $used_questions[$log_q_id] = $log_q_id;
      $this->process_screen_variables($log_screen, $log_duration);
    }
    $log_data->close();
    return array('used_questions' => $used_questions,
      'user_answers' => $user_answers,
      'user_dismiss' => $user_dismiss,
      'user_order' => $user_order,
      'previous_duration' => $this->previousduration,
      'screen_pre_submitted' => $this->screenpresubmitted,
      'current_screen' => $this->currentscreen);
  }
}