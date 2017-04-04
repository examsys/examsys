<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

namespace testing\unittest;

/**
 * Test version class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class versiontest extends UnitTest {
    /**
     * Test is_version_higher minor versions
     * @group version
     */
    public function test_is_version_higher_minor() {
        $this->assertTrue(\version::is_version_higher("1.0.3", "1.0.2"));
        $this->assertTrue(\version::is_version_higher("2.0.10", "2.0.1"));
        $this->assertFalse(\version::is_version_higher("1.0.1", "1.0.2"));
        $this->assertFalse(\version::is_version_higher("1.0.0", "1.0.0"));
    }
    /**
     * Test is_version_higher major versions
     * @group version
     */
    public function test_is_version_higher_major() {
        $this->assertTrue(\version::is_version_higher("1.2.2", "1.1.2"));
        $this->assertTrue(\version::is_version_higher("2.10.0", "2.3.0"));
        $this->assertFalse(\version::is_version_higher("1.0.1", "1.1.1"));
        $this->assertFalse(\version::is_version_higher("1.1.0", "1.1.0"));
    }
    /**
     * Test is_version_higher release versions
     * @group version
     */
    public function test_is_version_higher_release() {
        $this->assertTrue(\version::is_version_higher("2.1.2", "1.1.2"));
        $this->assertTrue(\version::is_version_higher("10.0.3", "10.0.1"));
        $this->assertFalse(\version::is_version_higher("1.0.1", "2.0.1"));
        $this->assertFalse(\version::is_version_higher("2.1.0", "2.1.0"));
    }
    /**
     * Test check_version_format
     * @group version
     */
    public function test_check_version_format() {
        $this->assertEquals(1, \version::check_version_format("2.1.2"));
        $this->assertEquals(1, \version::check_version_format("20.10.20"));
        $this->assertEquals(1, \version::check_version_format("200.100.200"));
        $this->assertEquals(0, \version::check_version_format("2000.1000.2000"));
        $this->assertEquals(0, \version::check_version_format("2.1"));
        $this->assertEquals(0, \version::check_version_format("2"));
        $this->assertEquals(0, \version::check_version_format("2.1.2.1"));
    }
    /**
     * Test sort_version
     * @group version
     */
    public function test_sort_version() {
        $versions = array("2.0.10", "2.0.2", "2.1.0", "2.0.1", "10.0.0", "0.0.1");
        $sorted = array("0.0.1", "2.0.1", "2.0.2", "2.0.10", "2.1.0", "10.0.0");
        $this->assertEquals($sorted, \version::sort_version($versions));
    }
}