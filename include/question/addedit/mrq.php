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
$disabled = ($dis_class != '') ? ' disabled="disabled"' : '';
?>
                <table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
                    <tbody>
<?php require_once 'details_common.php' ?>
            <tr>
              <th><?php echo $string['presentation'] ?></th>
              <td>
<?php
$checked = ($question->get_display_method() == 'other') ? ' checked="checked"' : '';
?>
                <input type="checkbox" id="display_method" name="display_method" value="other"<?php echo $checked . $disabled; ?> /> <label for="display_method"><?php echo $string['includeother'] ?></label>
              </td>
            </tr>
            <tr>
              <th><label for="option_order"><?php echo $string['optionorder'] ?></label></th>
              <td>
                <select id="option_order" name="option_order"<?php echo $disabled ?>>
<?php
echo ViewHelper::render_options($question->get_option_orders(), $question->get_option_order(), 3);
?>
                </select>
              </td>
            </tr>
                    </tbody>
                </table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['options'] ?></h2>
        </div>

        <table id="q-options" class="form" summary="<?php echo $string['oeditsummary'] ?>">
          <thead>
            <tr>
              <th colspan="2">&nbsp;</th>
              <th class="small align-centre"><?php echo $string['answer'] ?></th>
            </tr>
          </thead>
<?php
$index = 1;
foreach ($question->options as $o_id => $option) {
    include 'options/opt_mrq.php';
    $index++;
}

for ($index = $num_options + 1; $index <= $question->max_options; $index++) {
    $option = OptionEdit::option_factory($mysqli, $userObject->get_user_ID(), $question, $index, $string);
    include 'options/opt_mrq.php';
}

if ($question->get_locked() == '') {
    ?>
          <tbody class="add-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="3">
                <input class="next-option" value="<?php echo $string['addoptions'] ?>" type="button" />
              </td>
            </tr>
          </tbody>
    <?php
}
?>
        </table>
        <input type="hidden" name="mcqconvert" id="mcqconvert" value="0" />
