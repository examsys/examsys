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

$string['forgottenpassword'] = 'Forgotten Password';
$string['emailaddress'] = 'Email address';
$string['emailaddressinvalid'] = 'Please supply a valid email address';
$string['emailaddressnotfound'] = 'Email address not found';
$string['emailaddressininstitutionaldomains'] = 'Your account is from an institution which is managed using a central authentication service. Please contact IT support to inquire about the password reset procedure.';
$string['passwordreset'] = 'Password Reset';
$string['emailhtml'] = <<< EMAIL_HTML
<p>Dear %s %s,</p>
<p>We have received a request to reset your password on ExamSys. To complete the request click on the link below:</p>
<p><a href="https://%s/users/reset_password.php?token=%s">Reset password</a></p>
<p>If you did not ask for your password to be reset please <a href="mailto:%s">email us</a>. Your existing 
username and password will still allow you to log in to ExamSys.</p>

EMAIL_HTML;
$string['couldntsendemail'] = 'Could not send mail to <strong>%s</strong>';
$string['emailsentmsg'] = 'An email has been sent to <em>%s</em> containing a link that will allow you to reset your password. This link will remain valid for <strong>24 hours</strong>.';
$string['intromsg'] = 'Enter your email address and we will send you an email allowing you to reset your password.';
$string['send'] = 'Send';
