<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Naseem Sarwar
 * @version 1.0
 * @copyright Copyright (c) 2017 The University of Nottingham
 * @package
 */
class access_denied_logs
{
    private $db;
    private $logs;

    /**
     * Uses for access denied logs
     */
    public function __construct()
    {
        $configObject = Config::get_instance();
        $this->db = $configObject->db;
        $this->logs = array();
    }

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    /**
     * Get all the logs from denied access table
     * returns associative array of logs
     */
    public function get_access_denied_logs()
    {
        $this->logs = array();
        $result = $this->db->prepare('SELECT denied_log.id, UNIX_TIMESTAMP(tried), ipaddress, page, msg, users.id, users.title, initials, surname FROM denied_log, users WHERE denied_log.userID = users.id ORDER BY tried DESC LIMIT 10000');
        $result->execute();
        $result->store_result();
        $result->bind_result($id, $tried, $ipaddress, $page, $msg, $userID, $title, $initials, $surname);
        while ($result->fetch()) {
            $this->logs[] = array('id' => $id, 'tried' => $tried, 'ipaddress' => $ipaddress, 'page' => $page, 'msg' => $msg, 'userID' => $userID, 'title' => $title, 'initials' => $initials, 'surname' => $surname);
        }
        $result->close();
        return $this->logs;
    }

    /**
     * Clear All the logs from table
     */
    public function delete_access_denied_logs()
    {
        $result = $this->db->prepare('DELETE FROM denied_log');
        $result->execute();
        $result->close();
        if ($this->db->errno == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete a log from the table
     * @param $log_id
     */
    public function delete_a_access_denied_log($log_id)
    {
        $result = $this->db->prepare('DELETE FROM denied_log WHERE id = ?');
        $result->bind_param('i', $log_id);
        $result->execute();
        $result->close();
        if ($this->db->errno == 0) {
            return true;
        } else {
            return false;
        }
    }
}
