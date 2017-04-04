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
 *  Class to handle killer questions used with OSCE stations.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class Killer_Question {

  private $db;
  private $paperID;
	private $questions;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

	/**
	 * @param int $paperID  - ID of the current paper.
	 * @param object $db    - Link to mysqli
	 */
	 public function __construct($paperID, $db) {
  	$this->db = $db;
    $this->paper_id = $paperID;  		
  }

	/**
	 * Load all killer questions for the current paper.
	 */
  public function load() {
    $this->questions = array();
		
    $result = $this->db->prepare("SELECT q_id FROM killer_questions WHERE paperID = ?");
    $result->bind_param('i', $this->paper_id);
    $result->execute();
    $result->bind_result($q_id);
    while ($result->fetch()) {
      $this->questions[$q_id] = true;
    }
    $result->close();
  }
	
	/**
	 * Saves questions back to the database.
	 */
	public function save() {
		// Clear all previous killer questions for this paper.
		$result = $this->db->prepare("DELETE FROM killer_questions WHERE paperID = ?");
		$result->bind_param('i', $this->paper_id);
		$result->execute();
		$result->close();

		// Insert new records for each killer question.
		$result = $this->db->prepare("INSERT INTO killer_questions VALUES (NULL, ?, ?)");
	  foreach($this->questions as $questionID => $value) {
			$result->bind_param('ii', $this->paper_id, $questionID);
			$result->execute();
		}
		$result->close();
	}
	
	/**
	 * Returns true/false if a particular question is a killer one or not.
	 */
	public function is_killer_question($q_id) {
	  if (!is_array($this->questions)) {
			$this->load();
		}
	
	  if (isset($this->questions[$q_id])) {
		  return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Sets a question as being killer.
	 */
	public function set_question($q_id) {
	  if (!is_array($this->questions)) {
			$this->load();
		}
	
		$this->questions[$q_id] = true;
	}
	
	/**
	 * Unsets a question as being killer.
	 */
	public function unset_question($q_id) {
	  if (!is_array($this->questions)) {
			$this->load();
		}
	
		unset($this->questions[$q_id]);
	}
	
}