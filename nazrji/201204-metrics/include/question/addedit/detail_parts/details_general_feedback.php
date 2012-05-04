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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

$label_correct = (isset($label_correct)) ? $label_correct : $string['generalfeedback'];
$label_incorrect = (isset($label_incorrect)) ? $label_incorrect : $string['fbincorrect'] . '<br /><span class="note">' . $string['fbincorrectmsg'] . '</span>';
$show_incorrect = (isset($show_incorrect)) ? $show_incorrect : false;
$feedback_rows = (isset($feedback_rows)) ? $feedback_rows : 3;
?>
        <table id="q-feedback" class="form" summary="Edit question feedback">
          <tbody>
            <tr>
              <th><label for="correct_fback"><?php echo $label_correct ?></label></th>
              <td>
                <textarea id="correct_fback" name="correct_fback" cols="100" rows="<?php echo $feedback_rows ?>" class="form-large<?php echo $dis_class ?>"<?php echo $dis_readonly ?>><?php echo $question->get_correct_fback() ?></textarea>
              </td>
            </tr>
<?php
if ($show_incorrect):
?>
            <tr>
              <th><label for="incorrect_fback"><?php echo $label_incorrect ?></label></th>
              <td>
                <textarea id="incorrect_fback" name="incorrect_fback" cols="100" rows="<?php echo $feedback_rows ?>" class="form-large<?php echo $dis_class ?>"<?php echo $dis_readonly ?>><?php echo $question->get_incorrect_fback() ?></textarea>
              </td>
            </tr>
<?php
endif;
?>
          </tbody>
        </table>
