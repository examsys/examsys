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
 * Class for Multiple Choice questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionDICHOTOMOUS extends Question {
  protected $_fields_unified = array();
  protected $_score_methods = array('TF_NegativeAbstain' => 'True/False/Abstain (Negative Marking -1)', 'TF_NegativeAbstainHalf' => 'True/False/Abstain (Negative Marking -0.5)', 'TF_Positive' => 'True/False', 'YN_NegativeAbstain' => 'Yes/No/Abstain (Negative Marking -1)', 'YN_Positive' => 'Yes/No');
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);
    
    // 'correct' is not a unified field for Dichotomous questions
    self::$_fields_editable[] = 'correct';
  }

  /**
   * Get the labels for true/false options. These change depending on the score method
   */
  public function get_tf_labels() {
    if (substr($this->get_score_method(), 0, 2) == 'YN') {
      $labels = array('true' => 'Y', 'false' => 'N');
    } else {
      $labels = array('true' => 'T', 'false' => 'F');
    }
    
    return $labels;
  }
}

