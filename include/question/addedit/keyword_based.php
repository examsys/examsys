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

if (count($question->options) > 0) {
  $option = reset($question->options);
  $option_id = $option->id;
  $option_text = keyword_utils::get_keywordid_for_question($question->id, $mysqli);
} else {
  $option_id = -1;
  $option_text = '';
}
$q_teams = isset($q_teams) ? $q_teams : $question->get_teams();
$user_teams = $userObject->get_staff_modules();
$all_teams = array_unique(array_merge($q_teams, $user_teams));
$keywords = $question->get_user_keywords($all_teams);
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
              <th><span class="mandatory">*</span> <label for="option_text1"><?php echo $string['questionbasedon'] ?></label></th>
              <td>
<?php
if (count($keywords) > 0):
?>
                <select id="option_text1" name="option_text1" value="<?php echo $question->get_leadin() ?>">
                  <option value=""></option>
<?php
  $current_module = '';
  $first_module = true;
  foreach ($keywords as $keyword):
    if ($keyword[0] != $current_module):
      if (!$first_module):
?>
                  </optgroup>
<?php
      endif;
      
?>
                  <optgroup label="<?php echo $keyword[0] ?>">
<?php
      $current_module = $keyword[0];
      $first_module = false;
    endif;
    $sel = ($keyword[2] == $option_text) ? ' selected="selected"' : '';
?>
                    <option value="<?php echo $keyword[2] ?>"<?php echo $sel ?>><?php echo $keyword[1] ?></option>
<?php
  endforeach;
?>
                  </optgroup>
                </select>
<?php
else:
?>
                <span class="warning"><img src="../../artwork/small_yellow_warning_icon.gif" alt="!" height="12" width="11" /> <?php echo $string['keywordwarning'] ?></span>

<?php
endif;
?>
              </td>
            </tr>
<?php
?>
					</tbody>
				</table>
        <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
                