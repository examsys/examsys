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

/**
 *
 * PHP based maths functions for calculation questions.
 *
 * @author Simon Atack, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class EnhancedCalc_phpEval
{
    protected $impliments_api_calc_version = 1;
    protected static $cnx = false;

    protected $config;
    protected $toStrDefined;
    protected $powDefined;

    public $error = false;
    public $error_msg = '';

    public function __construct($config)
    {
        $this->config = $config;
        $this->toStrDefined = false;
        $this->powDefined = false;
    }

    public function error_handling($context = null)
    {
        return error_handling($this);
    }

    public function connect()
    {
        return true;
    }

    public function setup_R()
    {
    }

    // from php manual http://php.net/round
    public function RoundSigDigs($number, $sigdigs)
    {
        $i = 0;
        if ($number === 0) {
            return $number;
        }
        $multiplier = 1;
        while ($number < 0.1) {
            $number *= 10;
            $multiplier /= 10;
            if ($i > 30) {
                return($number);
            } $i++;
        }
        $i = 0;
        while ($number >= 1) {
            $number /= 10;
            $multiplier *= 10;
            if ($i > 30) {
                return($number);
            } $i++;
        }
        return round($number, $sigdigs) * $multiplier;
    }

    public function calculate_correct_ans($vars, $formula)
    {
        $formula_vars_subed = EnhancedCalc::substitute_vars($vars, $formula);
        $correctanswer = eval('return (' . $formula_vars_subed . ');');

        return (string)$correctanswer;
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
                $correctanswer = round($correctanswer, $stundent_precision);
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

    public function distance_from_correct_answer($useranswer, $correctanswer)
    {
        if ($useranswer == '') {
            return 'ERROR';
        }

        $res = abs(round(abs($useranswer - $correctanswer) / $correctanswer * 100, 3));
        return $res;
    }

    public function calculate_tolerance_percent($correctanswer, $percentage)
    {
        $cmd[] = "$correctanswer * (" . $percentage . '/100)';
        $cmd[] = "$correctanswer * (1 + (" . $percentage . '/100))';
        $cmd[] = "$correctanswer * (1 - (" . $percentage . '/100))';

        $result[0] = $correctanswer * ($percentage / 100);
        $result[1] = $correctanswer * (1 + ($percentage / 100));
        $result[2] = $correctanswer * (1 - ($percentage / 100));

        $res['tolerance'] = $result[0];

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

        if (round($useranswer, $dp) == $useranswer) {
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

    public function calc_sf($num)
    {
        $epos = mb_strpos($num, 'e');
        if ($epos === false) {
            $epos = mb_strlen($num);
        }

        if (mb_strpos($num, '0.') === 0) {
            $epos = $epos - 2;
        } elseif (mb_strpos($num, '.') !== false) {
            $epos = $epos - 1;
        }

        return $epos;
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
        return round($num, $dp);
    }

    public function format_number_dp_strict_zeros($num, $dp)
    {
        $str = '%.' . $dp . 'f';
        return sprintf($str, $num);
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

    private function set_error($msg)
    {
        $this->error = true;
        $this->error_msg = $msg;
    }

    private function reset_error()
    {
        $this->error = false;
        $this->error_msg = '';
    }

    public function get_error()
    {
        return $this->error_msg;
    }
}
