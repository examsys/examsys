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
 * Class for keyword block options
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

Class OptionKEYWORD_BASED extends OptionEdit {

  /**
   * Persist the object to the database
   * @return boolean Success or failure of the save operation
   */
  public function save($option_number = 0) {
    $success = false;
    $logger = new Logger($this->_mysqli);

    $valid = $this->validate();

    $keywordid = $this->_data[1];
    $this->_data[1] = null;
          
    if ($valid === true) {
      // If $id is -1 we're inserting a new record
      if ($this->id == -1) {
        $params = array_merge(array('issiisssddd'), $this->_data);
        $query = <<< QUERY
INSERT INTO options(o_id, option_text, o_media, o_media_width, o_media_height, feedback_right, feedback_wrong, correct, marks_correct, marks_incorrect, marks_partial)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
QUERY;
      } else {
        // Otherwise we're updating an existing one
        $params = array_merge(array('issiisssdddi'), $this->_data, array(&$this->id));
        $query = <<< QUERY
UPDATE options
SET o_id = ?, option_text = ?, o_media = ?, o_media_width = ?, o_media_height = ?, feedback_right = ?, feedback_wrong = ?, correct = ?, marks_correct = ?, marks_incorrect = ?, marks_partial = ?
WHERE id_num = ?
QUERY;
      }
      $result = $this->_mysqli->prepare($query);
      call_user_func_array (array($result,'bind_param'), $params);
      $result->execute();
      $success = ($result->affected_rows > -1);

      if ($success) {
        if ($this->id == -1) {
          $this->id = $this->_mysqli->insert_id;
          $this->track_new($logger, $option_number);
        } else {
          // Log any changes
          $this->save_changes($logger, $option_number);
        }
        // Insert reference into keywords_link table not option_text
        $success = keyword_utils::insert_keyword_link($this->_data[0], $keywordid, $this->_mysqli);
      }
      $result->close();

      $this->_modified_fields = array();
    } else {
      throw new ValidationException($valid);
    }

    return $success;
  }
}

