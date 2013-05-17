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

function get_table_structure($db_name, $table_name, $db) {
  $details = array();

  $result = $db->prepare("DESCRIBE $db_name.$table_name");
  $result->execute();
  $result->store_result();
  $result->bind_result($field, $type, $null, $key, $default, $extra);
  while ($result->fetch()) {
    $details[] = array($field, $type, $null, $key, $default, $extra);
  }
  $result->close();
  
  return $details;
}

function compare_tables($db_master, $db_test, $table_name, $masterdb, $testdb) {
  $master_details = get_table_structure($db_master, $table_name, $masterdb);
  $master_field_no = count($master_details);
    
  $test_details = get_table_structure($db_test, $table_name, $testdb);
  $test_field_no = count($test_details);
  
  if ($test_field_no == 0) {
    $class = 'dkred';
    echo "<table class=\"nonexist\" cellpadding=\"1\" cellspacing=\"0\" border=\"1\">";
  } else {
    $class = 'grey';
    echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"1\">";
  }
  echo "<tr><td class=\"$class\">Field</td><td class=\"$class\">Type</td><td class=\"$class\">Null</td><td class=\"$class\">Key</td><td class=\"$class\">Default</td><td class=\"$class\">Extra</td></tr>\n";
  
  for ($i=0; $i<$master_field_no; $i++) {
    echo "<tr>";
    $master_line = $master_details[$i];
    if (isset($test_details[$i])) {
      $test_line = $test_details[$i];
      for ($col=0; $col<6; $col++) {
        $text = format_text($master_line[$col]);
        if ($master_line[$col] === $test_line[$col]) {
          echo "<td>$text</td>";
        } else {
          echo "<td class=\"err\">$text</td>";
        }
      }
    } else {
      for ($col=0; $col<6; $col++) {
        $text = format_text($master_line[$col]);
        echo "<td class=\"err\">$text</td>";
      }
    }
    echo "</tr>";
  }
  
  // Display extra fields in test table.
  if (count($test_details) > count($master_details)) {
    for ($i=$master_field_no; $i<$test_field_no; $i++) {
      $test_line = $test_details[$i];
      for ($col=0; $col<6; $col++) {
        $text = format_text($test_line[$col]);
        echo "<td class=\"err\">$text</td>";
      }
    }
  }
  
  echo "</table>\n<br />\n";
}

function get_indexes($db_name, $table_name, $db) {
  $details = array();

  $result = $db->prepare("SHOW INDEXES IN $db_name.$table_name");
  $result->execute();
  $result->store_result();
  $result->bind_result($table, $non_unique, $key_name, $seq_in_index, $column_name, $collation, $cardinality, $sub_part, $packed, $null, $index_type, $comment, $index_comment);
  while ($result->fetch()) {
    $details[] = array($table, $non_unique, $key_name, $seq_in_index, $column_name, $collation, $cardinality, $sub_part, $packed, $null, $index_type, $comment, $index_comment);
  }
  $result->close();
  
  return $details;
}

function compare_indexes($db_master, $db_test, $table_name, $masterdb, $testdb) {
  $master_details = get_indexes($db_master, $table_name, $masterdb);
  $master_index_no = count($master_details);
    
  $test_details = get_indexes($db_test, $table_name, $testdb);
  $test_index_no = count($test_details);

  if ($test_index_no == 0) {
    $class = 'dkred';
    echo "<table class=\"nonexist\">";
  } else {
    $class = 'grey';
    echo "<table>";
  }
  echo "<tr><td class=\"$class\">Non_unique</td><td class=\"$class\">Key_name</td><td class=\"$class\">Column_name</td><td class=\"$class\">Index_type</td></tr>\n";

  $lines_to_check = array(1, 2, 4, 10);
  
  for ($i=0; $i<$master_index_no; $i++) {
    echo "<tr>";
    $master_line = $master_details[$i];
    if (isset($test_details[$i])) {
      $test_line = $test_details[$i];
      for ($col=0; $col<13; $col++) {
        if (in_array($col, $lines_to_check)) {
          $text = format_text($master_line[$col]);
          if ($master_line[$col] === $test_line[$col]) {
            echo "<td>$text</td>";
          } else {
            echo "<td class=\"err\">$text</td>";
          }
        }
      }
    } else {
      for ($col=0; $col<13; $col++) {
        if (in_array($col, $lines_to_check)) {
          $text = format_text($master_line[$col]);
          echo "<td class=\"err\">$text</td>";
        }
      }
    }
    echo "</tr>";
  }
  
  // Display extra fields in test table.
  if (count($test_details) > count($master_details)) {
    for ($i=$master_index_no; $i<$test_index_no; $i++) {
      $test_line = $test_details[$i];
      echo "<tr>";
      for ($col=0; $col<13; $col++) {
        if (in_array($col, $lines_to_check)) {
          $text = format_text($test_line[$col]);
          echo "<td class=\"err\">$text</td>";
        }
      }
      echo "</tr>\n";
    }
  }
  
  echo "</table>\n<br />\n";
}
?>
<html>
<head>
<title>DB Table Test</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <style>
  body {font-size:80%; margin:5px}
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

  $table_list = get_tables($_POST['master_dbname'], $master_mysqli);

  foreach ($table_list as $table) {
    echo "<h1>$table</h1>\n";
  
    compare_tables($_POST['master_dbname'], $_POST['test_dbname'], $table, $master_mysqli, $test_mysqli);
  }
} else {
  echo display_form();
}
?>
</body>
</html>
