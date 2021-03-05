<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

if (PHP_SAPI != 'cli') {
    die("Please run this script from the CLI!\n");
}

require_once '../../../../../../include/load_config.php';

cli_utils::prompt('Building combined css files');

function compress($buffer)
{
    /* remove comments */
    $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
    /* remove tabs, spaces, newlines, etc. */
    $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
  
    return $buffer;
}

/* your css files */
$output = "/*DO NOT MODIFY THIS FILE*/\n";
$files = array();
$files[] = 'edit.css';
$files[] = 'fonts.css';
$files[] = 'main.css';
$files[] = 'toolbar.css';
foreach ($files as $file) {
    cli_utils::prompt('Compressing ' . $file);
    $output .= compress(file_get_contents('../css/' . $file));
}

file_put_contents('../css/combined.css', $output);
cli_utils::prompt('Saved as css/combined.css');
