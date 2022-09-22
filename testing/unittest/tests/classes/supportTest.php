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

use testing\unittest\unittestdatabase;

/**
 * Test support class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class supporttest extends unittestdatabase
{

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('config', 'core');
        $datagenerator->change_setting(array('component' => 'core', 'setting' => 'support_contact_email', 'value' => array('test@example.com', 'joseph.baxter@example.com')));
    }

    /**
     * Test get email addresses
     * @group support
     */
    public function test_get_email()
    {
        $this->assertEquals('test@example.com;joseph.baxter@example.com', \support::get_email());
    }

    /**
     * Test get primary email address
     * @group support
     */
    public function test_get_primary_email()
    {
        $this->assertEquals('test@example.com', \support::get_primary_email());
    }
}
