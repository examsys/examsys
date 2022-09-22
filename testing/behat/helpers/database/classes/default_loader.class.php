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

namespace testing\behat\helpers\database;

use mysqli;
use testing\datagenerator\loader;
use testing\datagenerator\generator;
use testing\testcasetrait;

/**
 * Implements the PHP Unit database extension for ExamSys Behat tests.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class Default_Loader extends Data_Loader
{
    use testcasetrait {

    }

    public function __construct($load_help = false)
    {
        parent::__construct($load_help);
        $this->fixture_base = __DIR__ . '/../../../../fixtures/base/';
    }

    /**
     * Behat setup function.
     */
    public function load(): void
    {
        $this->setUp();
        $this->setup_dataset();
    }

    /**
     * Behat teardown function.
     */
    public function clean(): void
    {
        $this->tearDown();
    }

    /**
     * Wrapper function for loader::get function to load data generators
     * @param string $name The name of the generator.
     * @param string $component The component the generator is from (optional).
     * @return generator
     * @throws \testing\datagenerator\no_database
     * @throws \testing\datagenerator\not_found
     */
    public function get_datagenerator(string $name, string $component = 'core'): generator
    {
        return loader::get($name, $component);
    }

    /**
     * Set-up db connections.
     * @return mysqli
     */
    protected function get_db_connection(): mysqli
    {
        $config = \Config::get_instance();
        if (!isset($this->phpunit_db)) {
            // Open db connection.
            $dbconfig['host'] = $config->get('cfg_db_host');
            $dbconfig['user'] = $config->get('cfg_behat_db_user');
            $dbconfig['password'] = $config->get('cfg_behat_db_password');
            $dbconfig['database'] = $config->get('cfg_behat_db_database');
            $dbconfig['port'] = $config->get('cfg_db_port');
            $this->phpunit_db = $this->set_db_connection($dbconfig);
        }
        return $this->phpunit_db;
    }

    /**
     * Gets the base data that should always be present in ExamSys.
     *
     * There should be a yml file for every database table in ExamSys.
     */
    protected function setup_dataset(): void
    {
        // Base data generation.
        $this->base_datageneration();
    }
}
