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

Class QuestionTEXTBOX extends Question {
  
  protected $columns = 10;
  protected $rows = 4;
  
  protected $_fields_editable = array('theme', 'scenario', 'leadin', 'notes', 'correct_fback', 'incorrect_fback', 'rows', 'columns', 'bloom', 'status');
  protected $_fields_unified = array('correct' => 'Terms', 'text' => 'Editor', 'marks_correct' => 'Marks if Correct');
  
  /**
   * Does this question type allow changes to the correct answer after it is locked
   * @return boolean
   */
  public function allow_correction() {
    return false;
  }
  
  // ACCESSORS
  
  /**
   * Get the columns for the question
   * @return integer
   */
  public function get_columns() {
    $this->get_display_method();
    return $this->columns;
  }
  
  /**
   * Set the columns for the question
   * @param integer $value
   */
  public function set_columns($value) {
    if ($value != $this->get_columns()) {
      $this->set_modified_field('columns', $this->columns);
      $this->columns = $value;
      $this->set_display_method();
    }
  }

  /**
   * Get the rows for the question
   * @return integer
   */
  public function get_rows() {
    $this->get_display_method();
    return $this->rows;
  }
  
  /**
   * Set the rows for the question
   * @param integer $value
   */
  public function set_rows($value) {
    if ($value != $this->get_rows()) {
      $this->set_modified_field('rows', $this->rows);
      $this->rows = $value;
      $this->set_display_method();
    }
  }

  /**
   * Get the question display method, populating pseudo-properties as we go
   * @return string
   */
  public function get_display_method() {
    if ($this->display_method != '') {
      $parts = explode('x', $this->display_method);
      $this->columns = $parts[0];
      $this->rows = $parts[1];
    }
    return $this->display_method;
  }
  
  /**
   * Set the display method for the question - this is a composite of decimals, tolerance and units
   * @param unknown_type $value
   */
  public function set_display_method($value=-1) {
    $this->display_method = $this->columns . 'x' . $this->rows;
  }
  
}

