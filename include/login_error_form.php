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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

global $string;
$notice = UserNotices::get_instance();
$mysqli =& $this->db;
$configObject =& $this->configObj;
$message = $string['authenticationfailed'] . "</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['usernamecasesensitive'] . "</li>\n";
if (isset($displayerrformobj->li)) {
    foreach ($displayerrformobj->li as $li) {
        $message .= '<li>' . $li . '</li>';
    }
}

$message .= '<li>' . $string['pressf5'] . '</li>';
$message .= '</ul>';
$notice->display_notice($string['accessdenied'], $message, '/artwork/access_denied.png', '#C00000', $title_color = 'black', $output_header = true, $output_footer = true);
if (isset($displayerrformobj->messages)) {
    foreach ($displayerrformobj->messages as $message1) {
        $message .= '<p>' . $message1 . '</p>';
    }
}


echo <<<END
</body>
</html>

END;
