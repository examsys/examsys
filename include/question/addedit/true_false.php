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
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

$num_options = count($question->options);
if ($num_options > 0) {
    $option = reset($question->options);
    $opt_id = $option->id;
} else {
    $opt_id = -1;
}
$labels = $question->get_tf_labels();
?>
                <table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
                    <tbody>
<?php
require_once 'details_common.php';
require_once 'detail_parts/details_presentation.php';
?>
                    </tbody>
                </table>

<?php
require_once 'detail_parts/details_marking.php';
?>

        <table id="q-options" class="form" summary="<?php echo $string['oeditsummary'] ?>">
<?php
if ($num_options == 0) {
    $option = OptionEdit::option_factory($mysqli, $userObject->get_user_ID(), $question, 1, $string);
}
include 'options/opt_true_false.php';
?>
        </table>

<?php
$label_correct = $string['fbcorrect'] . '<br /><span class="note warning-severe">' . $string['fbcorrectmsg'] . '</span>';
$show_incorrect = true;
require_once 'detail_parts/details_general_feedback.php';
?>

