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

$num_options = count($question->options);
if($num_options > 0) {
  $option = reset($question->options);
  $option_id = $option->id;
} else {
  $option_id = -1;
}
?>
				<table id="q-details" class="form" summary="<?php echo $string['qeditsummary'] ?>">
					<tbody>
<?php require_once 'details_common.php' ?>
            <tr>
              <th><label for="start_year"><?php echo $string['startyear'] ?></label></th>
              <td>
                <input type="text" id="start_year" name="start_year" value="<?php echo $question->get_start_year() ?>" class="form-small spaced-right-large" />
                <label for="end_year" class="heavy"><?php echo $string['endyear'] ?></label>
                <input type="text" id="end_year" name="end_year" value="<?php echo $question->get_end_year() ?>" class="form-small" />
              </td>
            </tr>
            <tr>
              <th><label for="format"><?php echo $string['format'] ?></label></th>
              <td>
                <select id="format" name="format">
<?php 
echo ViewHelper::render_options($question->get_formats(), $question->get_format(), 3);
?>
                </select>
              </td>
            </tr>
            <tr>
              <th><?php echo $string['answer'] ?></th>
              <td>
                <label for="td_day" class="heavy hide">Day</label>
                <input type="text" id="td_day" name="td_day" value="<?php echo $question->get_td_day() ?>" class="clearinput form-tiny" title="dd" /> /
                <label for="td_month" class="heavy hide">Month</label>
                <input type="text" id="td_month" name="td_month" value="<?php echo $question->get_td_month() ?>" class="clearinput form-tiny" title="MM" /> /
                <label for="td_year" class="heavy hide">Year</label>
                <input type="text" id="td_year" name="td_year" value="<?php echo $question->get_td_year() ?>" class="clearinput form-tiny" title="yyyy" />&nbsp;&nbsp;
                <label for="td_hours" class="heavy hide">Hours</label>
                <input type="text" id="td_hours" name="td_hours" value="<?php echo $question->get_td_hours() ?>" class="clearinput form-tiny" title="hh" /> :
                <label for="td_minutes" class="heavy hide">Minutes</label>
                <input type="text" id="td_minutes" name="td_minutes" value="<?php echo $question->get_td_minutes() ?>" class="clearinput form-tiny" title="mm" />:
                <label for="td_seconds" class="heavy hide">Seconds</label>
                <input type="text" id="td_seconds" name="td_seconds" value="<?php echo $question->get_td_seconds() ?>" class="clearinput form-tiny" title="ss" />
                <input name="optionid1" value="<?php echo $option_id ?>" type="hidden" />
              </td>
            </tr>
            <tr>
              <th><span class="note"><?php echo $string['assessmentmsg'] ?></span></th>
              <td class="note align-centre">dd/MM/yyyy  hh:mm:ss</td>
            </tr>
					</tbody>
				</table>

<?php
require_once 'detail_parts/details_marking.php';
$label_correct = $string['feedback'] . '<br /><span class="note">' . $string['assessmentmsg'] . '</span>';
require_once 'detail_parts/details_general_feedback.php';
?>
