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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

$mediadirectory = rogo_directory::get_directory('media');
$media = $question->get_media();
$plugin_height = $media['height'] + 24;
$plugin_width = ($media['width'] < 235) ? 235 : $media['width'] + 2;
$correct_full = ($question->get_correct_full() == '') ? 95 : $question->get_correct_full();
$error_full = ($question->get_error_full() == '') ? 5 : $question->get_error_full();
$correct_partial = ($question->get_correct_partial() == '') ? 90 : $question->get_correct_partial();
$error_partial = ($question->get_error_partial() == '') ? 10 : $question->get_error_partial();
if (count($question->options) > 0) {
  $option = reset($question->options);
  $correct = $option->get_correct();
  $option_id = $option->id;
  $mark_correct = $option->get_marks_correct();
  $mark_incorrect = $option->get_marks_incorrect();
  $mark_partial = $option->get_marks_partial();
  $mark_partial = ($mark_partial != '') ? number_format($mark_partial, 1) : 0;
} else {
  $correct = '';
  $option_id = -1;
  $mark_correct = 1;
  $mark_incorrect = 0;
  $mark_partial = 0.5;
}

$configObject = Config::get_instance();
$marks_positive = $configObject->get_setting('core', 'paper_marks_postive');
$marks_negative = $configObject->get_setting('core', 'paper_marks_negative');
$marks_partial = $configObject->get_setting('core', 'paper_marks_partial');
$mark_range = range(100, 50);
$error_range = range(0, 50);
?>

				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_scenario.php';
require_once 'detail_parts/details_leadin.php';
?>
					</tbody>
				</table>
        
        <table class="form" summary="Hotspot flash movie">
          <tbody>
            <tr>
              <th class="align-top"><span class="mandatory">*</span> <?php echo $string['image'] ?></th>
              <td>
<?php
if ($media['filename'] != '' and !$show_correction_intermediate):
  $tmp_correct = str_replace("'", "\'", trim($correct));
  $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
  $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);

  //<!-- ======================== HTML5 part ================= -->
  echo '<canvas id="canvas1" width="' . $plugin_width . '" height="' . ($plugin_height+3) . '"></canvas>' . "\n";
  echo '<br /><div style="width:100%;text-align: left;" id="canvasbox"></div>' . "\n";
	echo '<script>' . "\n";
	echo 'setUpQuestion(1, "option_correct", "' . $language . '", "' . $media['filename'] . '", "' . $correct . '", "", "", "#FFC0C0", "area", "2");' . "\n";
  echo '</script>' . "\n";
  //<!-- ==================================================== -->
endif;
?>                
                <input name="optionid1" value="<?php echo $option_id; ?>" type="hidden" />
                <input type="hidden" id="option_correct" name="option_correct" value="<?php echo $correct ?>" />
                <input type="hidden" id="q_media" name="q_media" value="<?php echo $media['filename'] ?>" />
                <input type="hidden" id="q_media_width" name="q_media_width" value="<?php echo $media['width'] ?>" />
                <input type="hidden" id="q_media_height" name="q_media_height" value="<?php echo $media['height'] ?>" />
              </td>
            </tr>
          </tbody>
        </table>

<?php
$allow_neg = $question->allow_negative_marks();
$allow_change_method = ($question->allow_change_marking_method() and $dis_class == '') ? '' : ' disabled="disabled"';
?>
        <table id="q-marking" class="form" summary="<?php echo $string['qeditsummary'] ?>">
          <tbody>
          <tr>
            <th><label for="score_method" class="heavy"><?php echo $string['markingmethod'] ?></label></th>
            <td>

              <select id="score_method" name="score_method" class="spaced-right-large"<?php echo $allow_change_method ?>>
                <?php
                echo ViewHelper::render_options($question->get_score_methods(), $question->get_score_method('int'), 3, true);
                ?>
              </select>
            </td>
          </tr>
          </tbody>
        </table>

        <table id="q-marking-detail" class="form" summary="<?php echo $string['qeditsummary'] ?>">
          <tbody>
          <tr>
            <th class="align-left">&nbsp;</th>
            <td class="align-left heavy"><?php echo $string['answercorrect'] ?></td>
            <td class="align-left heavy"><?php echo ucfirst($string['error']) ?></td>
            <td class="align-left heavy"><?php echo $string['marks'] ?></td>
          </tr>
          <tr>
            <th>
              <?php echo $string['tolerance_full'] ?>
            </th>
            <td class="form-small">
              <select id="correct_full" name="correct_full">
                <?php
                echo ViewHelper::render_options($mark_range, $correct_full, 3);
                ?>
              </select> %
            </td>
            <td class="form-small">
              <select id="error_full" name="error_full">
                <?php
                echo ViewHelper::render_options($error_range, $error_full, 3);
                ?>
              </select> %
            </td>
            <td>
              <select id="option_marks_correct" name="option_marks_correct" class="spaced-right-large">
                <?php
                echo ViewHelper::render_options($marks_positive, $mark_correct, 3);
                ?>
              </select>
            </td>
          </tr>
          <?php
          if ($question->allow_partial_marks()):
            $show_partial = ($question->get_score_method() == $string['allowpartial']) ? '' : ' hide';
          ?>
          <tr class="marks-partial<?php echo $show_partial ?>">
            <th>
              <?php echo $string['tolerance_partial'] ?>
            </th>
            <td>
              <select id="correct_partial" name="correct_partial">
                <?php
                echo ViewHelper::render_options($mark_range, $correct_partial, 3);
                ?>
              </select> %
            </td>
            <td>
              <select id="error_partial" name="error_partial">
                <?php
                // TODO: value from question
                echo ViewHelper::render_options($error_range, $error_partial, 3);
                ?>
              </select> %
            </td>
            <td>
              <select id="option_marks_partial" name="option_marks_partial" class="spaced-right-large">
                <?php
                echo ViewHelper::render_options($marks_partial, $mark_partial, 3);
                ?>
              </select>
            </td>
          </tr>
          <?php
          endif;
          ?>
          <?php
          if ($allow_neg or $mark_incorrect != 0):
          ?>
          <tr>
            <th>
              <?php echo $string['marksincorrect'] ?>
            </th>
            <td colspan="2">&nbsp;</td>
            <td>
              <select id="option_marks_incorrect" name="option_marks_incorrect">
                <?php
                echo ViewHelper::render_options($marks_negative, $mark_incorrect, 3);
                ?>
              </select>
            </td>
          </tr>
          <?php
          endif;
          ?>
          </tbody>
        </table>

<?php
if (!$allow_neg and $mark_incorrect == 0):
?>
<input type="hidden" id="option_marks_incorrect" name="option_marks_incorrect" value="<?php echo $mark_incorrect ?>" />
<?php
endif;
require_once 'detail_parts/details_general_feedback.php';
?>
        

