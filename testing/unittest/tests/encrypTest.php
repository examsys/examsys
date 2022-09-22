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
 * Test encrypt class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class encryptest extends unittestdatabase
{
    /**
     * Generate data for test.
     */
    public function datageneration(): void
    {
        // Currently only base data required.
    }

    /**
     * Test gen_password - non readable asked for.
     * @group encryption
     */
    public function test_gen_password_non_readable()
    {
        $this->config->set_setting('misc_dictionary_file', $this->get_base_fixture_directory() . 'encrypTest' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'sampledict', 'string');
        $enc = new \encryp();
        // Random password.
        $pass = $enc->gen_password(false);
        $this->assertEquals($pass['password'], $pass['display_password']);
        $this->assertEquals(8, mb_strlen($pass['password']));
        $this->assertTrue($enc->is_readable());
    }

    /**
     * Test gen_password - non readable asked for, non default length.
     * @group encryption
     */
    public function test_gen_password_non_readable_with_length()
    {
        $this->config->set_setting('misc_dictionary_file', $this->get_base_fixture_directory() . 'encrypTest' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'sampledict', 'string');
        $enc = new \encryp();
        $pass = $enc->gen_password(false, 10);
        $this->assertEquals($pass['password'], $pass['display_password']);
        $this->assertEquals(10, mb_strlen($pass['password']));
        $this->assertTrue($enc->is_readable());
    }

    /**
     * Test gen_password - readable asked for
     * @group encryption
     */
    public function test_gen_password_readable()
    {
        $this->config->set_setting('misc_dictionary_file', $this->get_base_fixture_directory() . 'encrypTest' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'sampledict', 'string');
        $enc = new \encryp();
        $pass = $enc->gen_password(true);
        $this->assertEquals($pass['password'], str_replace(' ', '', $pass['display_password']));
        $this->assertTrue($enc->is_readable());
    }

    /**
     * Test gen_password - readable asked for, non default length.
     * @group encryption
     */
    public function test_gen_password_readable_with_length()
    {
        $this->config->set_setting('misc_dictionary_file', $this->get_base_fixture_directory() . 'encrypTest' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'sampledict', 'string');
        $enc = new \encryp();
        $pass = $enc->gen_password(true, 12);
        $this->assertEquals($pass['password'], str_replace(' ', '', $pass['display_password']));
        $this->assertTrue($enc->is_readable());
    }

    /**
     * Test gen_password - no dictionary available
     * @group encryption
     */
    public function test_gen_password_no_dict()
    {
        $enc = new \encryp();
        // Readable password asked for but default to non-readable.
        $pass = $enc->gen_password(true, 12);
        $this->assertEquals($pass['password'], $pass['display_password']);
        $this->assertEquals(12, mb_strlen($pass['password']));
    }

    /**
     * Test gen_password - dictionary too small
     * @group encryption
     */
    public function test_gen_password_small_dict()
    {
        $this->config->set_setting('misc_dictionary_file', $this->get_base_fixture_directory() . 'encrypTest' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'smallsampledict', 'string');
        $enc = new \encryp();
        // Readable password asked for but default to non-readable.
        $pass = $enc->gen_password(true, 12);
        $this->assertEquals($pass['password'], $pass['display_password']);
        $this->assertEquals(12, mb_strlen($pass['password']));
    }
}
