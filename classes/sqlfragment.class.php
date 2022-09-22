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
 * Class that stores a fragment of SQL.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class SQLFragment
{
    /** A blob parameter, will be sent to the database in packets. */
    public const TYPE_BLOB = 'b';

    /** Floating point number parameter. */
    public const TYPE_DOUBLE = 'd';

    /** Integer parameter.  */
    public const TYPE_INTEGER = 'i';

    /** String parameter. */
    public const TYPE_STRING = 's';

    /** @var string The SQL fragment. */
    public $sql = '';

    /**  @var array The parameters to be used in placeholders in the SQL fragment. */
    public $params = [];

    /**
     * The variable types for the parameters.
     *
     * It requires one character per parameter in the query.
     *
     * i = integer
     * s = string
     * d = double
     * b = blob
     *
     * @var string
     */
    public $param_types = '';

    /**
     * Add a value to be used in an SQL fragment placeholder.
     *
     * @param $parameter The value for a placeholder in the SQL fragment.
     * @param string $type One of the TYPE_ constants
     */
    public function addParameter($parameter, string $type): void
    {
        $this->params[] = $parameter;
        $this->param_types .= $type;
    }

    /**
     * Combines several SQLFragments into one.
     *
     * Invalid fragments will be discarded.
     *
     * @param string $glue The value used to glue the SLQ fragments together.
     * @param \SQLFragment ...$fragments The SQL fragments to be combined.
     * @return \SQLFragment
     */
    public static function combine(string $glue, SQLFragment ...$fragments): SQLFragment
    {
        $combined = new SQLFragment();
        $sql = [];

        foreach ($fragments as $fragment) {
            if (!$fragment->valid()) {
                // We should ignore a fragment if it is not valid.
                continue;
            }
            $sql[] = $fragment->sql;
            $combined->params = array_merge($combined->params, $fragment->params);
            $combined->param_types .= $fragment->param_types;
        }

        $combined->sql = implode($glue, $sql);
        return $combined;
    }

    /**
     * Tests if the fragment is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        // There must be an SQL fragment.
        $has_sql = !empty($this->sql);

        // The parameters must all be typed.
        $valid_args = mb_strlen($this->param_types) === count($this->params);

        // All of the placeholder in the SQL fragment must have a parameter.
        $placeholders = mb_substr_count($this->sql, '?') === count($this->params);

        return $has_sql && $valid_args && $placeholders;
    }
}
