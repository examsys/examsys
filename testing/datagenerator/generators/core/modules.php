<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

namespace testing\datagenerator;

use module_utils;
use yearutils;
use UserUtils;

/**
 * Generates ExamSys module.
 *
 * @author Yijun Xue <yijun.xue@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class modules extends generator
{

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
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_module($parameters)
    {
        if (empty($parameters['moduleid'])) {
            throw new data_error('moduleid must be provided');
        }
        if (empty($parameters['fullname'])) {
            throw new data_error('fullname must be provided');
        }
        $defaults = array(
            'active' => 1, 'schoolID' => 1, 'vle_api' => null, 'sms_api' => null, 'selfEnroll' => null,
            'peer' => null, 'external' => null, 'stdset' => null, 'mapping' => null, 'neg_marking' => 0, 'ebel_grid_template' => null,
            'db' => $this->db, 'sms_import' => 0, 'timed_exams' => 0, 'exam_q_feedback' => 1, 'add_team_members' => 1,
            'map_level' => 0, 'academic_year_start' => '07/01', 'externalID' => null, 'moduleid' => $parameters['moduleid'],
            'fullname' => $parameters['fullname']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $modid = module_utils::add_modules($settings['moduleid'], $settings['fullname'], $settings['active'], $settings['schoolID'], $settings['vle_api'], $settings['sms_api'], $settings['selfEnroll'], $settings['peer'], $settings['external'], $settings['stdset'], $settings['mapping'], $settings['neg_marking'], $settings['ebel_grid_template'], $settings['db'], $settings['sms_import'], $settings['timed_exams'], $settings['exam_q_feedback'], $settings['add_team_members'], $settings['map_level'], $settings['academic_year_start'], $settings['externalID']);
        if (empty($modid)) {
            throw new data_error('Create new module failed with parameters: ' . implode('--', $settings));
        }
        $settings['id'] = $modid;
        return $settings;
    }

    /**
     * Create a new module_team
     *
     * @param array
     *  string $modulename
     *  string $username The username of the user.
     * @throws data_error If passed parameter is invalid
     */
    public function create_module_team($parameters)
    {
        $moduleid = $parameters['moduleid'];
        $username = $parameters['username'];
        $userid = UserUtils::username_exists($username, $this->db);
        $moduleid = module_utils::get_idMod($moduleid, $this->db);

        if (empty($userid) or empty($moduleid)) {
            throw new data_error("Create new module team failed with wrong parameter $modulename | $username ");
        }
        $result = \UserUtils::add_staff_to_module($userid, $moduleid, $this->db);
        if (empty($result)) {
            throw new data_error("Create new module team failed with parameter $modulename | $username ");
        }
    }

    /**
     * Create a new module enrolment
     *
     * @param array parameters
     *  int parameters[moduleid] The database id of the module
     *  int parameters[userid] The database id of the user
     * @throws data_error If passed parameter is invalid
     */
    public function create_enrolment($parameters)
    {
        if (empty($parameters['moduleid'])) {
            throw new data_error('moduleid must be provided');
        }
        if (empty($parameters['userid'])) {
            throw new data_error('userid must be provided');
        }
        $moduleid = $parameters['moduleid'];
        $userid = $parameters['userid'];
        $defaults = array(
            'attempt' => 1,
            'calendar_year' => null,
            'auto_update' => 0,
        );
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        if (is_null($settings['calendar_year'])) {
            $yearutils = new yearutils($this->db);
            $settings['calendar_year'] = $yearutils->get_current_session();
        }

        $id = UserUtils::add_student_to_module($userid, $moduleid, $settings['attempt'], $settings['calendar_year'], $this->db, $settings['auto_update']);
        if ($id === false) {
            throw new data_error('Create new module enrolment failed with parameters: ' . $userid . '--' . $moduleid . '--' . implode('--', $settings));
        }
    }

    /**
     * Create a module keyword
     * @param array parameters
     *  string parameters[moduleid] the module to add the keyword to
     *  string parameters[keyword] the keyword
     * @throws data_error If passed parameter is invalid
     */
    public function createModuleKeywords(array $parameters): void
    {
        $moduleid = $parameters['moduleid'];
        $new_keyword = $parameters['keyword'];
        $moduleid = module_utils::get_idMod($moduleid, $this->db);

        if (empty($moduleid)) {
            throw new data_error('Create new module keyword failed as module (id ' . $moduleid . ') does not exist');
        }

        $sql = 'INSERT INTO keywords_user VALUES (NULL, ?, ?, ?)';
        $query = $this->db->prepare($sql);
        $type = 'team';
        $query->bind_param('iss', $moduleid, $new_keyword, $type);
        $query->execute();
        $query->close();
    }
}
