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

namespace testing\unittest;

use org\bovigo\vfs\vfsStreamWrapper;
use rogo_directory;

/**
 * Tests that should work for all rogo_directory sub-classes,
 * each ExamSys directory shoould have a testing class that extends this.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
abstract class DirectoryUnitTest extends UnitTest
{
    /** @var string The name of the rogo_directory sub-class to be tested. */
    protected $directory_class;

    /** @var string The relative path of the directory the class is expected to create. */
    protected $directory_name;

    /**
     * Test that the rogo_directory::get_directory method returns a media directory class.
     *
     * @group rogo_directory
     */
    public function test_get_directory()
    {
        $directory = rogo_directory::get_directory($this->directory_class);
        $this->assertInstanceOf('rogo_directory', $directory);
        $this->assertInstanceOf($this->directory_class, $directory);
    }

    /**
     * Test that the rogo_directory::get_directory method will not create new instances of the media directory.
     *
     * @group rogo_directory
     */
    public function test_get_directory_double_load()
    {
        $directory1 = rogo_directory::get_directory($this->directory_class);
        $directory2 = rogo_directory::get_directory($this->directory_class);
        $this->assertSame($directory2, $directory1);
    }

    /**
     * Tests the directory create function.
     *
     * @group rogo_directory
     */
    public function test_create()
    {
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChildren());
        $directory = rogo_directory::get_directory($this->directory_class);
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild($this->directory_name));
        $directory->create();
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild($this->directory_name));
    }

    /**
     * Test that a valid cache time has been set.
     *
     * @group rogo_directory
     */
    public function test_cachetime()
    {
        $directory = rogo_directory::get_directory($this->directory_class);
        $this->assertGreaterThanOrEqual(0, $directory->cachetime());
    }
}
