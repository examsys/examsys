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
use plugins\questions\enhancedcalc\engine\phpeval\Engine;

/**
 * Test PHPeval calculation engine does maths correctly.
 *
 * Other engines should extend this test class over-riding the getEngine method only.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2022 onwards The University of Nottingham
 * @package tests
 * @group questions
 */
class EngineTest extends \testing\unittest\UnitTest
{
    /** @var \plugins\questions\enhancedcalc\Engine */
    protected $engine;

    /**
     * Gets the calculation engine.
     *
     * When testing a new engine you must override this method to return the calculation object that should be tested.
     *
     * @return \plugins\questions\enhancedcalc\Engine
     */
    protected function getEngine(): BaseEngine
    {
        if (!empty($this->engine)) {
            return $this->engine;
        }

        $settings = [
            'locale' => 'en_GB',
        ];
        $engine = new Engine($settings);
        $this->engine = $engine;
        return $this->engine;
    }

    /**
     * Test that we interpret formulas correctly.
     *
     * @param array $variables An array with the key being the variable name and
     *                         the value being the value that should be used in the formula.
     * @param string $formula The formula used to calculate the answer.
     * @param int|float|string $expected The expected result of the calculation.
     * @return void
     * @dataProvider dataCalculateCorrectAns
     */
    public function testCalculateCorrectAns(array $variables, string $formula, int|float|string $expected)
    {
        $engine = $this->getEngine();
        if (is_float($expected)) {
            // Different engines can have different floating point precision so
            // force the minimum acceptable one for comparison.
            $engine->setRoundingMode(PHP_ROUND_HALF_UP);
            $this->assertEquals(
                $expected,
                $engine->format_number_to_precision_of_other_number(
                    $engine->calculate_correct_ans($variables, $formula),
                    $expected
                )
            );
        } else {
            $this->assertEquals($expected, $engine->calculate_correct_ans($variables, $formula));
        }
    }

    /**
     * A set of equations and the answers that should be calculated.
     *
     * @return array
     */
    public function dataCalculateCorrectAns(): array
    {
        return [
            // Simple maths.
            [['$A' => 1, '$B' => 1], '$A + $B', 2],
            [['$A' => 2, '$B' => 2], '$A * $B', 4],
            [['$A' => 3, '$B' => 1], '$A - $B', 2],
            [['$A' => 4, '$B' => 2], '$A / $B', 2],
            // Test precedence is done correctly.
            [['$A' => 4, '$B' => 2, '$C' => 3], '$A + $B * $C', 10],
            [['$A' => 4, '$B' => 2, '$C' => 3], '($A + $B) * $C', 18],
            // Test all documented functions.
            [['$A' => 2], 'abs($A)', 2],
            [['$A' => -2], 'abs($A)', 2],
            [['$A' => 0.1], 'acos($A)', 1.4706289056333],
            [['$A' => 3], 'acosh($A)', 1.7627471740391],
            [['$A' => 0.1], 'asin($A)', 0.10016742116156],
            [['$A' => 3], 'asinh($A)', 1.8184464592321],
            [['$A' => 3, '$B' => 5], 'atan2($A, $B)', 0.54041950027058],
            [['$A' => 3], 'atan($A)', 1.2490457723983],
            [['$A' => 0.1], 'atanh($A)', 0.10033534773108],
            [['$A' => 0.1], 'ceil($A)', 1],
            [['$A' => 0.1], 'cos($A)', 0.99500416527803],
            [['$A' => 5], 'cosh($A)', 74.209948524788],
            [['$A' => 180], 'deg2rad($A)', 3.1415926535898],
            [['$A' => 2], 'exp($A)', 7.3890560989307],
            [['$A' => 2], 'expm1($A)', 6.3890560989307],
            [['$A' => 2.9], 'floor($A)', 2],
            [['$A' => 2, '$B' => 5], 'fmod($A, $B)', 2],
            [['$A' => 10], 'log10($A)', 1],
            [['$A' => 10], 'log1p($A)', 2.3978952727984],
            [['$A' => 10], 'log($A)', 2.302585092994],
            [['$A' => 10, '$B' => 10], 'log($A, $B)', 1],
            [['$A' => 5, '$B' => 10], 'max($A, $B)', 10],
            [['$A' => 10, '$B' => 5], 'max($A, $B)', 10],
            [['$A' => 5, '$B' => 10], 'min($A, $B)', 5],
            [['$A' => 10, '$B' => 5], 'min($A, $B)', 5],
            [[], 'pi', 3.1415926535898],
            [[], 'pi()', 3.1415926535898],
            [['$A' => 10, '$B' => 2], 'pow($A, $B)', 100],
            // We are purposely not testing half rounding here as PHP and RServe will use different methods,
            // and there is nothing we can do about it.
            [['$A' => 123.2222, '$B' => 2], 'round($A, $B)', 123.22],
            [['$A' => 123.6666, '$B' => 1], 'round($A, $B)', 123.7],
            [['$A' => 10], 'sin($A)', -0.544021110889],
            [['$A' => 10], 'sinh($A)', 11013.232874703],
            [['$A' => 10], 'sqrt($A)', 3.16227766],
            [['$A' => 10], 'tan($A)', 0.64836083],
            [['$A' => 10], 'tanh($A)', 0.99999999587769],
            // Try some more complicated things (especially around pi)
            [[], 'pi-3', 0.1415926535898],
            [[], '3+pi', 6.1415926535898],
            [[], '3+pi()-3', 3.1415926535898],
            [['$A' => 180], 'deg2rad($A) - deg2rad($A)', 0],
            [['$A' => 5], 'round(pi, $A)', 3.14159],
            // Test that we can handle divide by zero well.
            [['$A' => 4, '$B' => 0], '$A / $B', 'Inf'],
        ];
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
        $this->assertEquals('ERROR', $engine->calculate_correct_ans($variables, $formula));
    }

    public function dataCalculationCorrectAnsError(): array
    {
        return [
            // Test that we can handle whn there is an error in a variable.
            [['$A' => 4, '$B' => 'ERROR'], '$A / $B'],
        ];
    }

    /**
     * Tests that we check is answers are correct.
     *
     * @param string $user_answer The users answer.
     * @param string $answer The answer to the question.
     * @param bool $round If the actual answer should be rounded to the student answer.
     * @param bool $expected
     * @return void
     * @dataProvider dataIsUseranswerCorrect
     */
    public function testIsUseranswerCorrect(string $user_answer, string $answer, bool $round, bool $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode(PHP_ROUND_HALF_UP);
        $this->assertEquals($expected, $engine->is_useranswer_correct($user_answer, $answer, $round));
    }

    /**
     * @return array
     */
    public function dataIsUseranswerCorrect(): array
    {
        return [
            ['3.14', '3.1415926535898', true, true],
            ['3.14', '3.1415926535898', false, false],
            ['3.14', '3.14', false, true],
            ['3.14', '3.14', true, true],
            // Engineering format.
            ['314e-2', '3.1415926535898', true, true],
            ['314e-2', '3.1415926535898', false, false],
            ['31.4e-1', '3.14', false, true],
            ['314e-2', '3.14', true, true],
        ];
    }

    /**
     * tests that we calculate the percentage distance the user answer is from the correct answer.
     *
     * @param int $method The rounding method to use.
     * @param string $answer The user answer.
     * @param string $correct The correct answer.
     * @param int|float $expected The expected distance as a percentage.
     * @return void
     * @dataProvider dataDistanceFromCorrectAnswer
     */
    public function testDistanceFromCorrectAnswer(int $method, string $answer, string $correct, int|float $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode($method);
        $this->assertEquals($expected, $engine->distance_from_correct_answer($answer, $correct));
    }

    /**
     * Data to test that distance calculations work correctly.
     *
     * @return array[]
     */
    public function dataDistanceFromCorrectAnswer(): array
    {
        return [
            [PHP_ROUND_HALF_UP, '125', '100', 25],
            [PHP_ROUND_HALF_UP, '75', '100', 25],
            [PHP_ROUND_HALF_UP, '1.25', '1', 25],
            [PHP_ROUND_HALF_UP, '100.5', '100', 0.5],
            // Check precision is to 3 decimal places.
            [PHP_ROUND_HALF_UP, '100.005', '100', 0.005],
            [PHP_ROUND_HALF_UP, '100.0006', '100', 0.001],
            [PHP_ROUND_HALF_UP, '100.0004', '100', 0],
            // Where the correct answer is 0.
            [PHP_ROUND_HALF_UP, '0', '0', 0],
            [PHP_ROUND_HALF_UP, '2', '0', 2],
            [PHP_ROUND_HALF_UP, '-5', '0', 5],
            [PHP_ROUND_HALF_UP, '-9.99999', '0', 10],
            [PHP_ROUND_HALF_UP, '-10.33333', '0', 10.333],
        ];
    }

    /**
     * @param string $correct The correct answer
     * @param string $percent The distance allowed from the correct answer
     * @param int|float $expectedtolerance The calculated tolerance
     * @param int|float $expectedmin The minimum answer allowed
     * @param int|float $expectedmax The maximum answer allowed
     * @return void
     * @dataProvider dataCalculateTolerancePercent
     */
    public function testCalculateTolerancePercent(
        string $correct,
        string $percent,
        int|float $expectedtolerance,
        int|float $expectedmin,
        int|float $expectedmax
    ) {
        $engine = $this->getEngine();
        $engine->setRoundingMode(PHP_ROUND_HALF_UP);
        $result = $engine->calculate_tolerance_percent($correct, $percent);
        $this->assertEquals($expectedtolerance, $result['tolerance']);
        $this->assertEquals($expectedmin, $result['tolerance_ansneg']);
        $this->assertEquals($expectedmax, $result['tolerance_ans']);
    }

    /**
     * Data for the percentage tolerance tests.
     *
     * @return array[]
     */
    public function dataCalculateTolerancePercent(): array
    {
        return [
            ['100', '1', 1, 99, 101],
            ['1', '1', 0.01, 0.99, 1.01],
            ['0.001', '1', 0.00001, 0.00099, 0.00101],
            ['-100', '1', 1, -101, -99],
            ['100', '0.01', 0.01, 99.99, 100.01],
            ['100', '0', 0, 100, 100],
        ];
    }

    /**
     * Tests that we calculate the minimum and maximum values correctly.
     *
     * @param string $correct The correct answer
     * @param string $allowed The distance allowed from the correct answer
     * @param int|float $expectedmin The minimum answer allowed
     * @param int|float $expectedmax The maximum answer allowed
     * @return void
     * @dataProvider dataCalculateToleranceAbsolute
     */
    public function testCalculateToleranceAbsolute(string $correct, string $allowed, int|float $expectedmin, int|float $expectedmax)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode(PHP_ROUND_HALF_UP);
        $result = $engine->calculate_tolerance_absolute($correct, $allowed);
        $this->assertEquals($allowed, $result['tolerance']);
        $this->assertEquals($expectedmin, $result['tolerance_ansneg']);
        $this->assertEquals($expectedmax, $result['tolerance_ans']);
    }

    /**
     * Data used to test that we calculate the answer bounds correctly.
     *
     * @return array[]
     */
    public function dataCalculateToleranceAbsolute(): array
    {
        return [
            ['100', '1', 99, 101],
            ['100', '0.1', 99.9, 100.1],
            ['-100', '0.1', -100.1, -99.9],
            ['-100', '0', -100, -100],
        ];
    }

    /**
     * Checks that tolerance checking works correctly.
     *
     * @param string $answer The users answer
     * @param int|float $min The minimum correct value
     * @param int|float $max The maximum correct value
     * @param bool $expected
     * @return void
     * @dataProvider dataIsUseranswerWithinTolerance
     */
    public function testIsUseranswerWithinTolerance(string $answer, int|float $min, int|float $max, bool $expected)
    {
        $engine = $this->getEngine();
        $this->assertEquals($expected, $engine->is_useranswer_within_tolerance($answer, $min, $max));
    }

    /**
     * Data for the tolerance checks.
     *
     * @return array[]
     */
    public function dataIsUseranswerWithinTolerance(): array
    {
        return [
            ['', -1, 1, false],
            ['0', -1, 1, true],
            ['-1', -1, 1, true],
            ['1', -1, 1, true],
            ['-1.0000000001', -1, 1, false],
            ['1.0000000001', -1, 1, false],
            ['0', 0, 0, true],
        ];
    }

    /**
     * Test that we can detect if an answer is given to the correct number of significant figures.
     *
     * @param string $answer The users answer
     * @param string $number The number of significant figures.
     * @param bool $expected
     * @return void
     * @dataProvider dataIsUseranswerWithinSignificantFigures
     */
    public function testIsUseranswerWithinSignificantFigures(string $answer, string $number, bool $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode(PHP_ROUND_HALF_UP);
        $this->assertEquals($expected, $engine->is_useranswer_within_significant_figures($answer, $number));
    }

    /**
     * Data for testing if an answer is to the correct number of significant figures.
     *
     * @return array[]
     */
    public function dataIsUseranswerWithinSignificantFigures(): array
    {
        return [
            ['', '3', false],
            ['0', '3', true],
            ['100', '3', true],
            ['1000', '3', true],
            ['1010', '3', true],
            ['125000', '3', true],
            ['1001', '3', false],
            ['1000', '3', true],
            ['0.0123', '3', true],
            ['-0.0123', '3', true],
            ['3e4', '3', true],
            ['314e-2', '3', true],
            ['3145e4', '3', false],
        ];
    }

    /**
     * Tests that we can correctly detect if numbers are within a specific number of decimal places.
     *
     * @param string $answer The user answer
     * @param string $number the number of decimal places
     * @param bool $expected The expected result.
     * @return void
     * @dataProvider dataIsUseranswerCorrectDecimalPlaces
     */
    public function testIsUseranswerCorrectDecimalPlaces(string $answer, string $number, bool $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode(PHP_ROUND_HALF_UP);
        $this->assertEquals($expected, $engine->is_useranswer_correct_decimal_places($answer, $number));
    }

    /**
     * Data to test that numbers are correct to a specific number of decimal places.
     *
     * @return array[]
     */
    public function dataIsUseranswerCorrectDecimalPlaces(): array
    {
        return [
            ['', '1', false],
            ['0', '1', true],
            ['1.1', '1', true],
            ['1.11', '1', false],
            ['1.11', '2', true],
            ['1111111.1', '1', true],
        ];
    }

    /**
     * Tests that we can correctly detect if numbers are exactly a specific number of decimal places.
     *
     * @param string $answer The user answer
     * @param string $number the number of decimal places
     * @param bool $expected The expected result.
     * @return void
     * @dataProvider dataIsUseranswerCorrectDecimalStrictzeros
     */
    public function testIsUseranswerCorrectDecimalPlacesStrictzeros(string $answer, string $number, bool $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode(PHP_ROUND_HALF_UP);
        $this->assertEquals($expected, $engine->is_useranswer_correct_decimal_places_strictzeros($answer, $number));
    }

    /**
     * Data to test that numbers are correct to a specific number of decimal places.
     *
     * @return array[]
     */
    public function dataIsUseranswerCorrectDecimalStrictzeros(): array
    {
        return [
            ['', '1', false],
            ['0', '1', false],
            ['1.1', '1', true],
            ['1.11', '1', false],
            ['1.11', '2', true],
            ['1111111.1', '1', true],
        ];
    }

    /**
     * Tests that the engine formats to a set of decimal places strictly.
     *
     * @param int $method The rounding method.
     * @param int $places The number of decimal places.
     * @param string $answer The value that should be changed.
     * @param string $expected The expected value.
     * @return void
     * @dataProvider dataRoundingStrict
     */
    public function testFormatNumberDPStrictZeros(int $method, int $places, string $answer, string $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode($method);
        $this->assertEquals($expected, $engine->format_number_dp_strict_zeros($answer, $places));
    }

    /**
     * The data for strict rounding tests.
     * @return array[]
     */
    public function dataRoundingStrict(): array
    {
        return [
            // Test Rounding.
            [PHP_ROUND_HALF_UP, 2, '0.123456', '0.12'],
            [PHP_ROUND_HALF_UP, 3, '0.123456', '0.123'],
            [PHP_ROUND_HALF_UP, 3, '0.987654321', '0.988'],
            [PHP_ROUND_HALF_UP, 3, '123456.100000000099999', '123456.100'],
            // Test rounding at halfway points.
            [PHP_ROUND_HALF_UP, 3, '0.5555', '0.556'],
            [PHP_ROUND_HALF_DOWN, 3, '0.5555', '0.555'],
            [PHP_ROUND_HALF_EVEN, 3, '0.5555', '0.556'],
            [PHP_ROUND_HALF_UP, 3, '0.4445', '0.445'],
            [PHP_ROUND_HALF_DOWN, 3, '0.4445', '0.444'],
            [PHP_ROUND_HALF_EVEN, 3, '0.4445', '0.444'],
            // Test rounding when number has fewer places.
            [PHP_ROUND_HALF_UP, 3, '1', '1.000'],
            [PHP_ROUND_HALF_UP, 3, '1.0', '1.000'],
        ];
    }

    /**
     * Tests that the engine formats to a set of decimal places.
     *
     * @param int $method The rounding method.
     * @param int $places The number of decimal places.
     * @param string $answer The value that should be changed.
     * @param string $expected The expected value.
     * @return void
     * @dataProvider dataRounding
     */
    public function testFormatNumberDP(int $method, int $places, string $answer, string $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode($method);
        $this->assertEquals($expected, $engine->format_number_dp($answer, $places));
    }

    /**
     * The data for rounding tests.
     *
     * @return array[]
     */
    public function dataRounding(): array
    {
        return [
            // Test Rounding.
            [PHP_ROUND_HALF_UP, 2, '0.123456', '0.12'],
            [PHP_ROUND_HALF_UP, 3, '0.123456', '0.123'],
            [PHP_ROUND_HALF_UP, 3, '0.987654321', '0.988'],
            [PHP_ROUND_HALF_UP, 3, '123456.100000000099999', '123456.1'],
            // Test rounding at halfway points.
            [PHP_ROUND_HALF_UP, 3, '0.5555', '0.556'],
            [PHP_ROUND_HALF_DOWN, 3, '0.5555', '0.555'],
            [PHP_ROUND_HALF_EVEN, 3, '0.5555', '0.556'],
            [PHP_ROUND_HALF_UP, 3, '0.4445', '0.445'],
            [PHP_ROUND_HALF_DOWN, 3, '0.4445', '0.444'],
            [PHP_ROUND_HALF_EVEN, 3, '0.4445', '0.444'],
            // Test rounding when number has fewer places.
            [PHP_ROUND_HALF_UP, 3, '1', '1'],
            [PHP_ROUND_HALF_UP, 3, '1.0', '1'],
        ];
    }

    /**
     * Tests that calculation engine correctly handles significant figures.
     *
     * @param int $method The rounding method.
     * @param int $significat The number of significant figures in the answer.
     * @param string $answer The value that should have its significant figures changed.
     * @param string $expected The expected value.
     * @return void
     * @dataProvider dataSignificantFigures
     */
    public function testFormatNumberSF(int $method, int $significat, string $answer, string $expected)
    {
        $engine = $this->getEngine();
        $engine->setRoundingMode($method);
        $this->assertSame($expected, $engine->format_number_sf($answer, $significat));
    }

    /**
     * Data used to test that significant figures work correctly.
     *
     * @return array[]
     */
    public function dataSignificantFigures(): array
    {
        return [
            // Test some different types of number.
            [PHP_ROUND_HALF_UP, 3, '124843', '125000'],
            [PHP_ROUND_HALF_UP, 4, '124843', '124800'],
            [PHP_ROUND_HALF_UP, 2, '1.24843', '1.2'],
            [PHP_ROUND_HALF_UP, 2, '0.00123', '0.0012'],
            [PHP_ROUND_HALF_UP, 3, '1011', '1010'],
            // Test rounding.
            [PHP_ROUND_HALF_UP, 3, '0.5555', '0.556'],
            [PHP_ROUND_HALF_DOWN, 3, '0.5555', '0.555'],
            [PHP_ROUND_HALF_EVEN, 3, '0.5555', '0.556'],
            [PHP_ROUND_HALF_UP, 3, '0.4445', '0.445'],
            [PHP_ROUND_HALF_DOWN, 3, '0.4445', '0.444'],
            [PHP_ROUND_HALF_EVEN, 3, '0.4445', '0.444'],
            // Test Rounding when there are less significant figures.
            [PHP_ROUND_HALF_UP, 3, '1', '1.00'],
            // Test negative numbers.
            [PHP_ROUND_HALF_UP, 2, '-0.00123', '-0.0012'],
        ];
    }

    /**
     * Tests that we can correctly round a number to a similar precision as another number.
     *
     * @param int $method The rounding method.
     * @param string $answer The number to be rounded
     * @param string $target The number to be used for precision
     * @param string $expected The expected result
     * @return void
     * @dataProvider dataPrecision
     */
    public function testFormatNumberToPrecisionOfOtherNumber(
        int $method,
        string $answer,
        string $target,
        string $expected
    ) {
        $engine = $this->getEngine();
        $engine->setRoundingMode($method);
        $this->assertEquals($expected, $engine->format_number_to_precision_of_other_number($answer, $target));
    }

    /**
     * Data for the precision comparison tests.
     *
     * @return array[]
     */
    public function dataPrecision(): array
    {
        return [
            // Test rounding.
            [PHP_ROUND_HALF_UP, '0.5555', '1.234', '0.556'],
            [PHP_ROUND_HALF_DOWN, '0.5555', '1.234', '0.555'],
            [PHP_ROUND_HALF_EVEN, '0.5555', '1.234', '0.556'],
            [PHP_ROUND_HALF_UP, '0.4445', '1.234', '0.445'],
            [PHP_ROUND_HALF_DOWN, '0.4445', '1.234', '0.444'],
            [PHP_ROUND_HALF_EVEN, '0.4445', '1.234', '0.444'],
            // Test shorter numbers.
            [PHP_ROUND_HALF_UP, '0.12', '1.234', '0.12'],
            [PHP_ROUND_HALF_UP, '0.9', '1.234', '0.9'],
            // Test to no decimal places.
            [PHP_ROUND_HALF_UP, '0.12', '1', '0'],
            [PHP_ROUND_HALF_UP, '1234', '1', '1234'],
            // Test engineering format.
            [PHP_ROUND_HALF_UP, '18e2', '3e15', '2e3'],
            [PHP_ROUND_HALF_UP, '18e5', '3e1', '2e6'],
            [PHP_ROUND_HALF_UP, '18e2', '3e-15', '2e3'],
            [PHP_ROUND_HALF_UP, '18e-5', '3e-15', '2e-4'],
        ];
    }

    /**
     * Tests that we can calculate the number of significant figures correctly.
     *
     * @param string $number The number to be checked.
     * @param int $expected The number of significant figures in the number.
     * @return void
     * @dataProvider dataCalcSF
     */
    public function testCalcSF(string $number, int $expected)
    {
        $engine = $this->getEngine();
        $this->assertEquals($expected, $engine->calc_sf($number));
    }

    /**
     * Data used to test that we calculate the number of significant figures correctly.
     *
     * @return array[]
     */
    public function dataCalcSF(): array
    {
        return [
            // Leading zeros should not be counted.
            ['012', 2],
            ['0.123', 3],
            ['0.0123', 3],
            // Trailing zeros should not be counted.
            ['12300', 3],
            ['1.00', 1],
            // Some normal numbers.
            ['123', 3],
            ['1.23', 3],
            ['1.0123', 5],
            ['120.0123', 7],
            // On to Scientific notation.
            ['3e45', 1],
            ['1.1e2', 2],
            ['0.11e2', 2],
            ['0.110e2', 2],
            ['15e-11', 2],
        ];
    }

    /**
     * Tests that we can calculate the number of decimal places correctly.
     *
     * @param string $number The number to be checked.
     * @param int $expected The number of decimal places in the number.
     * @return void
     * @dataProvider dataCalcDP
     */
    public function testCalcDP(string $number, int $expected)
    {
        $engine = $this->getEngine();
        $this->assertEquals($expected, $engine->calc_dp($number));
    }

    /**
     * Data for use in the decimal places calculation test.
     *
     * @return array[]
     */
    public function dataCalcDP(): array
    {
        return [
            ['1', 0],
            ['1.0', 1],
            ['1.1', 1],
            ['100.12345', 5],
        ];
    }
}
