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
* Utility class for mid-exam announcement related functionality.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

class ExamAnnouncements {

  private $db;
  private $paperID;
  private $string;

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
   * @param int $paperID    - ID of the exam paper we are dealing with
   * @param object $db      - Link to mysqli
   * @param string $string  - Language translations
   */
  public function __construct($paperID, $db, $string) {
    $this->db = $db;
    $this->paperID = $paperID;
    $this->string = $string;
  }

  /**
   * Return an array of the mid-exam announcements for the current paper.
   *
   * @return array         - Array of announcments keyed on Q_ID.
   */
  public function get_announcements() {
    $configObject = Config::get_instance();

    $announcements = array();
  
    $result = $this->db->prepare("SELECT q_id, q_number, screen, msg, DATE_FORMAT(created,'" . $configObject->get('cfg_long_date_time') . "') AS created FROM exam_announcements WHERE paperID = ? ORDER BY q_number");
    $result->bind_param('i', $this->paperID);
    $result->execute();
    $result->bind_result($q_id, $q_number, $screen, $msg, $created);
    while ($result->fetch()) {
      $announcements[$q_id] = array('q_number'=>$q_number, 'screen'=>$screen, 'msg'=>$msg, 'created'=>$created);
    }   
    $result->close();

    return $announcements;
  }

  /**
   * Add or update a mid-exam announcement for a particular question ID.
   * @param int $q_id      - The ID of question the announcment pertains to.
   * @param int $q_number  - The number of the question on the paper.
   * @param int $screen    - The number of the screen the question belongs to.
   * @param string $msg    - The content of the announcement message.
   */
  public function replace_announcement($q_id, $q_number, $screen, $msg) {
    if ($msg == '') {
      return false;
    }
  
    $result = $this->db->prepare("REPLACE INTO exam_announcements (paperID, q_id, q_number, screen, msg, created) VALUES (?, ?, ?, ?, ?, NOW())");
    $result->bind_param('iiiis', $this->paperID, $q_id, $q_number, $screen, $msg);
    $result->execute();
  }
  
  /**
   * Output HTML for mid-exam announcements for the current paper.
   */
  public function display_student_announcements() {
    $exam_announcements = $this->get_announcements();

    $maxscreen = Paper_utils::get_num_screens($this->paperID, $this->db);

    if (count($exam_announcements) == 0) return '';
    
    $html = '';
    
    $html .= "<table class=\"exam_announcement_box\">\n";
    $html .= "<tr><td rowspan=\"" . (count($exam_announcements) + 1) . "\" class=\"exam_announce_icon\" ><img src=\"../artwork/comment_48.png\" width=\"48\" height=\"48\" /></td><td class=\"exam_announce_title\">" . $this->string['questionclarification'] . "</td></tr>\n";
    foreach ($exam_announcements as $exam_announcement) {
      $html .= "<tr><td><ul><li><strong>" . $this->string['question'] . " ". $exam_announcement['q_number'] . "</strong> (" . sprintf($this->string['clarificationscreen'], $exam_announcement['screen'], $maxscreen) . ")<br />" . $exam_announcement['msg'] . "</li></ul></td></tr>\n";
    }
    $html .= '</table>';
    
    return $html;
  }
  
}
?>
