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
 * Class for Fill in the Blank questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionBLANK extends Question {
  
  protected $_answer_negative = null;
  
  protected $_display_methods = array('dropdown' => 'Dropdown Lists (randomised)', 'textboxes' => 'Blank Textboxes');
  
  protected $_fields_unified = array('text' => 'Question/Stem');

  /**
   * Does this question type allow changes to the correct answer after it is locked
   * @return boolean
   */
  public function allow_correction() {
    return false;
  }
}

