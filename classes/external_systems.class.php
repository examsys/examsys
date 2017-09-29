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
* External Systems package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

/**
 * External Systems helper class.
 */
class external_systems {
    /**
     * The db connection
     * @var mysqli
     */
    private $db;
    /**
     * API external system type
     * @var string
     */
    const API = 'api';
    /**
     * Pluin external system type
     * @var string
     */
    const PLUGIN = 'plugin';
    /**
     * Constructor
     * @return void 
     */
    function __construct() {
        $configObject = Config::get_instance();
        $this->db = $configObject->db;
    }
    /**
     * Get external system mapping info given user id
     * @param integer $client oauth client id
     * @return array external system name and id 
     */
    public function get_mapped_externalsystem_info($client) {
        $info = array();
        $result = $this->db->prepare("SELECT s.name, m.ext_id FROM external_systems s, external_systems_mapping m WHERE s.id = m.ext_id AND m.client_id = ?");
        $result->bind_param('s', $client);
        $result->execute();
        $result->bind_result($name, $id);
        $result->store_result();
        $result->fetch();
        if ($result->num_rows == 1) {
            $info['name'] = $name;
            $info['id'] = $id;
        }
        $result->close();
        if ($this->db->errno != 0) {
            return $info;
        }
        return $info;
    }
    /**
     * Get external systems
     * @return array external systems
     */
    public function get_all_externalsystems() {
        $exts = array();
        $result = $this->db->prepare("SELECT id, name FROM external_systems");
        $result->execute();
        $result->bind_result($extid, $name);
        while ($result->fetch()) {
            $exts[$extid] = $name;
        }
        $result->close();
        return $exts;
    }
    /**
     * Get external systems details
     * @return array external systems
     */
    public function get_all_externalsystems_details() {
        $exts = array();
        $result = $this->db->prepare("SELECT id, name, type FROM external_systems");
        $result->execute();
        $result->bind_result($extid, $name, $type);
        while ($result->fetch()) {
            $exts[$extid] = array('name' => $name, 'type' => $type);
        }
        $result->close();
        return $exts;
    }
    /**
     * Get API external systems
     * @return array external systems
     */
    public function get_all_api_externalsystems() {
        $exts = array();
        $result = $this->db->prepare("SELECT id, name FROM external_systems WHERE type = ?");
        $api = self::API;
        $result->bind_param('s', $api);
        $result->execute();
        $result->bind_result($extid, $name);
        while ($result->fetch()) {
            $exts[$extid] = $name;
        }
        $result->close();
        return $exts;
    }
    /**
     * Insert external system mapping for client
     * @param integer $client oauth client id
     * @param integer $extsys external system id
     * @return boolean true on success
     */
    public function insert_external_system_mapping($client, $extsys) {
        $result = $this->db->prepare("INSERT INTO external_systems_mapping (client_id, ext_id) values (?, ?)");
        $result->bind_param('si', $client, $extsys);
        $result->execute();
        $result->close();
        if ($this->db->errno != 0) {
            return false;
        }
        return true;
    }
    /**
     * Update external system mapping for client
     * @param integer $client oauth client id
     * @param integer $extsys external system id
     * @return boolean true on success
     */
    public function update_external_system_mapping($client, $extsys) {
        $extsysinfo = $this->get_mapped_externalsystem_info($client);
        if (count($extsysinfo) > 0) {
            $result = $this->db->prepare("UPDATE external_systems_mapping SET ext_id = ? WHERE client_id = ?");
            $result->bind_param('is', $extsys, $client);
            $result->execute();
            $result->close();
            if ($this->db->errno != 0) {
                return false;
            }
        } else {
            if (!$this->insert_external_system_mapping($client, $extsys)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Delete external system mapping for client
     * @param integer $client oauth client id
     */
    public function delete_external_system_mapping($client) {
        $extsysinfo = $this->get_mapped_externalsystem_info($client);
        if (count($extsysinfo) > 0) {
            $result = $this->db->prepare("DELETE FROM external_systems_mapping WHERE client_id = ?");
            $result->bind_param('s', $client);
            $result->execute();
            $result->close();
        }
    }
    /**
     * Insert external system
     * @param string $name name of external system
     * @param string $type type of external system
     * @return boolean true on success
     */
    public function insert_external_system($name, $type) {
        if (empty($name)) {
            return false;
        }
        $result = $this->db->prepare("INSERT INTO external_systems (name, type) VALUES (?, ?)");
        $result->bind_param('ss', $name, $type);
        $result->execute();
        $result->close();
        if ($this->db->errno != 0) {
            return false;
        }
        return true;
    }
    /**
     * Delete external system
     * @param string $id id of external system
     */
    public function delete_external_system($id) {
        $result = $this->db->prepare("DELETE FROM external_systems WHERE id = ?");
        $result->bind_param('i', $id);
        $result->execute();
        $result->close();
    }
    /**
     * Check if external system exists
     * @param integer $id internal id of external system
     * @return boolean
     */
    public function external_system_exists($id) {
        $exists = false;
        $result = $this->db->prepare("SELECT NULL FROM external_systems WHERE id = ?");
        $result->bind_param('i', $id);
        $result->execute();
        $result->store_result();
        if ($result->num_rows > 0) {
            $exists = true;
        }
        $result->close();
        return $exists;
    }
    /**
     * Check if external system is in use
     * @param integer $id internal id of external system
     * @return boolean
     */
    public function external_system_inuse($id) {
        $exists = false;
        if ($this->external_system_exists($id)) {
            // If not api external system flag as in use
            $apis = $this->get_all_api_externalsystems();
            if (!array_key_exists($id, $apis)) {
                return true;
            }
            // Check is api mapped to user.
            $result = $this->db->prepare("SELECT NULL FROM external_systems_mapping WHERE ext_id = ? LIMIT 1");
            $result->bind_param('i', $id);
            $result->execute();
            $result->store_result();
            if ($result->num_rows == 1) {
                $exists = true;
            }
            $result->close();
        }
        return $exists;
    }
}