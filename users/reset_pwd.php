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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  //require '../include/auth.inc';
?>
<html>
<title>Password Reset</title>
</head>

<body>
<?php

  $new_password = gen_password();
  
  $encrypt_password = encpw($_GET['username'],$new_password);

  $stmt = $mysqli->prepare("UPDATE users SET password=? WHERE id=?");
  $stmt->bind_param('si', $encrypt_password, $_GET['userID']);
  $stmt->execute();
  $mysqli->close();
  
  echo "<p>Password reset to: <strong><tt>$new_password</tt></strong></p>\n";
?>

</body>
</html>
