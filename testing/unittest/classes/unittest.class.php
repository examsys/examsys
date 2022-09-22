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

namespace testing\unittest;

use Config as RogoConfig;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests with no phpunit database.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
abstract class UnitTest extends TestCase
{
    /** @var object $default_config config object used during test. */
    public $config;

    /** @var object $default_config config object used to reset test. */
    public $default_config;

    /** The name of the ExamSys data directory in the virtual file system. */
    public const DATA_DIRECTORY = 'data';

    /**
     * Set-up config and db connections.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->config = RogoConfig::get_instance();
        $this->config->use_phpunit_site();
        $this->default_config = clone($this->config);
        vfsStream::setup(self::DATA_DIRECTORY, 0777);
        $this->config->set('cfg_rogo_data', vfsStream::url(self::DATA_DIRECTORY));
    }

    /**
     * Tear down config object and close db connections.
     * @return void
     */
    public function tearDown(): void
    {
        // Reset the config object.
        RogoConfig::set_mock_instance(clone($this->default_config));
        parent::tearDown();
    }
}
