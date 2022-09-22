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
use csv\csv_handler;
use import\import_modules;

/**
 * Test import csv class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class import_modulesTest extends unittestdatabase
{
    /**
     * Generate data for test.
     */
    public function datageneration(): void
    {
        // Currently only base data required.
    }

    /**
     * Get test file
     * @param string $name name of file
     * @return csv_handler
     * @throws file_load_exception
     */
    public function get_test_csv($name)
    {
        return new csv_handler($name . '.csv', $this->get_base_fixture_directory() . 'import' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR);
    }

    /**
     * Test module import add
     * @group import
     */
    public function test_execute_add()
    {
        $import = new import_modules($this->get_test_csv('modules'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid')));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
            ),
            1 => array(
                'moduleid' => 'TRAIN',
            ),
            2 => array(
                'moduleid' => 'TST',
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'add team member'
     * @group import
     */
    public function test_execute_update_addteammember()
    {
        $import = new import_modules($this->get_test_csv('modules_update_addteammember'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'add_team_members'), 'where' => array(array('column' => 'moduleid', 'value' => 'TRAIN'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'TRAIN',
                'add_team_members' => 0,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'checklist'
     * @group import
     */
    public function test_execute_update_checklist()
    {
        $import = new import_modules($this->get_test_csv('modules_update_checklist'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'checklist'), 'where' => array(array('column' => 'moduleid', 'value' => 'SYSTEM'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
                'checklist' => 'stdset,mapping',
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'active'
     * @group import
     */
    public function test_execute_update_active()
    {
        $import = new import_modules($this->get_test_csv('modules_update_active'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'active'), 'where' => array(array('column' => 'moduleid', 'value' => 'SYSTEM'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
                'active' => 0,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'external id'
     * @group import
     */
    public function test_execute_update_externalid()
    {
        $import = new import_modules($this->get_test_csv('modules_update_externalid'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'externalid'), 'where' => array(array('column' => 'moduleid', 'value' => 'TRAIN'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'TRAIN',
                'externalid' => '12341234',
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'negative marking'
     * @group import
     */
    public function test_execute_update_negmarking()
    {
        $import = new import_modules($this->get_test_csv('modules_update_negmarking'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'neg_marking'), 'where' => array(array('column' => 'moduleid', 'value' => 'SYSTEM'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
                'neg_marking' => 1,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'objective api'
     * @group import
     */
    public function test_execute_update_objectiveapi()
    {
        $import = new import_modules($this->get_test_csv('modules_update_objectiveapi'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'vle_api'), 'where' => array(array('column' => 'moduleid', 'value' => 'TRAIN'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'TRAIN',
                'vle_api' => 'NLE',
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'question based feedback'
     * @group import
     */
    public function test_execute_update_questionbasedfb()
    {
        $import = new import_modules($this->get_test_csv('modules_update_questionbasedfb'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'exam_q_feedback'), 'where' => array(array('column' => 'moduleid', 'value' => 'TRAIN'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'TRAIN',
                'exam_q_feedback' => 0,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'self enrol'
     * @group import
     */
    public function test_execute_update_selfenrol()
    {
        $import = new import_modules($this->get_test_csv('modules_update_selfenrol'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'selfenroll'), 'where' => array(array('column' => 'moduleid', 'value' => 'SYSTEM'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
                'selfenroll' => 1,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'timed exams'
     * @group import
     */
    public function test_execute_update_timedexams()
    {
        $import = new import_modules($this->get_test_csv('modules_update_timedexams'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'timed_exams'), 'where' => array(array('column' => 'moduleid', 'value' => 'SYSTEM'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
                'timed_exams' => 1,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import update 'year start'
     * @group import
     */
    public function test_execute_update_yearstart()
    {
        $import = new import_modules($this->get_test_csv('modules_update_yearstart'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid', 'academic_year_start'), 'where' => array(array('column' => 'moduleid', 'value' => 'TRAIN'))));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'TRAIN',
                'academic_year_start' => '07/02',
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test module import - missing school
     * @group import
     */
    public function test_execute_missing_school()
    {
        $import = new import_modules($this->get_test_csv('modules_missing_school'));
        $import->execute();
        // Test modules table is correct.
        $queryTable = $this->query(array('table' => 'modules', 'columns' => array('moduleid')));
        $expectedTable = array(
            0 => array(
                'moduleid' => 'SYSTEM',
            ),
            1 => array(
                'moduleid' => 'TRAIN',
            ),
            2 => array(
                'moduleid' => 'TST',
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        // Check failure caught.
        $this->assertEquals(array('TST2'), $import->get_failed());
    }

    /**
     * Test module import - missing required header
     * @group import
     */
    public function test_execute_missing_req_header()
    {
        $import = new import_modules($this->get_test_csv('modules_missing_req_header'));
        $this->expectExceptionMessage('modules_missing_req_header.csv has invalid headers');
        $import->execute();
    }
}
