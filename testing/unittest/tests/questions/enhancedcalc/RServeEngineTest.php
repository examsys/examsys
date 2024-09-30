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

use plugins\questions\enhancedcalc\Engine as BaseEngine;
use plugins\questions\enhancedcalc\engine\rrserve\Engine;

require_once('EngineTest.php');

/**
 * Test the Rserve calculation engine does maths correctly.
 *
 * The tests themselves come from the parent class, as the behaviour must be the same for all engines.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2022 onwards The University of Nottingham
 * @package tests
 * @group questions
 */
class RServeEngineTest extends EngineTest
{
    /**
     * Connects to RServe if possible.
     *
     * @return \plugins\questions\enhancedcalc\Engine
     */
    protected function getEngine(): BaseEngine
    {
        // We failed to connect previously.
        if ($this->engine === false) {
            $this->markTestSkipped('RServe Not available');
        }

        // Check we have a working connection.
        if (!empty($this->engine)) {
            return $this->engine;
        }

        // Get the RServe configuration, by default try to connect to the docker instance.
        $config = Config::get_instance();
        $host = $config->get('cfg_phpunit_rserve_host') ?? 'calc';
        $port = $config->get('cfg_phpunit_rserve_port') ?? '6311';

        // Try connecting to localhost.
        $localhostconfig = [
            'host' => $host,
            'port' => $port,
            'timeout' => '5',
            'locale' => 'en_GB',
        ];
        $localengine = new Engine($localhostconfig);
        if ($localengine->connect()) {
            $this->engine = $localengine;
            return $this->engine;
        }

        // We could not connect.
        $this->engine = false;
        $this->markTestSkipped('RServe Not available');
    }

    /**
     * Ensure that there are no cached connections.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        Engine::resetConnection();
        parent::tearDownAfterClass();
    }

    /**
     * Tests that the calculation engine can handle bad data.
     *
     * @param array $variables An array with the key being the variable name and
     *                          the value being the value that should be used in the formula.
     * @param string $formula The formula used to calculate the answer.
     * @return void
     * @dataProvider dataCalculationCorrectAnsError
     */
    public function testCalculationCorrectAnsError(array $variables, string $formula)
    {
        $engine = $this->getEngine();
        $this->expectException(Rserve_Exception::class);
        $this->expectExceptionMessage('unable to evaluate');
        $engine->calculate_correct_ans($variables, $formula);
    }
}
