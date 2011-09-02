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

Class QuestionSCT extends Question {
  
  protected $hypothesis = '';
  protected $new_information = '';
  
  protected $sct_types = array(
    array('Hypothesis', 'very unlikely', 'unlikely', 'neither likely nor unlikely', 'more likely', 'very likely'),
    array('Investigation', 'useless', 'less useful', 'neither more or less useful', 'more useful', 'very useful'),
    array('Prescription', 'contra-indicated totally or almost totally', 'not useful or even detrimental', 'nor less nor more useful', 'useful', 'absolutely necessary'),
    array('Intervention', 'contraindicated', 'less indicated', 'neither more or less indicated', 'indicated', 'strongly indicated'),
    array('Treatment', 'contraindicated', 'less indicated', 'neither more or less indicated', 'indicated', 'strongly indicated')
  );
  
  protected $_fields_editable = array('theme', 'scenario', 'hypothesis', 'new_information', 'notes', 'correct_fback', 'incorrect_fback', 'display_method', 'option_order', 'bloom', 'status');
  protected $_fields_required = array('type', 'leadin', 'display_method', 'owner_id', 'status');
  protected $_fields_unified = array();
  
  function __construct($mysqli, $user_id, $data = null) {
    parent::__construct($mysqli, $user_id, $data);

    $i = 1;
    foreach ($this->sct_types as $type) {
      $this->_display_methods[$i] = 'This ' . strtolower($type[0]) . ' becomes';
      $i++;
    }
    
    // 'correct' is not a unified field for SCT questions
    $this->_fields_editable[] = 'correct';
    
  }
  
  
  // ACCESSORS
  
  /**
   * Get the 'types' of SCT available - alters the label of the initial information and option texts
   * @return array
   */
  public function get_sct_types() {
    return $this->sct_types;
  }
  
  /**
   * Get the total number of experts used on this question.  This is a total of all the experts ('correct' value) on all the options
   * @return number
   */
  public function get_max_experts() {
    $total = 0;
    foreach ($this->options as $option) {
      if ($option->get_correct() > $total) {
        $total = $option->get_correct();
      }
    }
    return $total;
  }
  
  /**
   * Get the hypothesis for the question
   * @return integer
   */
  public function get_hypothesis() {
    $this->get_leadin();
    return $this->hypothesis;
  }
  
  /**
   * Set the hypothesis for the question
   * @param string $value
   */
  public function set_hypothesis($value) {
    if ($value != $this->get_hypothesis()) {
      $this->set_modified_field('hypothesis', $this->hypothesis);
      $this->hypothesis = $value;
      $this->set_leadin();
    }
  }

  /**
   * Get the new information for the question
   * @return integer
   */
  public function get_new_information() {
    $this->get_leadin();
    return $this->new_information;
  }
  
  /**
   * Set the new information for the question
   * @param string $value
   */
  public function set_new_information($value) {
    if ($value != $this->get_new_information()) {
      $this->set_modified_field('new_information', $this->new_information);
      $this->new_information = $value;
      $this->set_leadin();
    }
  }

  /**
   * Get the question leadin
   * @return string
   */
  public function get_leadin() {
    if ($this->leadin != '') {
      $parts = explode('~', $this->leadin);
      $this->hypothesis = $parts[0];
      $this->new_information = (isset($parts[1])) ? $parts[1] : '';
    }
    return $this->leadin;
  }
  
  /**
   * Set the question leadin
   * @param string $value
   */
  public function set_leadin($value=-1) {
    $this->leadin =  $this->hypothesis . '~' . $this->new_information;
  }
}

