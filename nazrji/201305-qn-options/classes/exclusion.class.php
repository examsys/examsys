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
 *  Class to handle question exclusions on papers.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

class Exclusion {

  private $db;
   

  public function __construct($db) {
  	$this->db = $db;
  }

  /*
  * static helper function to load the paper properties by property_id
  *	return @PaperProperties
  */
  static function get_exclusions_by_paperID($p_id, $db) {
  	$exclusions = new Exclusion($db);
  	$exclusions->set_paper_id($p_id);
  	if ($exclusions->load() !== false) {
  		return $exclusions;
  	} else {
  		return false;
  	}
  }

  public function load() {
    $paperID = $this->get_paper_id();

    $this->excluded = array();
    $result = $this->db->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($q_id, $parts);
    while ($result->fetch()) {
      $this->excluded[$q_id] = $parts;
    }
    $result->close();
    
  }
  
  private function set_paper_id($paperID) {
    $this->paper_id = $paperID;
  }
  
  
  private function get_paper_id() {
    return $this->paper_id;
  }
  
  public function get_exclusions_by_qid($q_id) {
    if (!isset($this->excluded[$q_id])) {
      return '0000000000000000000000000000000000000000';
    } else {
      return $this->excluded[$q_id];
    }
  }
  
  public function is_question_excluded($q_id) {
    if (isset($this->excluded[$q_id]) and strpos($this->excluded[$q_id], '1') !== false) {
      return true;
    } else {
      return false;
    }
  }
  
  public function get_excluded_no() {
    return count($this->excluded);
  }

}