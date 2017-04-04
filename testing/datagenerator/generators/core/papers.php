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

namespace testing\datagenerator;

use \encryp,
    \PaperUtils,
    \Config,
    \yearutils,
    \assessment,
    \UserUtils;

/**
 * Generates Rogo paper.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class papers extends generator {

  /**
   * Create a new paper with 4 mandatory or optional parametera
   * 
   * @param array $parameters
   * Mandatories param in $parameters are
   *  string parameters[papertitle]
   *  string parameters[papertype], any of 
    assessment::TYPE_PROGRESS,
    assessment::TYPE_SUMMATIVE,
    assessment::TYPE_SURVEY,
    assessment::TYPE_OSCE,
    assessment::TYPE_OFFLINE,
    assessment::TYPE_PEERREVIEW
   *  string parameters[paperowner]
   *  string parameters[modulename]
   */
  public function create_paper($parameters) {

    if (is_object($parameters)) {
      $parameters = (array) $parameters;
    }
    // Check that the right type has been passed.
    if (!is_array($parameters)) {
      throw new data_error('Must pass an array or object');
    }
    $db = loader::get_database();
    if (empty($parameters['papertitle']) or ! is_numeric($parameters['papertype']) or empty($parameters['paperowner']) or empty($parameters['modulename'])) {
      throw new data_error('Error in | papertitle | papertype | paperowner | modulename |');
    } else {
      $papertitle = $parameters['papertitle'];
      $papertype = (int) $parameters['papertype'];
      $paperowner = $parameters['paperowner'];
      $paperowner = UserUtils::username_exists($parameters['paperowner'], $db);
      $modulename = $parameters['modulename'];
    }
    $default = array('startdate' => null, 'enddate' => null, 'labs' => null, 'duration' => 700, 'session' => null, 'timezone' => 'Europe/London');
    $settings = array_merge($parameters, $default);

    if (!empty($settings['startdate'])) {
      $startdate = new \DateTime($settings['startdate']);
      if (empty($startdate)) { // If user's date is not vialid
        throw new data_error("Paper's startdate is wrong");
      }
    } else {
      $startdate = new \DateTime('NOW');
      $startdate = $startdate->modify('+3 hour');
    }
    if (!empty($settings['enddate'])) {
      $enddate = new \DateTime($settings['enddate']);
      if (empty($enddate)) { // If user's date is not vialid
        throw new data_error("Paper's enddate is wrong");
      }
    } else {
      $enddate = new \DateTime('NOW');
      $enddate = $enddate->modify('+6 hour');
    }

    $conf = Config::get_instance();

    $paper = new assessment($db, $conf);
    $session = date("Y");

    $moduleid = self::test_get_moduleidbyname($modulename, $db);
    $moduleids = array($moduleid); // create() need array type $moduleids 

    try {
      $paper->create($papertitle, $papertype, $paperowner, $startdate->format('Ymdhis'), $enddate->format('Ymdhis'), $settings['labs'], $settings['duration'], $session, $moduleids, $settings['timezone']);
    } catch (Exception $e) {
      $message = $e->getMessage();
      echo $message;
      throw new data_error("Error: " . $message);
    }
  }

  /**
   * Get module id by name
   * 
   * @param string $modulename
   * @param obj $db
   * @return int moduleid
   */
  public static function test_get_moduleidbyname($modulename, $db) {
    $result = $db->prepare("SELECT id FROM modules where fullname = ?");
    $result->bind_param('s', $modulename);
    $result->execute();
    $result->bind_result($moduleid);
    $result->store_result();
    $result->fetch();
    if ($result->num_rows == 0) {
      $result->close();
      return false;
    }
    $result->close();
    return $moduleid;
  }

}
