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
* Utility class for date related functionality
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

Class RecycleBin {
  /**
   * Get a list of recycle bin contents for the current user
   */
  private $recycle_bin;
  private $userID;

  /**
   * RecycleBin constructor.
   */
  public function __construct( ) {
    $this->recycle_bin = array();
    $configObject = Config::get_instance();
    $this->db = $configObject->db;
    $userObj = UserObject::get_instance();
    $this->userID = $userObj->get_user_ID();
	}

  /**
   * Gets the deleted content from the papers table.
   */
  public function get_papers_recyclebin_contents() {
    // Query the Papers tables.
    $counter = 0;
    $stmt = $this->db->prepare("SELECT property_id AS id, paper_type, paper_title, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM properties WHERE paper_ownerID = ? AND deleted IS NOT NULL");
    $stmt->bind_param('i', $this->userID);
    $stmt->execute();
    $stmt->bind_result($id, $paper_type, $paper_title, $deleted);
    while ($stmt->fetch()) {
      $this->recycle_bin[$counter]['id'] = $id;
      $this->recycle_bin[$counter]['type'] = 'paper';
      $this->recycle_bin[$counter]['name'] = $paper_title;
      $this->recycle_bin[$counter]['deleted'] = $deleted;
      $this->recycle_bin[$counter]['subtype'] = $paper_type;
      $counter++;
    }
    $stmt->close();

    return $this->recycle_bin;
  }

  /**
   * Gets the deleted content from the questions table.
   */
  public function get_questions_recyclebin_contents() {
    // Query the Questions tables.
    $counter = 0;
    $stmt = $this->db->prepare("SELECT q_id AS id, q_type, leadin_plain, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM questions WHERE ownerID = ? AND deleted IS NOT NULL");
    $stmt->bind_param('i', $this->userID);
    $stmt->execute();
    $stmt->bind_result($id, $q_type, $leadin_plain, $deleted);
    while ($stmt->fetch()) {
      $this->recycle_bin[$counter]['id'] = $id;
      $this->recycle_bin[$counter]['type'] = 'question';
      if ($q_type == 'sct') {
        $parts = explode('~', $leadin_plain);
        $this->recycle_bin[$counter]['name'] = $parts[0];
      } else {
        $this->recycle_bin[$counter]['name'] = $leadin_plain;
      }
      $this->recycle_bin[$counter]['deleted'] = $deleted;
      $this->recycle_bin[$counter]['subtype'] = $q_type;
      $counter++;
    }
    $stmt->close();
    return $this->recycle_bin;
  }

  /**
   * Gets the deleted content from the folders table.
   */
  public function get_folders_recyclebin_contents() {
    // Query the Folder tables.
    $counter = 0;
    $stmt = $this->db->prepare("SELECT id, name, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM folders WHERE ownerID = ? AND deleted IS NOT NULL");
    $stmt->bind_param('i', $this->userID);
    $stmt->execute();
    $stmt->bind_result($id, $name, $deleted);
    while ($stmt->fetch()) {
      $this->recycle_bin[$counter]['id'] = $id;
      $this->recycle_bin[$counter]['type'] = 'folder';
      $this->recycle_bin[$counter]['name'] = str_replace(';', '\\', $name);
      $this->recycle_bin[$counter]['deleted'] = $deleted;
      $this->recycle_bin[$counter]['subtype'] = '';
      $counter++;
    }
    $stmt->close();

    return $this->recycle_bin;
  }

  /**
   * Gets the deleted content from the modules table.
   */
  public function get_modules_recyclebin_contents() {
    // Query the modules table.
    $counter = 0;
    $stmt = $this->db->prepare("SELECT id, fullname as name, DATE_FORMAT(mod_deleted,'%Y%m%d%H%i') AS deleted FROM modules WHERE mod_deleted IS NOT NULL");
    $stmt->execute();
    $stmt->bind_result($id, $name, $deleted);
    while ($stmt->fetch()) {
      $this->recycle_bin[$counter]['id'] = $id;
      $this->recycle_bin[$counter]['type'] = 'modules';
      $this->recycle_bin[$counter]['name'] = $name;
      $this->recycle_bin[$counter]['deleted'] = $deleted;
      $this->recycle_bin[$counter]['subtype'] = '';
      $counter++;
    }
    $stmt->close();

    return $this->recycle_bin;
  }

  /**
   * Gets the deleted content from the courses table.
   */
  public function get_courses_recyclebin_contents() {
    // Query the courses table.
    $counter = 0;
    $stmt = $this->db->prepare("SELECT id, name, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM courses WHERE deleted IS NOT NULL");
    $stmt->execute();
    $stmt->bind_result($id, $name, $deleted);
    while ($stmt->fetch()) {
      $this->recycle_bin[$counter]['id'] = $id;
      $this->recycle_bin[$counter]['type'] = 'courses';
      $this->recycle_bin[$counter]['name'] = $name;
      $this->recycle_bin[$counter]['deleted'] = $deleted;
      $this->recycle_bin[$counter]['subtype'] = '';
      $counter++;
    }
    $stmt->close();

    return $this->recycle_bin;
  }

  /**
   * Gets the deleted content from the schools table.
   */
  public function get_schools_recyclebin_contents() {
    // Query the schools table.
    $counter = 0;
    $stmt = $this->db->prepare("SELECT id, school as name, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM schools WHERE deleted IS NOT NULL");
    $stmt->execute();
    $stmt->bind_result($id, $name, $deleted);
    while ($stmt->fetch()) {
      $this->recycle_bin[$counter]['id'] = $id;
      $this->recycle_bin[$counter]['type'] = 'schools';
      $this->recycle_bin[$counter]['name'] = $name;
      $this->recycle_bin[$counter]['deleted'] = $deleted;
      $this->recycle_bin[$counter]['subtype'] = '';
      $counter++;
    }
    $stmt->close();

    return $this->recycle_bin;
  }

  /**
   * Gets the deleted content from the faculties table.
   */
  public function get_faculties_recyclebin_contents() {
    // Query the faculty table.
    $counter = 0;
    $stmt = $this->db->prepare("SELECT id, name, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM faculty WHERE deleted IS NOT NULL");
    $stmt->execute();
    $stmt->bind_result($id, $name, $deleted);
    while ($stmt->fetch()) {
      $this->recycle_bin[$counter]['id'] = $id;
      $this->recycle_bin[$counter]['type'] = 'faculty';
      $this->recycle_bin[$counter]['name'] = $name;
      $this->recycle_bin[$counter]['deleted'] = $deleted;
      $this->recycle_bin[$counter]['subtype'] = '';
      $counter++;
    }
    $stmt->close();

    return $this->recycle_bin;
  }

  /**
   * Get the contents from papers, Questions, folders, modules, courses, schools and faculties
   * @return array
   */
  public function get_recyclebin_contents(){
    $this->recycle_bin = array_unique(
      array_merge(
        $this->get_papers_recyclebin_contents(),
        $this->get_modules_recyclebin_contents(),
        $this->get_courses_recyclebin_contents(),
        $this->get_questions_recyclebin_contents(),
        $this->get_schools_recyclebin_contents(),
        $this->get_faculties_recyclebin_contents(),
        $this->get_folders_recyclebin_contents()
      ), SORT_REGULAR);

    return $this->recycle_bin;
  }

}

?>