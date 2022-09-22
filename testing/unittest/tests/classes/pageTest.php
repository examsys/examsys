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
 * Testcase for class Url.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 * @group page
 */
class pageTest extends unittestdatabase
{

    /**
     * @var string Storage for system install type
     */
    private $type;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('config', 'core');
        $this->type = 'unittest';
        $datagenerator->change_setting(array('component' => 'core', 'setting' => 'system_install_type', 'value' => $this->type));
    }

    /**
     * Test title generation
     * @group page
     */
    public function test_title()
    {
        $this->set_active_user($this->admin['id']);
        $this->assertEquals('title ' . $this->type, \page::title('title'));
    }

    /**
     * Test title generation in demo mode
     * @group page
     */
    public function test_title_demo()
    {
        $this->set_active_user($this->admin['id'], self::USEROBJECT_DEMO);
        $langpack = new \langpack();
        $demomode = $langpack->get_string('classes/page', 'demomode');
        $this->assertEquals('title ' . $this->type . ' (' . $demomode . ')', \page::title('title'));
    }

    /**
     * Test title generation in impersonation mode
     * @group page
     */
    public function test_title_impersonate()
    {
        $this->set_active_user($this->admin['id'], self::USEROBJECT_IMPERSONATE, $this->student['id']);
        $langpack = new \langpack();
        $as = $langpack->get_string('classes/page', 'as');
        $this->assertEquals('title ' . $this->type . ' ' . $as . ' Dr User1', \page::title('title'));
    }
}
