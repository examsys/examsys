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
 * Path related utility functions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

function get_root_path()
{
    $path_parts = pathinfo(dirname(__FILE__));
    return normalise_path($path_parts['dirname']);
}

/**
 * Gets the relative path from the currently executing script to the root directory.
 *
 * @return string
 */
function get_relative_path_to_root()
{
    // First find the part of the file path that extend the root path.
    $diff = str_replace(get_root_path(), '', get_file_path());
    $trimmed = trim($diff, '/');

    // Build a relative path to the root directory.
    if (empty($trimmed)) {
        $relative = './';
    } else {
        $pathdepth = count(explode('/', $trimmed));

        $relative = '';
        for ($i = 0; $i < $pathdepth; $i++) {
            $relative .= '../';
        }
    }

    return $relative;
}

/**
 * Gets the path to the file that the user executed in this call.
 *
 * @return string
 */
function get_file_path()
{
    // Ensure we have the full path, even if used in a CLI script.
    $filepath = realpath($_SERVER['SCRIPT_FILENAME']);
    $path_parts = pathinfo($filepath);
    return normalise_path($path_parts['dirname']);
}

function normalise_path($path)
{
    return str_replace('\\', '/', $path);
}
