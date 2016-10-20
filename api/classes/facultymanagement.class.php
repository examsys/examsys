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
* Faculty api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Faculty class
 */
class facultymanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/facultymanagement';
       
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'FACUTLY_NOT_DELETED' => 400,
        'FACUTLY_DOES_NOT_EXIST' => 401,
        'FACUTLY_NOT_UPDATED' => 402,
        'FACUTLY_NOT_CREATED' => 403,
        'FACUTLY_NOT_DELETED_INUSE' => 404,
        'FACUTLY_ALREADY_EXISTS' => 405,
        'FACUTLY_NAME_NOT_SUPPLIED' => 406,
        'FACUTLY_NOTHING_TO_UPDATE' => 407
    );
    
    /**
     * Create faculty
     * @param array $params faculty creation parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and faculty id
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('faculty_not_created', 'faculty_already_exists', 'faculty_name_not_supplied'));
        
        // Name must be supplied.
        if (!isset($params['name']) or $params['name'] === '') {
            $data = array('statuscode' => $this->statuscodes['FACUTLY_NAME_NOT_SUPPLIED'], 'status' => $strings['faculty_name_not_supplied'], 'id' => null, 'externalid' => null);
        } else {
            // Create faculty.
            $facultyid = false;
            // Check external id unique.
            if (!empty($params['externalid'])) {
                $facultyid = \FacultyUtils::get_facultyid_from_externalid($params['externalid'], $this->db);
            }
            // Check faculty code unique.
            if (!$facultyid and !empty($params['code'])) {
                $facultyid = \FacultyUtils::get_facultyid_by_code($params['code'], $this->db);
            } elseif (!$facultyid) {
                $facultyid = \FacultyUtils::facultyid_by_name($params['name'], $this->db);
            }
            if (!$facultyid) {
                // Default null externalid.
                if (!isset($params['externalid'])) {
                    $params['externalid'] = null;
                }
                // Default null externalsys.
                if (!isset($params['externalsys'])) {
                    $params['externalsys'] = null;
                }
                // Default null code.
                if (!isset($params['code'])) {
                    $params['code'] = null;
                }
                $id = \FacultyUtils::add_faculty($params['name'], $this->db, $params['code'], $params['externalid'], $params['externalsys']);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'externalid' => $params['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_CREATED'], 'status' => $strings['faculty_not_created'], 'id' => null, 'externalid' => null);
                }
            } else {
                $details = \FacultyUtils::get_faculty_details_by_id($facultyid, $this->db);
                $externalid = $details['externalid'];
                $data = array('statuscode' => $this->statuscodes['FACUTLY_ALREADY_EXISTS'], 'status' => $strings['faculty_already_exists'], 'id' => $facultyid, 'externalid' => $externalid);
            }
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * Update faculty
     * @param array $params faculty update parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and faculty id
     */
    public function update($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('faculty_not_updated', 'faculty_does_not_exist'
            , 'faculty_already_exists', 'faculty_name_not_supplied', 'faculty_nothing_to_update'));
        if (isset($params['id']) and $params['id'] !== '') {
            $facultyid = \FacultyUtils::faculty_name_by_id($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id to update faculty.
            $facultyid = \FacultyUtils::get_facultyid_from_externalid($params['externalid'], $this->db);
            if ($facultyid) {
                $params['id'] = $facultyid;
            }
        } else {
            $facultyid = false;
        }
        
        if ($facultyid) {
            $details = \FacultyUtils::get_faculty_details_by_id($params['id'], $this->db);
            // Check if anything has been updated.
            $checkparameter = array('name', 'code');
            $change = $this->check_if_updated($checkparameter, $details, $params);
        }
        // Get code if not provided.
        if ($facultyid and (empty($params['code']))) {
            if (!isset($params['code'])) {
                $params['code'] = $details['code'];
            }
        }
        // Update faculty.
        if ($facultyid) {
            if ($change) {
                $update = \FacultyUtils::update_faculty($params['id'], $params['name'], $params['code'], $details['externalid'], $details['externalsys'], $this->db);
                if ($update) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_UPDATED'], 'status' => $strings['faculty_not_updated'], 'id' => null, 'externalid' => null);
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['FACUTLY_NOTHING_TO_UPDATE'], 'status' => $strings['faculty_nothing_to_update'], 'id' => null, 'externalid' => null);
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['FACUTLY_DOES_NOT_EXIST'], 'status' => $strings['faculty_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'update', $params['nodeid']);
    }

    /**
     * Delete faculty
     * @param array $parms delete faculty parameters
     * @param integer $userid rogo user id linked to web service client
     * @return success status and faculty id 
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('faculty_not_deleted_inuse', 'faculty_not_deleted'
            , 'faculty_does_not_exist'));
        if (isset($params['id']) and $params['id'] !== '') {
            $facultyid = \FacultyUtils::faculty_name_by_id($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id to delete faculty.
            $params['id'] = \FacultyUtils::get_facultyid_from_externalid($params['externalid'], $this->db);
            $facultyid = true;
        } else {
            $facultyid = false;
        }
        if ($facultyid) {
            $details = \FacultyUtils::get_faculty_details_by_id($params['id'], $this->db);
            // Only delete faculty if it contains no schools.
            $schools = \FacultyUtils::count_schools_in_faculty($params['id'], $this->db);
            if (isset($schools) and $schools > 0) {
                $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_DELETED_INUSE'], 'status' => $strings['faculty_not_deleted_inuse'], 'id' => null, 'externalid' => null);
            } else {
                $deleted = \FacultyUtils::delete_faculty($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['FACUTLY_NOT_DELETED'], 'status' => $strings['faculty_not_deleted'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['FACUTLY_DOES_NOT_EXIST'], 'status' => $strings['faculty_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}