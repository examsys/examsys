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
 * This class provides functionality needed by command line scripts.
 */
class cli_utils
{
    /**
     * Ask the user for a response.
     *
     * If expected responses are set then only the values in the passed array will be accepted.
     * The user will be reprompted until they enter one of the expected responses.
     *
     * @param string $message the message to be displayed to the user.
     * @param array $expected valid responses (optional)
     * @return string
     */
    public static function user_response($message, $expected = null)
    {
        self::prompt($message, ' ');
        if (is_array($expected) && count($expected) > 0) {
            $response = self::get_expected_response($expected);
        } else {
            $response = self::get_response();
        }
        return $response;
    }

    /**
     * Get a response from the command line.
     *
     * @return string
     */
    protected static function get_response()
    {
        $handle = fopen('php://stdin', 'r');
        $response = fgets($handle);
        return trim($response);
    }

    /**
     * Get the user to give a specific response.
     *
     * @param array $expected
     * @return string
     */
    protected static function get_expected_response(array $expected)
    {
        self::prompt('(' . implode(', ', $expected) . ')');
        $response = self::get_response();
        if (!in_array($response, $expected)) {
            // We will just keep prompting until the user gives us an expexted value!
            $response = self::get_expected_response($expected);
        }
        return $response;
    }

    /**
     * Display a message to the user.
     *
     * @param string $message The message to be displayed.
     * @param string $terminator The way the line should be ended
     */
    public static function prompt($message, $terminator = PHP_EOL)
    {
        echo "$message$terminator";
    }
}
