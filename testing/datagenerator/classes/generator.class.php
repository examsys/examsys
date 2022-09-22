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

/**
 * All ExamSys datagenerators must extend this class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class generator
{
    /** @var mysqli Store the database connection object to be used by data generators. */
    public $db = null;

    /**
     * Get a random value from a static array of the class.
     *
     * @param string $variable The name of the variable.
     * @return mixed A value from the array.
     * @throws data_error
     */
    protected function random_value($variable)
    {
        $class = get_class($this);
        if (!property_exists($class, $variable)) {
            throw new data_error("$class::\$$variable not found");
        }
        if (!is_array($class::${$variable})) {
            throw new data_error("$class::\$$variable is not an array");
        }
        $size = count($class::${$variable});
        $position = rand(0, $size - 1);
        return $class::${$variable}[$position];
    }

    /**
     * Will return an array that contains only the indicies from $defaults,
     * using the values from $paramterts if they are set.
     *
     * @param array $defaults Contains all the indicies that should be returned along their their default values.
     * @param array $parameters The values that should over write the defaults.
     * @return array
     */
    protected function set_defaults_and_clean(array $defaults, array $parameters)
    {
        $return = array_merge($defaults, $parameters);
        return array_intersect_key($return, $defaults);
    }
}
