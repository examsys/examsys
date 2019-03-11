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

use testing\unittest\unittestdatabase;
use PHPUnit\DbUnit\DataSet\YamlDataSet;

/**
 * Test support class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class supporttest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new YamlDataSet($this->get_base_fixture_directory() . "classes" . DIRECTORY_SEPARATOR . "support.yml");
    }
    /**
     * Test get email addresses
     * @group support
     */
    public function test_get_email() {
        $this->assertEquals("test@example.com;joseph.baxter@example.com", \support::get_email());
    }
    /**
     * Test get primary email address
     * @group support
     */
    public function test_get_primary_email() {
        $this->assertEquals("test@example.com", \support::get_primary_email());
    }

}