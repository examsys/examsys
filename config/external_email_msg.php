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

$url = 'https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path');
$themedirectory = rogo_directory::get_directory('theme');
$logo_path = $themedirectory->url($configObject->get_setting('core', 'misc_logo_email'));
$support_email = support::get_email();

$string['message0'] = "<div style=\"text-align:right\"><img src=\"\$logo_path\" width=\"160\" height=\"67\" /></div><p>Dear \$external_title \$external_surname,</p>\n<p>The online assessment <strong>\$paper_title</strong> is now available for you to log in and review. Please complete the review by the end of <strong>\$deadline</strong>. The exam will be delivered using our online assessment system ExamSys. To review the paper please log in at: <a href=\"\$rogo_url\">\$rogo_url</a></p>\n<p>Any problems with accessing the paper please do not hesitate to contact me. Technical support for ExamSys is also available from: <a href=\"mailto:\$support_email\">\$support_email</a></p>\n<p>Kind regards</p>\n<p>\$users_name</p>\n";
$string['message1'] = "<div style=\"text-align:right\"><img src=\"\$logo_path\" width=\"160\" height=\"67\" /></div><p>Dear \$external_title \$external_surname,</p>\n<p>We have noticed that you have not yet reviewed the online assessment <strong>\$paper_title</strong>. We would be very grateful if you could review this assessment by the end of <span style=\"font-weight:bold; color:#C00000\">\$deadline</span>. To review the paper please log in at: <a href=\"\$rogo_url\">\$rogo_url</a></p>\n<p>Any problems with accessing the paper please do not hesitate to contact me. Technical support for ExamSys is also available from: <a href=\"mailto:\$support_email\">\$support_email</a></p>\n<p>Kind regards</p>\n<p>\$users_name</p>\n";
$string['message2'] = "<div style=\"text-align:right\"><img src=\"\$logo_path\" width=\"160\" height=\"67\" /></div><p>Dear \$external_title \$external_surname,</p>\n<p>The \$cfg_company has finished reading your comments regarding <strong>\$paper_title</strong> and have finished any amendments. You may now log back into ExamSys to review our responses to your comments: <a href=\"\$rogo_url\">\$rogo_url</a></p>\n<p>Any problems with accessing the paper please do not hesitate to contact me. Technical support for ExamSys is also available from: <a href=\"mailto:\$support_email\">\$support_email</a></p>\n<p>Kind regards</p>\n<p>\$users_name</p>\n";
