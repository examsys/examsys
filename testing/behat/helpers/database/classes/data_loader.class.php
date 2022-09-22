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

/**
 * Base class used to load data into the ExamSys database for behat tests.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
abstract class Data_Loader
{
    /** @var PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection Database connector used by the PUP Unit database extension. */
    protected $phpunit_db;

    /** @var string The location of the base fixtures directory. */
    protected $fixture_base;

    /** @var string The location that the help sql files are located in. */
    protected $help_base;

    /** @var boolean Defines if the ExamSys help files should be loaded. */
    protected $load_help;

    public function __construct($load_help = false)
    {
        $this->fixture_base = __DIR__ . '/../../../../fixtures/base/';
        $this->help_base = __DIR__ . '/../../../../../install/';
        $this->load_help = $load_help;
    }

    /**
     * Do some base setup of the database.
     *
     * Required by testcasetrait
     */
    protected function setUp(): void
    {
        if ($this->load_help) {
            $this->load_help();
        }
    }

    /**
     * So some base tear down of the database
     */
    protected function tearDown(): void
    {
        // Intentionally empty.
    }

    /**
     * Load the help data into the database.
     *
     * @throws \Exception
     */
    protected function load_help()
    {
        $db = state::get_db();
        $db->autocommit(false);
        // An array of the names of the files we expect to be present, that contain the records to insert help.
        $help = array(
            'staff_help' => 'staff_help.sql',
            'student_help' => 'student_help.sql',
        );
        foreach ($help as $file) {
            $sql = file_get_contents($this->help_base . $file);
            $db->multi_query($sql);
            if ($db->error) {
                // The first query errored.
                $db->rollback();
                $db->autocommit(true);
                throw new \Exception('Failed to load help');
            }
            while ($db->more_results()) {
                $db->next_result();
                if ($db->error) {
                    // Another query errored.
                    $db->rollback();
                    $db->autocommit(true);
                    throw new \Exception('Failed to load help');
                }
            }
        }
        $db->commit();
        $db->autocommit(true);
    }
}
