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

  require '../include/staff_auth.inc';
  require '../include/errors.inc';
  
  check_var('paperID', 'POST', true, false);

  if (isset($_POST['questions'])) {
    // Look up and retire the questions
    $result = $mysqli->prepare("SELECT question FROM papers WHERE paper=?");
    $result->bind_param('i', $_POST['paperID']);
    $result->execute();
    $result->store_result();
    $result->bind_result($question_id);
    while ($result->fetch()) {
      $stmt = $mysqli->prepare("UPDATE questions SET status='Retired' WHERE q_id=?");
      $stmt->bind_param('i', $question_id);
      $stmt->execute();
      $stmt->close();
    }
    $result->close();   
  }
  
  // Retire the paper itself
  $result = $mysqli->prepare("UPDATE properties SET retired=NOW() WHERE property_id=?");
  $result->bind_param('i', $_POST['paperID']);
  $result->execute();  
  $result->close();

  $mysqli->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['paperretired'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <script language="JavaScript">
    function closeWindow() {
      window.opener.location.reload(true);
      window.close();
    }
  </script>
</head>

<body onload="closeWindow();" style="background-color:#F1F5FB; font-size:90%">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/formative_retired.png" width="48" height="48" border="0" alt="<?php echo $string['paperretired']; ?>" /></td>

<td><p><?php echo $string['msg']; ?><p>

<div style="text-align:center">
<form action="" method="get">
<input type="button" name="ok" value="  <?php echo $string['ok']; ?>  " onclick="window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>