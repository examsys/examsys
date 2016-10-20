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
* Course api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Course class
 */
class coursemanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/coursemanagement';
    
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'COURSE_NOT_DELETED' => 300,
        'COURSE_DOES_NOT_EXIST' => 301,
        'COURSE_NOT_DELETED_INUSE' => 302,
        'COURSE_INVALID_FACULTY' => 303,
        'COURSE_NOT_UPDATED' => 304,
        'COURSE_NOT_CREATED' => 305,
        'COURSE_ALREADY_EXISTS' => 306,
        'COURSE_INVALID_SCHOOL' => 307,
        'COURSE_NOTHING_TO_UPDATE' => 308,
        'COURSE_SCHOOL_EXTID_INVALID' => 309
    );
    
    /**
     * Update course
     * @param array $params course update parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and course id
     */
    public function update($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('course_not_updated', 'course_does_not_exist',
            'course_already_exists', 'faculty_not_supplied', 'school_not_supplied', 'course_nothing_to_update', 'external_school_invalid'));
        $faculty = true;
        if (isset($params['id']) and $params['id'] !== '') {
            // Using internal rogo id to update course.
            $courseid = \CourseUtils::courseid_exists($params['id'], $this->db);  
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id to update course.
            $courseid = \CourseUtils::get_courseid_from_externalid($params['externalid'], $this->db);
            $params['id'] = $courseid;
        } else {
            $courseid = false;
        }
        // Get course details.
        if ($courseid) {
            $details = \CourseUtils::get_course_details_by_id($params['id'], $this->db);
            // Check if anything has been updated.
            $checkparameter = array('name', 'description');
            $change = $this->check_if_updated($checkparameter, $details, $params);
        } else {
            $data = array('statuscode' => $this->statuscodes['COURSE_DOES_NOT_EXIST'], 'status' => $strings['course_does_not_exist'], 'id' => null, 'externalid' => null);
            return $this->get_response($data, 'update', $params['nodeid']);
        }

        // Use external school/faculty id if provided
        if (isset($params['schoolextid']) and $params['schoolextid'] !== '') {
            // Get school id if school external id provided.
            $schoolid = \SchoolUtils::get_schoolid_from_externalid($params['schoolextid'], $this->db);
            if ($schoolid) {
                $faculty = true;
            } else {
                $data = array('statuscode' => $this->statuscodes['COURSE_SCHOOL_EXTID_INVALID'], 'status' => $strings['external_school_invalid'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'update', $params['nodeid']);
            }
        // Get school id if school name not provided.
        } elseif (isset($params['school']) and $params['school'] !== '') {
            $schoolid = \SchoolUtils::school_name_exists($params['school'], $this->db);
            if (!$schoolid) {
                if (isset($params['faculty']) and $params['faculty'] !== '') {
                    $schoolid = \SchoolUtils::generate_school_id($params['school'], $params['faculty'], $this->db);
                } else {
                    $faculty = false;
                }
            }
            // Mark something is to be updated.
            if ($details['schoolid'] != $schoolid) {
                $change = true;
            }
        } else {
            $schoolid = $details['schoolid'];
        }
        // If creating/updating module with a new school, faculty needs to be supplied.
        if ($faculty) {
            // Get description if not provided.
            if (!isset($params['description']) or $params['description'] === '') {
                $params['description'] = $details['description'];
            }
            // Get name if not provided.
            if (!isset($params['name']) or $params['name'] === '') {
                $params['name'] = $details['name'];
            }
            // Get externalid if not provided.
            if (!isset($params['externalid']) or $params['externalid'] === '') {
                $params['externalid'] = $details['externalid'];
            }
            // Update Course.
            if ($change) {
                if ($schoolid == $details['schoolid'] and isset($params['faculty']) and $params['faculty'] !== '') {
                    $data = array('statuscode' => $this->statuscodes['COURSE_INVALID_SCHOOL'], 'status' => $strings['school_not_supplied'], 'id' => null, 'externalid' => null);
                } else {
                    $update = \CourseUtils::update_course($params['id'], $schoolid, $params['name'], $params['description'], $params['externalid'], $details['externalsys'], $this->db);
                    if ($update) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['externalid']);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['COURSE_NOT_UPDATED'], 'status' => $strings['course_not_updated'], 'id' => null, 'externalid' => null);
                    }
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['COURSE_NOTHING_TO_UPDATE'], 'status' => $strings['course_nothing_to_update'], 'id' => null, 'externalid' => null);
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['COURSE_INVALID_FACULTY'], 'status' => $strings['faculty_not_supplied'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'update', $params['nodeid']);
    }
    
    /**
     * Create course
     * @param array $params course creation parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and course id
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('course_not_created', 'course_already_exists', 'faculty_not_supplied', 'external_school_invalid'));
        $faculty = true;
        $schoolid = false;
        if (isset($params['schoolextid']) and $params['schoolextid'] !== '') {
            // Get school id if school external id provided.
            $schoolid = \SchoolUtils::get_schoolid_from_externalid($params['schoolextid'], $this->db);
            if (!$schoolid) {
                $data = array('statuscode' => $this->statuscodes['COURSE_SCHOOL_EXTID_INVALID'], 'status' => $strings['external_school_invalid'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'create', $params['nodeid']);
            }
        } elseif (isset($params['school']) and $params['school'] !== '') {
            // Get school id if school name provided.
            $schoolid = \SchoolUtils::school_name_exists($params['school'], $this->db);
        }
        // No school provided so create one.
        if (!$schoolid) {
            if (isset($params['faculty']) and $params['faculty'] !== '') {
                // Create a school using faculty name.
                $schoolid = \SchoolUtils::generate_school_id($params['school'], $params['faculty'], $this->db);
            } else {
                $faculty = false;
            }
        }
        // If creating a module with a new school, faculty needs to be supplied.
        if ($faculty) {
            // Create Course.
            $courseid = \CourseUtils::get_course_id($params['name'], $this->db);
            if (!$courseid) {
                // Default null externalid.
                if (empty($params['externalid'])) {
                    $params['externalid'] = null;
                }
                // Default null externalsys.
                if (empty($params['externalsys'])) {
                    $params['externalsys'] = null;
                }
                $id = \CourseUtils::add_course($schoolid, $params['name'], $params['description'], $params['externalid'], $params['externalsys'], $this->db);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'externalid' => $params['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['COURSE_NOT_CREATED'], 'status' => $strings['course_not_created'], 'id' => null, 'externalid' => null);
                }
            } else {
                $details = \CourseUtils::get_course_details_by_id($courseid, $this->db);
                $externalid = $details['externalid'];
                $data = array('statuscode' => $this->statuscodes['COURSE_ALREADY_EXISTS'], 'status' => $strings['course_already_exists'], 'id' => $courseid, 'externalid' => $externalid);
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['COURSE_INVALID_FACULTY'], 'status' => $strings['faculty_not_supplied'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }
    
    /**
     * Delete course
     * @param array $parms delete course parameters
     * @param integer $userid rogo user id linked to web service client
     * @return success status and course id 
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('course_not_deleted_inuse', 'course_not_deleted'
            , 'course_does_not_exist'));
        if (isset($params['id']) and $params['id'] !== '') {
            // Try using rogo internal id to delete course.
            $courseid = \CourseUtils::courseid_exists($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id to delete course.
            $courseid = \CourseUtils::get_courseid_from_externalid($params['externalid'], $this->db);
            $params['id'] = $courseid;
        } else {
            $courseid = false;
        }
        if ($courseid) {
            $details = \CourseUtils::get_course_details_by_id($params['id'], $this->db);
            // Only delete course if it contains no users.
            $users = \CourseUtils::count_users_on_course($details['name'], $this->db);
            if (isset($users) and $users > 0) {
                $data = array('statuscode' => $this->statuscodes['COURSE_NOT_DELETED_INUSE'], 'status' => $strings['course_not_deleted_inuse'], 'id' => null, 'externalid' => null);
            } else {
                $deleted = \CourseUtils::delete_course_by_id($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['COURSE_NOT_DELETED'], 'status' => $strings['course_not_deleted'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['COURSE_DOES_NOT_EXIST'], 'status' => $strings['course_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}