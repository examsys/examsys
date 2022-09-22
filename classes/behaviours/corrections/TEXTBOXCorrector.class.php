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
 * Class for Correction behaviour for Fill in the Blank questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

include_once 'Corrector.class.php';

class TEXTBOXCorrector extends Corrector
{
    /**
     * Change the correct answer after the question has been locked. Update user marks in summative log table
     * @param integer $new_correct new correct answer
     * @param integer $paper_id
     */
    public function execute($new_correct, $paper_id, &$changes, $paper_type)
    {
        $errors = array();
        if ($changes) {
            $first = reset($this->_question->options);
            $mark_correct = $first->get_marks_correct();

            try {
                if (!$this->_question->save()) {
                    $errors[] = $this->_lang_strings['datasaveerror'];
                } elseif ($paper_id !== -1) {
                    // Get metadata ID
                    $result = $this->_mysqli->prepare('SELECT id FROM log_metadata WHERE paperID = ?');
                    $result->bind_param('i', $paper_id);
                    $result->execute();
                    $result->bind_result($md_id);
                    $result->fetch();
                    $result->close();

                    // Set new value for totalpos in log{$paper_type} but don't change student marks
                    $updateLog = $this->_mysqli->prepare("UPDATE log{$paper_type} SET totalpos = ? WHERE q_id = ? AND metadataID = ?");
                    $updateLog->bind_param('iii', $mark_correct, $this->_question->id, $md_id);
                    $updateLog->execute();
                    $updateLog->close();
                }
            } catch (ValidationException $vex) {
                $errors[] = $vex->getMessage();
            }

            if (count($errors) == 0) {
                $this->invalidate_paper_cache($paper_id);
            }
        }

        return $errors;
    }
}
