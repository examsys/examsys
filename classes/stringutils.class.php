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
 * Utility class for useful string functions
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class StringUtils
{
    /**
     * Return true if string $string end with $test
     *
     * From mcrumley on StackOverflow
     * http://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string
     *
     * @static
     * @param $string
     * @param $test
     * @return bool
     */
    public static function ends_with($string, $test)
    {
        $strlen = mb_strlen($string);
        $testlen = mb_strlen($test);
        if ($testlen > $strlen) {
            return false;
        }
        return substr_compare($string, $test, -$testlen) === 0;
    }

    /**
     * Converts a number to a localised ordinal.
     *
     * Returns N/A if 0 or a non-numeric value is passed.
     *
     * @param mixed $number
     * @param string $language
     * @return string
     */
    public static function ordinal_suffix($number, $language = 'en')
    {
        if (!is_numeric($number) || $number === 0) {
            return 'N/A';
        }
        $formatter = new NumberFormatter($language, NumberFormatter::ORDINAL);
        return $formatter->format($number);
    }

    /**
     * Remove special characters and trim a string
     * @param  string $string Subject string
     * @return string         Cleaned version of the string
     */
    public static function clean_and_trim($string)
    {
        $searches = array('&nbsp;');
        $replaces = array(' ');

        $string = str_replace($searches, $replaces, $string);

        return trim($string);
    }

    /**
     * Convert characters in MS Word format to UTF8
     * @param  string $str Input string
     * @return string      Input string with Word characters converted to UTF8 equivalent
     */
    public static function wordToUtf8($str)
    {
        $wordChr = array(
        '&',
            '<',
            '>',
            "\\xe2\\x80\\xa6",        // ellipsis
            "\\xe2\\x80\\x93",        // long dash
            "\\xe2\\x80\\x94",        // long dash
            "\x96",                   // long dash
            "\x91",                    // single quote
            "\x92",                    // single quote
            "\\xe2\\x80\\x98",        // single quote opening
            "\\xe2\\x80\\x99",        // single quote closing
            "\\xe2\\x80\\x9c",        // double quote opening
            "\\xe2\\x80\\x9d",        // double quote closing
            "\\xe2\\x80\\xa2"        // dot used for bullet points
        );

        $utf8Chr = array(
            '&amp;',
            '&lt;',
            '&gt;',
            '...',
            '-',
            '-',
            '-',
            '\'',
            '\'',
            '\'',
            '\'',
            '"',
            '"',
            '*'
        );

        $str = str_replace($wordChr, $utf8Chr, $str);
        return $str;
    }

    public static function my_ucwords($s)
    {
        $s = preg_replace_callback("/(?:^|-|\pZ|')([\pL]+)/su", 'StringUtils::fixcase_callback', $s);
        return $s;
    }

    public static function mb_ucfirst($string, $encoding)
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    public static function fixcase_callback($word)
    {
        $word = $word[1];
        $word = mb_strtolower($word, 'UTF-8');

        if ($word == 'de') {
            return $word;
        }

        $word = StringUtils::mb_ucfirst($word, 'UTF-8');

        if (mb_substr($word, 1, 1, 'UTF-8') == "'") {
            if (mb_substr($word, 0, 1, 'UTF-8') == 'D') {
                $word = mb_strtolower($word, 'UTF-8');
            }
            $next = mb_substr($word, 2, 1, 'UTF-8');
            $next = mb_strtoupper($next, 'UTF-8');
            $word = mb_substr_replace($word, $next, 2, 1, 'UTF-8');
        }
        return $word;
    }

    public static function nice_duration($mins, $string)
    {
        if ($mins < 60) {
            $display_duration = $mins .  ' ' . $string['mins'];
        } else {
            $hours = floor($mins / 60);
            $remainder = $mins - ($hours * 60);


            if ($hours == 1) {
                $display_duration = $hours . ' ' . $string['hour'];
            } else {
                $display_duration = $hours . ' ' . $string['hours'];
            }
            if ($remainder > 0) {
                $display_duration .= ' ' . $remainder . ' ' . $string['mins'];
            }
        }

        return $display_duration;
    }
}
