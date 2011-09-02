<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

$types = $question->get_sct_types();
$type = $types[$question->get_display_method() - 1];

$sct_type_js = '[';
foreach ($question->get_sct_types() as $typs_js) {
  $sct_type_js .= '[';
  $label_str = '';
  foreach ($typs_js as $label) {
    $label_str .= "'$label',";
  }
  $sct_type_js .= rtrim($label_str, ',');
  $sct_type_js .= '],';

}
$sct_type_js = rtrim($sct_type_js, ',') . ']';

?>

<script type="text/javascript">
var sct_types = <?php echo $sct_type_js ?>;
</script>

				<table id="q-details" class="form" summary="Edit question details">
					<tbody>
<?php
require_once 'detail_parts/details_theme_notes.php';
require_once 'detail_parts/details_media.php';
$mandatory_editor = true;
$label_editor = '<label for="scenario">Clinical Vignette</label>';
require_once 'detail_parts/details_scenario.php';

$mandatory_editor = false;
$field_editor = 'hypothesis';
$label_editor = '<label for="' . $field_editor . '" id="sct-hypothesis">' . $type[0] . '</label>';
$value_editor = $question->get_hypothesis();
require 'detail_parts/details_editor.php';

$field_editor = 'new_information';
$label_editor = '<label for="' . $field_editor . '">New Information</label>';
$value_editor = $question->get_new_information();
require 'detail_parts/details_editor.php';
?>
          </tbody>
        </table>

<?php
require_once 'detail_parts/details_marking.php';
require_once 'detail_parts/details_general_feedback.php';
?>

        <div class="form">
          <h2>Options</h2>
        </div>
        
        <table id="q-options" class="form" summary="Edit question options">
          <tbody>
            <tr>
              <th>Type</th>
              <td>
                <select id="display_method" name="display_method" class="sct-type">
<?php
echo ViewHelper::render_options($question->get_display_methods(), $question->get_display_method(), 3);
?>
                </select>
              </td>
              <th class="small">Experts</th>
            </tr>
<?php
$experts = range(0, 40);
$index = 1;
foreach ($question->options as $o_id => $option) {
  include 'options/opt_sct.php';
  $index++;
}
?>
          </tbody>
        </table>