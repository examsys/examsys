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

use CourseUtils;

/**
 * Generates ExamSys course.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class course extends generator
{

    /** @var int Stores how many courses have been created. */
    protected static $coursescreated = 0;

    /**
     * Create a new course
     *
     * @param array parameters
     *  string parameters[schoolid]
     * options are (name, description, externalid, externalsys)
     * @return array
     * @throws data_error If passed parameter is invalid
     */
    public function create_course($parameters)
    {
        if (empty($parameters['schoolid'])) {
            if (empty($parameters['school'])) {
                throw new data_error('schoolid or school must be provided');
            }
            $parameters['schoolid'] = \SchoolUtils::get_school_id_by_name($parameters['school'], $this->db);
        }
        $coursenumber = ++self::$coursescreated;
        $defaults = array('name' => 'TEST' . $coursenumber, 'description' => 'a course description ' . $coursenumber, 'externalid' => null, 'externalsys' => null, 'schoolid' => $parameters['schoolid']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $courseid = CourseUtils::add_course($parameters['schoolid'], $settings['name'], $settings['description'], $settings['externalid'], $settings['externalsys'], $this->db);
        if (!$courseid) {
            throw new data_error('Create new course failed with parameters: ' . $parameters['schoolid'] . '--' . implode('--', $settings));
        }
        $settings['id'] = $courseid;
        return $settings;
    }
}
