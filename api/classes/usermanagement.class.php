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
* Course enrolment api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Person class
 */
class usermanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/usermanagement';
    /**
     * List of valid roles for students within Rogo that the API can assign.
     * @var array $studentroles
     */
    private static $studentroles = array('Student', 'Left', 'Graduate', 'Suspended', 'Locked');
    /**
     * List of valid roles for staff members within Rogo that the API can assign.
     * @var array $staffroles
     */
    private static $staffroles = array('Staff', 'Inactive Staff');
    /**
     * List of valid staff member courses within Rogo that the API can assign.
     * @var array $staffcourses
     */
    private static $staffcourses = array('University Lecturer', 'NHS Lecturer');
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'USER_NOT_DELETED' => 700,
        'USER_DOES_NOT_EXIST' => 701,
        'USER_NOT_UPDATED' => 702,
        'USER_NOT_CREATED' => 703,
        'USER_NOT_DELETED_INUSE' => 704,
        'USER_INVALID_COURSE' => 705,
        'USER_ALREADY_EXISTS' => 706,
        'USER_INVALID_ROLE' => 707,
        'USER_NOTHING_TO_UPDATE' => 708
    );
        
    /**
     * Enrol users onto modules.
     * @param integer $id - user id
     * @param array $modules - modules to (un)enrol user
     * @param string $role  - role of user
     * @return array error status
     */
    private function user_modules($id, $modules, $role) {
        $langpack = new \langpack();
        $error = array();
        $yearutils = new \yearutils($this->db);
        $session = $yearutils->get_current_session();
        if (count($modules) > 0) {
            foreach ($modules as $module) {
                if ($module['name'] == 'moduleid') {
                    if ($role == 'Student') {
                        $enrol = \UserUtils::add_student_to_module($id, $module['value'], 1, $session, $this->db, 1);
                        if (!$enrol) {   
                            $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'enrol_onto_module_fail'), $module['value']);
                        }
                    } elseif ($role == 'Staff') {
                        $enrol = \UserUtils::add_staff_to_module($id, $module['value'], $this->db);
                        if (!$enrol) {
                            $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'enrol_onto_module_fail'), $module['value']);
                        }
                    }
                }
            }
        }
        return $error;
    }
    
    /**
     * Check role is valid.
     * @param string $params action parameters
     * @return string|bool course student is enrolled on or staff type if staff, false if staff member course is invalid,
     * 'UNKNOWN if user role is invalid.
     */
    private function check_roles($params) {
        $roles = array_merge(self::$studentroles, self::$staffroles);
        if (!in_array($params['role'], $roles)) {
            return 'UNKNOWN';
        } else {
            // Students.
            if (in_array($params['role'], self::$studentroles)) {
                $course = \CourseUtils::course_exists($params['course'], $this->db);
            // Staff.
            } else {
                if (in_array($params['course'], self::$staffcourses)) {
                    $course = $params['course'];
                } else {
                    $course = false;
                }
            }
        }
        return $course;
    }
    /**
     * Create user
     * @param array $params create user params
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and user id
     */ 
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_invalid_role', 'user_not_created', 'course_does_not_exist', 'user_already_exists'));
        $error = array();
        $userexists = false;
        $checkparameter = array('username', 'password', 'title', 'forename', 'surname', 'email', 'course',
                    'gender', 'year', 'role', 'studentid', 'initials');
        // Set defaults if not provided.
        foreach ($checkparameter as $name) {
            if (empty($params[$name])) {
                $params[$name] = '';
            }
        }
        $course = $this->check_roles($params);
        if ($course === 'UNKNOWN') {
            $data = array('statuscode' => $this->statuscodes['USER_INVALID_ROLE'], 'status' => $strings['user_invalid_role'], 'id' => null, 'externalid' => null);
            return $this->get_response($data, 'create', $params['nodeid'], array());
        }
        if ($course) {
            // Create.
            $id = \UserUtils::create_user($params['username'], $params['password'], $params['title'],
                $params['forename'], $params['surname'], $params['email'], $params['course'],
                $params['gender'], $params['year'], $params['role'], $params['studentid'], $this->db, $params['initials']);
            if ($id) {
                if (!empty($params['modules'])) {
                    $error = $this->user_modules($id, $params['modules'], $params['role']);
                }
                $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'error' => $error, 'externalid' => $params['studentid']);
            } else {
                // Check if user exists, otherwise throw generic error.
                $userexists = \UserUtils::username_exists($params['username'], $this->db);
                if ($userexists) {
                    $details = \UserUtils::get_full_details_by_ID($userexists, $this->db);
                    $data = array('statuscode' => $this->statuscodes['USER_ALREADY_EXISTS'], 'status' => $strings['user_already_exists'], 'id' => $userexists, 'externalid' => $details['studentid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['USER_NOT_CREATED'], 'status' => $strings['user_not_created'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['USER_INVALID_COURSE'], 'status' => $strings['course_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'create', $params['nodeid'], $error);
    }
 
    /**
     * Update user
     * @param array $params update user params
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and user id
     */ 
    public function update($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_invalid_role', 'user_does_not_exist'
            , 'user_not_updated', 'user_not_created', 'course_does_not_exist', 'user_already_exists', 'user_nothing_to_update'));
        $error = array();
        $userexists = false;
        $checkparameter = array('username', 'password', 'title', 'forename', 'surname', 'email', 'course',
                    'gender', 'year', 'role', 'studentid', 'initials');
                    
        if (!empty($params['id'])) {
            $userexists = \UserUtils::userid_exists($params['id'], $this->db);
            if ($userexists) {
                $details = \UserUtils::get_full_details_by_ID($params['id'], $this->db);
                 // Check if anything has been updated.
                $change = $this->check_if_updated($checkparameter, $details, $params);
            }
        } else {
            // Set id to false if not supplied others to 0 if suppleid but invalid.
            if (isset($params['id'])) {
                $params['id'] = 0;
            } else {
                $params['id'] = false;
            }
        }

        if ($userexists) {
            // Mark something is to be updated.
            if (!empty($params['modules'])) {
                $change = true;
            }
            
            // If nothing updated return.
            if (!$change) {
                $data = array('statuscode' => $this->statuscodes['USER_NOTHING_TO_UPDATE'], 'status' => $strings['user_nothing_to_update'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'update', $params['nodeid'], $error);
            }
        }
        
        // Set defaults if not provided.
        foreach ($checkparameter as $name) {
            if (empty($params[$name])) {
                if ($userexists) {
                    $params[$name] = $details[$name];
                } else {
                    $params[$name] = '';
                }
            }
        }
        // If parameter id supplied but not a valid user - exception.
        // If parameter id supplied as 0 - exception.
        if ((!$userexists and $params['id']) or (!$userexists and $params['id'] === 0)) {
            $data = array('statuscode' => $this->statuscodes['USER_DOES_NOT_EXIST'], 'status' => $strings['user_does_not_exist'], 'id' => null, 'externalid' => null);
        } else {
            $course = $this->check_roles($params);
            if ($course === 'UNKNOWN') {
                $data = array('statuscode' => $this->statuscodes['USER_INVALID_ROLE'], 'status' => $strings['user_invalid_role'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'update', $params['nodeid'], array());
            }
            if ($course) {
                // Update.
                $update = \UserUtils::update_user($params['id'], $params['username'], $params['password'], $params['title'],
                            $params['forename'], $params['surname'], $params['email'], $params['course'],
                            $params['gender'], $params['year'], $params['role'], $params['studentid'], $this->db, $params['initials']);
                if ($update) {
                    if (!empty($params['modules'])) {
                        $error = $this->user_modules($params['id'], $params['modules'], $params['role']);
                    }
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['studentid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['USER_NOT_UPDATED'], 'status' => $strings['user_not_updated'], 'id' => null, 'externalid' => null);
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['USER_INVALID_COURSE'], 'status' => $strings['course_does_not_exist'], 'id' => null, 'externalid' => null);
            }
        }
        return $this->get_response($data, 'update', $params['nodeid'], $error);
    }

    /**
     * Delete user
     * @param array $parms delete user parameters
     * @param integer $userid rogo user id linked to web service client
     * @return  
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_paper_exists' ,'user_not_deleted',
            'user_does_not_exist'));
        if (!empty($params['id'])) {
            $userexists = \UserUtils::userid_exists($params['id'], $this->db);
        } else {
            $userexists = false;
        }
        if ($userexists) {
            // Only delete user they have taken no papers
            $inuse = \UserUtils::user_paper_started($params['id'], $this->db);
            $details = \UserUtils::get_full_details_by_ID($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['USER_NOT_DELETED_INUSE'], 'status' => $strings['user_paper_exists'], 'id' => null, 'externalid' => null);
            } else {
                $deleted = \UserUtils::delete_userID($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['studentid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['USER_NOT_DELETED'], 'status' => $strings['user_not_deleted'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['USER_DOES_NOT_EXIST'], 'status' => $strings['user_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }  
}