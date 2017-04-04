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
    \module_utils,
    \Config,
    testing\datagenerator\papers,
    \yearutils,
    UserUtils;

/**
 * Generates Rogo module.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class modules extends generator {

  /**
   * Create a new module
   * 
   * @param array parameters
   *  string parameters[moduleid] This is NOT module's id
   *  string parameters[fullname]
   * options are (
   *   $active, $schoolID, $vle_api, $sms_api, $selfEnroll, $peer, $external, $stdset, $mapping, 
   *   $neg_marking, $ebel_grid_template, $db, $sms_import = 0, $timed_exams = 0, $exam_q_feedback = 1, 
   *   $add_team_members = 1, $map_level = 0, $academic_year_start = '07/01')
   * @throws Exception If passed parameter is invailid
   */
  public function create_module($parameters) {
    if (empty($parameters['moduleid'])) {
      throw new data_error('moduleid must be provided');
    }
    if (empty($parameters['fullname'])) {
      throw new data_error('fullname must be provided');
    }
    $moduleid = $parameters['moduleid'];
    $fullname = $parameters['fullname'];
    $db = loader::get_database();
    $defaults = array(
        'active' => 1, 'schoolID' => 1, 'vle_api' => null, 'sms_api' => null, 'selfEnroll' => null,
        'peer' => null, 'external' => null, 'stdset' => null, 'mapping' => null, 'neg_marking' => null, 'ebel_grid_template' => null,
        'db' => $db, 'sms_import' => 0, 'timed_exams' => 0, 'exam_q_feedback' => 1, 'add_team_members' => 1,
        'map_level' => 0, 'academic_year_start' => '07/01');
    $settings = array_merge($parameters, $defaults);
    // All params for  add_module() $active, $schoolID, $vle_api, $sms_api, $selfEnroll, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $db, $sms_import = 0, $timed_exams = 0, $exam_q_feedback = 1, $add_team_members = 1, $map_level = 0, $academic_year_start = '07/01'
    $modid = module_utils::add_modules($moduleid, $fullname, $settings['active'], $settings['schoolID'], $settings['vle_api'], $settings['sms_api'], $settings['selfEnroll'], $settings['peer'], $settings['external'], $settings['stdset'], $settings['mapping'], $settings['neg_marking'], $settings['ebel_grid_template'], $settings['db'], $settings['sms_import'], $settings['timed_exams'], $settings['exam_q_feedback'], $settings['add_team_members'], $settings['map_level'], $settings['academic_year_start']);
    if (empty($modid)) {
      throw new data_error("Create new module failed with parameters: " . $moduleid . "--" . $fullname . "--" . implode("--", $settings));
    }
  }

  /**
   * Create a new module_team
   * 
   * @param array 
   *  string $modulename
   *  string $username
   * @throws Exception If passed parameter is invailid
   */
  public function create_module_team($parameters) {
    loader::get('papers');
    $modulename = $parameters['modulename'];
    $username = $parameters['username'];
    $db = loader::get_database();
    $userid = UserUtils::username_exists($username, $db);
    $moduleid = papers::test_get_moduleidbyname($modulename, $db);

    if (empty($userid) or empty($moduleid)) {
      throw new data_error("Create new module team failed with wrong parameter $modulename | $username ");
    }
    $result = \UserUtils::add_staff_to_module($userid, $moduleid, $db);
    if (empty($result)) {
      throw new data_error("Create new module team failed with parameter $modulename | $username ");
    }
  }
}
