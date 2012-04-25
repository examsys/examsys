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

  require_once $cfg_web_root . 'classes/dbutils.class.php';
  $mysqli = DBUtils::get_mysqli_link($cfg_db_host , $cfg_db_staff_user, $cfg_db_staff_passwd, $cfg_db_database, $cfg_db_charset, $dbclass);
  
  if (isset($_GET['paperID'])) {
    $paperID = $_GET['paperID'];
  } else {
    $paperID = $_POST['paperID'];
  }
    
  $stmt = $mysqli->prepare("SELECT password FROM properties WHERE property_id=?");
  $stmt->bind_param('i', $paperID);
  $stmt->execute();
  $stmt->bind_result($paper_password);
  $stmt->fetch();
  $stmt->close();
  
  if ($paper_password == '') {
    echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"{$cfg_root_path}/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied 1</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Access to this paper is not currently allowed.</p>\n";
    echo "</body>\n</html>\n";
    $mysqli->close();
    exit;
  }
  
  
  if (!isset($_SERVER['PHP_AUTH_USER'])) {
    Header("WWW-authenticate: basic realm=\"Rogo\"");
    Header("HTTP/1.0 401 Unauthorised");
    echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"{$cfg_root_path}/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Authentication with Rogo has failed.</p>\n";
    echo "</body>\n</html>\n";
    $mysqli->close();
    exit;
  } else {
    if ($_SERVER['PHP_AUTH_USER'] != 'sctreviewer' or $_SERVER['PHP_AUTH_PW'] != $paper_password) {
      Header("WWW-authenticate: basic realm=\"Rogo\"");
      Header("HTTP/1.0 401 Unauthorised");
      echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
      echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"{$cfg_root_path}/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
      echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
      echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Authentication with Rogo has failed.</p>\n";
      echo "</body>\n</html>\n";
      $mysqli->close();
      exit;
    }
  }
  
  
  function getPaperTitle($paperID, $db) {
    $paper_title = '';
    
    $stmt = $db->prepare("SELECT paper_title FROM properties WHERE property_id=?");
    $stmt->bind_param('i', $paperID);
    $stmt->execute();
    $stmt->bind_result($paper_title);
    $stmt->fetch();
    $stmt->close();
    
    return $paper_title;  
  }
?>