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
* Dchool api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * School class
 */
class schoolmanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/schoolmanagement';
    
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'SCHOOL_NOT_DELETED' => 600,
        'SCHOOL_DOES_NOT_EXIST' => 601,
        'SCHOOL_NOT_UPDATED' => 602,
        'SCHOOL_NOT_CREATED' => 603,
        'SCHOOL_NOT_DELETED_INUSE' => 604,
        'SCHOOL_FACULTY_INVALID' => 605,
        'SCHOOL_ALREADY_EXISTS' => 606,
        'SCHOOL_NOTHING_TO_UPDATE' => 607,
        'SCHOOL_FACULTY_EXTID_INVALID' => 608
    );
        
    /**
     * Create school
     * @param array $params school creation parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and school id
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('school_not_created', 'school_already_exists', 'faculty_not_supplied' ,'external_faculty_invalid'));
        $faculty = true;
        // Get faculty if provided.
        if (isset($params['facultyextid']) and $params['facultyextid'] !== '') {
            $facultyid = \FacultyUtils::get_facultyid_from_externalid($params['facultyextid'], $this->db);
            if (!$facultyid) {
                $data = array('statuscode' => $this->statuscodes['SCHOOL_FACULTY_EXTID_INVALID'], 'status' => $strings['external_faculty_invalid'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'create', $params['nodeid']);
            }
        } elseif (!empty($params['faculty'])) {
            $facultyid = \FacultyUtils::facultyid_by_name($params['faculty'], $this->db);
            if (!$facultyid) {
                $facultyid = \FacultyUtils::add_faculty($params['faculty'], $this->db);
            }
        } else {
            $faculty = false;
        }
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
        if ($faculty) {
            $schoolid = false;
            if (!empty($params['externalid'])) {
                $schoolid = \SchoolUtils::get_schoolid_from_externalid($params['externalid'], $this->db);
            }
            if (!$schoolid and !empty($params['code'])) {
                $schoolid = \SchoolUtils::get_schoolid_by_code($params['code'], $this->db);
            } elseif (!$schoolid) {
                $schoolid = \SchoolUtils::get_school_id_by_name($params['name'], $this->db);
            }
            if (!$schoolid) {
                $id = \SchoolUtils::add_school($facultyid, $params['name'], $this->db, $params['code'], $params['externalid'], $params['externalsys']);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'externalid' => $params['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_CREATED'], 'status' => $strings['school_not_created'], 'id' => null, 'externalid' => null);
                }
            } else {
                $details = \SchoolUtils::get_school_details_by_id($schoolid, $this->db);
                $externalid = $details['externalid'];
                $data = array('statuscode' => $this->statuscodes['SCHOOL_ALREADY_EXISTS'], 'status' => $strings['school_already_exists'], 'id' => $schoolid, 'externalid' => $externalid);
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['SCHOOL_FACULTY_INVALID'], 'status' => $strings['faculty_not_supplied'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * Update school
     * @param array $params school update parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and school id
     */
    public function update($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('school_not_updated', 'school_does_not_exist'
            , 'faculty_not_supplied' , 'school_nothing_to_update', 'external_faculty_invalid'));
        $faculty = true;
        if (!empty($params['id'])) {
            $schoolid = \SchoolUtils::schoolid_exists($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id to update school.
            $schoolid = \SchoolUtils::get_schoolid_from_externalid($params['externalid'], $this->db);
            $params['id'] = $schoolid;
        } else {
            $schoolid = false;
        }
        
        if ($schoolid) {
            $details = \SchoolUtils::get_school_details_by_id($params['id'], $this->db);
            // Check if anything has been updated.
            $checkparameter = array('name', 'code');
            $change = $this->check_if_updated($checkparameter, $details, $params);
        } else {
            $data = array('statuscode' => $this->statuscodes['SCHOOL_DOES_NOT_EXIST'], 'status' => $strings['school_does_not_exist'], 'id' => null, 'externalid' => null);
            return $this->get_response($data, 'update', $params['nodeid']);
        }
        
        // Get name if not provided.
        if (empty($params['name'])) {
            if (!isset($params['name'])) {
                $params['name'] = $details['name'];
            }
        }
        
        // Get code if not provided.
        if (!isset($params['code'])) {
            $params['code'] = $details['code'];
        }
        
        // Get faculty if provided.
        if (!empty($params['facultyextid'])) {
            $facultyid = \FacultyUtils::get_facultyid_from_externalid($params['facultyextid'], $this->db);
            if (!$facultyid) {
                $data = array('statuscode' => $this->statuscodes['SCHOOL_FACULTY_EXTID_INVALID'], 'status' => $strings['external_faculty_invalid'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'update', $params['nodeid']);
            }
            // Mark something is to be updated.
            if ($details['faculty'] != $facultyid) {
                $change = true;
            }
        } elseif (!empty($params['faculty'])) {
            $facultyid = \FacultyUtils::facultyid_by_name($params['faculty'], $this->db);
            if (!$facultyid) {
                $facultyid = \FacultyUtils::add_faculty($params['faculty'], $this->db);
            }
            // Mark something is to be updated.
            if ($details['faculty'] != $facultyid) {
                $change = true;
            }
        // Get faculty if not provided.           
        } elseif (!isset($params['faculty'])) {
            $facultyid = $details['faculty'];
        } else {
            $faculty = false;
        }
        
        if ($faculty) {        
            // Update school.
            if ($change) {
                $update = \SchoolUtils::update_school($schoolid, $facultyid, $params['name'], $params['code'], $details['externalid'], $details['externalsys'], $this->db);
                if ($update) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $schoolid, 'externalid' => $details['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_UPDATED'], 'status' => $strings['school_not_updated'], 'id' => null, 'externalid' => null);
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['SCHOOL_NOTHING_TO_UPDATE'], 'status' => $strings['school_nothing_to_update'], 'id' => null, 'externalid' => null);
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['SCHOOL_FACULTY_INVALID'], 'status' => $strings['faculty_not_supplied'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'update', $params['nodeid']);
    }

    /**
     * Delete school
     * @param array $parms delete school parameters
     * @param integer $userid rogo user id linked to web service client
     * @return success status and school id
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('school_not_deleted_inuse', 'school_not_deleted'
            , 'school_does_not_exist'));
        if (!empty($params['id'])) {
            $schoolid = \SchoolUtils::schoolid_exists($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id to delete school.
            $params['id'] = \SchoolUtils::get_schoolid_from_externalid($params['externalid'], $this->db);
            $schoolid = true;
        } else {
            $schoolid = false;
        }
        if ($schoolid) {
            $details = \SchoolUtils::get_school_details_by_id($params['id'], $this->db);
            // Only delete school if it contains no modules or courses.
            $inuse = \SchoolUtils::school_in_use($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_DELETED_INUSE'], 'status' => $strings['school_not_deleted_inuse'], 'id' => null, 'externalid' => null);
            } else {
                $deleted = \SchoolUtils::delete_school($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['SCHOOL_NOT_DELETED'], 'status' => $strings['school_not_deleted'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['SCHOOL_DOES_NOT_EXIST'], 'status' => $strings['school_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}