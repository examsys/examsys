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
 * Changes a summative exam into a formativ quiz. Part of summative exam scheduling system.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/admin_auth.inc';
require_once '../include/errors.php';

$paperid = check_var('paperID', 'GET', true, false, true);

if (!Paper_utils::paper_exists($paperid, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['convert'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
  body {background-color:#F1F5FB; font-size:80%; text-align:justifed}
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src='../js/convertformativeinit.min.js'></script>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/formative.png" width="48" height="48" alt="" /></td>

<td><p><?php echo $string['msg']; ?></p>
<br />
<div style="text-align: right">
<form id="convertformative" action="" method="post" autocomplete="off">
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="submit" name="submit" value="<?php echo $string['convert']; ?>" class="ok" />&nbsp;
<input type="button" name="cancel" value=" <?php echo $string['cancel']; ?> " onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>
