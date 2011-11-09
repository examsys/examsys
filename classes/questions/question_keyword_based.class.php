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
 * Class for Multiple Choice questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

Class QuestionKEYWORD_BASED extends Question {

  public $max_options = 1;
  protected $_allow_change_marking_method = false;
  protected $_allow_correction = false;
  
  protected $_fields_editable = array('leadin');
  
  function __construct($mysqli, $user_id, $lang_strings, $data = null) {
    parent::__construct($mysqli, $user_id, $lang_strings, $data);

  }
  
  public function get_user_keywords($teams) {
    $keywords = array();
    
    $result = $this->_mysqli->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND moduleid IN ('" . implode("','",$teams) . "') ORDER BY moduleid, keyword");
    $result->execute();  
    $result->store_result();
    $result->bind_result($module_id, $keyword, $keyword_id);
    while ($result->fetch()) {
      $keywords[] = array($module_id, $keyword, $keyword_id);
    }
    
    return $keywords;
  }
}

