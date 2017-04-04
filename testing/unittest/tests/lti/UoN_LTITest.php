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

// LTI classes not in the usual place so not in base namespace.
require_once 'LTI/ims-lti/UoN_LTI.php';

/**
 * Test uon lti class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class uonltitest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "lti" . DIRECTORY_SEPARATOR . "uonlti.yml");
    }

    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "lti" . DIRECTORY_SEPARATOR . $name . ".yml");
    }

    /**
     * Test lti context lookup
     * @group lti
     */
    public function test_lookup_lti_context() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        // Test context lookup for a student.
        $expected = array('TEST',"2016-02-11 16:29:11");
        $this->assertEquals($expected, $lti->lookup_lti_context('test:1'));
        // Test context lookup for a staff memeber.
        $expected = array('TEST',"2016-02-11 17:29:11");
        $this->assertEquals($expected, $lti->lookup_lti_context('test:2'));
    }

    /**
     * Test the get_user_by_external_id method.
     * @group lti
     */
    public function test_get_user_by_external_id() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            '1000-1' => array(
                'id' => 1000,
                'title' => 'Miss',
                'surname' => 'test',
                'firstnames' => 'one',
                'initials' => 'o',
                'username' => 'unit',
                'externalid' => '1',
            ),
        );
        $this->assertEquals($expected, $lti->get_user_by_external_id('1', 'test'));
    }

    /**
     * Test the get_links_by_username method
     * @group lti
     */
    public function test_get_links_by_username() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            '1001-2' => array(
                'id' => 1001,
                'title' => 'Mx',
                'surname' => 'staff',
                'firstnames' => 'two',
                'initials' => 't',
                'username' => 'staff',
                'externalid' => '2',
            ),
        );
        $this->assertEquals($expected, $lti->get_links_by_username('staff', 1));
    }

    /**
     * Test the get_lti_key method
     * @group lti
     */
    public function test_get_lti_key() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            'id' => 1,
            'oauth_consumer_key' => 'test',
            'secret' => 'testsecret',
            'name' => 'test lti',
            'context_id' => '',
        );
        $this->assertEquals($expected, $lti->get_lti_key(1));
    }

    /**
     * Test the delete_user_link method
     * @group lti
     */
    public function test_delete_user_link() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->delete_user_link(1000, 'test', '1');
        $querytable = $this->getConnection()->createQueryTable('lti_user', 'SELECT * FROM lti_user');
        $expectedtable = $this->get_expected_data_set('deleteuserlink')->getTable("lti_user");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test the generate_user_key method
     * @group lti
     */
    public function test_generate_user_key() {
        $lti = new UoN_LTI();
        $this->assertEquals('test:1', $lti->generate_user_key('test', '1'));
        $this->assertEquals('test:1', $lti->generate_user_key('test', 1));
        $this->assertEquals('myspecialkey:username', $lti->generate_user_key('myspecialkey', 'username'));
    }

    /**
     * Tests the add_lti_context method with the second parameter in it's defualt state.
     * It should use the values in the info array.
     * @group lti
     */
    public function test_add_lti_context_default() {
        $lti = new UoN_LTI();
        // Fake some page parameters, they should be used.
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'context_id' => '52',
        );
        $lti->init_lti0($this->db);
        $lti->add_lti_context(1);
        $querytable = $this->getConnection()->createQueryTable('lti_context', 'SELECT lti_context_key, c_internal_id FROM lti_context');
        $expectedtable = $this->get_expected_data_set('add_lti_context')->getTable("lti_context");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Tests the add_lti_context method with the second parameter set.
     * It should ignore the values in the info array.
     * @group lti
     */
    public function test_add_lti_context_no_default() {
        $lti = new UoN_LTI();
        // Fake some page parameters, they should be ignored.
        $lti->info = array(
            'oauth_consumer_key' => 'bandits',
            'context_id' => '52',
        );
        $lti->init_lti0($this->db);
        $lti->add_lti_context(1, 'test:9');
        $querytable = $this->getConnection()->createQueryTable('lti_context', 'SELECT lti_context_key, c_internal_id FROM lti_context');
        $expectedtable = $this->get_expected_data_set('add_lti_context_no_params')->getTable("lti_context");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_key() puts a key into the database correctly.
     * @group lti
     */
    public function test_add_lti_key() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->add_lti_key('My new system', 'newkey', 'IWillNeverTell');
        $querytable = $this->getConnection()->createQueryTable('lti_keys', 'SELECT id, oauth_consumer_key, secret, name FROM lti_keys');
        $expectedtable = $this->get_expected_data_set('add_lti_key')->getTable("lti_keys");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_resource() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_resource_default_key() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '15',
        );
        $lti->add_lti_resource(2, 'paper');
        $querytable = $this->getConnection()->createQueryTable('lti_resource', 'SELECT lti_resource_key, internal_id, internal_type FROM lti_resource');
        $expectedtable = $this->get_expected_data_set('add_lti_resource')->getTable("lti_resource");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_resource() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_resource_no_default() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '23',
        );
        $lti->add_lti_resource(2, 'paper', 'bandits:16');
        $querytable = $this->getConnection()->createQueryTable('lti_resource', 'SELECT lti_resource_key, internal_id, internal_type FROM lti_resource');
        $expectedtable = $this->get_expected_data_set('add_lti_resource_no_params')->getTable("lti_resource");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_user() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_user() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'blue',
            'user_id' => '169',
        );
        $lti->add_lti_user(1000);
        $querytable = $this->getConnection()->createQueryTable('lti_user', 'SELECT lti_user_key, lti_user_equ FROM lti_user');
        $expectedtable = $this->get_expected_data_set('add_lti_user')->getTable("lti_user");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_user() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_user_no_default() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'blue',
            'user_id' => '169',
        );
        $lti->add_lti_user(1000, 'bandits:69');
        $querytable = $this->getConnection()->createQueryTable('lti_user', 'SELECT lti_user_key, lti_user_equ FROM lti_user');
        $expectedtable = $this->get_expected_data_set('add_lti_user_no_params')->getTable("lti_user");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that delete_lti_key() removes an lti key safely.
     * @group lti
     */
    public function test_delete_lti_key() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->delete_lti_key(2);
        // Check that the live keys are present.
        $querytable = $this->getConnection()->createQueryTable('lti_keys', 'SELECT id, oauth_consumer_key, secret, name FROM lti_keys WHERE deleted IS NULL');
        $expectedtable = $this->get_expected_data_set('delete_lti_key_live')->getTable("lti_keys");
        $this->assertTablesEqual($expectedtable, $querytable);
        // Check that the deleted keys been marked as deleted and not removed completely.
        $querytable = $this->getConnection()->createQueryTable('lti_keys', 'SELECT id, oauth_consumer_key, secret, name FROM lti_keys WHERE deleted IS NOT NULL');
        $expectedtable = $this->get_expected_data_set('delete_lti_key_deleted')->getTable("lti_keys");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that lookup_lti_resource() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_resource() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            1,
            'paper',
            '2016-02-11 17:29:11',
        );
        $this->assertEquals($expected, $lti->lookup_lti_resource('test:9'));
    }
    
    /**
     * Test that lookup_lti_resource() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_resource_by_param() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '9',
        );
        $expected = array(
            1,
            'paper',
            '2016-02-11 17:29:11',
        );
        $this->assertEquals($expected, $lti->lookup_lti_resource());
    }

    /**
     * Test that lookup_lti_resource() returns false when the ket does not exist.
     * @group lti
     */
    public function test_lookup_lti_resource_no_found() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $this->assertFalse($lti->lookup_lti_resource('test:7'));
        $this->assertFalse($lti->lookup_lti_resource());
    }

    /**
     * Test that lookup_lti_user() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_user() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'user_id' => '2',
        );
        $expected = array(
            1000,
            '2016-02-11 16:29:11',
        );
        $this->assertEquals($expected, $lti->lookup_lti_user('test:1'));
    }

    /**
     * Test that lookup_lti_user() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_user_no_param() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'user_id' => '2',
        );
        $expected = array(
            1001,
            '2016-02-11 17:29:11',
        );
        $this->assertEquals($expected, $lti->lookup_lti_user());
    }

    /**
     * Test that lookup_lti_user() returns false when the key is not in use.
     * @group lti
     */
    public function test_lookup_lti_user_not_found() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'user_id' => '13',
        );
        $this->assertFalse($lti->lookup_lti_user('test:16'));
        $this->assertFalse($lti->lookup_lti_user());
    }

    /**
     * Test that lti_key_exists() detects the presnce of keys correctly.
     * @group lti
     */
    public function test_lti_key_exists() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $this->assertTrue($lti->lti_key_exists(1));
        $this->assertFalse($lti->lti_key_exists(3));
    }

    /**
     * Test that update_lti_key() updates the correct key.
     * @group lti
     */
    public function test_update_lti_key() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->update_lti_key(1, 'New name', 'newconsumerkey', 'DoNotTellAnyone', 'context');
        $querytable = $this->getConnection()->createQueryTable('lti_keys', 'SELECT id, oauth_consumer_key, secret, name, context_id, deleted FROM lti_keys');
        $expectedtable = $this->get_expected_data_set('update_lti_key')->getTable("lti_keys");
        $this->assertTablesEqual($expectedtable, $querytable);
    }

    /**
     * Test that update_lti_resource() updates the correct key.
     * @group lti
     */
    public function test_update_lti_resource() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '8',
        );
        $lti->update_lti_resource(6, 'module', 'test:9');
        $querytable = $this->getConnection()->createQueryTable('lti_resource', 'SELECT lti_resource_key, internal_id, internal_type FROM lti_resource');
        $expectedtable = $this->get_expected_data_set('update_lti_resource')->getTable("lti_resource");
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    
    /**
     * Test that update_lti_resource() updates the correct key.
     * @group lti
     */
    public function test_update_lti_resource_no_resource_key() {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '8',
        );
        $lti->update_lti_resource(6, 'module');
        $querytable = $this->getConnection()->createQueryTable('lti_resource', 'SELECT lti_resource_key, internal_id, internal_type FROM lti_resource');
        $expectedtable = $this->get_expected_data_set('update_lti_resource_no_key')->getTable("lti_resource");
        $this->assertTablesEqual($expectedtable, $querytable);
    }
}
