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

require_once $cfg_web_root . 'classes/options/option_enhancedcalc.class.php';

$str_decimals = mb_strtolower($string['decimals']);
$str_sigs = mb_strtolower($string['sigfigures']);
$vars = $question->get_variables();
$num_vars = count($vars);
$answers = $question->get_answers();
$num_answers = count($answers);
$decimals = array(0, 1, 2, 3, 4, 5, 6, 7, 8);
$decimal_opts = array('0 dp' => '0 ' . $str_decimals, '1 dp' => '1 ' . $string['decimal'], '2 dp' => '2 ' . $str_decimals, '3 dp' => '3 ' . $str_decimals, '4 dp' => '4 ' . $str_decimals, '5 dp' => '5 ' . $str_decimals);
$decimal_opts_zero = array('1 dp zero' => '1 ' . $string['decimal'] . ' ' . $string['withzeros'], '2 dp zero' => '2 ' . $str_decimals . ' ' . $string['withzeros'], '3 dp zero' => '3 ' . $str_decimals . ' ' . $string['withzeros'], '4 dp zero' => '4 ' . $str_decimals . ' ' . $string['withzeros'], '5 dp zero' => '5 ' . $str_decimals . ' ' . $string['withzeros']);
$sf_opts = array('1 sf' => '1 ' . $string['sigfigure'], '2 sf' => '2 ' . $str_sigs, '3 sf' => '3 ' . $str_sigs, '4 sf' => '4 ' . $str_sigs, '5 sf' => '5 ' . $str_sigs);
$labels = $question->get_variable_labels();
?>
                <table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
                    <tbody>
<?php require_once 'details_common.php' ?>
                    </tbody>
                </table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['variables'] ?></h2>
        </div>

        <table id="q-options" class="form" summary="Edit question variables">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th class="align-left auto"><?php echo $string['min'] ?></th>
              <th class="align-left auto"><?php echo $string['max'] ?></th>
              <th class="align-left auto"><?php echo $string['decimals'] ?></th>
              <th class="align-left auto"><?php echo $string['increment'] ?></th>
            </tr>
          </thead>
<?php
$index = 1;

$options = array_filter($question->options, function ($var) {
    return ($var->get_variable() != '');
});

foreach ($options as $variable) {
    include 'options/opt_enhancedcalc.php';
    $index++;
}

for ($index; $index <= count($labels); $index++) {
    $variable = new OptionENHANCEDCALC($mysqli, $userObject->get_user_ID(), $question, $index, $string, array());
    $variable->set_variable('$' . $labels[$index - 1]);
    include 'options/opt_enhancedcalc.php';
}

if ($question->get_locked() == '') {
    ?>
          <tbody class="add-option-holder">
            <tr>
              <th>&nbsp;</th>
              <td colspan="4">
                <input name="next-option" class="next-option" value="<?php echo $string['addoptions'] ?>" type="button">
              </td>
            </tr>
          </tbody>
    <?php
}
?>
        </table>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['answer'] ?></h2>
        </div>

        <table id="q-options" class="form" summary="Edit question formulae">
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th class="align-left auto"><?php echo $string['formula'] ?> <span class="note indent"><a href="#" class="help-link" rel="68"><img src="../../artwork/tooltip_icon.gif" class="help_tip" alt="i" title="<?php echo $string['tooltip_formula'] ?>"/></a>&nbsp;<a href="#" class="help-link" rel="68"><?php echo $string['suppfunctions'] ?></a></span></th>
              <th class="align-left auto"><?php echo $string['units'] ?></th>
            </tr>
          </thead>
<?php
$index = 1;
$all_ans = array_filter($question->options, function ($var) {
    return ($var->get_formula() != '');
});
foreach ($all_ans as $answer) {
    include 'options/ans_enhancedcalc.php';
    $index++;
}

for ($index = $num_answers + 1; $index <= $question->max_options; $index++) {
    $answer = new OptionENHANCEDCALC($mysqli, $userObject->get_user_ID(), $question, $index, $string, array());
    include 'options/ans_enhancedcalc.php';
}
?>
          <tbody class="add-option-holder">
            <tr>
              <td>&nbsp;</td>
              <td colspan="2" class="align-left">
                <input name="next-answer" class="next-option" value="<?php echo $string['addanswers'] ?>" type="button" data-target="answer">
              </td>
            </tr>
          </tbody>
          <tbody>
            <tr>
              <th class="spaced-top"><label for="show_units" style="padding:0"><?php echo $string['displayunits'] ?></label></th>
<?php
$sel_mod = ($question->get_show_units()) ? ' checked' : '';
$disabled_mod = ($question->get_locked() == '') ? '' : ' disabled';
?>
              <td class="spaced-top"><input type="checkbox" name="show_units" id="show_units"<?php echo $sel_mod . $disabled_mod ?>></td>
            </tr>
<?php
/*
Removed until we can reconcile how it may work with multiple formulae
?>
            <tr>
              <th class="spaced-top">
                <label for="marks_unit"><?php echo $string['unitmarking'] ?></label><br>
                <span class="note"><?php echo $string['ifincorrect'] ?></span>
              </th>
              <td class="spaced-top align-top" colspan="2">
                <select name="marks_unit" id="marks_unit">
<?php
echo ViewHelper::render_options($marks_unit, $question->get_marks_unit(), 3);
?>
                </select>
              </td>
            </tr>
<?php
*/
?>
          </tbody>
        </table>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['tolerance'] ?></h2>
        </div>
        <table id="q-options" class="form" summary="Edit question tolerances">
          <tbody>
            <tr>
              <th class="spaced-top"><img src="../../artwork/tooltip_icon.gif" class="help_tip" alt="Information" title="<?php echo $string['percenttolerance'] ?>" /> <?php echo $string['tolerance'] ?></th>
              <td class="spaced-top"><label for="tolerance_full" class="spaced-right"><strong><?php echo $string['tolerance_full'] ?></strong></label><input type="text" id="tolerance_full" name="tolerance_full" value="<?php echo $question->get_tolerance_full() ?>" /></td>
              <td class="spaced-top"><span class="marks-partial<?php echo $show_partial ?>"><label for="tolerance_partial" class="spaced-right"><strong><?php echo $string['tolerance_partial'] ?></strong></label><input type="text" id="tolerance_partial" name="tolerance_partial" value="<?php echo $question->get_tolerance_partial() ?>" /></span></td>
            </tr>
          </tbody>
        </table>

        <div class="form">
          <h2 class="midblue_header"><?php echo $string['precision'] ?></h2>
        </div>
        <table class="form" summary="Edit question precision">
          <tbody>
            <tr>
              <th><?php echo $string['enforceto'] ?></th>
              <td>
                <select name="answer_precision" id="answer_precision">
                  <option value=""><?php echo $string['notenforced'] ?></option>
                  <optgroup label="<?php echo $string['decimals'] ?>">
<?php
echo ViewHelper::render_options($decimal_opts, $question->get_answer_precision(), 4);
?>
                  </optgroup>
                  <optgroup label="<?php echo $string['decimals'] . ' ' . $string['withzeros'] ?>">
<?php
echo ViewHelper::render_options($decimal_opts_zero, $question->get_answer_precision(), 4);
?>
                  </optgroup>
                  <optgroup label="<?php echo $string['sigfigures'] ?>">
<?php
echo ViewHelper::render_options($sf_opts, $question->get_answer_precision(), 4);
?>
                  </optgroup>
                </select>
              </td>
            </tr>
          </tbody>
        </table>

