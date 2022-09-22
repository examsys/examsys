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
class uonltitest extends unittestdatabase
{
    /** @var array Storage for context in tests. */
    private $context;

    /** @var array Storage for context in tests. */
    private $context2;

    /** @var array Storage for user data in tests. */
    private $user;

    /** @var array Storage for user data in tests. */
    private $user2;

    /** @var array Storage for consumer data in tests. */
    private $key;

    /** @var array Storage for consumer data in tests. */
    private $key2;

    /** @var array Storage for resource data in tests. */
    private $resource;

    /** @var array Storage for resource data in tests. */
    private $resource2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('lti', 'core');
        $this->key = $datagenerator->create_key(array('name' => 'test lti'));
        // The externalid is the id from an external sys so place holders in this test.
        $this->context = $datagenerator->create_context(array('oauth_consumer_key' => 'test', 'externalid' => 1, 'internalid' => $this->module));
        $this->context2 = $datagenerator->create_context(array('oauth_consumer_key' => 'test', 'externalid' => 2, 'internalid' => $this->module));
        $this->user = $datagenerator->create_user(array('oauth_consumer_key' => 'test', 'externalid' => 1, 'internalid' => $this->student['id']));
        $this->user2 = $datagenerator->create_user(array('oauth_consumer_key' => 'test', 'externalid' => 2, 'internalid' => $this->admin['id']));
        // The internalid is a paper id - these are placeholders in these tests.
        $this->resource = $datagenerator->create_resource(array('oauth_consumer_key' => 'test', 'externalid' => 9, 'internalid' => 1, 'internaltype' => 'paper'));
        $this->resource2 = $datagenerator->create_resource(array('oauth_consumer_key' => 'test', 'externalid' => 8, 'internalid' => 2, 'internaltype' => 'paper'));
        $this->key2 = $datagenerator->create_key(array('name' => 'test lti 2', 'secret' => 'MyLittleSecret', 'oauth_consumer_key' => 'test2'));
    }

    /**
     * Test lti context lookup
     * @group lti
     */
    public function test_lookup_lti_context()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        // Test context lookup for a student.
        $lookup = $lti->lookup_lti_context($this->context['lti_context_key']);
        $this->assertEquals('TRAIN', $lookup[0]);
        // Test context lookup for a staff memeber.
        $lookup = $lti->lookup_lti_context($this->context2['lti_context_key']);
        $this->assertEquals('TRAIN', $lookup[0]);
    }

    /**
     * Test the get_user_by_external_id method.
     * @group lti
     */
    public function test_get_user_by_external_id()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            $this->student['id'] . '-1' => array(
                'id' => $this->student['id'],
                'title' => 'Dr',
                'surname' => 'User1',
                'firstnames' => 'A',
                'initials' => 'A',
                'username' => 'test1',
                'externalid' => '1',
            ),
        );
        $this->assertEquals($expected, $lti->get_user_by_external_id('1', 'test'));
    }

    /**
     * Test the get_links_by_username method
     * @group lti
     */
    public function test_get_links_by_username()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            $this->admin['id'] . '-2' => array(
                'id' => $this->admin['id'],
                'title' => 'Miss',
                'surname' => 'Administrator',
                'firstnames' => 'System',
                'initials' => 'S',
                'username' => 'admin',
                'externalid' => '2',
            ),
        );
        $this->assertEquals($expected, $lti->get_links_by_username('admin', $this->key['id']));
    }

    /**
     * Test the get_lti_key method
     * @group lti
     */
    public function test_get_lti_key()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            'id' => $this->key['id'],
            'oauth_consumer_key' => $this->key['oauth_consumer_key'],
            'secret' => $this->key['secret'],
            'name' => $this->key['name'],
            'context_id' => $this->key['contextid'],
        );
        $this->assertEquals($expected, $lti->get_lti_key($this->key['id']));
    }

    /**
     * Test the delete_user_link method
     * @group lti
     */
    public function test_delete_user_link()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->delete_user_link($this->student['id'], 'test', '1');
        $querytable = $this->query(array('table' => 'lti_user', 'columns' => array('lti_user_key', 'lti_user_equ')));
        $expectedtable = array(
            0 => array(
                'lti_user_key' => $this->user2['lti_user_key'],
                'lti_user_equ' =>  $this->user2['lti_user_equ'],
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test the generate_user_key method
     * @group lti
     */
    public function test_generate_user_key()
    {
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
    public function test_add_lti_context_default()
    {
        $lti = new UoN_LTI();
        // Fake some page parameters, they should be used.
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'context_id' => '52',
        );
        $lti->init_lti0($this->db);
        $lti->add_lti_context($this->module);
        $querytable = $this->query(array('columns' => array('lti_context_key', 'c_internal_id'), 'table' => 'lti_context'));
        $expectedtable = array(
            0 => array(
                'lti_context_key' => $this->context['lti_context_key'],
                'c_internal_id' =>  $this->context['c_internal_id'],
            ),
            1 => array(
                'lti_context_key' => $this->context2['lti_context_key'],
                'c_internal_id' =>  $this->context2['c_internal_id'],
            ),
            2 => array(
                'lti_context_key' => 'test:52',
                'c_internal_id' =>  $this->module,
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Tests the add_lti_context method with the second parameter set.
     * It should ignore the values in the info array.
     * @group lti
     */
    public function test_add_lti_context_no_default()
    {
        $lti = new UoN_LTI();
        // Fake some page parameters, they should be ignored.
        $lti->info = array(
            'oauth_consumer_key' => 'bandits',
            'context_id' => '52',
        );
        $lti->init_lti0($this->db);
        $lti->add_lti_context($this->module, 'test:9');
        $querytable = $this->query(array('columns' => array('lti_context_key', 'c_internal_id'), 'table' => 'lti_context'));
        $expectedtable = array(
            0 => array(
                'lti_context_key' => $this->context['lti_context_key'],
                'c_internal_id' =>  $this->context['c_internal_id'],
            ),
            1 => array(
                'lti_context_key' => $this->context2['lti_context_key'],
                'c_internal_id' =>  $this->context2['c_internal_id'],
            ),
            2 => array(
                'lti_context_key' => 'test:9',
                'c_internal_id' =>  $this->module,
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_key() puts a key into the database correctly.
     * @group lti
     */
    public function test_add_lti_key()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->add_lti_key('My new system', 'newkey', 'IWillNeverTell');
        $querytable = $this->query(array('columns' => array('oauth_consumer_key', 'secret', 'name'), 'table' => 'lti_keys',
            'where' => array(array('column' => 'oauth_consumer_key', 'value' => 'newkey'))));
        $expectedtable = array(
            0 => array(
                'oauth_consumer_key' => 'newkey',
                'secret' =>  'IWillNeverTell',
                'name' =>  'My new system',
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_resource() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_resource_default_key()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '15',
        );
        $lti->add_lti_resource(2, 'paper');
        $querytable = $this->query(array('columns' => array('lti_resource_key', 'internal_id', 'internal_type'), 'table' => 'lti_resource',
            'where' => array(array('column' => 'internal_id', 'value' => 2))));
        $expectedtable = array(
            0 => array(
                'lti_resource_key' => 'test:15',
                'internal_id' =>  '2',
                'internal_type' =>  'paper',
            ),
            1 => array(
                'lti_resource_key' => $this->resource2['lti_resource_key'],
                'internal_id' =>  $this->resource2['internal_id'],
                'internal_type' =>  $this->resource2['internal_type'],
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_resource() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_resource_no_default()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '23',
        );
        $lti->add_lti_resource(2, 'paper', 'bandits:16');
        $querytable = $this->query(array('columns' => array('lti_resource_key', 'internal_id', 'internal_type'), 'table' => 'lti_resource',
            'where' => array(array('column' => 'internal_id', 'value' => 2))));
        $expectedtable = array(
            0 => array(
                'lti_resource_key' => 'bandits:16',
                'internal_id' =>  '2',
                'internal_type' =>  'paper',
            ),
            1 => array(
                'lti_resource_key' => $this->resource2['lti_resource_key'],
                'internal_id' =>  $this->resource2['internal_id'],
                'internal_type' =>  $this->resource2['internal_type'],
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_user() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_user()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'blue',
            'user_id' => '169',
        );
        $lti->add_lti_user($this->student['id']);
        $querytable = $this->query(array('columns' => array('lti_user_key', 'lti_user_equ'), 'table' => 'lti_user', 'orderby' => array('lti_user_key'),
            'where' => array(array('column' => 'lti_user_equ', 'value' => $this->student['id']))));
        $expectedtable = array(
            0 => array(
                'lti_user_key' => 'blue:169',
                'lti_user_equ' =>  $this->student['id'],
            ),
            1 => array(
                'lti_user_key' => $this->user['lti_user_key'],
                'lti_user_equ' =>  $this->user['lti_user_equ'],
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that add_lti_user() puts a resource into the database correctly.
     * @group lti
     */
    public function test_add_lti_user_no_default()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'blue',
            'user_id' => '169',
        );
        $lti->add_lti_user($this->student['id'], 'bandits:69');
        $querytable = $this->query(array('columns' => array('lti_user_key', 'lti_user_equ'), 'table' => 'lti_user', 'orderby' => array('lti_user_key'),
            'where' => array(array('column' => 'lti_user_equ', 'value' => $this->student['id']))));
        $expectedtable = array(
            0 => array(
                'lti_user_key' => 'bandits:69',
                'lti_user_equ' =>  $this->student['id'],
            ),
            1 => array(
                'lti_user_key' => $this->user['lti_user_key'],
                'lti_user_equ' =>  $this->user['lti_user_equ'],
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that delete_lti_key() removes an lti key safely.
     * @group lti
     */
    public function test_delete_lti_key()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->delete_lti_key($this->key2['id']);
        // Check that the live keys are present.
        $querytable = $this->query(array('columns' => array('oauth_consumer_key', 'secret', 'name'), 'table' => 'lti_keys', 'where' => array(array('column' => 'deleted', 'value' => null, 'operator' => 'IS'))));
        $expectedtable = array(
            0 => array(
                'oauth_consumer_key' => 'test',
                'secret' =>  'testsecret',
                'name' =>  'test lti',
            )
        );
        $this->assertEquals($expectedtable, $querytable);
        // Check that the deleted keys been marked as deleted and not removed completely.
        $querytable = $this->query(array('columns' => array('oauth_consumer_key', 'secret', 'name'), 'table' => 'lti_keys', 'where' => array(array('column' => 'deleted', 'value' => null, 'operator' => 'IS NOT'))));
        $expectedtable = array(
            0 => array(
                'oauth_consumer_key' => 'test2',
                'secret' =>  'MyLittleSecret',
                'name' =>  'test lti 2',
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that lookup_lti_resource() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_resource()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $expected = array(
            1,
            'paper'
        );
        $lookup = $lti->lookup_lti_resource('test:9');
        $this->assertEquals($expected, array($lookup[0], $lookup[1]));
    }

    /**
     * Test that lookup_lti_resource() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_resource_by_param()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '9',
        );
        $expected = array(
            1,
            'paper'
        );
        $lookup = $lti->lookup_lti_resource();
        $this->assertEquals($expected, array($lookup[0], $lookup[1]));
    }

    /**
     * Test that lookup_lti_resource() returns false when the ket does not exist.
     * @group lti
     */
    public function test_lookup_lti_resource_no_found()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $this->assertFalse($lti->lookup_lti_resource('test:7'));
        $this->assertFalse($lti->lookup_lti_resource());
    }

    /**
     * Test that lookup_lti_user() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_user()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'user_id' => '2',
        );
        $lookup = $lti->lookup_lti_user('test:1');
        $this->assertEquals($this->student['id'], $lookup[0]);
    }

    /**
     * Test that lookup_lti_user() finds keys correctly.
     * @group lti
     */
    public function test_lookup_lti_user_no_param()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'user_id' => '2',
        );
        $lookup = $lti->lookup_lti_user();
        $this->assertEquals($this->admin['id'], $lookup[0]);
    }

    /**
     * Test that lookup_lti_user() returns false when the key is not in use.
     * @group lti
     */
    public function test_lookup_lti_user_not_found()
    {
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
    public function test_lti_key_exists()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $this->assertTrue($lti->lti_key_exists($this->key['id']));
        $this->assertFalse($lti->lti_key_exists(3));
    }

    /**
     * Test that update_lti_key() updates the correct key.
     * @group lti
     */
    public function test_update_lti_key()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->update_lti_key($this->key['id'], 'New name', 'newconsumerkey', 'DoNotTellAnyone', 'context');
        $querytable = $this->query(array('columns' => array('oauth_consumer_key', 'secret', 'name', 'context_id'), 'table' => 'lti_keys',
            'where' => array(array('column' => 'id', 'value' => $this->key['id']))));
        $expectedtable = array(
            0 => array(
                'oauth_consumer_key' => 'newconsumerkey',
                'secret' =>  'DoNotTellAnyone',
                'name' =>  'New name',
                'context_id' =>  'context',
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that update_lti_resource() updates the correct key.
     * @group lti
     */
    public function test_update_lti_resource()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '8',
        );
        $lti->update_lti_resource(6, 'module', 'test:9');
        $querytable = $this->query(array('columns' => array('lti_resource_key', 'internal_id', 'internal_type'), 'table' => 'lti_resource',
            'where' => array(array('column' => 'lti_resource_key', 'value' => 'test:9'))));
        $expectedtable = array(
            0 => array(
                'lti_resource_key' => $this->resource['lti_resource_key'],
                'internal_id' =>  '6',
                'internal_type' =>  'module',
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test that update_lti_resource() updates the correct key.
     * @group lti
     */
    public function test_update_lti_resource_no_resource_key()
    {
        $lti = new UoN_LTI();
        $lti->init_lti0($this->db);
        $lti->info = array(
            'oauth_consumer_key' => 'test',
            'resource_link_id' => '8',
        );
        $lti->update_lti_resource(6, 'module');
        $querytable = $this->query(array('columns' => array('lti_resource_key', 'internal_id', 'internal_type'), 'table' => 'lti_resource',
            'where' => array(array('column' => 'lti_resource_key', 'value' => 'test:8'))));
        $expectedtable = array(
            0 => array(
                'lti_resource_key' => $this->resource2['lti_resource_key'],
                'internal_id' =>  '6',
                'internal_type' =>  'module',
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }
}
