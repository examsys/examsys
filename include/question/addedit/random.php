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
?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
            <tr>
              <th><span class="mandatory">*</span> <label for="leadin"><?php echo $string['description'] ?></label></th>
              <td>
                <input type="text" id="leadin" name="leadin" value="<?php echo $question->get_leadin() ?>" class="form-med" />
              </td>
            </tr>
            <tr>
              <th><span class="mandatory">*</span> <label for="option_text1"><?php echo $string['questions'] ?></label></th>
              <td>
                <div id="qlist-holder" class="select-group">
                  <ul id="questionlist" class="radio-list clearfix">
<?php
$i = 1;
$j = 0;
$randomids = random_utils::get_random_qids_for_question($question->id, $mysqli);
if (count($randomids) > 0) {
  foreach ($question->options as $option) {
    $qid = $randomids[$j];
    $j++;
    $option_text = ltrim(strip_tags(QuestionUtils::get_leadin($qid, $mysqli)));
    if (strlen($option_text) > 200) {
      $option_text = wordwrap($option_text, 200);
      $option_text = substr($option_text, 0, strpos($option_text, "\n")) . '&hellip;';
    }
?>
                    <li><label for="option_text<?php echo $i ?>" class="fullwidth"><input id="option_text<?php echo $i ?>" name="option_text<?php echo $i ?>" value="<?php echo $qid ?>" type="checkbox" checked="checked" class="random-q" /> <?php echo $option_text ?></label><input name="optionid<?php echo $i ?>" value="<?php echo $option->id ?>" type="hidden" /></li>
<?php
  $i++;
  }
}
?>
                  </ul>
                </div>
              </td>
            </tr>
            <tr>
              <th>&nbsp;</th>
              <td>
                <input id="questioncheck" name="questioncheck" value="" type="hidden" />
                <input type="button" id="addquestion" name="addquestion" value="<?php echo $string['addquestions']; ?>" />
              </td>
            </tr>
<?php
?>
					</tbody>
				</table>
                