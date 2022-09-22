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
 * An exception to report that a directory class could not be found.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package core
 */
class directory_not_found extends Exception
{
    /**
     * Constructor for the exception.
     *
     * @param string $file path for the file that was not found.
     */
    public function __construct($directory)
    {
        $strings = LangUtils::loadlangfile('exceptions/messages.php', array());
        $message = sprintf($strings['directorynotfound'], array($directory));
        parent::__construct($message);
    }
}
