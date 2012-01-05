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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

$root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root . 'config/config.inc.php';
require '../include/media.inc';
require '../include/errors.inc';
require '../include/sct_review.inc';
require '../config/start.inc';
  
check_var('paperID', 'GET', true, false);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>SCT Review</title>
<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/jquery.validate.min.js"></script>
<script language="JavaScript">
  $(document).ready(function(){
    $("#myform").validate();
  });
</script>
<style type="text/css">
body {background-color:white;color:black;padding:0px;margin:0px;border:0px;font-family:Arial,sans-serif;font-size:90%}
li {margin-left:15px;margin-right:15px;font-size:100%}
select,input{font-family:$font,sans-serif;font-size:100%}
table {font-size:100%}
.error {color:red}
</style>
</head>

<body>
<?php
  // Output the top logo banner.
  echo $top_table_html;
  echo '<tr><td><div style="margin-left:0px;font-size:180%;color:white;font-weight:bold">' . getPaperTitle($paperID, $mysqli) . '</div></td>';
  echo $logo_html;
?>

  <form id="myform" name="myform" action="sct_review.php" method="post">
  <br />
  
  <blockquote>
  <table cellpadding="2" cellspacing="0" border="0" style="padding:10px; border: 1px solid #C0C000; background-color:#FFFFC0; width:100%; font-size:100%">
  <col width="80"><col>
  <tr><td colspan="2">Please enter your details below.</td></tr>
  <tr><td><strong>Name</strong></td><td><input type="text" name="reviewer_name" id="reviewer_name" size="50" class="required" /></td></tr>
  <tr><td><strong>Email</strong></td><td><input type="text" name="reviewer_email" id="reviewer_email" size="50" class="required" /></td></tr>
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr><td colspan="2" style="text-align:center"><input type="submit" name="loginsubmit" value="OK" style="width:100px" /></td></tr>
  </table>
  </blockquote>
  <input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
  </form>

</body>
</html>
