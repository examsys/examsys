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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

Class QuestionKEYWORD_BASED extends Question {

  public $max_options = 1;
  protected $_allow_change_marking_method = false;
  protected $_allow_correction = false;
  
  protected $_fields_editable = array('leadin');
  
  function __construct($mysqli, $userObj, $lang_strings, $data = null) {
    parent::__construct($mysqli, $userObj, $lang_strings, $data);

  }
  
  public function get_user_keywords($teams) {
    $keywords = array();

    $team_list = implode("','",$teams);
    $team_query = <<< SQL
SELECT moduleid, keyword, keywords_user.id
FROM keywords_user, modules
WHERE keyword_type = 'team' AND keywords_user.userID=modules.id AND moduleid IN ('{$team_list}')
ORDER BY moduleid, keyword
SQL;

    $team_result = $this->_mysqli->prepare($team_query);
    $team_result->execute();
    $team_result->store_result();
    $team_result->bind_result($module_id, $keyword, $keyword_id);
    while ($team_result->fetch()) {
      $keywords[] = array($module_id, $keyword, $keyword_id);
    }
    $team_result->close();

    $user_query = <<< SQL
SELECT keyword, id
FROM keywords_user
WHERE keyword_type = 'personal' AND userID=?
ORDER BY keyword
SQL;

    $user_result = $this->_mysqli->prepare($user_query);
    $user_result->bind_param('i', $this->_user_id);
    $user_result->execute();
    $user_result->store_result();
    $user_result->bind_result($keyword, $keyword_id);
    while ($user_result->fetch()) {
      $keywords[] = array('Personal', $keyword, $keyword_id);
    }
    $user_result->close();

    return $keywords;
  }
}

