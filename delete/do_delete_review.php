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
* Delete a standards setting review.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$std_setID = check_var('std_setID', 'POST', true, false, true);

$row_no = 0;
$result = $mysqli->prepare("SELECT id FROM std_set WHERE id = ?");
$result->bind_param('i', $std_setID);
$result->execute();  
$result->store_result();
$row_no = $result->num_rows;
$result->close();

if ($row_no == 0) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Delete main std_set record.
$result = $mysqli->prepare("DELETE FROM std_set WHERE id = ?");
$result->bind_param('i', $std_setID);
$result->execute();  
$result->close();

// Delete from sthe std_set_questions table.
$result = $mysqli->prepare("DELETE FROM std_set_questions WHERE std_setID = ?");
$result->bind_param('i', $std_setID);
$result->execute();  
$result->close();

// Delete from ebel table.
$result = $mysqli->prepare("DELETE FROM ebel WHERE std_setID = ?");
$result->bind_param('i', $std_setID);
$result->execute();
$result->close();

// Delete from hofstee table.
$result = $mysqli->prepare("DELETE FROM hofstee WHERE std_setID = ?");
$result->bind_param('i', $std_setID);
$result->execute();
$result->close();

// Clear any dangling properties
$old_marking = '2,' . $std_setID;
$result = $mysqli->prepare("UPDATE properties SET marking = '0' WHERE marking = ?");
$result->bind_param('s', $old_marking);
$result->execute();
$result->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Review Deleted</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.top.location.reload();
      self.close();
    });
  </script>
</head>

<body>

<p>Standards setting review successfully deleted.<p>

<div class="button_bar">
<form action="" method="get" autocomplete="off">
<input type="button" name="cancel" value="OK" class="ok" onclick="javascript:window.opener.top.location.reload(); window.close();" />
</form>
</div>

</body>
</html>