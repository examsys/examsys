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

require '../include/sysadmin_auth.inc';
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>ExamSys Email Test</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
</head>
<body>
<?php
if (isset($_POST['submit'])) {
    $to = trim($_POST['email']);
    $subject = 'Test email from ExamSys ' . $configObject->get_setting('core', 'rogo_version');
    $message = 'This is a test email message sent at ' . date('F j, Y, g:i a') . ' from ' . gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME'])) . '.';
    $headers = 'From: ' . support::get_primary_email();

    mail($to, $subject, $message, $headers);
    echo 'Email sent, please check your inbox.';
} else {
    ?>
  <form name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
    <input type="text" name="email" style="width: 250px" placeholder="email address" required /> <input type="submit" name="submit" value="Send" class="ok" /> 
  </form>
    <?php
}
?>
</body>
</html>
