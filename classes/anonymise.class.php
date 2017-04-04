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
* This class randomises all the names, usernames, student ID and email addresses
* in the database. This is intended only for use on test/beta servers. The action
* cannot be undone.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

class Anonymise {

  private $db;
  private $male_names;
  private $female_names;
  private $unknown_names;
  private $male_no;
  private $female_no;
  private $unknown_no;
  private $type;
  private $config;

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
    $this->db             = $db;
    $this->male_names     = array();
    $this->female_names   = array();
    $this->unknown_names  = array();
    $this->male_no        = 0;
    $this->female_no      = 0;
    $this->unknown_no     = 0; 
  }
  
	/**
	 * Check we are NOT running the script on a live server.
	 */
  public function check_security() {
    // Only allow to run the script if the server has test, local, alpha or beta in its name. 
    if (strstr(strtolower($_SERVER['HTTP_HOST']), 'test') === false and strstr(strtolower($_SERVER['HTTP_HOST']), 'local') === false and strstr(strtolower($_SERVER['HTTP_HOST']), 'alpha') === false and strstr(strtolower($_SERVER['HTTP_HOST']), 'beta') === false) {
      exit();
    }
  }

	/**
	 * Load existing names in male, female and unknown gender categories.
	 */
  public function load_names() {
    $result = $this->db->prepare("SELECT first_names, surname, title, gender FROM users WHERE first_names != ''");
    $result->execute();
    $result->bind_result($first_names, $surname, $title, $gender);
    while ($result->fetch()) {
      $first_names = str_replace('  ', ' ', $first_names);
      if ($gender == 'male' or strtolower($title) == 'mr') {
        $this->male_names[] = array('first_names'=>$first_names, 'surname'=>$surname);
      } elseif ($gender == 'female' or strtolower($title) == 'ms' or strtolower($title) == 'mrs' or strtolower($title) == 'miss') {
        $this->female_names[] = array('first_names'=>$first_names, 'surname'=>$surname);    
      } else {
        $this->unknown_names[] = array('first_names'=>$first_names, 'surname'=>$surname);
      }  
    }
    $result->close();

    $this->male_no    = count($this->male_names);
    $this->female_no  = count($this->female_names);
    $this->unknown_no = count($this->unknown_names);    
  }
  
	/**
	 * Update all accounts apart from SysAdmin and the temp user accounts.
	 */
  public function process_names() {
    // Update the user table (Do NOT update SysAdmin accounts or the temp accounts: user1, user2, etc).
    $result = $this->db->prepare("SELECT id, title, gender FROM users WHERE roles NOT LIKE '%SysAdmin%' AND username NOT LIKE 'user%'");
    $result->execute();
    $result->bind_result($id, $title, $gender);
    $result->store_result();
    while ($result->fetch()) {
      if ($gender == 'male' or strtolower($title) == 'mr') {
        $type = 'male';
      } elseif ($gender == 'female' or strtolower($title) == 'ms' or strtolower($title) == 'mrs' or strtolower($title) == 'miss') {
        $type = 'female';
      } else {
        $type = 'unknown';
      }
      $this->update_user($id, $type);
    }
    $result->close();
  }
  
	/**
	 * Process all the sid (Student ID) records.
	 */
  public function process_sids() {
    $new_student_id = 4000000;
    
    $result = $this->db->prepare("SELECT userID FROM sid");
    $result->execute();
    $result->bind_result($userID);
    $result->store_result();
    while ($result->fetch()) {      
      $this->update_sid($userID, $new_student_id);
      $new_student_id++;
    }
    $result->close();
  }
  
	/**
	 * Update all the user records.
	 */
  private function update_user($id, $type) {
    $first_names  = $this->pick_first_names($type);
    $surname      = $this->pick_surname($type);
    $initials     = $this->get_initials($first_names);
    $email        = $this->get_email($first_names, $surname);
    $username     = 'usr' . $id;

    $result = $this->db->prepare("UPDATE users SET first_names = ?, surname = ?, initials = ?, email = ?, username = ? WHERE id = ?");
    $result->bind_param('sssssi', $first_names, $surname, $initials, $email, $username, $id);
    $result->execute();
    $result->close();
  }
  
  /**
	 * Update all a sid (Student ID) records.
	 */
  private function update_sid($userID, $student_id) {
    $result = $this->db->prepare("UPDATE sid SET student_id = ? WHERE userID = ?");
    $result->bind_param('si', $student_id, $userID);
    $result->execute();
    $result->close();
  }
  
  /**
	 * Select a random first name based on gender.
	 * @param string $type - The gender
   * @return string - The selected first names.
	 */
  private function pick_first_names($type) {
    if ($type == 'male') {
      $picked = (rand(1, $this->male_no)) - 1;
      $first_name = $this->male_names[$picked]['first_names'];
    } elseif ($type == 'female') {
      $picked = (rand(1, $this->female_no)) - 1;
      $first_name = $this->female_names[$picked]['first_names'];
    } else {
      $picked = (rand(1, $this->unknown_no)) - 1;
      $first_name = $this->unknown_names[$picked]['first_names'];
    }
    
    return $first_name;
  }
  
  /**
	 * Select a random surname based on gender.
	 * @param string $type - The gender
   * @return string - The selected surname.
	 */
  private function pick_surname($type) {
    if ($type == 'male') {
      $picked = (rand(1, $this->male_no)) - 1;
      $surname = $this->male_names[$picked]['surname'];
    } elseif ($type == 'female') {
      $picked = (rand(1, $this->female_no)) - 1;
      $surname = $this->female_names[$picked]['surname'];
    } else {
      $picked = (rand(1, $this->unknown_no)) - 1;
      $surname = $this->unknown_names[$picked]['surname'];
    }
    
    return $surname;
  }

  /**
	 * Generate initials from passed in forenames.
	 * @param string $forenames - List of user forenames.
   * @return string - The generated initials. 
	 */
  private function get_initials($fornames) {
    $initial = explode(' ', $fornames);
    $initials = '';
    foreach ($initial as $name) {
      if ($name != '') {
        $initials .= $name{0};
      }
    }
    $initials = strtoupper($initials);
    
    return $initials;
  }
  
  /**
	 * Generate an email address based on first name and surname.
	 * @param string $first_names - The first names of the user.
	 * @param string $surname     - The surname of the user.
   * @return string - The generated email address. 
	 */
  private function get_email($first_names, $surname) {
    $parts = explode(' ', $first_names);
    
    $email = strtolower($parts[0]) . '.' . strtolower(str_replace(' ', '', $surname)) . '@example.com';
    
    return $email;
  }
  
}
?>
