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
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\DbUnit\Operation;
use mysqli;
use PDO;

/**
 * Unit test database class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
abstract class unittestdatabase extends TestCase {
    use TestCaseTrait {
        setUp as protected parentSetUp;
        tearDown as protected parentTearDown;
    }
    /**
     * @var pdo object $pdo Only instantiate pdo once for test clean-up/fixture load.
     */
    static private $pdo = null;

    /**
     * @var pdo connection $conn Only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test.
     */
    private $conn = null;

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

    /** The name of the Rogo data directory in the virtual file system. */
    const DATA_DIRECTORY = 'data';
    
    /**
     * Set-up config and db connections.
     */
    public function setup_db() {
        $this->config = RogoConfig::get_instance();
        $this->default_config = clone($this->config);
        // Open db connection.
        $this->db = new mysqli($this->config->get('cfg_db_host'), $this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'),
            $this->config->get('cfg_db_database'), $this->config->get('cfg_db_port'));
        $this->config->set_db_object($this->db);
        // Create user object.
        $this->userobject = new RogoUserObject($this->config, $this->db);
        $this->config->use_phpunit_site();
        vfsStream::setup(self::DATA_DIRECTORY, 0777);
        $this->config->set('cfg_rogo_data', vfsStream::url(self::DATA_DIRECTORY));
    }
    
    /**
     * Phpunit setup function.
     */
    public function setUp() {
        $this->setup_db();
        $this->parentSetUp();
    }
    
    /**
     * Tear down config object and close db connections.
     */
    public function tearDown() {
        // Reset the config object.
        RogoConfig::set_mock_instance(clone($this->default_config));
        // Destory user object.
        $this->userobject->destory();
        // Close db connection.
        $this->db->close();
        $this->parentTearDown();
    }

    /**
     * Get PDO connection for dbunit
     * @return PDOobject
     */
    final public function getConnection() {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO("mysql:dbname=" . $this->config->get('cfg_db_database') . ";" . "host=" . $this->config->get('cfg_db_host'), $this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $this->config->get('cfg_db_database'));
        }
        return $this->conn;
    }
    
    /**
     * Get the fixure directory location for tests. 
     * @return string path to fixtures directory 
     */
    public function get_base_fixture_directory() {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Delete the dataset.
     * @param dataset $dataset
     */
    public function delete_dataset($dataset) {
        $delete = new Operation\DeleteAll();

        $delete->execute($this->conn, $dataset);
    }
}
