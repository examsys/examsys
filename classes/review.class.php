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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
class Review {

  private $db;
  private $paperID;
	private $reviewerID;
	private $review_type;
	private $metadataID;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  public function __construct($paperID, $reviewerID, $review_type, $db) {
    $this->db      			= $db;
    $this->paperID 			= $paperID;
		$this->reviewerID		= $reviewerID;
		$this->review_type 	= $review_type;
		
    $this->get_metadataID();
  }
  
  private function time_to_seconds($seconds) {
    $hr = intval(substr($seconds,8,2));
    $min = intval(substr($seconds,10,2));
    $sec = intval(substr($seconds,12,2));

    return ($hr * 3600) + ($min * 60) + $sec;
  }

  private function create_metadataID() {
    $ipaddress = NetworkUtils::get_client_address();

    $stmt = $this->db->prepare("INSERT INTO review_metadata VALUES(NULL, ?, ?, NOW(), NULL, ?, ?, NULL)");
    $stmt->bind_param('iiss', $this->reviewerID, $this->paperID, $this->review_type, $ipaddress);
    $stmt->execute();
    $reviewID = $this->db->insert_id;
    $stmt->close();

    return $reviewID;
  }

  private function get_metadataID() {
    $stmt = $this->db->prepare("SELECT id FROM review_metadata WHERE paperID = ? AND reviewerID = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('ii', $this->paperID, $this->reviewerID);
    $stmt->execute();
    $stmt->bind_result($reviewID);
    $stmt->store_result();
    if ($stmt->num_rows < 1) {
      $reviewID = $this->create_metadataID();
    } else {
      $stmt->fetch();
    }
    $stmt->close();

    $this->metadataID = $reviewID;
  }

  public function record_comments($screen_no) {
    $question_no = 0;
    $old_q_id = NULL;
    $submit_time = date("YmdHis", time());

    $stmt = $this->db->prepare("SELECT q_id, q_type FROM (papers, questions) WHERE paper = ? AND screen = ? AND papers.question = questions.q_id ORDER BY display_pos");
    $stmt->bind_param('ii', $this->paperID, $screen_no);
    $stmt->execute();
    $stmt->bind_result($q_id, $q_type);
    $stmt->store_result();
    while ($stmt->fetch()) {
      if ($old_q_id != $q_id) {
        // Record external examiner comments.
        if ($q_type != 'info') {
          $question_no++;

          $result = $this->db->prepare("DELETE FROM review_comments WHERE metadataID = ? AND q_id = ?");
          $result->bind_param('ii', $this->metadataID, $q_id);
          $result->execute();  
          $result->close();

          $tmp_duration = $this->time_to_seconds($submit_time) - $this->time_to_seconds($_POST['page_start']);
          if ($tmp_duration < 0) $tmp_duration += 86400;
          $tmp_duration += $_POST['previous_duration'];
          if (isset($_POST["extcomments$question_no"])) {
            $extcomments = $_POST["extcomments$question_no"];

            $result = $this->db->prepare("INSERT INTO review_comments VALUES (NULL, ?, ?, ?, 'Not actioned', '', $tmp_duration, ?, ?)");
            $result->bind_param('iisii', $q_id, $_POST["exttype$question_no"], $extcomments, $_POST['old_screen'], $this->metadataID);
            $result->execute();
            $result->close();
          }
        }
      }
      $old_q_id = $q_id;
    }                    // End of while loop.
    $stmt->close();
  }

  public function record_general_comments($paper_comment, $finish) {
    if ($finish) {
      $stmt = $this->db->prepare("UPDATE review_metadata SET paper_comment = ?, complete = NOW() WHERE id = ?");
      $stmt->bind_param('si', $paper_comment, $this->metadataID);
    } else {
      $stmt = $this->db->prepare("UPDATE review_metadata SET paper_comment = ? WHERE id = ?");
      $stmt->bind_param('si', $paper_comment, $this->metadataID);    
    }
    $stmt->execute();
    $stmt->close();
  }

  public function get_paper_comments() {
    $stmt = $this->db->prepare("SELECT paper_comment FROM review_metadata WHERE id = ?");
    $stmt->bind_param('i', $this->metadataID);
    $stmt->execute();
    $stmt->bind_result($paper_comment);
    $stmt->fetch();
    $stmt->close();

    return $paper_comment;
  }

  public function load_reviews() {	
    $this->reviews_array = array();

    $result = $this->db->prepare("SELECT q_id, category, comment, duration, action, response FROM review_comments, review_metadata WHERE review_comments.metadataID = review_metadata.id AND paperID = ? AND reviewerID = ?");
    $result->bind_param('ii', $this->paperID, $this->reviewerID);
    $result->execute();
    $result->store_result();
    $result->bind_result($q_id, $category, $comment, $previous_duration, $action, $response);
    while ($result->fetch()) {
      $this->reviews_array[$q_id]['category'] = $category;
      $this->reviews_array[$q_id]['comment'] = $comment;
      $this->reviews_array[$q_id]['action'] = $action;
      $this->reviews_array[$q_id]['response'] = $response;
    }
    $result->close();
  }
  
  public function get_category($q_id) {
    if (isset($this->reviews_array[$q_id]['category'])) {
      return $this->reviews_array[$q_id]['category'];
    } else {
      return null;
    }
  }

  public function get_comment($q_id) {
    if (isset($this->reviews_array[$q_id]['comment'])) {
      return $this->reviews_array[$q_id]['comment'];
    } else {
      return null;
    }
  }

  public function get_action($q_id) {
    if (isset($this->reviews_array[$q_id]['action'])) {
      return $this->reviews_array[$q_id]['action'];
    } else {
      return null;
    }
  }

  public function get_response($q_id) {
    if (isset($this->reviews_array[$q_id]['response'])) {
      return $this->reviews_array[$q_id]['response'];
    } else {
      return null;
    }
  }

}
