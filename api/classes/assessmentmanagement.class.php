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
* Assessment api functions
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

namespace api;

/**
 * Assessment class
 */
class assessmentmanagement extends \api\abstractmanagement {
    
    /**
     * Language pack component.
     */
    private $langcomponent = 'api/assessmentmanagement';
    
    /**
     * Status codes
     */
    private $statuscodes = array(
        'OK' => 100,
        'PAPER_GENERAL_ERROR' => 200,
        'PAPER_NOT_DELETED' => 201,
        'PAPER_DOES_NOT_EXIST' => 202,
        'PAPER_NOT_DELETED_INUSE' => 203,
        'PAPER_NOT_CREATED' => 204,
        'PAPER_NOT_SCHEDULED' => 205,
        'PAPER_INVALID_TITLE' => 206,
        'PAPER_INVALID_OWNER' => 207,
        'PAPER_INVALID_ROLE' => 208,
        'PAPER_INVALID_YEAR' => 209,
        'PAPER_INVALID_PAPER' => 210,
        'PAPER_INVALID_MODULES' => 211,
        'PAPER_INVALID_START' => 212,
        'PAPER_NOT_UPDATED' => 213,
        'PAPER_SCHEDULE_SUMMATIVE' => 214,
        'PAPER_INVALID_TYPE' => 215,
        'PAPER_NOTHING_TO_UPDATE' => 216,
        'PAPER_EXTERNALID_INUSE' => 217,
        'PAPER_INVALID_NO_MODULES' => 218
    );
    
    /**
     * Check labs
     * Currently we are working with the lab name as there is no lab management web service.
     * @param array $labs list of labs we ant assignments to run in
     * @return array validated list of labs assignment will run in, and any non fatal errors
     */
    private function check_labs($labs) {
        $langpack = new \langpack();
        $labsarray = array();
        $labfactory = new \LabFactory($this->db);
        $error = array();
        if (!empty($labs)) {
            foreach ($labs as $lab) {
                // We allow empty lab elements so labs so the paper can have all labs removed.
                if ($lab['value'] != '') {
                    $labid = $labfactory->get_lab_id($lab['value']);
                    if ($labid) {
                        $labsarray[] = $labid;
                    } else {
                        $error[$lab['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_lab'), $lab['value']);
                    }
                }
            }
        }
        if (count($labsarray) > 0) {
            $labsstring = implode(',', $labsarray);
        } else {
            $labsstring = '';
        }
        return array($labsstring, $error);
    }
    /**
     * Handle thrown exceptions
     * @param string $exception - the thrown exception
     * @return array containg the relevant status code and status message
     */
    private function handle_exception($exception) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_calendar_year_invalid', 'paper_owner_role_invalid',
            'paper_owner_does_not_exist', 'paper_title_inuse', 'paper_startdate_invalid', 'paper_general_error','paper_type_invalid', 'paper_externalid_inuse', 'paper_no_modules'));
        switch ($exception) {
            case 'NON_UNIQUE_TITLE':
                return array('statuscode' => $this->statuscodes['PAPER_INVALID_TITLE'], 'status' => $strings['paper_title_inuse'], 'id' => null);
            case 'INVALID_PAPER_TYPE':
                return array('statuscode' => $this->statuscodes['PAPER_INVALID_TYPE'], 'status' => $strings['paper_type_invalid'], 'id' => null);
            case 'INVALID_USER':
                return array('statuscode' => $this->statuscodes['PAPER_INVALID_OWNER'], 'status' => $strings['paper_owner_does_not_exist'], 'id' => null);
            case 'INVALID_ROLE':
                return array('statuscode' => $this->statuscodes['PAPER_INVALID_ROLE'], 'status' => $strings['paper_owner_role_invalid'], 'id' => null);
            case 'INVALID_SESSION':
                return array('statuscode' => $this->statuscodes['PAPER_INVALID_YEAR'], 'status' => $strings['paper_calendar_year_invalid'], 'id' => null);
            case 'INVALID_DATES':
                return array('statuscode' => $this->statuscodes['PAPER_INVALID_START'], 'status' => $strings['paper_startdate_invalid'], 'id' => null);
            case 'NON_UNIQUE_EXTID':
                return array('statuscode' => $this->statuscodes['PAPER_EXTERNALID_INUSE'], 'status' => $strings['paper_externalid_inuse'], 'id' => null);
            case 'INVALID_NO_MODULES':
                return array('statuscode' => $this->statuscodes['PAPER_INVALID_NO_MODULES'], 'status' => $strings['paper_no_modules'], 'id' => null);
            default:
                return array('statuscode' => $this->statuscodes['PAPER_GENERAL_ERROR'], 'status' => $strings['paper_general_error'], 'id' => null);
        }
    }
    /**
     * Create assessment
     * @param array $parms create assessment parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array assessment id and status
     */
    public function create($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_created', 'paper_scheduled_summative', 'paper_invalid_module',
             'paper_invalid_lab', 'paper_module_error'));
        $error = array();
        $configObject = \Config::get_instance();
        $paper = new \assessment($this->db, $configObject);
        $papertype = $paper->get_type_value($params['type']);
        // Check valid paper type.
        if ($papertype === false) {
             $data = $this->handle_exception('INVALID_PAPER_TYPE');
             return $this->get_response($data, 'create', $params['nodeid'], $error);
        }
        // Error if trying to create a summative exam when they are set to be scheduled only.
        if ($configObject->get('cfg_summative_mgmt') and $papertype == $paper::TYPE_SUMMATIVE) {
            $data = array('statuscode' => $this->statuscodes['PAPER_SCHEDULE_SUMMATIVE'], 'status' => $strings['paper_scheduled_summative'], 'id' => null);
            return $this->get_response($data, 'create', $params['nodeid'], $error);
        }
        // Use system timezone if not provided on creation.
        if (empty($params['timezone'])) {
            $params['timezone'] = $configObject->get('cfg_timezone');
        }
        // Check modules
        $modulesarray = array();
        if (!empty($params['extmodules'])) {
            foreach ($params['extmodules'] as $module) {
                $moduleid = \module_utils::get_id_from_externalid($module['value'], $this->db);
                if ($moduleid) {
                    $modulesarray[] = $moduleid;
                } else {
                    $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                }
            }
        } elseif (!empty($params['modules'])) {
            foreach ($params['modules'] as $module) {
                $moduleid = \module_utils::get_moduleid_from_id($module['value'], $this->db);
                if ($moduleid) {
                    $modulesarray[] = $module['value'];
                } else {
                    $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                }
            }
        }
        
        if (count($error) > 0) {
            $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_MODULES'], 'status' => $strings['paper_module_error'], 'id' => null);
        } else {
            // Check labs.
            if (!empty($params['labs'])) {
                $checklabs = $this->check_labs($params['labs']);
                $labs = $checklabs[0];
                $error += $checklabs[1];
            } else {
                $labs = '';
            }
            
            if (empty($params['duration'])) {
                $params['duration'] = '';
            }
            // Create exam.
            try {
                // Default null externalid.
                if (empty($params['externalid'])) {
                    $params['externalid'] = null;
                }
                // Default null externalsys.
                if (empty($params['externalsys'])) {
                    $params['externalsys'] = null;
                }
                $id = $paper->create($params['title'], $papertype, $params['owner'], $params['startdatetime'],
                    $params['enddatetime'], $labs, $params['duration'], $params['session'], $modulesarray, $params['timezone'], $params['externalid'], $params['externalsys']);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $id, 'error' => $error, 'externalid' => $params['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['PAPER_NOT_CREATED'], 'status' => $strings['paper_not_created'], 'id' => null);
                }
            } catch (\Exception $e) {
                $data = $this->handle_exception($e->getMessage());
            }
        }
        
        return $this->get_response($data, 'create', $params['nodeid'], $error);
    }
    /**
     * Update assessment
     * @param array $parms update assessment parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array assessment id and status
     */
    public function update($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_scheduled_summative', 'paper_does_not_exist',
            'paper_not_updated', 'paper_invalid_module', 'paper_invalid_lab', 'paper_module_error', 'paper_nothing_to_update'));
        $error = array();
        $configObject = \Config::get_instance();
        $paper = new \assessment($this->db, $configObject);
        if (isset($params['id']) and $params['id'] !== '') {
            // Try internal rogo id.
            $paperid = \Paper_utils::paper_exists($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id.
            $paperid = \Paper_utils::get_id_from_externalid($params['externalid'], $this->db);
            $params['id'] = $paperid;
        }
        // Get current paper properties.
        if ($paperid) {
            $details = \Paper_utils::get_paper_properties($params['id'], $this->db);
            $papertype = $details['type'];
        } else {
            // Paper does not exist so cannot update.
            $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_PAPER'], 'status' => $strings['paper_does_not_exist'], 'id' => null);
            return $this->get_response($data, 'update', $params['nodeid'], $error);
        }
        // Check if anything has been updated.
        $checkparameter = array('title', 'owner', 'session', 'duration', 'timezone');
        $change = $this->check_if_updated($checkparameter, $details, $params);

        // Error if trying to update a summative exam when they are set to be scheduled only.
        if ($configObject->get('cfg_summative_mgmt') and $papertype == $paper::TYPE_SUMMATIVE) {
            $data = array('statuscode' => $this->statuscodes['PAPER_SCHEDULE_SUMMATIVE'], 'status' => $strings['paper_scheduled_summative'], 'id' => null);
            return $this->get_response($data, 'update', $params['nodeid'], $error);
        }

        // Get title if not provided.
        if (empty($params['title'])) {
            $params['title'] = $details['title'];
        }
        // Get owner if not provided.
        if (empty($params['owner'])) {
            $params['owner'] = $details['owner'];
        }
        // Get session if not provided.
        if (empty($params['session'])) {
            $params['session'] = $details['session'];
        }
        // Get start datetime if not provided.
        if (empty($params['startdatetime'])) {
            $params['startdatetime'] = $details['startdatetime'];
        } else {
            // Mark something is to be updated.
            $startdate = str_replace("T", " ", $params['startdatetime']);
            if ($startdate != $details['startdatetime']) {
                $change = true;
            }
        }
        // Get end datetime if not provided.
        if (empty($params['enddatetime'])) {
            $params['enddatetime'] = $details['enddatetime'];
        } else {
            // Mark something is to be updated.
            $enddate = str_replace("T", " ", $params['enddatetime']);
            if ($enddate != $details['enddatetime']) {
                $change = true;
            }
        }
        // Get end timezone if not provided.
        if (empty($params['timezone'])) {
            $params['timezone'] = $details['timezone'];
        }

        // Check modules
        $modulesarray = array();
        if (!empty($params['extmodules'])) {
            foreach ($params['extmodules'] as $module) {
                $moduleid = \module_utils::get_id_from_externalid($module['value'], $this->db);
                if ($moduleid) {
                    $modulesarray[] = $moduleid;
                } else {
                    $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                }
            }
        } elseif (!empty($params['modules'])) {
            foreach ($params['modules'] as $module) {
                $moduleid = \module_utils::get_moduleid_from_id($module['value'], $this->db);
                if ($moduleid) {
                    $modulesarray[] = $module['value'];
                } else {
                    $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                }
            }
        }
        // Mark something is to be updated.
        if (!empty($params['extmodules']) or !empty($params['modules'])) {
            if ($paperid) {
                $current_modules = \Paper_utils::get_modules($params['id'], $this->db);
                ksort($current_modules);
                sort($modulesarray);
                if (array_keys($current_modules) != $modulesarray) {
                    $change = true;
                }
            }
        }
        
        if (count($error) > 0) {
            $data = array('statuscode' => $this->statuscodes['PAPER_INVALID_MODULES'], 'status' => $strings['paper_module_error'], 'id' => null);
        } else {
            if (empty($params['labs'])) {
                $labs = $details['labs'];
            } else {
                // Check labs.
                $checklabs = $this->check_labs($params['labs']);
                $labs = $checklabs[0];
                $error += $checklabs[1];
            }
            // Mark something is to be updated.
            if ($paperid) {
                if ($details['labs'] != $labs) {
                    $change = true;
                }
            }
            
            if (empty($params['duration'])) {
                $params['duration'] = $details['duration'];   
            }
            // Update exam.
            if ($change) {
                try {
                    $id = $paper->update($params['id'], $params['title'], $papertype, $params['owner'], $params['startdatetime'],
                        $params['enddatetime'], $labs, $params['duration'], $params['session'], $modulesarray, $params['timezone'], $userid, $details['externalid'], $details['externalsys']);
                    if ($id) {
                        $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id'], 'error' => $error, 'externalid' => $details['externalid']);
                    } else {
                        $data = array('statuscode' => $this->statuscodes['PAPER_NOT_UPDATED'], 'status' => $strings['paper_not_updated'], 'id' => null);
                    }
                } catch (\Exception $e) {
                    $data = $this->handle_exception($e->getMessage());
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['PAPER_NOTHING_TO_UPDATE'], 'status' => $strings['paper_nothing_to_update'], 'id' => null);
            }
        }
        
        return $this->get_response($data, 'update', $params['nodeid'], $error);
    }
    
    /**
     * Schedule a summative assessment
     * @param array $parms schedule summative parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array summative assessment id and status
     */
    public function schedule($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_created', 'paper_not_scheduled', 'paper_invalid_module'));
        $error = array();
        $configObject = \Config::get_instance();
        $paper = new \assessment($this->db, $configObject);
        $papertype = $paper::TYPE_SUMMATIVE;

        // Check modules
        $modulesarray = array();
        if (!empty($params['extmodules'])) {
            foreach ($params['extmodules'] as $module) {
                $modid = \module_utils::get_id_from_externalid($module['value'], $this->db);
                if ($modid) {
                    $modulesarray[] = $modid;
                } else {
                    $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                }
            }
        } elseif (!empty($params['modules'])) {
            foreach ($params['modules'] as $module) {
                $moduleid = \module_utils::get_moduleid_from_id($module['value'], $this->db);
                if ($moduleid) {
                    $modulesarray[] = $module['value'];
                } else {
                    $error[$module['id']] = sprintf($langpack->get_string($this->langcomponent, 'paper_invalid_module'), $module['value']);
                }
            }
        }
        $labs = '';
        $start = '';
        $end = '';
        // Set defaults.
        if (empty($params['cohort_size'])) {
            $params['cohort_size'] = '<whole cohort>';
        }
        if (empty($params['sittings'])) {
            $params['sittings'] = null;
        }
        if (empty($params['barriers'])) {
            $params['barriers'] = null;
        }
        if (empty($params['month'])) {
            $params['month'] = null;
        }
        if (empty($params['campus'])) {
            $params['campus'] = null;
        }
        if (empty($params['notes'])) {
            $params['notes'] = null;
        }
        // Default null externalid.
        if (empty($params['externalid'])) {
            $params['externalid'] = null;
        }
        // Default null externalsys.
        if (empty($params['externalsys'])) {
            $params['externalsys'] = null;
        }
        // Create.
        try {
            $paperid = $paper->create($params['title'], $papertype, $params['owner'], $start,
                $end, $labs, $params['duration'], $params['session'], $modulesarray, $configObject->get('cfg_timezone'), $params['externalid'], $params['externalsys']);
            if ($paperid) {
                // Schedule.
                $id = $paper->schedule($paperid, $params['month'], $params['barriers'], $params['cohort_size'], $params['notes'], $params['sittings'], $params['campus']);
                if ($id) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $paperid, 'error' => $error, 'externalid' => $params['externalid']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['PAPER_NOT_SCHEDULED'], 'status' => $strings['paper_not_scheduled'], 'id' => null);
                    // Not scheduled so remove new properties entry from db.
                    if (!\Paper_utils::complete_delete_paper($paperid, $this->db)) {
                        // Log warning to system if delete failed, as we want to clean up orhpaned papers.
                        $type = 'Assessment Management';
                        $errorstring = 'Error deleting unscheduled paper';
                        $errorfile = $_SERVER['PHP_SELF'];
                        $errorline = __LINE__ - 5;
                        $logger = new \logger($this->db);
                        $logger->record_application_warning($userid, $type, $errorstring, $errorfile, $errorline);
                    }
                }
            } else {
                $data = array('statuscode' => $this->statuscodes['PAPER_NOT_CREATED'], 'status' => $strings['paper_not_created'], 'id' => null);
            }
        } catch (\Exception $e) {
            $data = $this->handle_exception($e->getMessage());
        }
        
        return $this->get_response($data, 'schedule', $params['nodeid'], $error);
    }

    /**
     * Delete assessment
     * @param array $parms delete assessment parameters
     * @param integer $userid rogo user id linked to web service client
     * @return array assessment id and status
     */
    public function delete($params, $userid) {
        $langpack = new \langpack();
        $strings = $langpack->get_strings($this->langcomponent, array('paper_not_deleted_inuse', 'paper_not_deleted'
            , 'paper_does_not_exist'));
        if (isset($params['id']) and $params['id'] !== '') {
            $paperexists = \Paper_utils::paper_exists($params['id'], $this->db);
        } elseif (isset($params['externalid']) and $params['externalid'] !== '') {
            // Try using external system id.
            $paperid = \Paper_utils::get_id_from_externalid($params['externalid'], $this->db);
            $params['id'] = $paperid;
            $paperexists = true;
        } else {
            $paperexists = false;
        }
        if ($paperexists) {
            // Only delete assessment if no one has taken the paper.
            $inuse = \Paper_utils::paper_taken($params['id'], $this->db);
            if ($inuse) {
                $data = array('statuscode' => $this->statuscodes['PAPER_NOT_DELETED_INUSE'], 'status' => $strings['paper_not_deleted_inuse'], 'id' => null);
            } else {
                $details = \Paper_utils::get_paper_properties($params['id'], $this->db);
                $deleted = \Paper_utils::delete_paper($params['id'], $details['owner'], $this->db);
                if ($deleted) {
                    $data = array('statuscode' => $this->statuscodes['OK'], 'status' => 'OK', 'id' => $params['id']);
                } else {
                    $data = array('statuscode' => $this->statuscodes['PAPER_NOT_DELETED'], 'status' => $strings['paper_not_deleted'], 'id' => null);
                }
            }
        } else {
            $data = array('statuscode' => $this->statuscodes['PAPER_DOES_NOT_EXIST'], 'status' => $strings['paper_does_not_exist'], 'id' => null);
        }
        return $this->get_response($data, 'delete', $params['nodeid']);
    }
}