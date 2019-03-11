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
* Using English as a base-language, checks for missing pages in other languages.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8"/>

	<title>Rog&#333;: Online Help gaps</title>

	<style>
		.content {font-size:80%}
		li {margin-left:20px; line-height:150%}
	</style>
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
	<link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style>
    h2 {margin-left: 20px; font-size: 150%}
    li {font-size: 110%}
  </style>
	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content">
  
  <div class="head_title">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../admin/index.php"><?php echo $string['administrativetools'] ?></a></div>
    <div class="page_title">Online Help gaps</div>
  </div>
  
<?php
  // Get a list of the distinct languages used.
  $languages = get_languages($mysqli);  

  if (isset($_GET['type'])) {
    $help_table = $_GET['type'];
  } else {
    $help_table = 'staff_help';
  }
  
  // Get a list of the pages in the English (en) version.
  $en_pages = array();
  
  if ($help_table == 'staff_help') {
    $result = $mysqli->prepare("SELECT articleid, title, body, type, roles FROM $help_table WHERE language = 'en' AND deleted IS NULL");
  } else {
    $result = $mysqli->prepare("SELECT articleid, title, body, type, NULL AS roles FROM $help_table WHERE language = 'en' AND deleted IS NULL");
  }
  $result->execute();
  $result->bind_result($articleid, $title, $body, $type, $roles);
  while ($result->fetch()) {
    $en_pages[$articleid]['title'] = $title;
    $en_pages[$articleid]['body'] = $body;
    $en_pages[$articleid]['type'] = $type;
    $en_pages[$articleid]['roles'] = $roles;
  }
  $result->close();
  
  foreach ($languages as $language) {
    echo "<h1>$language</h1>\n";
    echo "<ul>";
    $lang_pages = pages_in_lang($language, $help_table, $mysqli);
    
    foreach ($en_pages as $pageID => $page_details) {
      if (!isset($lang_pages[$pageID])) {
        create_page($language, $pageID, $page_details, $help_table, $mysqli);
        echo "<li>Creating #$pageID - " . $page_details['title'] . "</li>";
        
      }
    }
    echo "</ul>";
  }
  
?>
  
</div>
</body>
</html>

<?php

function create_page($language, $articleid, $page_details, $help_table, $db) {
  $title = $page_details['title'];
  $body = $page_details['body'];
  $body_plain = strip_tags($page_details['body']);
  $type = $page_details['type'];
  $roles = $page_details['roles'];

  if ($help_table == 'staff_help') {
    $result = $db->prepare("INSERT INTO $help_table (
        id,
        title,
        body,
        body_plain,
        type,
        checkout_time,
        checkout_authorID,
        roles,
        deleted,
        language,
        articleid)
      VALUES (
        NULL,
        ?,
        ?,
        ?,
        ?,
        NULL,
        NULL,
        ?,
        NULL,
        ?,
        ?)");
    $result->bind_param('ssssssi', $title, $body, $body_plain, $type, $roles, $language, $articleid);
  } else {
    $result = $db->prepare("INSERT INTO $help_table (
        id,
        title,
        body,
        body_plain,
        type,
        checkout_time,
        checkout_author,
        deleted,
        language,
        articleid)
      VALUES (
        NULL,
        ?,
        ?,
        ?,
        ?,
        NULL,
        NULL,
        NULL,
        ?,
        ?)");
    $result->bind_param('sssssi', $title, $body, $body_plain, $type, $language, $articleid);
  }
	$result->execute();
	$result->close();
}

function pages_in_lang($lang, $help_table, $db) {
  $lang_pages = array();
  
  $result = $db->prepare("SELECT articleid FROM $help_table WHERE language = ? AND deleted IS NULL");
  $result->bind_param('s', $lang);
  $result->execute();
  $result->bind_result($articleid);
  while ($result->fetch()) {
    $lang_pages[$articleid] = $articleid;
  }
  $result->close();
  
  return $lang_pages;
}

function get_languages($db) {
  $languages = array();
  
  $result = $db->prepare("SELECT DISTINCT(language) FROM staff_help WHERE language != 'en'");
  $result->execute();
  $result->bind_result($language);
  while ($result->fetch()) {
    $languages[] = $language;
  }
  $result->close();  
  
  if (isset($_GET['lang'])) {
    // Take a language through GET, useful for creating whole new languages.
    $languages[] = $_GET['lang'];
  }
  
  return $languages;
}

?>
