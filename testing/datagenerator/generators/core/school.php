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

use SchoolUtils;

/**
 * Generates ExamSys school.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class school extends generator
{
    /** @var int Stores how many schools have been created. */
    protected static $schoolscreated = 0;

    /**
     * Create a new school
     *
     * @param array parameters
     *  string parameters[school]
     *  string parameters[facultyID]
     * options are ($code, $externalid, $externalsys)
     * @throws data_error If passed parameter is invalid
     * @return array
     */
    public function create_school($parameters)
    {
        if (empty($parameters['facultyID'])) {
            throw new data_error('facultyID must be provided');
        }
        $schoolscreated = ++self::$schoolscreated;
        $defaults = array('school' => 'School ' . $schoolscreated,'code' => null, 'externalid' => null, 'externalsys' => null,
            'facultyID' => $parameters['facultyID']);
        $settings = $this->set_defaults_and_clean($defaults, $parameters);
        $schoolid = SchoolUtils::add_school($parameters['facultyID'], $settings['school'], $this->db, $settings['code'], $settings['externalid'], $settings['externalsys']);
        if (!$schoolid) {
            throw new data_error('Create new faculty failed with parameters: ' . implode('--', $settings));
        }
        $settings['id'] = $schoolid;
        return $settings;
    }
}
