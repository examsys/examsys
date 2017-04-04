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
 *  Class to handle standard setting routines.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class StandardSetting {

  private $db;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  public function __construct($db) {
  	$this->db = $db;
  }

  public function get_ratings_by_question($std_setID) {
    $std_set_array = array();

    $result = $this->db->prepare("SELECT questionID, rating FROM std_set_questions WHERE std_setID = ?");
    $result->bind_param('i', $std_setID);
    $result->execute();
    $result->bind_result($questionID, $rating);
    while ($result->fetch()) {
      $std_set_array[$questionID] = $rating;
    }
    $result->close();
    
    return $std_set_array;
  }
  
  public function get_pass_distinction($std_setID) {
    $result = $this->db->prepare("SELECT pass_score, distinction_score FROM std_set WHERE id = ? LIMIT 1");
    $result->bind_param('i', $std_setID);
    $result->execute();
    $result->bind_result($pass_score, $distinction_score);
    $result->fetch();
    $result->close();
    
    return array('pass_score'=>$pass_score, 'distinction_score'=>$distinction_score);
  }
  
  
  
  
}