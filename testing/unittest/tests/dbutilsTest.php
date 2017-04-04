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
 * Test dbutils class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class dbutilstest extends unittestdatabase {

    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "dbutilsTest" . DIRECTORY_SEPARATOR . "campus.yml");
    }
    
    /**
     * Get expected data set from yml
     * @param string $name filename of fixtures
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "dbutilsTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    
    /**
     * Test generic db insert function
     * @group dbutils
     */
    public function test_exec_db_insert() {
        $table = 'campus';
        $params = array('name' => array('s', 'Test Campus'), 'isdefault' => array('i', 0));
        $campus = DBUtils::exec_db_insert($table, $params, $this->db);
        $queryTable = $this->getConnection()->createQueryTable('campus', 'SELECT * FROM campus');
        $expectedTable = $this->get_expected_data_set('insertcampus')->getTable("campus");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    
    /**
     * Test function check_sqlparams
     * @group dbutils
     */
    public function test_check_sqlparams() {
      
      $bindtype = array("i","i","s");
      $bindvalue = array(4, 7, "hello");
      $sql = "select something from somewhere where thisis = ? and thatis = ? and theyall = ? ";
      $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
      $this->assertTrue($checker);
      
      $bindtype = array("i","i","s");
      $bindvalue = array("4", 7, "hello"); // "4" is not int
      $sql = "select something from somewhere where thisis = ? and thatis = ? and theyall = ? ";
      $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
      $this->assertFalse($checker);
      
      $bindtype = array("i","i","s", "d"); // More types than values
      $bindvalue = array(4, 7, "hello");
      $sql = "select something from somewhere where thisis = ? and thatis = ? and theyall = ? ";
      $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
      $this->assertFalse($checker);
      
      $bindtype = array("i","i","s"); 
      $bindvalue = array(4, 7, "hello", "100"); // More value than types
      $sql = "select something from somewhere where thisis = ? and thatis = ? and theyall = ? ";
      $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
      $this->assertFalse($checker);
      
      $bindtype = array("i","i","s");
      $bindvalue = array(4, 7, "hello");
      $sql = "select something from somewhere where thisis = ? and thatis = ? and theyall = ? but notwant = ?"; // More ? than value/type
      $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
      $this->assertFalse($checker);
      
      $bindtype = array("i","i","s");
      $bindvalue = array(4, 7, 5); // 5 is not string
      $sql = "select something from somewhere where thisis = ? and thatis = ? and theyall = ? ";
      $checker = DBUtils::check_sqlparams($bindtype, $bindvalue, $sql);
      $this->assertFalse($checker);
    }
    
    /**
     * Test generic db upadte function
     * @group dbutils
     */
    public function test_exec_db_update() {
        $table = 'campus';
        $tableid = 'id';
        $id = 1;
        $params = array('isdefault' => array('i', 0));
        $campus = DBUtils::exec_db_update($table, $tableid, $params, $id, $this->db);
        $queryTable = $this->getConnection()->createQueryTable('campus', 'SELECT * FROM campus');
        $expectedTable = $this->get_expected_data_set('updatecampus')->getTable("campus");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
}
