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
* Utility class for keyword related functionality
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/


Class keyword_utils {
 
  /**
   * See if a reference material ID actually exists.
   * @return true or false.
   */
  static function refmaterials_exist($idMod, $db) {
    $row_no = 0;
  
    $result = $db->prepare("SELECT id FROM reference_material WHERE id = ?");
    $result->bind_param('i', $refID);
    $result->execute();
    $result->store_result();
    $result->bind_result($id);
    $result->fetch();
    $row_no = $result->num_rows;
    $result->close();
    
    return $row_no > 0;
  }
  
  static function name_from_ID($keywordID, $db) {
    $result = $db->prepare("SELECT keyword FROM keywords_user WHERE id = ?");
    $result->bind_param('i', $keywordID);
    $result->execute();
    $result->store_result();
    $result->bind_result($keyword);
    if ($result->num_rows == 0) {
      $keyword = false;
    } else {
      $result->fetch();
    }
    $result->close();
    
    return $keyword;
  }

  /**
   * Function to get questions from keyword
   *
   * @param int $kid keyword identifier
   * @param mysqli $db
   * @return array question identifiers
   */
  static function get_keyword_questions($kid, $db) {
    $keyword = $db->prepare("SELECT q_id FROM keywords_question WHERE keywordID = ?");
    $keyword->bind_param('i', $kid);
    $keyword->execute();
    $keyword->store_result();
    $keyword->bind_result($question);
    $keywordarray = array();
    while ($keyword->fetch()) {
        $keywordarray[] = $question;
    }
    $keyword->close();
    return $keywordarray;
  }
  
  /**
   * Function to get the keyword id based on the question id
   * @param integer $q_id question id
   * @param mysqli $db db connection
   * @return integer|bool keyword id or false is non found
   */
  static public function get_keywordid_for_question($q_id, $db) {
    $keyword = $db->prepare("SELECT keyword_id FROM keywords_link WHERE q_id = ?");
    $keyword->bind_param('i', $q_id);
    $keyword->execute();
    $keyword->store_result();
    $keyword->bind_result($keyword_id);
    if ($keyword->num_rows == 0) {
      $keyword_id = false;
    } else {
      $keyword->fetch();
    }
    $keyword->close();
    return $keyword_id;
  }
  
  /**
   * Insert keyword/question reference row
   * @param integer $q_id question id
   * @param integer $keyword_id keyword id
   * @param mysqli $db db connection
   * @return bool true on success, false otherwise
   */
  static public function insert_keyword_link($q_id, $keyword_id, $db) {
    $sql = $db->prepare("INSERT INTO keywords_link (q_id, keyword_id) VALUES (?, ?)");
    $sql->bind_param('ii', $q_id, $keyword_id);
    $sql->execute();
    $sql->close();
    if ($db->errno != 0) {
        return false;
    }
    return true;
  }
  
  /**
   * Delete keyword/question reference row
   * @param integer $q_id question id
   * @param mysqli $db db connection
   * @return bool true on success, false otherwise
   */
  static public function delete_keyword_link($q_id, $db) {
    $sql = $db->prepare("DELETE FROM keywords_link WHERE q_id = ?");
    $sql->bind_param('i', $q_id);
    $sql->execute();
    $sql->close();
    if ($db->errno != 0) {
        return false;
    }
    return true;
  }
}