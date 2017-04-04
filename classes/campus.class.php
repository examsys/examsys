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
* Campus package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Campus helper class.
 */
class campus {
    
    // DB connection
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

    /**
     * @brief Constuctor
     * @param mysqli $db
     */
    function __construct($db) {
        $this->db = $db;
    }
   
    /**
     * Get details for all campus 
     * @return array|bool details or false on error
     */
    public function get_all_campus_details() {
        $result = $this->db->prepare("SELECT id, name, isdefault FROM campus");
        $result->execute();
        $result->store_result();
        $result->bind_result($campusid, $campusname, $isdefault);
        $campuses = array();
        if ($result->num_rows > 0) {
            while ($result->fetch()) {
                $campuses[$campusid] = array('campusname' => $campusname, 'isdefault' => $isdefault);
            }
            $result->close();
            return $campuses;
        }
        $result->close();
        return false;
    }
    
    /**
     * Get details of campus 
     * @param integer $id campus id
     * @return array|bool details or false on error
     */
    public function get_campus_details($id) {
        $result = $this->db->prepare("SELECT id, name, isdefault FROM campus WHERE id = ?");
        $result->bind_param('i', $id);
        $result->execute();
        $result->store_result();
        $result->bind_result($campusid, $campusname, $isdefault);
        $result->fetch();
        if ($result->num_rows > 0) {
            $result->close();
            return array('campusid' => $campusid, 'campusname' => $campusname, 'isdefault' => $isdefault);
        }
        $result->close();
        return false;
    }
    
    /**
     * Check is campus name is in use
     * @param string $name name of campus
     * @return bool true if in use
     */
    public function check_campus_name_inuse($name) {
        $result = $this->db->prepare("SELECT NULL FROM campus WHERE name = ?");
        $result->bind_param('s', $name);
        $result->execute();
        $result->store_result();
        $result->fetch();
        if ($result->num_rows > 0) {
            $result->close();
            return true;
        }
        $result->close();
        return false;
    }
    
    /**
     * Check if the provided campus has labs associated with it
     * @param string $id - id of campus
     * @return bool true labs associated with campus, false otherwise
    */
    public function check_campus_in_use($id) {
        $result = $this->db->prepare("SELECT NULL FROM labs WHERE campus = ?");
        $result->bind_param('i', $id);
        $result->execute();
        $result->store_result();
        $result->fetch();
        if ($result->num_rows > 0) {
            $result->close();
            return true;
        }
        $result->close();
        return false;
    }
}

