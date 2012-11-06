<?php
$string['forgottenpassword'] = 'Forgotten Password';
$string['emailaddress'] = 'Email address';
$string['emailaddressinvalid'] = 'Please supply a valid email address';
$string['emailaddressnotfound'] = 'Email address not found';
$string['passwordreset'] = 'Password Reset';
$string['emailhtml'] = <<< EMAIL_HTML
<p>Dear %s %s,</p>
<p>We have received a request to reset your password on Rog&#333;. To complete the request click on the link below:</p>
<p><a href="https://%s/users/reset_password.php?token=%s">Reset password</a></p>
<p>If you did not ask for your password to be reset please <a href="mailto:%s">email us</a>. Your existing 
username and password will still allow you to log in to Rog&#333;.</p>

EMAIL_HTML;
$string['couldntsendemail'] = 'Could not send mail to <strong>%s</strong>';
$string['emailsentmsg'] = 'An email has been sent to <em>%s</em> containing a link that will allow you to reset your password. This link will remain valid for <strong>24 hours</strong>.';
$string['intromsg'] = 'Enter your email address and we will send you an email allowing you to reset your password.';
$string['send'] = 'Send';
?>