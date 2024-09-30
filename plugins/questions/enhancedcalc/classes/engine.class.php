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

namespace plugins\questions\enhancedcalc;

use NumberFormatter;

require_once(dirname(__DIR__) . '/enhancedcalc.class.php');

/**
 * The base calculation engine class.
 *
 * PHP based maths functions for calculation questions.
 *
 * @author Simon Atack, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class Engine
{
    /** @var string The default locale to use for formatting numbers. */
    protected const DEFAULT_LOCALE = 'en_GB';

    protected $impliments_api_calc_version = 1;
    protected static $cnx = false;

    protected $config;
    protected $toStrDefined;
    protected $powDefined;

    /** @var int The default rounding mode of engine. */
    protected $default_rounding_mode = PHP_ROUND_HALF_UP;

    /** @var int The rounding mode the question is configured to use. */
    protected $rounding_mode;

    public $error = false;
    public $error_msg = '';

    public function __construct($config)
    {
        $this->config = $config;
        $this->toStrDefined = false;
        $this->powDefined = false;
    }

    /**
     * Gets the enhanced calculation engine ExamSys is configured to use.
     *
     * @return \plugins\questions\enhancedcalc\Engine
     */
    public static function getEngine(): Engine
    {
        $config = \Config::get_instance();
        $enhancedcalcType = $config->get_setting('core', 'cfg_calc_type');
        $enhancedcalcSettings = $config->get_setting('core', 'cfg_calc_settings');

        $name = '\\plugins\\questions\\enhancedcalc\\engine\\' . mb_strtolower($enhancedcalcType) . '\\Engine';
        if (empty($enhancedcalcType) or !class_exists($name)) {
            $name = '\\plugins\\questions\\enhancedcalc\\engine\\phpeval\\Engine';
        }

        return new $name($enhancedcalcSettings);
    }

    public function error_handling($context = null)
    {
        return error_handling($this);
    }

    /**
     * Gets the default rounding mode for the engine.
     *
     * @return int
     */
    public function getDefaultRoundingMode(): int
    {
        return $this->default_rounding_mode;
    }

    /**
     * Gets the locale that should be used to format numbers by the calculation engine.
     *
     * @return string
     */
    public function getLocale(): string
    {
        if (!isset($this->config['locale'])) {
            // No locale set.
            return self::DEFAULT_LOCALE;
        }

        if (empty(locale_parse($this->config['locale']))) {
            // An invalid local has been sent.
            return self::DEFAULT_LOCALE;
        }

        return $this->config['locale'];
    }

    /**
     * Sets the rounding mode that should be used by the question type.
     *
     * Should be one of:
     * - PHP_ROUND_HALF_UP
     * - PHP_ROUND_HALF_DOWN
     * - PHP_ROUND_HALF_EVEN
     *
     * Unfortunately the following rounding method cannot be supported as
     * it is not also part of the intl NumberFormat class:
     * - PHP_ROUND_HALF_ODD
     *
     * @param int $mode
     * @return void
     */
    public function setRoundingMode(int $mode)
    {
        $this->rounding_mode = $mode;
    }

    /**
     * Return a list of supported rounding methods.
     *
     * The key is the mode, the value the key for the language string.
     *
     * @return string[]
     */
    public function getSupportedRoundingModeForSelects(): array
    {
        return [
            PHP_ROUND_HALF_UP => 'roundhalfup',
            PHP_ROUND_HALF_DOWN => 'roundhalfdown',
            PHP_ROUND_HALF_EVEN => 'roundhalfeven',
        ];
    }

    /**
     * Gets the format in a way that is suitable for the number formatter.
     *
     * @return int
     */
    protected function getNumFormatterRoundingMode(): int
    {
        switch ($this->getRoundingMode()) {
            case PHP_ROUND_HALF_UP:
                return NumberFormatter::ROUND_HALFUP;
            case PHP_ROUND_HALF_DOWN:
                return NumberFormatter::ROUND_HALFDOWN;
            case PHP_ROUND_HALF_EVEN:
                return NumberFormatter::ROUND_HALFEVEN;
        }
        return NumberFormatter::ROUND_HALFUP;
    }

    /**
     * Gets the rounding mode that will be used by the engine.
     *
     * @return int
     */
    public function getRoundingMode(): int
    {
        return $this->rounding_mode ?? $this->default_rounding_mode;
    }

    public function connect()
    {
        return true;
    }

    public function setup_R()
    {
    }

    /**
     * Round the number to use a set number of significant figures.
     *
     * @param $number The number to be rounded.
     * @param $sigdigs The number of significant figures.
     * @return string
     */
    public function RoundSigDigs($number, $sigdigs): string
    {
        if ($this->is_engineering_format($number)) {
            $format = NumberFormatter::SCIENTIFIC;
        } else {
            $format = NumberFormatter::DECIMAL;
        }
        $formatter = new NumberFormatter(
            $this->getLocale(),
            $format
        );
        $formatter->setAttribute(NumberFormatter::MAX_SIGNIFICANT_DIGITS, $sigdigs);
        $formatter->setAttribute(NumberFormatter::MIN_SIGNIFICANT_DIGITS, $sigdigs);
        $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, $this->getNumFormatterRoundingMode());
        $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
        $formatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, '.');
        return strtolower($formatter->format($number));
    }

    /**
     * Calculates the correct answer for the question based.
     *
     * It uses the formula provided by the teacher, along with the values generated
     * for the student answering the question.
     *
     * @param array $vars An array where the key is the variable and the value is
     *                    the number used in the question, i.e. ['$A'=>15.2]
     * @param string $formula The formula used in the question.
     * @return string The answer returned by the calculation engine cast as a string.
     */
    public function calculate_correct_ans($vars, $formula)
    {
        $formula = $this->substituteMethodCalls($formula);
        $formula_vars_subed = \EnhancedCalc::substitute_vars($vars, $formula);

        try {
            $correctanswer = eval('return (' . $formula_vars_subed . ');');
        } catch (\DivisionByZeroError $e) {
            // Return the same answer we would get from RServe when a division by 0 happens.
            $correctanswer = 'Inf';
        } catch (\Error $e) {
            // Catch any parsing errors.
            $correctanswer = 'ERROR';
        }

        return (string)$correctanswer;
    }

    /**
     * This method allows us to fix formulas that have different names in different engines.
     *
     * @param string $formula
     * @return string
     */
    protected function substituteMethodCalls(string $formula): string
    {
        $formula = $this->substitutePi($formula);
        return $formula;
    }

    /**
     * Fixes the formula for pi to work in the engine.
     *
     * @param string $formula
     * @return string
     */
    protected function substitutePi(string $formula): string
    {
        $regexp = '#(^|[ *+/\-(])(pi(\(\))?)([ *,+/\-)]|$)#m';
        $replace = '$1' . $this->piFormula() . '$4';
        return preg_replace($regexp, $replace, $formula);
    }

    /**
     * The formula used for getting pi in this engine.
     *
     * @return string
     */
    protected function piFormula(): string
    {
        return 'pi()';
    }

    public function is_useranswer_correct($useranswer, $correctanswer, $round_to_stundent_precision)
    {
        $status = false;
        if ($useranswer == '') {
            return false;
        }

        if ($round_to_stundent_precision) {
            if ($this->is_engineering_format($useranswer)) {
                $stundent_precision = $this->calc_sf($useranswer);
                $correctanswer = $this->RoundSigDigs($correctanswer, $stundent_precision);
            } else {
                $stundent_precision = $this->calc_dp($useranswer);
                $correctanswer = round($correctanswer, $stundent_precision, $this->getRoundingMode());
            }
        }

        if ($correctanswer == $useranswer) {
            $status = true;
        }

        if ($status === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Calculates the distance of the user's answer from the correct answer.
     *
     * Where the correct answer is not 0 this will be expressed as a percentage distance.
     *
     * Where the correct answer is 0 it will return an absolute value.
     *
     * @param float|int $useranswer The answer the user gave.
     * @param float|int $correctanswer The correct answer for the question.
     * @return float|int|string
     */
    public function distance_from_correct_answer($useranswer, $correctanswer)
    {
        if ($useranswer == '') {
            return 'ERROR';
        }

        if ($correctanswer == 0) {
            // If the correct answer is 0 then we cannot calculate a percentage, so we will use an absolute value.
            $res = abs(round(abs($useranswer), 3, $this->getRoundingMode()));
        } else {
            // When the correct answer is not 0 calculate the distance as a percentage.
            $res = abs(round(abs($useranswer - $correctanswer) / $correctanswer * 100, 3, $this->getRoundingMode()));
        }
        return $res;
    }

    /**
     * Calculates minimum and maximum value a user answer can be considered correct
     *
     *  This method is used when the calculation question is configured to use a
     *  percentage difference from the correct mark.
     *
     * This does mean that the amount of difference will be greater
     * when the correct answer is larger.
     *
     * @param int|float $correctanswer The correct answer to the question
     * @param int $percentage The percentage that the answer can be off by and still considered correct
     * @return array
     */
    public function calculate_tolerance_percent($correctanswer, $percentage)
    {
        $result[0] = $correctanswer * ($percentage / 100);
        $result[1] = $correctanswer * (1 + ($percentage / 100));
        $result[2] = $correctanswer * (1 - ($percentage / 100));

        $res['tolerance'] = abs($result[0]);

        //
        // Make sure the min and max are correct tolerances on negative numbers causes problems
        //
        if ($result[1] > $result[2]) {
            $res['tolerance_ans'] = $result[1];
            $res['tolerance_ansneg'] = $result[2];
        } else {
            $res['tolerance_ans'] = $result[2];
            $res['tolerance_ansneg'] = $result[1];
        }
        return $res;
    }

    /**
     * Calculates minimum and maximum value a user answer can be considered correct
     *
     * This method is used when the calculation question is configured to use an
     * absolute difference from the correct mark.
     *
     * @param int|float $correctanswer The calculated correct answer
     * @param int|float $value The absolute difference the user is allowed to be away from the correct answer
     * @return array
     */
    public function calculate_tolerance_absolute($correctanswer, $value)
    {
        $result[0] = $correctanswer + $value;
        $result[1] = $correctanswer - $value;

        $res['tolerance'] = $value;
        $res['tolerance_ans'] = $result[0];
        $res['tolerance_ansneg'] = $result[1];

        return $res;
    }

    public function is_useranswer_within_tolerance($useranswer, $min, $max)
    {
        $status = false;
        if ($useranswer == '') {
            return false;
        }

        if ($useranswer <= $max and $useranswer >= $min) {
            $status = true;
        }

        if ($status === true) {
            //correct
            return true;
        } else {
            return false;
        }
    }

    public function is_useranswer_within_significant_figures($useranswer, $sf)
    {

        if ($useranswer == '') {
            return false;
        }
        if ($this->RoundSigDigs($useranswer, $sf) == $useranswer) {
            //correct
            return true;
        } else {
            return false;
        }
    }

    public function is_useranswer_correct_decimal_places($useranswer, $dp)
    {
        if ($useranswer == '') {
            return false;
        }

        if (round($useranswer, $dp, $this->getRoundingMode()) == $useranswer) {
            return true;
        } else {
            return false;
        }
    }

    public function is_useranswer_correct_decimal_places_strictzeros($useranswer, $dp)
    {
        if ($useranswer == '') {
            return false;
        }

        $status = $this->is_useranswer_correct_decimal_places($useranswer, $dp);

        if (!$status) {
            return false;
        }

        $dps = $this->calc_dp($useranswer);

        if ($dps == $dp) {
            return true;
        } else {
            return false;
        }
    }

    public function calc_dp($num)
    {
        $dotpos = mb_strpos($num, '.');
        if ($dotpos === false) {
            return 0;
        }

        $epos = mb_strpos($num, 'e');
        if ($epos !== false) {
            $end = $epos;
        } else {
            $end = mb_strlen($num);
        }

        return $end - ($dotpos + 1);
    }

    /**
     * Calculate the number of significant figures the number is using.
     *
     * @param string $num
     * @return int
     */
    public function calc_sf($num)
    {
        $epos = mb_strpos($num, 'e');
        if ($epos !== false) {
            // Remove the exponential.
            $num = mb_substr($num, 0, $epos);
        }

        // Leading and trailing zeros are not significant.
        $num = trim($num, '0');

        // If we now start with a decimal place we need to trim off the remaining zeros.
        if (mb_strpos($num, '.') === 0) {
            $num = mb_substr($num, 1);
            $num = trim($num, '0');
        }

        $count = mb_strlen($num);

        if (mb_strpos($num, '.') !== false) {
            // Take one for a decimal place.
            $count = $count - 1;
        }

        return $count;
    }

    public function is_engineering_format($num)
    {
        $epos = mb_stripos($num, 'e');
        if ($epos !== false) {
            return true;
        }
        return false;
    }

    public function format_number_dp($num, $dp)
    {
        return round($num, $dp, $this->getRoundingMode());
    }

    public function format_number_dp_strict_zeros($num, $dp)
    {
        $str = '%.' . $dp . 'f';
        return sprintf($str, $this->format_number_dp($num, $dp));
    }

    public function format_number_sf($num, $sf)
    {
        return $this->RoundSigDigs($num, $sf);
    }

    public function format_number_to_precision_of_other_number($roundme, $likethisone)
    {
        if ($this->is_engineering_format($likethisone)) {
            $precision = $this->calc_sf($likethisone);
            return $this->format_number_sf($roundme, $precision);
        } else {
            $precision = $this->calc_dp($likethisone);
            return $this->format_number_dp($roundme, $precision);
        }
    }

    protected function set_error($msg)
    {
        $this->error = true;
        $this->error_msg = $msg;
    }

    protected function reset_error()
    {
        $this->error = false;
        $this->error_msg = '';
    }

    public function get_error()
    {
        return $this->error_msg;
    }
}
