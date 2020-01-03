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

namespace testing\unittest;

use Config as RogoConfig;
use UserObject as RogoUserObject;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use mysqli;
use testing\datagenerator\loader;
use testing\datagenerator\generator;
use testing\testcasetrait;

/**
 * Unit test database class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
abstract class unittestdatabase extends TestCase {
    use testcasetrait {

    }

    /** The state of the userobject - default mode */
    const USEROBJECT_DEEFAULT = 1;

    /** The state of the userobject - demo mode */
    const USEROBJECT_DEMO = 2;

    /** The state of the userojbect - impersonate mode */
    const USEROBJECT_IMPERSONATE = 3;

    /**
     * @var object $default_config config object used during test.
     */
    public $config;

    /** @var object $userObject user object used during test. */
    public $userobject;

    /**
     * @var object $default_config config object used to reset test.
     */
    public $default_config;

    /**
     * @var mysqli $db database object.
     */
    public $db;

    /**
     * @var integer Storage for admin user id in tests
     */
    public $admin;

    /**
     * @var integer Storage for student user id in tests
     */
    public $student;

    /**
     * @var integer Storage for module user id in tests
     */
    public $module;

    /**
     * @var integer Storage for school id in tests
     */
    public $school;

    /**
     * @var integer Storage for faculty id in tests
     */
    public $faculty;

    /** The name of the Rogo data directory in the virtual file system. */
    const DATA_DIRECTORY = 'data';

    /**
     * @var array $table_names table names
     */
    protected $table_names;

    /** @var string The location of the base fixtures directory. */
    protected $fixture_base;

    /**
     * Set-up test suite
     * @throws \Exception
     */
    private function setup_suite() : void {
        $this->fixture_base = dirname(dirname(__DIR__) ). DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR;
        $this->config = RogoConfig::get_instance();
        $this->default_config = clone($this->config);
        $db = $this->get_db_connection();
        $this->config->set_db_object($db);
        loader::set_database($db);
        $this->get_table_names();
        // Create user object.
        $this->userobject = new RogoUserObject($this->config, $db);
        $this->config->use_phpunit_site();
        vfsStream::setup(self::DATA_DIRECTORY, 0777);
        $this->config->set('cfg_rogo_data', vfsStream::url(self::DATA_DIRECTORY));
        // Load 'current' session into database if not already created. Needs to be here as config table required.
        $yearutils = new \yearutils($db);
        $currentsession = $yearutils->get_current_session();
        if (!$yearutils->check_calendar_year($currentsession)) {
            $datagenerator = $this->get_datagenerator('academic_year', 'core');
            $datagenerator->create_academic_year(array('year' => $currentsession . '/01/01'));
        }
    }

    /**
     * Wrapper function for loader::get function to load data generators
     * @param string $name The name of the generator.
     * @param string $component The component the generator is from (optional).
     * @return generator
     * @throws \testing\datagenerator\no_database
     * @throws \testing\datagenerator\not_found
     */
    public function get_datagenerator(string $name, string $component = 'core') : generator {
        return loader::get($name, $component);
    }

    /**
     * Phpunit setup function.
     */
    public function setUp() : void {
        $this->setup_suite();
        $this->setup_dataset();
    }

    /**
     * Tear down config object and close db connections.
     */
    public function tearDown() : void {
        // Reset the config object.
        RogoConfig::set_mock_instance(clone($this->default_config));
        // Destory user object.
        $this->userobject->destory();
        $this->teardown_dataset();
        // Close db connection.
        $this->get_db_connection()->close();
    }

    /**
     * Get the fixure directory location for tests.
     * @return string path to fixtures directory
     */
    public function get_base_fixture_directory() : string {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the admin user.
     * @return array
     */
    public function get_base_admin() : array {
        $id = $this->get_user_id('admin');
        $data = \userutils::get_user_details($id, $this->db);
        $data['id'] = $id;
        return $data;
    }

    /**
     * Get the student user.
     * @return array
     */
    public function get_base_student() : array {
        $id = $this->get_user_id('test1');
        $data = \userutils::get_user_details($id, $this->db);
        $data['id'] = $id;
        return $data;
    }

    /**
     * Get the id of the a user.
     * @param string $username username
     * @return int
     */
    public function get_user_id(string $username) : int {
        $exists = \userutils::username_exists($username, $this->db);
        if ($exists === false) {
            // Set to invalid id.
            $exists = 0;
        }
        return $exists;
    }

    /**
     * Get the id of the module loaded by the base file.
     * @return int
     */
    public function get_base_module() : int {
        return $this->get_module_id('TRAIN');
    }

    /**
     * Get the id of the a module.
     * @param string $module module
     * @return int
     */
    public function get_module_id(string $module) : int {
        $exists = \module_utils::get_idMod($module, $this->db);
        if ($exists === false) {
            // Set to invalid id.
            $exists = 0;
        }
        return $exists;
    }

    /**
     * Get the id of the module loaded by the base file.
     * @return int
     */
    public function get_base_faculty() : int {
        return $this->get_faculty_id('UNKNOWN Faculty');
    }

    /**
     * Get the id of the a faculty.
     * @param string $faculty faculty
     * @return int
     */
    public function get_faculty_id(string $faculty) : int {
        $exists = \facultyutils::facultyid_by_name($faculty, $this->db);
        if ($exists === false) {
            // Set to invalid id.
            $exists = 0;
        }
        return $exists;
    }

    /**
     * Get the id of the school loaded by the base file.
     * @return int
     */
    public function get_base_school() : int {
        return $this->get_school_id('UNKNOWN School');
    }

    /**
     * Get the id of the a school.
     * @param string $school school
     * @return int
     */
    public function get_school_id(string $school) : int {
        $exists = \schoolutils::get_school_id_by_name($school, $this->db);
        if ($exists === false) {
            // Set to invalid id.
            $exists = 0;
        }
        return $exists;
    }

    /**
     * Set the user object active
     * @param int $id user id
     * @param int $state userobject state
     * @param int $impersonate user to impersonate
     */
    public function set_active_user(int $id, int $state = self::USEROBJECT_DEEFAULT, int $impersonate = null) : void {
        $this->userobject->load($id);
        if ($state === self::USEROBJECT_DEMO) {
            $this->userobject->set_demo();
        } elseif ($state === self::USEROBJECT_IMPERSONATE and !is_null($impersonate)) {
            $this->userobject->impersonate($impersonate);
        }
    }

    /**
     * Set-up db connections.
     * @return mysqli
     */
    protected function get_db_connection() : mysqli {
        $this->config = RogoConfig::get_instance();
        if (!isset($this->db)) {
            // Open db connection.
            $dbconfig['host'] = $this->config->get('cfg_db_host');
            $dbconfig['user'] = $this->config->get('cfg_phpunit_db_user');
            $dbconfig['password'] = $this->config->get('cfg_phpunit_db_password');
            $dbconfig['database'] = $this->config->get('cfg_db_database');
            $dbconfig['port'] = $this->config->get('cfg_db_port');
            $this->db = $this->set_db_connection($dbconfig);
        }
        return $this->db;
    }

    /**
     * Load/Generate test data
     */
    protected function setup_dataset() : void {
        // Base data generation.
        $this->base_datageneration();
        // Load base ids into memory.
        $this->admin = $this->get_base_admin();
        $this->student = $this->get_base_student();
        $this->module = $this->get_base_module();
        $this->school = $this->get_base_school();
        $this->faculty = $this->get_base_faculty();
        // Test specific data generation.
        $this->datageneration();
    }

    /**
     * Clear data added to test db by dataset
     */
    protected function teardown_dataset() : void {
        $this->disable_fk_check();
        foreach ($this->table_names as $table) {
            $this->delete_from_table($table);
        }
        $this->enable_fk_check();
    }

    /**
     * Test specific data generation
     */
    abstract public function datageneration() : void;
}
