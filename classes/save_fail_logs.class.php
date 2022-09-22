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
class save_fail_logs
{
    private $db;
    private $logs;

    /**
     * Uses for save fail logs
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
     * Get all the logs from save fail table
     * returns associative array of logs
     */
    public function get_save_fail_logs()
    {
        $this->logs = array();
        $result = $this->db->prepare("SELECT save_fail_log.id, surname, title, initials, userID, paperID, screen, ipaddress, FROM_UNIXTIME(failed, '%d/%m/%Y %H:%i:%s'), paper_title, status, request_url, response_data FROM save_fail_log, users, properties WHERE save_fail_log.userID = users.id AND save_fail_log.paperID = properties.property_id ORDER BY failed");
        $result->execute();
        $result->store_result();
        $result->bind_result($id, $surname, $title, $initials, $userID, $paperID, $screen, $ipaddress, $failed, $paper_title, $status, $request, $response);
        while ($result->fetch()) {
            $this->logs[] = array('id' => $id,
            'surname' => $surname,
            'title' => $title,
            'initials' => $initials,
            'userID' => $userID,
            'paperID' => $paperID,
            'screen' => $screen,
            'ipaddress' => $ipaddress,
            'paper_title' => $paper_title,
            'failed' => $failed,
            'status' => $status,
            'request' => $request,
            'response' => $response);
        }
        $result->close();
        return $this->logs;
    }

    /**
     * Clear All the logs from the table
     */
    public function delete_save_fail_logs()
    {
        $result = $this->db->prepare('DELETE FROM save_fail_log');
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
    public function delete_a_save_fail_log($log_id)
    {
        $result = $this->db->prepare('DELETE FROM save_fail_log WHERE id = ?');
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
