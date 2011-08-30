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
 * Class for Extended Matching options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class OptionMATRIX extends Option {
  
  protected $all_corrects = array();
  protected $_fields_compound = array('correct' => 'raw');
  
  // ACCESSORS
  
  /**
   * Get all the correct answers for this option.  Actually the correct answer across the board. Return as an array of array of correct 
   * answers for each 'question'
   * @return string
   */
  public function get_all_corrects() {
    $this->get_correct();
    return $this->all_corrects;
  }
  
  public function set_all_corrects($value) {
    $this->all_corrects = $value;
    $this->set_correct();
  }

  public function get_correct() {
    $this->all_corrects = explode('|', $this->correct);
    return $this->correct;
  }
  
  public function set_correct($value=-1) {
    $this->correct = implode('|', $this->all_corrects);
  }
}

