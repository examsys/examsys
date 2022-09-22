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
        $et = UserUtils::getExtraTime($this->student['id'], $this->db);
        $this->assertEquals($et['breaktime'], $this->userobject->getRequiresBreaks());
    }

    /**
     * Test if user requires breaks
     * @group user
     */
    public function testGetRequiresBreaks2()
    {
        // Does not require breaks.
        $this->set_active_user($this->studentneeds['id']);
        $this->assertEquals(0, $this->userobject->getRequiresBreaks());
    }

    /**
     * Test if user setting accessibility
     * @group user
     */
    public function testuserSetAccessibility()
    {
        $this->set_active_user($this->student['id']);
        $this->userobject->userSetAccessibility(
            null,
            null,
            null,
            '#C0C0C0',
            null,
            null,
            null,
            null,
            null,
            '#DDDDDD',
            null,
            null
        );
        $expected = array(
            0 => array(
                'background' => UserObject::BGCOLOUR,
                'foreground' => UserObject::FGCOLOUR,
                'textsize' => UserObject::TEXTSIZE,
                'marks_color' => '#C0C0C0',
                'themecolor' => UserObject::THEMECOLOUR,
                'labelcolor' => UserObject::LABELCOLOUR,
                'font' => UserObject::FONT,
                'unanswered' => UserObject::UNANSWEREDCOLOUR,
                'dismiss' => UserObject::DISMISSCOLOUR,
                'globalthemecolour' => '#DDDDDD',
                'globalthemefont_colour' => UserObject::GLOBALTHEMEFONTCOLOUR,
                'highlight_bgcolour' => UserObject::HIGHLIGHTCOLOUR,
            ),
        );
        $actual = $this->query(
            array('columns' => array(
                    'background','foreground', 'textsize', 'marks_color', 'themecolor', 'labelcolor',
                    'font', 'unanswered', 'dismiss', 'globalthemecolour', 'globalthemefont_colour',
                    'highlight_bgcolour'),
                'table' => 'special_needs',
                'where' => array(array('column' => 'userID', 'value' => $this->student['id']))
            )
        );

        $this->assertEquals($expected, $actual);

        // Check tracked changes.
        $expectedtrack = array(
            0 => array(
                'type' => 'User Profile',
                'typeID' => $this->student['id'],
                'editor' => $this->student['id'],
                'old' => UserObject::MARKSCOLOUR,
                'new' => '#C0C0C0',
                'part' => 'marks',
            ),
            1 => array(
                'type' => 'User Profile',
                'typeID' => $this->student['id'],
                'editor' => $this->student['id'],
                'old' => UserObject::GLOBALTHEMECOLOUR,
                'new' => '#DDDDDD',
                'part' => 'globaltheme',
            ),
        );

        $actualtrack = $this->query(
            array('columns' => array(
                'type', 'typeID', 'editor', 'old', 'new', 'part'),
                'table' => 'track_changes',
                'where' => array(array('column' => 'typeID', 'value' => $this->student['id']))
            )
        );

        $this->assertEquals($expectedtrack, $actualtrack);
    }
}
