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
  if (!defined('STDIN')) {
    exit;
  }
  require '../config/config.inc';
  set_time_limit(0);
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
  ob_start();

  function convert_year($old_year) {
    $new_year = 1;
    switch ($old_year) {
      case 'year1':
        $new_year = 1;
        break;
      case 'year2':
        $new_year = 2;
        break;
      case 'year3':
      case 'cp1':
        $new_year = 3;
        break;
      case 'year4':
      case 'cp2':
        $new_year = 4;
        break;
      case 'year5':
      case 'cp3':
      case 'f1':
      case 'graduate':
        $new_year = 5;
        break;
      default:
        $new_year = 1;
    }
    return $new_year;
  }
?>
<html>
<head>
<title>Database Updates</title>
</head>
<body>
<?php

  // 07/09/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='faculty'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  
  if (strpos($column_type,'enum') !== false) {
    $result = $mysqli->prepare("ALTER TABLE users CHANGE COLUMN faculty faculty varchar(80)");
    $result->execute();
    $result->close();
    echo "<div>ALTER TABLE users CHANGE COLUMN faculty faculty varchar(80)</div>\n";
    ob_flush();
    flush();
  }
  
  // 07/09/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='schools' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='faculty'");
  $result->execute();
  $result->bind_result($column_type);
  $result->fetch();
  $result->close();
  
  if (strpos($column_type,'enum') !== false) {
    $adjust = $mysqli->prepare("ALTER TABLE schools CHANGE COLUMN faculty faculty varchar(80)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE schools CHANGE COLUMN faculty faculty varchar(80)</div>\n";
    ob_flush();
    flush();
  }
  
  // 14/09/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='staff_help' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='roles'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE staff_help ADD COLUMN roles enum('SysAdmin','Admin','Staff')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE staff_help ADD COLUMN roles enum('SysAdmin','Admin','Staff')</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 14/10/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='paper_notes' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='note_id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE paper_notes (note_id int not null primary key auto_increment, note text, note_date datetime, paper_id smallint, note_authorID mediumint)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE paper_notes (note_id int not null primary key auto_increment, note text, note_date datetime, paper_id smallint, note_workstation varchar(15))</div>\n";
    ob_flush();
    flush();
  }   
   
  // 16/11/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log2' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='option_order'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE log0 ADD COLUMN option_order varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log0 ADD COLUMN option_order varchar(255)</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log1 ADD COLUMN option_order varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log1 ADD COLUMN option_order varchar(255)</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log2 ADD COLUMN option_order varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log2 ADD COLUMN option_order varchar(255)</div>\n";

    $adjust = $mysqli->prepare("ALTER TABLE log3 ADD COLUMN option_order varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log3 ADD COLUMN option_order varchar(255)</div>\n";
    ob_flush();
    flush();
  }   
   
  // 17/11/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='q_option_order'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE questions ADD COLUMN q_option_order ENUM('display order','alphabetic','random')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE questions ADD COLUMN q_option_order ENUM('display order','alphabetic','random')</div>\n";
    
    $adjust = $mysqli->prepare("UPDATE questions SET q_option_order = 'display order'");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE questions SET q_option_order = 'display_order'</div>\n";
    ob_flush();
    flush();
  }
  
  //25/11/2010 - Alter the storage mechanism for standards setting values for MRQ question type
  if ($ts_version < '3.8') {
    $result = $mysqli->prepare("SELECT id, rating, questionID, q_type FROM (standards_setting, questions) WHERE standards_setting.questionID=questions.q_id AND q_type='mrq'");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $rating, $questionID, $q_type);
    while ($result->fetch()) {
      if ($rating != '') {
        $new_rating = '';
        
        $rating_parts = explode(',',$rating);
      
        $result2 = $mysqli->prepare("SELECT correct FROM options WHERE o_id=$questionID ORDER BY id_num");
        $result2->execute();
        $result2->store_result();
        $result2->bind_result($correct);
        $i=1;
        $correct_part = 0;
        while ($result2->fetch()) {
          if ($i == 1) {
            if ($correct == 'y') {
              $new_rating = $rating_parts[$correct_part];
              $correct_part++;
            } else {
              $new_rating = '';
            }
          } else {
            if ($correct == 'y') {
              $new_rating .= ',' . $rating_parts[$correct_part];
              $correct_part++;
            } else {
              $new_rating .= ',';
            }
          }
          $i++;
        }      
        $result2->close();
        //echo "$questionID = $rating = $new_rating<br />";

        $adjust = $mysqli->prepare("UPDATE standards_setting SET rating = '$new_rating' WHERE id=$id");
        $adjust->execute();
        $adjust->close();
      }
    }
    $result->close();
    echo "<div>Completed updating standards setting values for MRQ question type</div>\n";
    ob_flush();
    flush();
  }
  
  // 26/11/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='q_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if (strpos($column_type,'keyword_based') === false) {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','timedate','info','extmatch','random','sct','keyword_based')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE questions CHANGE COLUMN q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','timedate','info','extmatch','random','sct','keyword_based')</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 06/12/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='staff_help' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='deleted'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE staff_help ADD COLUMN deleted datetime");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE staff_help ADD COLUMN deleted datetime</div>\n";

    $adjust = $mysqli->prepare("ALTER TABLE student_help ADD COLUMN deleted datetime");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE student_help ADD COLUMN deleted datetime</div>\n";
    ob_flush();
    flush();
  }  
  $result->close();

  //20/12/2010 - Alter special needs
  //$result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='special_needs'");
  //$result->execute();
  //$result->store_result();
  //$result->bind_result($column_type);
  //$result->fetch();
  //if ($result->num_rows() == 0) {
    //$adjust = $mysqli->prepare("ALTER TABLE users ADD COLUMN special_needs tinyint DEFAULT 0");
    //$adjust->execute();
    //$adjust->close();
    //echo "<div>ALTER TABLE users ADD COLUMN special_needs tinyint DEFAULT 0</div>\n";
  //if ($ts_version < '3.9') {
  //  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  //  $result->execute();
  //  $result->store_result();
  //  $result->bind_result($tmp_userID);
  //  while ($result->fetch()) {
  //    $adjust = $mysqli->prepare("UPDATE users SET special_needs=1 WHERE id=$tmp_userID");
  //    $adjust->execute();
  //    $adjust->close();
  //  }
  // $result->close();
  //}
  
  //21/12/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='ip_addresses' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='low_bandwidth'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE ip_addresses ADD COLUMN low_bandwidth tinyint default 0");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE ip_addresses ADD COLUMN low_bandwidth tinyint default 0</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  //21/12/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='latex_needed'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE properties ADD COLUMN latex_needed tinyint DEFAULT 0");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE properties ADD COLUMN latex_needed tinyint DEFAULT 0</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 21/12/2010
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log_late' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='option_order'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE log_late ADD COLUMN option_order varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log_late ADD COLUMN option_order varchar(255)</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 10/01/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='sms'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN sms varchar(255)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN sms varchar(255)</div>\n";
    
    $adjust = $mysqli->prepare("UPDATE modules SET sms='http://webexports-uat.nottingham.ac.uk/touchstone.ashx?campus=uk' WHERE moduleid != 'TRAIN' AND moduleid != 'SYSTEM' AND moduleid != 'A10Mon'");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE modules SET sms='http://webexports-uat.nottingham.ac.uk/touchstone.ashx?campus=uk' WHERE moduleid != 'TRAIN' AND moduleid != 'SYSTEM' AND moduleid != 'A10Mon'</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 10/01/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sms_imports' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE sms_imports (id int not null primary key auto_increment, updated date, moduleid char(25), enrolments int, enrolement_details text, deletions int, deletion_details text)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE sms_imports (id int not null primary key auto_increment, updated date, moduleid char(25), enrolments int, enrolement_details text, deletions int, deletion_details text)</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 11/01/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='initials'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == 'char(5)') {
    $adjust = $mysqli->prepare("ALTER TABLE users CHANGE COLUMN initials initials char(10)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE users CHANGE COLUMN initials initials char(10)</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 15/01/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='password'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE properties ADD column password char(20)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE properties ADD column password char(20)</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 19/01/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='paper_ownerID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == 'mediumint(8) unsigned') {
    $adjust = $mysqli->prepare("ALTER TABLE properties CHANGE COLUMN paper_ownerID paper_ownerID mediumint");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE properties CHANGE COLUMN paper_ownerID paper_ownerID mediumint</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 19/01/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='ownerID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == 'mediumint(8) unsigned') {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN ownerID ownerID mediumint");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE questions CHANGE COLUMN ownerID ownerID mediumint</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 28/01/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sct_reviews' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE sct_reviews (id int not null primary key auto_increment, reviewer_name text, reviewer_email text, paperID smallint(5) unsigned, q_id int(4), answer tinyint, reason text)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE sct_reviews (id int not null primary key auto_increment, reviewer_name text, reviewer_email text, paperID smallint(5) unsigned, q_id int(4), answer tinyint, reason text)</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 14/02/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='student_modules' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='auto_update'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE student_modules ADD column auto_update tinyint");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE student_modules ADD column auto_update tinyint</div>\n";


    $adjust = $mysqli->prepare("UPDATE student_modules SET auto_update=1");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE student_modules SET auto_update=1</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 18/02/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log0' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('year1','year2','year3','year4','year5','year6','cp1','cp2','cp3','f1','graduate')") {
    $update_logs = array(0,1,2,3,5);
    foreach($update_logs as $log_type) {
      $adjust = $mysqli->prepare("ALTER TABLE log$log_type ADD COLUMN yearofstudy tinyint");
      $adjust->execute();
      $adjust->close();
      echo "<div>ALTER TABLE log$log_type ADD COLUMN yearofstudy tinyint</div>\n";

      //$result2 = $mysqli->prepare("SELECT id, year FROM log$log_type");
      //$result2->execute();
      //$result2->store_result();
      //$result2->bind_result($tmp_id, $year);
      //while ($result2->fetch()) {
      //  $adjust = $mysqli->prepare("UPDATE log$log_type SET yearofstudy=" . convert_year($year) . " WHERE id=$tmp_id");
      //  $adjust->execute();
      //  $adjust->close();
      //}
      //$result2->close();
      $convert_years = array('year1'=>1,'year2'=>2,'year3'=>3,'year4'=>4,'year5'=>5,'year6'=>6,'cp1'=>3,'cp2'=>4,'cp3'=>5,'f1'=>5,'graduate'=>6);
      foreach ($convert_years as $old_year=>$new_year) {
        $adjust = $mysqli->prepare("UPDATE log$log_type SET yearofstudy=$new_year WHERE year='$old_year'");
        $adjust->execute();
        $adjust->close();
      }
    }
    ob_flush();
    flush();
  }
  $result->close();

  // 18/02/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == "enum('year1','year2','year3','year4','year5','year6','cp1','cp2','cp3','f1','graduate')") {
    $adjust = $mysqli->prepare("ALTER TABLE users ADD COLUMN yearofstudy tinyint");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE users ADD COLUMN yearofstudy tinyint</div>\n";

    $result2 = $mysqli->prepare("SELECT id, year FROM users");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($tmp_id, $year);
    while ($result2->fetch()) {
      $adjust = $mysqli->prepare("UPDATE users SET yearofstudy=" . convert_year($year) . " WHERE id=$tmp_id");
      $adjust->execute();
      $adjust->close();
    }
    $result2->close();
    
    $convert_years = array('year1'=>1,'year2'=>2,'year3'=>3,'year4'=>4,'year5'=>5,'year6'=>6,'cp1'=>3,'cp2'=>4,'cp3'=>5,'f1'=>5,'graduate'=>6);
    foreach ($convert_years as $old_year=>$new_year) {
      $adjust = $mysqli->prepare("UPDATE users SET yearofstudy=$new_year WHERE year='$old_year'");
      $adjust->execute();
      $adjust->close();
    }

    $adjust = $mysqli->prepare("ALTER TABLE users DROP COLUMN year");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE users DROP COLUMN year</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 18/02/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log_metadata' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE log_metadata (id int not null primary key auto_increment, userID mediumint, paperID smallint, started datetime, ipaddress char(15), student_grade char(25), year tinyint, attempt tinyint)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE log_metadata (id int not null primary key auto_increment, userID mediumint, paperID smallint, started datetime, ipaddress char(15), student_grade char(25), year tinyint, attempt tinyint)</div>\n";
    
    $update_logs = array(0,1,2,3,5);
    foreach ($update_logs as $log_type) {
      echo "<div>Reading from Log$log_type and inserting into log_metadata</div>";
      if ($log_type == 5) {
        $result2 = $mysqli->prepare("SELECT userID, q_paper, started, '' AS ipaddress, student_grade, yearofstudy FROM log$log_type GROUP BY userID, q_paper, started");
      } else {
        $result2 = $mysqli->prepare("SELECT userID, q_paper, started, ipaddress, student_grade, yearofstudy FROM log$log_type GROUP BY userID, q_paper, started");
      }
      //$result2 = $mysqli->prepare("SELECT DISTINCT userID, q_paper, started, ipaddress, student_grade, yearofstudy FROM log$log_type");
      $result2->execute();
      $result2->store_result();
      $result2->bind_result($userID, $q_paper, $started, $ipaddress, $student_grade, $yearofstudy);
      while ($result2->fetch()) {
        if ($yearofstudy == '') $yearofstudy = 1;
        //echo "<div>INSERT INTO log_metadata VALUES(NULL, $userID, $q_paper, '$started', '$ipaddress', '$student_grade', $yearofstudy, 1)</div>\n";
        $adjust = $mysqli->prepare("INSERT INTO log_metadata VALUES(NULL, $userID, $q_paper, '$started', '$ipaddress', '$student_grade', $yearofstudy, 1)");
        $adjust->execute();
        $adjust->close();
      }
      $result2->close();
    }
    echo "<div>ALTER TABLE log_metadata ADD INDEX(userID,paperID,started)</div>\n";
    $adjust = $mysqli->prepare("ALTER TABLE log_metadata ADD INDEX(userID,paperID,started)");
    $adjust->execute();
    $adjust->close();
    ob_flush();
    flush();
  }
  $result->close();
  
  // 21/02/2011 remove unised cols from log(0,1,2,3)
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log0' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='ipaddress'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($column_type == 'varchar(15)') {
    $update_logs = array(0,1,2,3,5);
    foreach ($update_logs as $log_type) {
      if ($log_type != 5) {
        echo "<div>ALTER TABLE log$log_type DROP COLUMN ipaddress</div>\n";
        $adjust = $mysqli->prepare("ALTER TABLE log$log_type DROP COLUMN ipaddress");
        $adjust->execute();
        $adjust->close();
        echo "<div>Dropped ipaddress</div>\n";
      }
      
      echo "<div>ALTER TABLE log$log_type DROP COLUMN student_grade</div>\n";
      $adjust = $mysqli->prepare("ALTER TABLE log$log_type DROP COLUMN student_grade");
      $adjust->execute();
      $adjust->close();
      echo "<div>Droped student_grade</div>\n";
      
      
      echo "<div>ALTER TABLE log$log_type DROP COLUMN yearofstudy</div>\n";
      $adjust = $mysqli->prepare("ALTER TABLE log$log_type DROP COLUMN yearofstudy");
      $adjust->execute();
      $adjust->close();
      echo "<div>Dropped yearofstudy</div>\n";
      
      echo "<div>ALTER TABLE log$log_type DROP COLUMN year</div>\n";
      $adjust = $mysqli->prepare("ALTER TABLE log$log_type DROP COLUMN year");
      $adjust->execute();
      $adjust->close();
      echo "<div>Dropped year</div>\n";
    }
    ob_flush();
    flush();
  }

  // 09/03/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sms_imports' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='import_type'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sms_imports ADD COLUMN import_type ENUM('manual','SATURN UK','SATURN Malaysia','SATURN China','ARC')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sms_imports ADD COLUMN import_type ENUM('manual','SATURN UK','SATURN Malaysia','SATURN China','ARC')</div>\n";

    $adjust = $mysqli->prepare("UPDATE sms_imports SET import_type='SATURN UK'");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE sms_imports SET import_type='SATURN UK'</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 15/03/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='hide_if_unanswered'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE properties ADD COLUMN hide_if_unanswered ENUM('0','1') AFTER display_feedback");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE properties ADD COLUMN hide_if_unanswered ENUM('0','1') AFTER display_feedback</div>\n";
    
    $adjust = $mysqli->prepare("UPDATE properties SET hide_if_unanswered = '0'");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE properties SET hide_if_unanswered = '0'</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  if ($ts_version == '4.0') {
    // Do a conversion for the Image Hotspot question type.
    $result2 = $mysqli->prepare("SELECT q_id, leadin FROM questions WHERE q_type = 'hotspot'");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($q_id, $leadin);
    while ($result2->fetch()) {
      $adjust = $mysqli->prepare("UPDATE options SET correct=CONCAT(\"" . strip_tags($leadin) . "\",'~16711680~',REPLACE(correct,';','~')) WHERE o_id=$q_id");
      $adjust->execute();
      $adjust->close();

      // Clear the leadin in questions to save space.
      $adjust = $mysqli->prepare("UPDATE questions SET leadin=CONCAT('A) ', \"" . strip_tags($leadin) . "\") WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    echo "<div>Converted Image Hotspot data format.</div>\n";
    ob_flush();
    flush();
  }
  

  // 28/03/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='keywords_question' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='q_id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE keywords_question (q_id int, keywordID int)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE keywords_question (q_id int, keywordID int)</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE keywords_question ADD INDEX (q_id)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE keywords_question ADD INDEX (q_id)</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("RENAME TABLE keywords TO keywords_user");
    $adjust->execute();
    $adjust->close();
    echo "<div>RENAME TABLE keywords TO keywords_user</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE keywords_user ADD COLUMN keyword_type enum('personal','team')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE keywords_user ADD COLUMN keyword_type enum('personal','team')</div>\n";
    ob_flush();
    flush();
  
    $adjust = $mysqli->prepare("UPDATE keywords_user SET keyword_type = 'personal'");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE keywords_user SET keyword_type = 'personal'</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN id int not null primary key auto_increment FIRST");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN id int not null primary key auto_increment FIRST</div>\n";
    ob_flush();
    flush();

    $result2 = $mysqli->prepare("SELECT keywords, q_id, ownerID FROM questions WHERE keywords != ''");
    $result2->execute();
    $result2->store_result();
    $result2->bind_result($keywords, $q_id, $ownerID);
    while ($result2->fetch()) {
      $parts = explode(';',$keywords);
      foreach ($parts as $part) {
        $part = trim($part);
        if ($part != '') {
          $keywordID = 0;
          
          $result3 = $mysqli->prepare("SELECT id FROM keywords_user WHERE keyword=? AND userID=?");
          $result3->bind_param('si', $part, $ownerID);
          $result3->execute();
          $result3->store_result();
          $result3->bind_result($keywordID);
          if ($result3->num_rows == 0) {
            $result4 = $mysqli->prepare("SELECT id FROM keywords_user WHERE keyword=?");
            $result4->bind_param('s', $part);
            $result4->execute();
            $result4->store_result();
            $result4->bind_result($keywordID);
            if ($result4->num_rows == 0) {
              echo "<div>Error: cannot find $part for $ownerID</div>\n";
            } else {
              $result4->fetch();
            }
            $result4->close();
          } else {
            $result3->fetch();
          }
          $result3->close();

          if ($keywordID != 0) {
            $adjust = $mysqli->prepare("INSERT INTO keywords_question VALUES ($q_id,$keywordID)");
            $adjust->execute();
            $adjust->close();
          }
        }
      }
    }
    $result2->close();

    $adjust = $mysqli->prepare("ALTER TABLE questions DROP COLUMN keywords");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE questions DROP COLUMN keywords</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  //01/04/2011 add sequence to objectives
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='objectives' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='sequence'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE objectives ADD COLUMN sequence int");
    $adjust->execute();
    $adjust->close();
    $adjust = $mysqli->prepare("UPDATE objectives SET sequence = obj_id");
    $adjust->execute();
    $adjust->close();
  }
  $result->close();
  
  // Fix issues where 'NULL' was being inserted into database instead of NULL
  // 02/05/2011
  $result = $mysqli->prepare("SELECT COUNT(*) AS badrows FROM properties WHERE exam_duration = 'NULL'");
  $result->execute();
  $result->store_result();
  $result->bind_result($badrows);
  $result->fetch();
  if ($badrows > 0) {
  	$query = "UPDATE properties SET exam_duration = NULL WHERE exam_duration = 0";
    $adjust = $mysqli->prepare($query);
    $adjust->execute();
    $adjust->close();
    echo "<div>{$query}</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
    
  $result = $mysqli->prepare("SELECT COUNT(*) AS badrows FROM properties WHERE external_review_deadline = '0000-00-00'");
  $result->execute();
  $result->store_result();
  $result->bind_result($badrows);
  $result->fetch();
  if ($badrows > 0) {
  	$query = "UPDATE properties SET external_review_deadline = NULL WHERE external_review_deadline = '0000-00-00'";
    $adjust = $mysqli->prepare($query);
    $adjust->execute();
    $adjust->close();
    echo "<div>{$query}</div>\n";
    ob_flush();
    flush();
  	  }
  $result->close();
  
  $result = $mysqli->prepare("SELECT COUNT(*) AS badrows FROM properties WHERE internal_review_deadline = '0000-00-00'");
  $result->execute();
  $result->store_result();
  $result->bind_result($badrows);
  $result->fetch();
  if ($badrows > 0) {
  	$query = "UPDATE properties SET internal_review_deadline = NULL WHERE internal_review_deadline = '0000-00-00'";
    $adjust = $mysqli->prepare($query);
    $adjust->execute();
    $adjust->close();
    echo "<div>{$query}</div>\n";
    ob_flush();
    flush();
  	  }
  $result->close();
  
  $result = $mysqli->prepare("SELECT COUNT(*) AS badrows FROM properties WHERE sound_demo = ''");
  $result->execute();
  $result->store_result();
  $result->bind_result($badrows);
  $result->fetch();
  if ($badrows > 0) {
  	$query = "UPDATE properties SET sound_demo = '0' WHERE sound_demo = ''";
    $adjust = $mysqli->prepare($query);
    $adjust->execute();
    $adjust->close();
    echo "<div>{$query}</div>\n";
    ob_flush();
    flush();
  	  }
  $result->close();
  
  // 13/06/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE sys_errors (id int not null primary key auto_increment, occurred datetime, userID int, errtype enum('Notice','Warning','Fatal Error','Unknown'), errstr text, errfile text, errline int)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE sys_errors (id int not null primary key auto_increment, occurred datetime, userID int, errtype enum('Notice','Warning','Fatal Error','Unknown'), errstr text, errfile text, errline int)</div>\n";
    ob_flush();
    flush();
  }   
  
  //Close the database
  $mysqli->close();
  ob_end_flush();
  
?>
<p>Finished!</p>
</body>
</html>