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
 * Base class for Correction behaviour
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
abstract class Corrector
{
    protected $_mysqli;
    protected $_lang_strings;
    protected $_question;

    public function __construct($mysqli, $lang_strings, $question)
    {
        $this->_mysqli = $mysqli;
        $this->_lang_strings = $lang_strings;
        $this->_question = $question;
    }

    /**
     * Change the correct answer after the question has been locked. Update user marks in summative log table
     * @param integer $new_correct new correct answer
     * @param integer $paper_id
     */
    abstract public function execute($new_correct, $paper_id, &$changes, $paper_type);

    /**
     * Invalidate the cache for the given paper
     * @param  integer $paper_id ID of paper for which the cache should be invalidated
     */
    protected function invalidate_paper_cache($paper_id)
    {
        if ($paper_id !== -1) {
            $properties = new PaperProperties($this->_mysqli);
            $properties->set_property_id($paper_id);
            $properties->load();

            $properties->set_recache_marks(1);
            $properties->save();
        }
    }
}
