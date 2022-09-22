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

use LogBreakTime;

/**
 * Generates data related to exam breaks.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class breaks extends generator
{
    /**
     * Creates a record showing that the user has used exam breaks.
     *
     * Required params:
     * - paperID: The id of the paper the break was for
     * - userID: The id of the user taking the break.
     *
     * Optional params:
     * time: The time in seconds remaining to the user. (default: 0)
     *
     * @param array|\stdClass $parameters
     * @return array
     * @throws \testing\datagenerator\data_error
     */
    public function createExamBreak($parameters): array
    {
        // If an object is passed convert it into an array.
        if (is_object($parameters)) {
            $parameters = (array)$parameters;
        }
        // Check that the right type has been passed.
        if (!is_array($parameters)) {
            throw new data_error('Must pass an array or object');
        }
        // Check that the required parameters have been passed.
        if (empty($parameters['paperID'])) {
            throw new data_error('Must pass paperID');
        }
        if (empty($parameters['userID'])) {
            throw new data_error('Must pass userID');
        }

        $defaults = [
            'paperID' => null,
            'userID' =>  null,
            'time' => 0, // All break time used.
        ];
        $values = $this->set_defaults_and_clean($defaults, $parameters);

        if ($values['time'] < 0) {
            throw new data_error('The value of time must be positive or zero');
        }

        LogBreakTime::setBreak($values['userID'], $values['paperID'], $values['time']);

        return $values;
    }
}
