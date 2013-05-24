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

$show_notes = (isset($show_notes)) ? $show_notes : true;
?>
            <tr>
              <th><label for="theme"><?php echo $string['theme'] ?></label></th>
              <td>
                <input type="text" id="theme" name="theme" class="form-large<?php echo $dis_class ?>" value="<?php echo $question->get_theme() ?>"<?php echo $dis_readonly ?> />
              </td>
            </tr>
<?php 
if ($show_notes):
?>
            <tr>
              <th><label for="notes"><?php echo $string['notes'] ?></label><br /><span class="note"><?php echo $string['notesmsg'] ?></span></th>
              <td>
                <textarea id="notes" name="notes" cols="100" rows="2" class="form-large<?php echo $dis_class ?>"<?php echo $dis_readonly ?>><?php echo $question->get_notes() ?></textarea>
              </td>
            </tr>
<?php 
endif;
?>