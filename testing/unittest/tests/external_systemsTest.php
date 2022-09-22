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
 * Test external_systems class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class externalsystemstest extends unittestdatabase
{
    /** @var array Storage for external system data in tests. */
    private $ext1;

    /** @var array Storage for external system data in tests. */
    private $ext2;

    /** @var array Storage for external system data in tests. */
    private $ext3;

    /** @var array Storage for external system data in tests. */
    private $ext4;

    /** @var array Storage for client data in tests. */
    private $client1;

    /** @var array Storage for client data in tests. */
    private $client2;

    /** @var array Storage for client data in tests. */
    private $client3;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('users', 'core');
        $user1 = $datagenerator->create_user(array('surname' => 'sysadmin', 'username' => 'sysadmin', 'roles' => 'Staff,SysAdmin', 'grade' => 'University Lecturer'));
        $user2 = $datagenerator->create_user(array('surname' => 'sysadmin2', 'username' => 'sysadmin2', 'roles' => 'Staff,SysAdmin', 'grade' => 'University Lecturer'));
        $user3 = $datagenerator->create_user(array('surname3' => 'sysadmin', 'username3' => 'sysadmin', 'roles' => 'Staff,SysAdmin', 'grade' => 'University Lecturer'));
        $datagenerator = $this->get_datagenerator('api', 'core');
        $this->client1 = $datagenerator->create_client(array('clientid' => 'test1', 'userid' => $user1['id'], 'secret' => 'test'));
        $this->client2 = $datagenerator->create_client(array('clientid' => 'test2', 'userid' => $user2['id'], 'secret' => 'test'));
        $this->client3 = $datagenerator->create_client(array('clientid' => 'test3', 'userid' => $user3['id'], 'secret' => 'test'));
        $this->ext1 = $datagenerator->create_external(array('clientid' => $this->client1['clientid'], 'name' => 'external api', 'type' => 'api'));
        $this->ext2 = $datagenerator->create_external(array('clientid' => $this->client2['clientid'], 'name' => 'external api 2', 'type' => 'api'));
        $this->ext3 = $datagenerator->create_external(array('name' => 'Campus Solutions', 'type' => 'plugin'));
        $this->ext4 = $datagenerator->create_external(array('name' => 'external api 3', 'type' => 'api'));
    }

    /**
     * Test external system info for user.
     * @group extsys
     */
    public function test_get_mapped_externalsystem_info()
    {
        $external = new external_systems();
        $info = $external->get_mapped_externalsystem_info($this->client2['clientid']);
        $this->assertEquals($this->ext2['name'], $info['name']);
        $this->assertEquals($this->ext2['id'], $info['id']);
    }

    /**
     * Test list all external systems.
     * @group extsys
     */
    public function test_get_all_externalsystems()
    {
        $external = new external_systems();
        $extsys = array($this->ext1['id'] => $this->ext1['name'], $this->ext2['id'] => $this->ext2['name'],
            $this->ext3['id'] => $this->ext3['name'], $this->ext4['id'] => $this->ext4['name']);
        $this->assertEquals($extsys, $external->get_all_externalsystems());
    }

    /**
     * Test list all external systems details included.
     * @group extsys
     */
    public function test_get_all_externalsystems_details()
    {
        $external = new external_systems();
        $extsys = array($this->ext1['id'] => array('name' => $this->ext1['name'], 'type' => \external_systems::API),
            $this->ext2['id'] => array('name' => $this->ext2['name'], 'type' => \external_systems::API),
            $this->ext3['id'] => array('name' => $this->ext3['name'], 'type' => \external_systems::PLUGIN),
            $this->ext4['id'] => array('name' => $this->ext4['name'], 'type' => \external_systems::API));
        $this->assertEquals($extsys, $external->get_all_externalsystems_details());
    }

    /**
     * Test list all API external systems.
     * @group extsys
     */
    public function test_get_all_api_externalsystems()
    {
        $external = new external_systems();
        $extsys = array($this->ext1['id'] => $this->ext1['name'], $this->ext2['id'] => $this->ext2['name'],
            $this->ext4['id'] => $this->ext4['name']);
        $this->assertEquals($extsys, $external->get_all_api_externalsystems());
    }

    /**
     * Test inserting new external system mapping
     * @group extsys
     */
    public function test_insert_external_system_mapping()
    {
        $external = new external_systems();
        $external->insert_external_system_mapping('test3', $this->ext2['id']);
        $queryTable = $this->query(array('table' => 'external_systems_mapping'));
        $expectedTable = array(
            0 => array(
                'client_id' => $this->client1['clientid'],
                'ext_id' => $this->ext1['id']
            ),
            1 => array(
                'client_id' => $this->client2['clientid'],
                'ext_id' => $this->ext2['id']
            ),
            2 => array(
                'client_id' => $this->client3['clientid'],
                'ext_id' => $this->ext2['id']
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test updating new external system mapping
     * @group extsys
     */
    public function test_update_external_system_mapping()
    {
        $external = new external_systems();
        $external->update_external_system_mapping('test1', $this->ext2['id']);
        $queryTable = $this->query(array('table' => 'external_systems_mapping'));
        $expectedTable = array(
            0 => array(
                'client_id' => $this->client1['clientid'],
                'ext_id' => $this->ext2['id']
            ),
            1 => array(
                'client_id' => $this->client2['clientid'],
                'ext_id' => $this->ext2['id']
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test deleting external system mapping
     * @group extsys
     */
    public function test_delete_external_system_mapping()
    {
        $external = new external_systems();
        $external->insert_external_system_mapping('test3', $this->ext2['id']);
        $external->delete_external_system_mapping('test3');
        $queryTable = $this->query(array('table' => 'external_systems_mapping'));
        $expectedTable = array(
            0 => array(
                'client_id' => $this->client1['clientid'],
                'ext_id' => $this->ext1['id']
            ),
            1 => array(
                'client_id' => $this->client2['clientid'],
                'ext_id' => $this->ext2['id']
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test inserting new external system
     * @group extsys
     */
    public function test_insert_external_system()
    {
        $external = new external_systems();
        $external->insert_external_system('test', \external_systems::PLUGIN);
        $queryTable = $this->query(array('table' => 'external_systems'));
        $expectedTable = array(
            0 => array(
                'id' => $this->ext1['id'],
                'name' => $this->ext1['name'],
                'type' => $this->ext1['type']
            ),
            1 => array(
                'id' => $this->ext2['id'],
                'name' => $this->ext2['name'],
                'type' => $this->ext2['type']
            ),
            2 => array(
                'id' => $this->ext3['id'],
                'name' => $this->ext3['name'],
                'type' => $this->ext3['type']
            ),
            3 => array(
                'id' => $this->ext4['id'],
                'name' => $this->ext4['name'],
                'type' => $this->ext4['type']
            ),
            4 => array(
                'id' => $this->ext4['id'] + 1,
                'name' => 'test',
                'type' => 'plugin'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test inserting new external system invalid name
     * @group extsys
     */
    public function test_insert_external_system_invlaid()
    {
        $external = new external_systems();
        $this->assertFalse($external->insert_external_system('', \external_systems::PLUGIN));
        $this->assertFalse($external->insert_external_system('0', \external_systems::PLUGIN));
    }

    /**
     * Test deleting external system
     * @group extsys
     */
    public function test_delete_external_system()
    {
        $external = new external_systems();
        $external->insert_external_system('test', \external_systems::PLUGIN);
        $external->delete_external_system($this->ext4['id'] + 1);
        $queryTable = $this->query(array('table' => 'external_systems'));
        $expectedTable = array(
            0 => array(
                'id' => $this->ext1['id'],
                'name' => $this->ext1['name'],
                'type' => $this->ext1['type']
            ),
            1 => array(
                'id' => $this->ext2['id'],
                'name' => $this->ext2['name'],
                'type' => $this->ext2['type']
            ),
            2 => array(
                'id' => $this->ext3['id'],
                'name' => $this->ext3['name'],
                'type' => $this->ext3['type']
            ),
            3 => array(
                'id' => $this->ext4['id'],
                'name' => $this->ext4['name'],
                'type' => $this->ext4['type']
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test checking external system exists
     * @group extsys
     */
    public function test_external_system_exists()
    {
        $external = new external_systems();
        $this->assertTrue($external->external_system_exists($this->ext1['id']));
        $this->assertTrue($external->external_system_exists($this->ext2['id']));
        $this->assertTrue($external->external_system_exists($this->ext3['id']));
        $this->assertFalse($external->external_system_exists('99999999999'));
    }

    /**
     * Test checking external system in use
     * @group extsys
     */
    public function test_external_system_inuse()
    {
        $external = new external_systems();
        $this->assertTrue($external->external_system_inuse($this->ext1['id']));
        $this->assertTrue($external->external_system_inuse($this->ext2['id']));
        $this->assertTrue($external->external_system_inuse($this->ext3['id']));
        $this->assertFalse($external->external_system_inuse($this->ext4['id']));
    }

    /**
     * Test checking external system not in use
     * @group extsys
     */
    public function test_external_system_not_inuse()
    {
        $external = new external_systems();
        $this->assertFalse($external->external_system_inuse($this->ext4['id']));
    }
}
