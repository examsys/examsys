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
*
* Curriculum Map API, all Curriculum Map related functions go in here
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once 'CMAPI.if.php';

class CM_UoNCM implements iCMAPI {
  private $_root_url;
  private $_sess_year;
  private $_module_id;
  private $_mapping_level = self::LEVEL_SESSION;
  private $_moodle_base_url;

  /**
   * Return objectives from the University of Nottingham Curriculum Mapping system
   * @param string $moduleID the module code
   * @param int $session The year the academic year starts in
   * @param mysqli $db database connection
   * @return mixed Array of session and objective data in format required by Rogō
   */
  public function getObjectives($moduleID, $session, $db) {
    $configObject = Config::get_instance();
    $this->_sess_year = $session;
    $originalmodid = $moduleID;
    // Map module code if necessary.
    if ($session < 2016) {  
      $moduleID = \plugins\plugins_mapping::do_mapping($db, $moduleID);
    }
    $this->_root_url = $configObject->get_setting('core', 'cfg_cmap_url') . "/" . $this->_sess_year . "/index.php/";
    $this->_module_id = $moduleID;
    $this->_moodle_base_url = $configObject->get_setting('core', 'cfg_moodle_base_url') . '/local/uonlib/findcourse.php?m=%s&y=%s&nid=%s';
    $url = $this->_root_url . "api/find_json?search={$moduleID}&type=module&where=attribute&attrib=code&output=module_session_obs";
    $req = new restful($db);
    $options = array(CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
        );
    $res = json_decode($req->get($url, $options), true);
    if ($req->get_last_http_code() == 0) {
      $objectives = 'error';
    } else {
      switch ($this->_mapping_level) {
        case self::LEVEL_MODULE:
          $objectives = $this->transformCMResponseModule($res, $session, $originalmodid);
          break;
        default:
          $objectives = $this->transformCMResponse($res, $session, $originalmodid);
          break;
      }
    }
		
    return $objectives;
  }

  /**
   * Get a friendly name for the source system, with the indefinite article if required
   * @param bool $a     Include the definite article?
   * @param bool $long  Return the long form of the name?
   * @return string     The name in the required format
   */
  public function getFriendlyName($a = false, $long = false) {
    return ($a) ? 'a Curriculum Map' : 'Curriculum Map';
  }

  /**
   * Get the levels of mapping that are supported by this class
   * @return array Array of mapping levels supported
   */
  public function getMappingLevels() {
    return array(self::LEVEL_SESSION, self::LEVEL_MODULE);
  }

  /**
   * Set the mapping level at which the class should work
   * @param integer $level Mapping level
   */
  public function setMappingLevel($level) {
    if (!in_array($level, $this->getMappingLevels())) {
      throw new UnsupportedMappingLevelException();
    }
    $this->_mapping_level = $level;
  }

  /**
   * Transform the module level data returned by the Curriculum Map into the format required by Rogō
   * @param array $input data
   * @param integer $calendar_year year we are interested in
   * @param string $originalmodid the orginal (non mapped) module id
   * @return array data converted to rogo structure
   */
  private function transformCMResponse($input, $calendar_year, $mod_id) {
    if (isset($input['cmapi']['module'])) {
      $sessions = array();
      $i = 0;
      if (isset($input['cmapi']['module']['session'])) {
        if (isset($input['cmapi']['module']['session']['@attributes'])) {
          $this->process_session($sessions, $input['cmapi']['module']['session'], $calendar_year, $i);
        } else {
          foreach ($input['cmapi']['module']['session'] as $session) {
            $this->process_session($sessions, $session, $calendar_year, $i);
          }
        }
      }
      if (isset($input['cmapi']['module']['learning_act'])) {
        if (isset($input['cmapi']['module']['learning_act']['@attributes'])) {
          $this->process_learning_act($sessions, $input['cmapi']['module']['learning_act'], $calendar_year, $i);
        } else {
          foreach ($input['cmapi']['module']['learning_act'] as $learning_act) {
            $this->process_learning_act($sessions, $learning_act, $calendar_year, $i);
          }
        }
      }
      $output = array($mod_id => $sessions);
      return $output;
    } else {
      return array();
    }
  }
  /**
   * Transform the session level data returned by the Curriculum Map into the format required by Rogō
   * @param array $input data
   * @param integer $calendar_year year we are interested in
   * @param string $originalmodid the orginal (non mapped) module id
   * @return array data converted to rogo structure
   */
  private function transformCMResponseModule($input, $calendar_year, $mod_id) {
    if (isset($input['cmapi']['module'])) {
      $sessions = array();
      $i = 0;
      if (isset($input['cmapi']['module']['objectives']) and isset($input['cmapi']['module']['objectives']['group'])) {
        if (isset($input['cmapi']['module']['objectives']['group']['@attributes'])) {
          $this->process_group($sessions, $input['cmapi']['module']['objectives']['group'], $calendar_year, $i);
        } else {
          foreach ($input['cmapi']['module']['objectives']['group'] as $group) {
            $this->process_group($sessions, $group, $calendar_year, $i);
          }
        }
      }
      $output = array($mod_id => $sessions);
      return $output;
    } else {
      return array();
    }
  }

  /**
   * @param $sessions List of sessions with objectives
   * @param $session The current session
   * @param $calendar_year
   * @param $count
   */
  private function process_session(&$sessions, $session, $calendar_year, &$count) {
    // If no objectives don't bother showing the session
    if (is_array($session['objectives'])) {
      $sess_data = array(
        'identifier' => $session['@attributes']['id'],
        'GUID' => $session['@attributes']['guid'],
        'ttGUID' => $session['ttguid'],
        'class_code' => $session['code'],
        'title' => $session['title'],
        'occurrance' => date('d/m/y H:i', strtotime($session['start'])),
        'calendar_year' => $calendar_year,
        'VLE' => 'UoNCM',
        'source_url' => sprintf($this->_moodle_base_url, $this->_module_id, $this->_sess_year, $session['@attributes']['id']) . '&ses=' . $session['code'],
        'mapped' => 0,
        'objectives' => array()
      );

      $obs = $session['objectives']['outcome_session'];
      if (isset($obs['@attributes'])) {
        $obj_data = array(
          'content' => (isset($obs['title']) and $obs['title'] != '') ? $obs['title'] : $obs['content'],
          'id' => $obs['@attributes']['id']
        );
        $sess_data['objectives'][++$count] = $obj_data;
      } else {
        foreach ($obs as $objective) {
          $obj_data = array(
            'content' => (isset($objective['title']) and $objective['title'] != '') ? $objective['title'] : $objective['content'],
            'id' => $objective['@attributes']['id'],
            'guid' => $objective['@attributes']['guid'],
            'mapped' => 0
          );
          $sess_data['objectives'][++$count] = $obj_data;
        }
      }
      $sessions[$session['@attributes']['guid']] = $sess_data;
    }
  }

  /**
   * @param $sessions List of sessions with objectives
   * @param $session The current session
   * @param $calendar_year
   * @param $count
   */
  private function process_learning_act(&$sessions, $learning_act, $calendar_year, &$count) {
    // If no objectives don't bother showing the session
    if (is_array($learning_act['objectives'])) {
      $act_data = array(
        'identifier' => $learning_act['@attributes']['id'],
        'guid' => $learning_act['@attributes']['guid'],
        'class_code' => '',
        'title' => $learning_act['title'],
        'occurrance' => 'Non-timetabled',
        'calendar_year' => $calendar_year,
        'VLE' => 'UoNCM',
//        'source_url' => sprintf($this->_moodle_base_url, $this->_module_id, $this->_sess_year, $learning_act['@attributes']['id']) . '&ses=' . $learning_act['code'],
        'source_url' => '',
        'mapped' => 0,
        'objectives' => array()
      );

      $obs = $learning_act['objectives']['outcome_learning_act'];
      if (isset($obs['@attributes'])) {
        $obj_data = array(
          'content' => (isset($obs['title']) and $obs['title'] != '') ? $obs['title'] : $obs['content'],
          'id' => $obs['@attributes']['id']
        );
        $act_data['objectives'][++$count] = $obj_data;
      } else {
        foreach ($obs as $objective) {
          $obj_data = array(
            'content' => (isset($objective['title']) and $objective['title'] != '') ? $objective['title'] : $objective['content'],
            'id' => $objective['@attributes']['id'],
            'guid' => $objective['@attributes']['guid'],
            'mapped' => 0
          );
          $act_data['objectives'][++$count] = $obj_data;
        }
      }
      $sessions[$learning_act['@attributes']['guid']] = $act_data;
    }
  }

  /**
   * Process objective groups for module level mapping
   * @param  array   $sessions      Sessions extracted from group data
   * @param  array   $group         Array of outcome groups
   * @param  string  $calendar_year Academic year in the format YYYY/YY, e.g. 2012/13
   * @param  integer $count         Count of sessions created
   */
  private function process_group(&$sessions, $group, $calendar_year, &$count) {
    // If no objectives don't bother showing the session
    if (is_array($group['outcome_module'])) {
      $sess_data = array(
        'identifier' => $group['@attributes']['id'],
        'GUID' => $group['@attributes']['id'],
        'class_code' => '',
        'title' => ($group['title'] == '') ? 'No group' : $group['title'],
        'occurrance' => '',
        'calendar_year' => $calendar_year,
        'VLE' => 'UoNCM',
        'source_url' => '',   // TODO
        // 'source_url' => sprintf($this->_moodle_base_url, $this->_module_id, $this->_sess_year, $session['@attributes']['id']) . '&ses=' . $session['code'],
        'mapped' => 0,
        'objectives' => array()
      );

      $obs = $group['outcome_module'];
      if (isset($obs['@attributes'])) {
        $obj_data = array(
          'content' => (isset($obs['title']) and $obs['title'] != '') ? $obs['title'] : $obs['content'],
          'id' => $obs['@attributes']['id']
        );
        $sess_data['objectives'][++$count] = $obj_data;
      } else {
        foreach ($obs as $objective) {
          $obj_data = array(
            'content' => (isset($objective['title']) and $objective['title'] != '') ? $objective['title'] : $objective['content'],
            'id' => $objective['@attributes']['id'],
            'mapped' => 0
          );
          $sess_data['objectives'][++$count] = $obj_data;
        }
      }
      $sessions[$group['@attributes']['id']] = $sess_data;
    }
  }
}
?>
