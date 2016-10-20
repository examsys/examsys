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
* Publish marks confirmation page
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @version 1.0
* @copyright Copyright (c) 2015 The University of Nottingham
*/

require '../include/staff_auth.inc';

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['publishmarks'] . " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#F0F0F0; margin:4px}
  </style>

  <script>
    function submitValues() {
      opener.document.theform.publishmarks.value = "yes";
      window.opener.document.theform.submit();
      window.close();
      return false;
    }
  </script>
</head>

<body>
<p><?php echo $string['publishmarkscheck'] ?></p>
<?php
 if ($configObject->get_setting('core', 'cfg_gradebook_enabled')) {
?>
<form name="templateform" onsubmit="return submitValues()" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">

<table cellpadding="2" cellspacing="0" border="0" width="100%" style="text-align:left">
<tr>
<td colspan="3" style="text-align: center">
<input type="submit" class="ok" name="submit" value="<?php echo $string['publishmarks'] ?>" /><input type="button" name="cancel" class="cancel" value="<?php echo $string['cancel'] ?>" onclick="window.close();" />
</td>
</tr>
</table>
</form>
<?php
 } else {
?>
<p><?php echo $string['cannotpublishmarks'] ?></p>
<?php
 }
?>
</body>
</html>
