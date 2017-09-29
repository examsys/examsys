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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';

$migration_path = 'version5';

set_time_limit(0);

$old_version = $configObject->get('rogo_version');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>Rog&#333; <?php echo $configObject->get('rogo_version') ?> update creation</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
		<link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/updater.css"/>

    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="header">
    <tr>
      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
          <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />

          <div class="logo_lrg_txt">Rog&#333;</div>
          <div class="logo_small_txt">Update Creation Utility (<?php echo $migration_path; ?>)</div>
      </th>
      <th style="text-align:right; padding-right:10px"><img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" /></th>
    </tr>
  </table>
<?php
if (!isset($_POST['create'])) {
?>
<script>
  $(document).ready(function () {
    $("#create_form").validate();
  });
</script>
  <form id="create_form" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete="off">
      <p><?php echo $string['msg1']; ?></p>
      <div><label for="tag"><?php echo $string['tag']; ?></label> <input type="text" value="" id="tag" name="tag" class="required" minlength="2" /></div>

      <div class="submit"><input type="submit" name="create" class="ok" value="<?php echo $string['create'] ?>" /></div>
  </form>
   </body>
   </html>
  <?php

} else {
  $template = <<<TEMPLATE
<?php

// Your code here

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */
TEMPLATE;

  $tag = str_replace(' ', '_', strtolower(trim($_POST['tag'])));
  $datestamp = date('YmdHi');
  $filename = $migration_path . '/' . $datestamp . '_' . $tag . '.php';

  if (file_put_contents($filename, $template) !== false) {
    printf('<p>' . $string['success'] . '</p>', $filename);
    echo "<p><a href=\"version5.php\">{$string['runupdate']}</a></p>\n";
  } else {
    echo "<p>{$string['createerror']}</p>";
  }
}
?>
