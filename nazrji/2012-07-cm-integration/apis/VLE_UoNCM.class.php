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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

// TODO: Make URL configurable?
// TODO: Add API key for connection as Moodle/Rogo user

require_once 'VLEAPI.if.php';
require_once $cfg_web_root . 'webServices/RestRequest.class';

class VLE_UoNCM implements iVLEAPI {
  private $_root_url = 'http://curriculum.nottingham.ac.uk/%s/index.php/';
  private $_sess_year;

  /**
   * Return objectives from the University of Nottingham Curriculum Mapping system
   * @param $moduleID
   * @param $session
   * @return mixed Array of session and objective data in format required by Rogō
   */
  public function getObjectives($moduleID, $session) {
    $this->_sess_year = strstr($session, '/', true);
    $this->_root_url = sprintf($this->_root_url, $this->_sess_year);
    $req = new RestRequest($this->_root_url . "api/find_json?search={$moduleID}&type=module&where=attribute&attrib=code&output=module_session_obs");
    $req->execute();

    $res = $req->getResponseBody();

    return $this->transformCMResponse($res, $session);
  }

  /**
   * Get a friendly name for the source system, with the indefinite article if required
   * @param bool $a
   * @return string
   */
  public function getFriendlyName($a = false) {
    return ($a) ? 'a Curriculum Map' : 'Curriculum Map';
  }

  /**
   * Transform the data returned by the Curriculum Map into the format required by Rogō
   * @param $data
   */
  private function transformCMResponse($input, $calendar_year) {
    if (isset($input['cmapi']['module'])) {
      $mod_id = $input['cmapi']['module']['code'];
      $sessions = array();

      $i = 0;
      if (isset($input['cmapi']['module']['session'])) {
        foreach ($input['cmapi']['module']['session'] as $session) {
          // If no objectives don't bother showing the session
          if (is_array($session['objectives'])) {
            $sess_data = array(
              'identifier' => $session['@attributes']['id'],
              'class_code' => $session['code'],
              'title' => $session['title'],
              'occurrance' => date('d/m/y H:i', strtotime($session['start'])),
              'calendar_year' => $calendar_year,
              'VLE' => 'UoNCM',
              'source_url' => $this->_root_url . 'view/' . $session['@attributes']['id'],
              'mapped' => 0,
              'objectives' => array()
            );

            $obs = $session['objectives']['outcome_session'];
            if (isset($obs['@attributes'])) {
              $obj_data = array(
                'content' => (isset($obs['title']) and $obs['title'] != '') ? $obs['title'] : $obs['content'],
                'id' => $obs['@attributes']['id']
              );
              $sess_data['objectives'][++$i] = $obj_data;
            } else {
              foreach ($obs as $objective) {
                $obj_data = array(
                  'content' => (isset($objective['title']) and $objective['title'] != '') ? $objective['title'] : $objective['content'],
                  'id' => $objective['@attributes']['id'],
                  'mapped' => 0
                );
                $sess_data['objectives'][++$i] = $obj_data;
              }
            }
            $sessions[$session['@attributes']['id']] = $sess_data;
          }
        }
      }

      if (isset($input['cmapi']['module']['learning_act'])) {
        foreach ($input['cmapi']['module']['learning_act'] as $learning_act) {
          // If no objectives don't bother showing the session
          if (is_array($learning_act['objectives'])) {
            $act_data = array(
              'identifier' => $learning_act['@attributes']['id'],
              'class_code' => '',
              'title' => $learning_act['title'],
              'occurrance' => 'Non-timetabled',
              'calendar_year' => $calendar_year,
              'VLE' => 'UoNCM',
              'source_url' => $this->_root_url . 'view/' . $learning_act['@attributes']['id'],
              'mapped' => 0,
              'objectives' => array()
            );

            $obs = $learning_act['objectives']['outcome_learning_act'];
            if (isset($obs['@attributes'])) {
              $obj_data = array(
                'content' => (isset($obs['title']) and $obs['title'] != '') ? $obs['title'] : $obs['content'],
                'id' => $obs['@attributes']['id']
              );
              $act_data['objectives'][++$i] = $obj_data;
            } else {
              foreach ($obs as $objective) {
                $obj_data = array(
                  'content' => (isset($objective['title']) and $objective['title'] != '') ? $objective['title'] : $objective['content'],
                  'id' => $objective['@attributes']['id'],
                  'mapped' => 0
                );
                $act_data['objectives'][++$i] = $obj_data;
              }
            }
            $sessions[$learning_act['@attributes']['id']] = $act_data;
          }
        }
      }

      $output = array($mod_id => $sessions);

      return $output;
    } else {
      return array();
    }
  }
}
?>
