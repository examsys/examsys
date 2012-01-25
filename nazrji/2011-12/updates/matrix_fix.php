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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
  //require '../include/sysadmin_auth.inc';
  require '../config/config.inc';
  require_once $cfg_web_root . 'classes/dbutils.class.php';
  set_time_limit(0);
  $mysqli = DBUtils::get_mysqli_link($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database, $cfg_db_charset, $dbclass);
  ob_start();
?>
<html>
<head>
<title>Matrix Question Fix</title>
</head>
<body>
<table border="1">
<?php

  // 07/09/2010
  $result = $mysqli->prepare("SELECT id, user_answer, option_order FROM log2, questions WHERE log2.q_id=questions.q_id AND q_type='matrix' AND q_option_order='random'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $old_answer, $option_order);
  while ($result->fetch()) {
    $tmp_answer_parts = explode('|',$old_answer);
    $tmp_order = explode(',',$option_order);
    
    if (count($tmp_order) > 1) {
      $new_answers = array(0=>'',1=>'',2=>'',3=>'',4=>'',5=>'',6=>'',7=>'',8=>'',9=>'',10=>'');
      
      $answered = false;
      $new_answers= array();
      for ($i=0; $i<count($tmp_answer_parts); $i++) {
        if (isset($tmp_order[$tmp_answer_parts[$i]-1]) and $tmp_order[$tmp_answer_parts[$i]-1] != '') {
          $new_answers[] = $tmp_order[$tmp_answer_parts[$i]-1] + 1;
        } else {
          $new_answers[] = 'u';
        }
      }
      
      $new_answer = implode('|',$new_answers);
      
      echo "<tr><td>$old_answer</td><td>$option_order</td><td>UPDATE log2 SET user_answer = '$new_answer' WHERE id=$id</td></tr>\n";
      
      $adjust = $mysqli->prepare("UPDATE log2 SET user_answer = '$new_answer' WHERE id=$id");
      $adjust->execute();
      $adjust->close();
      
    }
  }
  $result->close();
  
  //Close the database
  $mysqli->close();
  ob_end_flush();
  
?>
</table>
<p>Finished!</p>
</body>
</html>