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
 * Test dbutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class dbutilstest extends unittestdatabase
{
    /**
     * @var array Storage for campus data in tests
     */
    private $campus;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('labs', 'core');
        $this->campus = $datagenerator->create_campus(array('name' => 'Main Campus', 'isdefault' => 1));
        ;
    }

    /**
     * Test generic db insert function
     * @group dbutils
     */
    public function test_exec_db_insert()
    {
        $table = 'campus';
        $params = array('name' => array('s', 'Test Campus'), 'isdefault' => array('i', 0));
        DBUtils::exec_db_insert($table, $params, $this->db);
        $queryTable = $this->query(array('table' => 'campus', 'columns' => array('name', 'isdefault')));
        $expectedTable = array(
            0 => array(
                'name' => $this->campus['name'],
                'isdefault' => 1
            ),
            1 => array(
                'name' => 'Test Campus',
                'isdefault' => 0
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test function check_sqlparams
     * @group dbutils
     */
    public function test_check_sqlparams()
    {
        $bindtype = array('i', 'i', 's');
        $bindvalue = array(4, 7, 'hello');
        $sql = 'select something from somewhere where thisis = ? and thatis = ? and theyall = ? ';
        $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
        $this->assertTrue($checker);

        $bindtype = array('i', 'i', 's');
        $bindvalue = array('4', 7, 'hello'); // "4" is not int
        $sql = 'select something from somewhere where thisis = ? and thatis = ? and theyall = ? ';
        $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
        $this->assertFalse($checker);

        $bindtype = array('i', 'i', 's', 'd'); // More types than values
        $bindvalue = array(4, 7, 'hello');
        $sql = 'select something from somewhere where thisis = ? and thatis = ? and theyall = ? ';
        $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
        $this->assertFalse($checker);

        $bindtype = array('i', 'i', 's');
        $bindvalue = array(4, 7, 'hello', '100'); // More value than types
        $sql = 'select something from somewhere where thisis = ? and thatis = ? and theyall = ? ';
        $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
        $this->assertFalse($checker);

        $bindtype = array('i', 'i', 's');
        $bindvalue = array(4, 7, 'hello');
        $sql = 'select something from somewhere where thisis = ? and thatis = ? and theyall = ? but notwant = ?'; // More ? than value/type
        $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
        $this->assertFalse($checker);

        $bindtype = array('i', 'i', 's');
        $bindvalue = array(4, 7, 5); // 5 is not string
        $sql = 'select something from somewhere where thisis = ? and thatis = ? and theyall = ? ';
        $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
        $this->assertFalse($checker);
    }

    /**
     * Test generic db upadte function
     * @group dbutils
     */
    public function test_exec_db_update()
    {
        $table = 'campus';
        $tableid = 'id';
        $params = array('isdefault' => array('i', 0));
        DBUtils::exec_db_update($table, $tableid, $params, $this->campus['id'], $this->db);
        $queryTable = $this->query(array('table' => 'campus', 'columns' => array('name', 'isdefault')));
        $expectedTable = array(
            0 => array(
                'name' => $this->campus['name'],
                'isdefault' => 0
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }
}
