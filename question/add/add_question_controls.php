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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../../include/staff_auth.inc';

echo "<form id='addquestions' name=\"theform\" method=\"post\" action=\"\" autocomplete=\"off\">\n";
echo '<div align="right">' . $string['screen'] . "&nbsp;<select name=\"screen\">\n";

$max_screen = $_GET['max_screen'];
for ($i = 1; $i <= $max_screen + 1; $i++) {
    if ($i == $max_screen) {
        echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
?>
</select>&nbsp;
<input type="hidden" name="questions_to_add" id="questions_to_add" value="" /><input type="submit" name="submit" value="<?php echo $string['addquestions'] ?>" /></div>
</form>
