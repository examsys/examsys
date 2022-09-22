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

/**
 *
 * Class for Multiple Response options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class OptionLABELLING extends OptionEdit
{
    /**
     * Is this option blank?
     * @return boolean
     */
    public function is_blank()
    {
        return ($this->correct == '');
    }

    /**
     * Check that the minimum set of fields exist in the given data to create a new option
     * @param array $data
     * @param array $files expects PHP FILES array
     * @param integer $index option number
     * @return boolean
     */
    public function minimum_fields_exist($data, $files, $index)
    {
        return true;
    }
}
