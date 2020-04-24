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

/**
 * Test userobject class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class UserObjectTest extends unittestdatabase
{
    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('log', 'core');
        $datagenerator->create_metadata(
            array(
                'userID' => $this->student['id'],
                'paperID' => 1,
                'started' => '2017-01-01 00:00:00',
                'completed' => '2017-01-02 00:00:00'
            )
        );
        $datagenerator->create_metadata(
            array(
                'userID' => $this->student['id'],
                'paperID' => 2,
                'started' => '2017-01-01 00:00:00'
            )
        );
    }

    /**
     * Test user completed paper
     * @group user
     */
    public function testUserCompletedPaper()
    {
        $this->set_active_user($this->student['id']);
        // User completed a paper.
        $this->assertTrue($this->userobject->user_completed_paper(1));
        // User did not complete a paper.
        $this->assertFalse($this->userobject->user_completed_paper(2));
    }

    /**
     * Test if user requires breaks
     * @group user
     */
    public function testGetRequiresBreaks()
    {
        // Does require breaks.
        $this->set_active_user($this->student['id']);
        $this->assertTrue($this->userobject->getRequiresBreaks());
        // Does not require breaks.
        $this->set_active_user($this->get_user_id('test2'));
        $this->assertTrue($this->userobject->getRequiresBreaks());
    }
}
