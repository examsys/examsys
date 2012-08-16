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

$marks_positive = range(1, 20);
$marks_negative = array(0, -0.25, -0.5, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10);
$marks_partial = array_merge(range(0, 1, 0.1), range(2, 5));
$mark_range = range(100, 50);
$error_range = range(0, 50);
?>
<script type="text/javascript" src="../../js/jquery.flash_q.js"></script>
<script type="text/javascript">
//<![CDATA[
$(function () {
  sendTextToAS3('<?php echo $language ?>', 'option_correct', '2', <?php echo "'../../media/" . $media['filename'] . "', '" . $correct . "'" ?>);
});
//]]>
</script>

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
?>
                <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="<?php echo $plugin_width ?>" height="<?php echo $plugin_height ?>" id="externalinterfaceoption_correct_1" align="top">
                  <param name="movie" value="<?php echo $cfg_root_path ?>/question/edit/area.swf" />
                  <param name="quality" value="high" />
                  <param name="bgcolor" value="#ffffff" />
                  <param name="play" value="true" />
                  <param name="loop" value="true" />
                  <param name="wmode" value="opaque" />
                  <param name="scale" value="showall" />
                  <param name="menu" value="true" />
                  <param name="devicefont" value="false" />
                  <param name="salign" value="top" />
                  <param name="allowScriptAccess" value="sameDomain" />
                  <!--<param name="FlashVars" value="imgtt=Isles" />-->
                  <!--[if !IE]>-->
                  <object type="application/x-shockwave-flash" data="<?php echo $cfg_root_path ?>/question/edit/area.swf" id="externalinterfaceoption_correct_2" width="<?php echo $plugin_width ?>" height="<?php echo $plugin_height ?>">
                    <param name="movie" value="<?php echo $cfg_root_path ?>/question/edit/area.swf" />
                    <param name="quality" value="high" />
                    <param name="bgcolor" value="#ffffff" />
                    <param name="play" value="true" />
                    <param name="loop" value="true" />
                    <param name="wmode" value="opaque" />
                    <param name="scale" value="showall" />
                    <param name="menu" value="true" />
                    <param name="devicefont" value="false" />
                    <param name="salign" value="top" />
                    <param name="allowScriptAccess" value="sameDomain" />
                    <!--<![endif]-->
                    <a href="http://www.adobe.com/go/getflash"> <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>
                    <!--[if !IE]>-->
                  </object>
                  <!--<![endif]-->
                </object>
  <?php
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
$allow_neg = $question->allow_negative_marks($module);
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
              <strong><?php echo $string['tolerance_full'] ?></strong>
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
              <strong><?php echo $string['tolerance_partial'] ?></strong>
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
              <strong><?php echo $string['marksincorrect'] ?></strong>
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
        

