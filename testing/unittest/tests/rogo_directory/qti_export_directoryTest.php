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

use testing\unittest\DirectoryUnitTest;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * Test qti export directory class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class qti_export_directorytest extends DirectoryUnitTest
{
    /** @see parent::directory_class */
    protected $directory_class = 'qti_export';

    /** @see parent::directory_name */
    protected $directory_name = 'qti/exports';
}
