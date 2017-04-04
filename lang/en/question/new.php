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

require $configObject->get('cfg_web_root') . 'lang/' . $language . '/include/question_types.php';

$string['newquestion'] = 'New Question';
$string['area_desc'] = 'Allows student to specify an area over a background image as their answer.';
$string['enhancedcalc_desc'] = 'Numeric answer entry based on questions with random variables.';
$string['dichotomous_desc'] = 'Presentation of multiple true/false questions.';
$string['extmatch_desc'] = 'Presentation of multiple scenarios sharing a common set of answer options.';
$string['blank_desc'] = 'A paragraph of text with blanks inserted which the student completes.';
$string['info_desc'] = 'Not a question as such - this provides information to the student to assist them with the rest of the questions/paper.';
$string['matrix_desc'] = 'Match questions to answers in a matrix presentation.';
$string['hotspot_desc'] = 'Student has to click on the correct part of an image. Multiple parts can be presented in a single question.';
$string['labelling_desc'] = 'Student has to drag labels to the correct placeholders on top of a background image.';
$string['likert_desc'] = 'Psychometric scale for use on surveys.';
$string['mcq_desc'] = 'Pick one correct option from many.';
$string['mrq_desc'] = 'Pick several correct options from many.';
$string['keyword_based_desc'] = "This question is a container for a set of 'source' questions based on a specified keyword, one of which will be chosen at random when sat by a student";
$string['random_desc'] = "This question is a container for a set of 'source' questions, one of which will be chosen at random when sat by a student.";
$string['rank_desc'] = 'Rank a set of options in order.';
$string['sct_desc'] = 'Questions designed to assess clinical data interpretation skills.';
$string['textbox_desc'] = 'Textboxes capture free-text student responses. Can be used in surveys and assessments. Textbox answers on assessments require manual marking by academics.';
$string['true_false_desc'] = 'A single question which is answered True or False.';
?>