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

if (isset($low_bandwidth) and $low_bandwidth == 1) {
    // Lowbandwidth
    ob_start('ob_gzhandler');   // enable compression
}
$top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
$themedirectory = rogo_directory::get_directory('theme');
$logo_path = $themedirectory->url($configObject->get_setting('core', 'misc_logo_main'));
$logo_html = '<td width="160"><img src="' . $logo_path . '" width="160" height="67" alt="Logo" /></td></tr></table>';
$bottom_html = '<table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#5590CF; min-height:29px">';
if ($original_paper_type == '2') {
    $fire = '<td id="fire_exit"></td>';
} else {
    $fire = '';
}
$bottom_html .= '<tr>' . $fire . '<td class="copyright">&#169; 2018, e-Assessment University</td><td style="width:14em;text-align:center">';
