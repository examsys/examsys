<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';

check_var('q_id', 'GET', true, false);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<title>Delete Question?</title>

<style>
body {margin:0px; background-color:#F1F5FB; font-family:Arial,sans-serif; font-size:90%; text-align:justifed}
</style>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" border="0" alt="Recycle Bin" /></td>

<td>
<?php
  $result = $mysqli->prepare("SELECT DISTINCT paper_title, paper FROM (papers, properties) WHERE papers.paper=properties.property_id AND properties.deleted IS NULL AND question=?");
  $result->bind_param('i', $_GET['q_id']);
  $result->execute();  
  $result->store_result();
  $result->bind_result($paper_title, $paper);

  if ($result->num_rows == 0) {
  ?>
<p>You are attempting to delete a question from the question bank.</p><p><strong>Please confirm that this is your intention.</strong></p>
<br />
<div style="text-align:right">
<form action="do_delete_q_original.php" method="post">
<input type="hidden" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input style="width:140px" type="submit" name="submit" value="Delete" />&nbsp;
<input style="width:80px" type="button" name="cancel" value=" Cancel " onclick="javascript:window.close();" />
</form>
</div>
    <?php
  } else {
    echo "<p>You cannot delete this question, it is used in the following papers:</p>\n<ul>\n";
    while ($row = $result->fetch()) {
      echo "<li>" . $paper_title . "</li>\n";
    }
    echo "</ul>\n";
  ?>
<p>Delete all pointers to this question before deleting the original.</p>
<div style="text-align:right">
<form action="do_delete_q_original.php" method="post">
<input type="hidden" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input type="button" name="cancel" value=" Cancel " onclick="javascript:window.close();" />
</form>
</div>
    <?php
  }
  $result->free_result();
  $result->close();
  $mysqli->close();
    ?>
</td></tr>
</table>

</body>
</html>