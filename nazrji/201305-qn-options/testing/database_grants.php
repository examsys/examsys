<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* This script takes the database structure, as modified by /updates/version4.php and
* checks it with a secondary database as created by /install/index.php. It assumes
* the same root username/password between the two databases.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
require './database_common.inc';

function get_table_name_from_grant($grant) {
  $parts = explode('.', $grant);
  $sub_parts = explode(' TO', $parts[1]);

  $table_name = str_replace('`', '', $sub_parts[0]);

  return $table_name;
}

function get_grants($db_name, $user, $db, $replace_name = '') {
  $details = array();

  $result = $db->query("SHOW GRANTS FOR '" . $db_name . $user . "'@'localhost'");

  if($result === false) {
    return array();
  }

  while ($row = $result->fetch_row()) {
    $grant = $row[0];
    if ($replace_name != '') {
      $grant = str_replace("`$db_name`", "`$replace_name`", $grant);  // Replace the database name.
      $grant = str_replace($db_name . '_', $replace_name . '_', $grant);  // Replace the database name.
    }
    $pos = strpos($grant, 'IDENTIFIED BY PASSWORD');
    if ($pos !== false) {
      $grant = substr($grant, 0, $pos + 22) . '...';
    }
    $table_name = get_table_name_from_grant($grant);

    $details[$table_name] = $grant;
  }
  $result->close();

  return $details;

}

function compare_permissions($db_master, $db_test, $masterdb, $testdb, $dbusername) {
  $master_details = get_grants($db_master, $dbusername, $masterdb);
  $master_grant_no = count($master_details);

  $test_details = get_grants($db_test, $dbusername, $testdb, $db_master);

  $rows = array_keys($master_details);

  echo "<table>\n";
  foreach ($rows as $row) {
    $error = false;
    if (!isset($test_details[$row])) {
      $error = true;
    } else {
      if ($master_details[$row] !== $test_details[$row]) {
        $error = true;
      }
    }

    if ($error) {
      echo "<tr><td class=\"err\">" . $master_details[$row] . "</td><td class=\"err\">";
      if (isset($test_details[$row])) {
        echo $test_details[$row];
      } else {
        echo '&nbsp;';
      }
      echo "</td></tr>\n";
    } else {
      echo "<tr><td>" . $master_details[$row] . "</td><td>" . $test_details[$row] . "</td></tr>\n";
    }
  }
  echo "</table>\n";
}
?>
<html>
<head>
<title>DB Permissions Test</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <style>
  body {font-size:80%; margin:5px;}
  h1 {font-size:140%}
  table {margin-left:40px; border-collapse:collapse}
  td {border:1px solid #C0C0C0; padding:2px}
  .grey {background-color:#EAEAEA; font-weight:bold}
  .nonexist {background-color:#FFC0C0}
  .dkred {background-color:#C00000; color:white; font-weight:bold}
  .err {color:red; font-weight:bold}
  </style>
</head>

<body>
<?php
if (isset($_POST['submit'])) {
  make_db_connections();

  $users = array('_auth', '_stu', '_staff', '_ext', '_sys', '_sct', '_inv');

  foreach ($users as $user) {
    echo "<h1>" . $_POST['master_dbname'] . $user . "</h1>\n";

    compare_permissions($_POST['master_dbname'], $_POST['test_dbname'], $master_mysqli, $test_mysqli, $user);
  }
} else {
  echo display_form();
}
?>
</body>
</html>
