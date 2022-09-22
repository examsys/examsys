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
 * The Question interface
 *
 * @author Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

interface questionInterface
{

    /*
    * Mark the users answer
    *
    *  This Must handle exclusions
    */
    public function calculate_user_mark();

    /*
    * caulate how many marks is this question worth form its options
    *
    *   This Must handle exclusions
    */
    public function calculate_question_mark();

    /*
    * caculate the Random Mark for this question
    *  This Must handle exclusions
    */
    public function calculate_random_mark();


    /*
    * Display the question
    *
    * The Paper handles question numbering this function renders the inner part of the question
    */
    public function render();

    /**
     * Is the question negatively marked?
     * @return boolean True if incorrect mark is less than 0
     */
    public function is_negative_marked();


    /*
    * NOTE:
    *   1) Saving the awnser to a question is perfomed by the paper not the question
    *   2) Editing questions is hanndled in the QuestionEdit Classes
    */
}
