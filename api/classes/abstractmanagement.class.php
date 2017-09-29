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
* Abstract API functionality
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Abstract management class.
 * 
 * This class should be extend by classes used to creation academic constructs such as
 *  modules, schools, courses etc.
 */
abstract class abstractmanagement {
           
    /**
     * Abstract create function
     * 
     * Operation to create an academic construct such as a module or school.
     * @param array $params - parametes in request
     * @param integer $userid rogo user id linked to web service client
     * @return array response to operation, id of construct or error message.
     */
    abstract public function create($params, $userid);
    
    /**
     * Abstract delete function
     * 
     * Operation to delete an academic construct such as a module or school.
     * @param array $params - parametes in request
     * @param integer $userid rogo user id linked to web service client
     * @return array response to operation, id of construct or error message.
     */
    abstract public function delete($params, $userid);
    
    /**
     * The database connection.
     */
    protected $db;
    /**
     * The oauth client id.
     */
    protected $client_id;
    /**
     * The config object.
     */
    protected $config;
    
    /**
     * Constructor
     * @param mysqli $mysqli the database connection
     * @param string $client_id the oauth client connecting
     */
    public function __construct($mysqli, $client_id = null) {
        $this->db = $mysqli;
        $this->client_id = $client_id;
        $this->config = \Config::get_instance();
    }
    
    /**
     * Response creator
     * 
     * A response to an academic construct operation.
     * @param array $data - Response data
     * @param string $action - Relevant action
     * @param integer $nodeid - Request Node id
     * @param array $error - array of errors generated
     * return array response to operation, id of construct or error message.
     */
    public function get_response($data, $action, $nodeid = null, $error = null) {
        // Not all APIs support externalid so set to null if not set.
        if (!isset($data['externalid'])) {
            $data['externalid'] = null;
        }
        return $response = array(
            "statuscode" => $data['statuscode'],
            "status" => $data['status'],
            "id" => $data['id'],
            "externalid" => $data['externalid'],
            "error" => $error,
            "node" => $action,
            "nodeid" => $nodeid);
    }
    
    /**
     * Check if the parameters sent to the API will result in an update
     * @param array $parameters the parameters to check
     * @param array $current current values
     * @param array $update request update values
     * @return bool true if a value has been updated
     */
    public function check_if_updated($parameters, $current, $update) {
        foreach ($parameters as $check) {
            if (!empty($update[$check])) {
                if ($current[$check] != $update[$check]) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Get the external system the request is for
     * @param string $externalsys system provided in request (used by sms plugins)
     * @return string external system
     */
    public function get_external_system($externalsys) {
        if (is_null($this->client_id)) {
            // SMS plugin
            return $externalsys;
        } else {
            // API so check external system mapping.
            $external = new \external_systems();
            $system = $external->get_mapped_externalsystem_info($this->client_id);
            if (count($system) == 2) {
                // Mapped so can only manipulate $system.
                return $system['name'];
            } else {
                // Super users can call the api for an external system.
                if ($this->config->get_setting('core', 'api_allow_superuser')) {
                    // Not mapped to specific external system so can manipulate any.
                    return $externalsys;
                } else {
                    // A null external system will cause future checks to fail.
                    return null;
                }
            }
        }
    }
}