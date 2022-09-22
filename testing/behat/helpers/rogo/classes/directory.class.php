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

namespace testing\behat\helpers\rogo;

use rogo_directory;

/**
 * Helpers for ExamSys directories in Behat.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class directory
{
    /**
     * Clear the contents of the ExamSys directories.
     */
    public static function reset_directories()
    {
        $mediadirectory = rogo_directory::get_directory('media');
        $mediadirectory->clear();
        $qtiimportdirectory = rogo_directory::get_directory('qti_import');
        $qtiimportdirectory->clear();
        $qtiexportdirectory = rogo_directory::get_directory('qti_export');
        $qtiexportdirectory->clear();
        $emailtemplatesdirectory = rogo_directory::get_directory('email_templates');
        $emailtemplatesdirectory->clear();
        $photodirectory = rogo_directory::get_directory('user_photo');
        $photodirectory->clear();
    }
}
