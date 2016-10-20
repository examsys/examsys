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
* Module api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Course class
 */
class modulemanagement extends \api\abstractmanagement {
       
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/modulemanagement';
    
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'MODULE_NOT_DELETED' => 500,
        'MODULE_DOES_NOT_EXIST' => 501,
        'MODULE_NOT_DELETED_INUSE' => 502,
        'MODULE_NOT_UPDATED' => 503,
        'MODULE_NOT_CREATED' => 504,
        'MODULE_ALREADY_EXISTS' => 505,
        'MODULE_INVALID_FACULTY' => 506,
        'MODULE_INVALID_USER' => 507,
        'MODULE_USER_NOT_ENROLLED' => 508,
        'MODULE_USER_NOT_UNENROLLED' => 509,
        'MODULE_SESSION_NOT_SUPPLIED' => 510,
        'MODULE_INVALID_SCHOOL' => 511,
        'MODULE_NOTHING_TO_UPDATE' => 512,
        'MODULE_SCHOOL_EXTID_INVALID' => 513,
        'MODULE_USER_ALREADY_ENROLLED' => 514
    );
           
    /**
     * Enrol student on a Module.
     * @param array $params module enrol parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array - success status and enrolment id
     */
    public function enrol($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_not_enrolled', 'user_does_not_exist', 'user_already_enrolled'));
        $userexists = false;
        if (isset($params['userid'])) {
            $userexists = \UserUtils::userid_exists($params['userid'], $this->db);
        } elseif (isset($params['studentid'])) {
            $params['userid'] = \UserUtils::studentid_exists($params['studentid'], $this->db);
            $userexists = $params['userid'];
        }
        if ($userexists) {
            $yearutils = new \yearutils($this->db);
            if (empty($params['session'])) {
                $session = $yearutils->get_current_session();
            } else {
                $session = $params['session'];
            }
            if (!empty($params['moduleid'])) {
                $modid = $params['moduleid'];
            } elseif (!empty($params['moduleextid'])) {
                // Try using external system id to enrol.
                $modid = \module_utils::get_id_from_externalid($params['moduleextid'], $this->db);
                if ($modid === false) {
                    $modid = '';
                }
            } else {
                $modid = '';
            }
            $ret = \UserUtils::add_student_to_module($params['userid'], $modid, $params['attempt'], $session, $this->db, 1);
            if ($ret === 0) {
                // Already enrolled so just update. Essential the web service taking ownership.
                $id = \UserUtils::get_enrolement_id($params['userid'], $modid, $session, $this->db);
                \UserUtils::update_module_enrolement($id, $params['attempt'], 1, $this->db);
                $data = array('statuscode' => $this->statuscodes['MODULE_USER_ALREADY_ENROLLED'], 'status' => 'OK', 'id' => $id, 'externalid' => null);
            } else {
                if ($ret) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $ret, 'externalid' => null);
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_USER_NOT_ENROLLED'], 'status' => $strings['user_not_enrolled'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_USER'], 'status' => $strings['user_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'enrol', $params['nodeid']);
    }
    
    /**
     * UnEnrol student on a Module.
     * @param array $params module enrol parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array - success status and enrolment id
     */
    public function unenrol($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('user_not_unenrolled', 'user_does_not_exist', 'session_not_supplied'));
        $userexists = false;
        if (isset($params['userid'])) {
            $userexists = \UserUtils::userid_exists($params['userid'], $this->db);
        } elseif (isset($params['studentid'])) {
            $params['userid'] = \UserUtils::studentid_exists($params['studentid'], $this->db);
            $userexists = $params['userid'];
        }
        if ($userexists) {
            if (!empty($params['moduleid'])) {
                $modid = $params['moduleid'];
            } elseif (!empty($params['moduleextid'])) {
                // Try using external system id to enrol.
                $modid = \module_utils::get_id_from_externalid($params['moduleextid'], $this->db);
                if ($modid === false) {
                    $modid = '';
                }
            } else {
                $modid = '';
            }
            $yearutils = new \yearutils($this->db);
            if (empty($params['session'])) {
                $data = array('statuscode' => $this->statuscodes['MODULE_SESSION_NOT_SUPPLIED'], 'status' => $strings['session_not_supplied'], 'id' => null, 'externalid' => null);
            } else {
                $session = $params['session'];
                if (isset($params['userid'])) {
                    $ret = \UserUtils::remove_student_from_module($params['userid'], $modid, $session, $this->db);
                } elseif (isset($params['studentid'])) {
                    $ret = \UserUtils::remove_student_from_module_by_sid($params['studentid'], $modid, $session, $this->db);
                }
                if ($ret) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $ret, 'externalid' => null);
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_USER_NOT_UNENROLLED'], 'status' => $strings['user_not_unenrolled'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_USER'], 'status' => $strings['user_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'unenrol', $params['nodeid']);
    }
    
    /**
     * Create module
     * @param array $params module creation parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and module id
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('module_not_created', 'module_already_exists', 'faculty_not_supplied', 'school_not_supplied', 'external_school_invalid'));
        $faculty = true;
        $schoolid = false;
        if (!empty($params['schoolextid'])) {
            // Get school id if school external id provided.
            $schoolid = \SchoolUtils::get_schoolid_from_externalid($params['schoolextid'], $this->db);
            if (!$schoolid) {
                $data = array('statuscode' => $this->statuscodes['MODULE_SCHOOL_EXTID_INVALID'], 'status' => $strings['external_school_invalid'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'create', $params['nodeid']);
            }
        } elseif (!empty($params['school'])) {
            // Get school id if school name provided.
            $schoolid = \SchoolUtils::school_name_exists($params['school'], $this->db);
        }
        if (!$schoolid) {
            if (!empty($params['faculty'])) {
                $schoolid = \SchoolUtils::generate_school_id($params['school'], $params['faculty'], $this->db);
            } else {
                $faculty = false;
            }
        }
        // Check if module externalid in use.
        $modextidinuse = false;
        if (isset($params['externalid'])) {
            $idMod = \module_utils::get_id_from_externalid($params['externalid'], $this->db);
            // module externalid in use already
            if ($idMod != false) {
                $modextidinuse = true;
            }
        }
        // Check if module code in use.
        $modcodeinuse = false;
        if (!$modextidinuse and !empty($params['modulecode'])) {
            $idMod = \module_utils::get_idMod($params['modulecode'], $this->db);
            // module code in use already
            if ($idMod != false) {
                $modcodeinuse = true;
            }
        }
        if ($modcodeinuse or $modextidinuse) {
            $details = \module_utils::get_full_details_by_ID($idMod, $this->db);
            $data = array('statuscode' => $this->statuscodes['MODULE_ALREADY_EXISTS'], 'status' => $strings['module_already_exists'], 'id' => $idMod, 'externalid' => $details['externalid']);
        } else {
            if ($faculty) {
                // Create Module.
                if (empty($params['sms'])) {
                    $params['sms'] = 'rogo webservice';
                }
                // Default null externalid.
                if (!isset($params['externalid'])) {
                    $params['externalid'] = null;
                }
                $id = \module_utils::add_modules($params['modulecode'], $params['name'], 1, $schoolid, '', $params['sms'],
                    '', false, false, false, false, '', '', $this->db, false, '', '', '', 0, '07/01', $params['externalid']);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'externalid' => $params['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_NOT_CREATED'], 'status' => $strings['module_not_created'], 'id' => null, 'externalid' => null);
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_FACULTY'], 'status' => $strings['faculty_not_supplied'], 'id' => null, 'externalid' => null);
            }
        }
        return $this->get_response($data, 'create', $params['nodeid']);
    }

    /**
     * Update module
     * @param array $params module update parameters
     * @param integer $userid rogo user id linked to web service client
     * @return - success status and module id
     */
    public function update($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('module_not_updated', 'module_does_not_exist', 'faculty_not_supplied', 'school_not_supplied', 'module_nothing_to_update',
             'external_school_invalid', 'module_already_exists'));
        $faculty = true;
        $moduleid = false;
        if (!empty($params['id'])) {
            $moduleid = (bool) \module_utils::get_moduleid_from_id($params['id'], $this->db);
        } elseif (!empty($params['externalid'])) {
            // Try using external system id to update course.
            $params['id'] = \module_utils::get_id_from_externalid($params['externalid'], $this->db);;
            $moduleid = (bool) $params['id'];
        }
        
        if(!$moduleid) {
            $data = array('statuscode' => $this->statuscodes['MODULE_DOES_NOT_EXIST'], 'status' => $strings['module_does_not_exist'], 'id' => null, 'externalid' => null);
            return $this->get_response($data, 'update', $params['nodeid']);
        }

        $details = \module_utils::get_full_details_by_ID($params['id'], $this->db);
        // Check if anything has been updated.
        if (!empty($params['modulecode'])) {
            $params['moduleid'] = $params['modulecode'];
        }
        if (!empty($params['name'])) {
            $params['fullname'] = $params['name'];
        }
        $checkparameter = array('moduleid', 'fullname', 'sms');
        $change = $this->check_if_updated($checkparameter, $details, $params);

        // Use external school/faculty id if provided
        if (isset($params['schoolextid']) and $params['schoolextid'] !== '') {
            // Get school id if school external id provided.
            $schoolid = \SchoolUtils::get_schoolid_from_externalid($params['schoolextid'], $this->db);
            if (!$schoolid) {
                $data = array('statuscode' => $this->statuscodes['MODULE_SCHOOL_EXTID_INVALID'], 'status' => $strings['external_school_invalid'], 'id' => null, 'externalid' => null);
                return $this->get_response($data, 'update', $params['nodeid']);
            }
        // Get school id if school name provided.
        } elseif (!empty($params['school'])) {
            $schoolid = \SchoolUtils::school_name_exists($params['school'], $this->db);
            if (!$schoolid) {
                if (!empty($params['faculty'])) {
                    $schoolid = \SchoolUtils::generate_school_id($params['school'], $params['faculty'], $this->db);
                } else {
                    $faculty = false;
                }
            }
            // Mark something is to be updated.
            if ($details['schoolid'] != $schoolid) {
                $change = true;
            }
        // Get school id if school name not provided.
        } else {
            $schoolid = $details['schoolid'];
        }
        
        // Cheeck if module code in use.
        $modcodeinuse = false;
        if (!empty($params['modulecode'])) {
            $modid = \module_utils::get_idMod($params['modulecode'], $this->db);
            // module code in use already
            if ($modid != false) {
                if ($modid != $params['id']) {
                    $modcodeinuse = true;
                }
            }
        }
        
        if ($modcodeinuse) {
            $data = array('statuscode' => $this->statuscodes['MODULE_ALREADY_EXISTS'], 'status' => $strings['module_already_exists'], 'id' => $modid, 'externalid' => null);
        } else {
            if ($faculty) {
                // Get module code if not provided.
                if (empty($params['modulecode'])) {
                    $params['modulecode'] = $details['moduleid'];
                }
                // Get name if not provided.
                if ((empty($params['name']))) {
                    $params['name'] = $details['fullname'];
                }
                
                // Get student management system if not provided.
                if ((empty($params['sms']))) {
                    $params['sms'] = $details['sms'];
                }
                // Get externalid if not provided.
                if ((!isset($params['externalid']) or $params['externalid'] === '')) {
                    $params['externalid'] = $details['externalid'];
                }
                // Update Module.
                if ($change) {
                    // If faculty supplied, school must be supplied.
                    if (empty($params['school']) and !empty($params['faculty'])) {
                        $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_SCHOOL'], 'status' => $strings['school_not_supplied'], 'id' => null, 'externalid' => null);
                    } else {
                        $update = \module_utils::update_module_by_id($params['id'], $params['modulecode'], 
                            $params['name'], $schoolid, $params['sms'], $this->db, $details['externalid']);
                        if ($update) {
                            $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['externalid']);
                        } else {
                            $data = array('statuscode' => $this->statuscodes['MODULE_NOT_UPDATED'], 'status' => $strings['module_not_updated'], 'id' => null, 'externalid' => null);
                        }
                    }
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_NOTHING_TO_UPDATE'], 'status' => $strings['module_nothing_to_update'], 'id' => null, 'externalid' => null);
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['MODULE_INVALID_FACULTY'], 'status' => $strings['faculty_not_supplied'], 'id' => null, 'externalid' => null);
            }
        }
        return $this->get_response($data, 'update', $params['nodeid']);
    }

    /**
     * Delete module
     * @param array $parms delete module parameters
     * @param integer $userid rogo user id linked to web service client
     * @return success status and module id
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('module_not_deleted_inuse', 'module_not_deleted',
            'module_does_not_exist'));
        if (!empty($params['id'])) {
            $moduleid = \module_utils::get_moduleid_from_id($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id to delete module.
            $moduleid = \module_utils::get_id_from_externalid($params['externalid'], $this->db);
            $params['id'] = $moduleid;
        } else {
            $moduleid = false;
        }
        if ($moduleid) {
             // Only delete module if it contains no enrolments, and no papers
            $inuse = \module_utils::module_in_use($params['id'], $this->db);
            $details = \module_utils::get_full_details_by_ID($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['MODULE_NOT_DELETED_INUSE'], 'status' => $strings['module_not_deleted_inuse'], 'id' => null, 'externalid' => null);
            } else {
                $deleted = \module_utils::delete_module($params['id'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'externalid' => $details['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['MODULE_NOT_DELETED'], 'status' => $strings['module_not_deleted'], 'id' => null, 'externalid' => null);
                }
            }
        } else {
             $data = array('statuscode' => $this->statuscodes['MODULE_DOES_NOT_EXIST'], 'status' => $strings['module_does_not_exist'], 'id' => null, 'externalid' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}