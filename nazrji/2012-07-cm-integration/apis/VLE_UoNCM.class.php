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
* NLE API, all NLE related functions go in here
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require 'VLEAPI.if.php';
require_once '../webServices/RestRequest.class';

class VLE_UoNCM implements iVLEAPI {
  private $root_url = 'http://cm.rji.ac.uk/2011/index.php/';
  /**
   * Return objectives from the University of Nottingham Curriculum Mapping system
   * @param $moduleID
   * @param $session
   * @return mixed Array of session and objective data in format required by Rogō
   */
  public function getObjectives($moduleID, $session) {
    // TODO: need to use the find interface to get the Module ID for code and session
    $req = new RestRequest($this->root_url . "api/json/125/module_session_obs");
//    $split_username = explode('_', $_SERVER['PHP_AUTH_USER']);
//    $req->setUsername('admin');
//    $req->setPassword('admin');
//    $req->setUsername($split_username[0]);
//    $req->setPassword($_SERVER['PHP_AUTH_PW']);
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
   * Tranaform the data returned by the Curriculum Map into the format required by Rogō
   * @param $data
   */
  private function transformCMResponse($input, $calendar_year) {
    if (isset($input['cmapi']['module'])) {
      $mod_id = $input['cmapi']['module']['code'];
      $sessions = array();

      $i = 0;
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
            'source_url' => $this->root_url . 'view/' . $session['@attributes']['id'],
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
                'id' => $objective['@attributes']['id']
              );
              $sess_data['objectives'][++$i] = $obj_data;
            }
          }
          $sessions[$session['@attributes']['id']] = $sess_data;
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