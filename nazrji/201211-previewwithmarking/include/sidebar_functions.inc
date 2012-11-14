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
* Functions to get data for the sub-menus used in /staff/index.php, /paper/details.php and /question/list.php.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

/**
 * Get a list of personal folders for the current user.
 * @param array $staff_modules list of staff modules current user is on
 * @param integer $userID ID of the current user
 * @param object $db database connection
 * @return array list of personal folder URLs
 */
function get_folders($staff_modules, $userID, $db) {
  $staff_modules_string = '';
  if (count($staff_modules) > 0) {
    $staff_modules_string = " OR idMod IN('" . implode("','",$staff_modules) . "')";
  }
  $my_folders_array = array();
  $result = $db->prepare("SELECT id, name FROM folders,folders_modules_staff WHERE folders.id = folders_modules_staff.folders_id AND (ownerID=? $staff_modules_string) AND deleted IS NULL ORDER BY name");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($folder_id, $folder_name);
  while ($result->fetch()) {
    $path_parts = substr_count($folder_name,';');
    $folder_array = explode(';', $folder_name);
    $display_name = str_repeat('&nbsp;', $path_parts * 4) . $folder_array[$path_parts];
    $display_name = str_replace("'", "\'", $display_name);
    $url = '/folder/details.php?folder=' . $folder_id;
    $my_folders_array[$display_name] = $url;
  }
  $result->close();
  
  return $my_folders_array;
}

/**
 * Get a list of modules the current user is on and add any personal folders.
 * @param array $teams list of teams current user is on
 * @param array $my_folders_array list of personal folders the user is on
 * @param array $modules_array list of modules the user is on
 * @param object $db database connection
 * @return array list of module URLs
 */
function get_modules($teams, $my_folders_array, &$modules_array, $db) {
  if (count($teams) > 0) {
    $team_sql = implode("','", $teams);
    if ($team_sql != '') $team_sql = "'$team_sql'";
    
    $result = $db->prepare("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($team_sql) ORDER BY moduleID");
    $result->execute();
    $result->bind_result($tmp_moduleid, $tmp_fullname);
    while ($result->fetch()) {
      $title = $tmp_moduleid . ' - ' . str_replace("'", "\'", $tmp_fullname);
      $url = '/folder/details.php?module=' . $tmp_moduleid;
      $modules_array[$title] = $url;
    }
    $result->close();
  }

  if (count($my_folders_array) > 0) {
    $divider = array('-'=>'-');
    $full_modules_array = array();
    $full_modules_array += $my_folders_array;
    if (count($modules_array) > 0) {
      $full_modules_array += $divider + $modules_array;
    } else {
      //$full_modules_array = $modules_array;
    }
  } else {
    $full_modules_array = $modules_array;
  }

  return $full_modules_array;
}

/**
 * Get a list of staff modules the current user is on
 * @param array $teams list of teams current user is on
 * @param object $db database connection
 * @return array list of module URLs.
 */
function get_staff_module_links($staff_modules, $db) {
  global $cfg_root_path;
  
  $modules_array = array();

  if (count($staff_modules) > 0) {
    $staff_modules_sql = "'" . implode("','", $staff_modules) . "'";
    
    $result = $db->prepare("SELECT DISTINCT moduleid, fullname FROM modules WHERE moduleid IN($staff_modules_sql) ORDER BY moduleID");
    $result->execute();
    $result->bind_result($tmp_moduleid, $tmp_fullname);
    while ($result->fetch()) {
      $title = $tmp_moduleid . ' - ' . str_replace("'", "\'", $tmp_fullname);
      $modules_array[$title] = $cfg_root_path . '/question/list.php?type=%&module=' . $tmp_moduleid;
    }
    $result->close();
  }

  return $modules_array;
}

/**
 * Get a list of keywords for the current user
 * @param array $teams list of teams current user is on
 * @param integer $userID ID of the current user
 * @param object $db database connection
 * @return array list of keywords URLs.
 */
function get_keywords($teams, $userID, $db) {
  global $cfg_root_path;

  $keywords_array = array();
  $keyword_no = 0;
  $old_moduleID = '';
  $result = $db->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND moduleid IN ('" . implode("','",$teams) . "') ORDER BY moduleid, keyword");
  $result->execute();
  $result->store_result();
  $result->bind_result($moduleID, $tmp_keyword, $keywordID);
  while ($result->fetch()) {
    if ($old_moduleID != $moduleID) {
      $keywords_array[$keyword_no] = "#$moduleID";
    }
    $keywords_array[htmlspecialchars($moduleID . '[BR]' . $tmp_keyword)] = $cfg_root_path . '/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
    $old_moduleID = $moduleID;
    $keyword_no++;
  }
  if ($result->num_rows > 0) $keywords_array[$keyword_no] = '#Personal Keywords';
  $result->close();

  $result = $db->prepare("SELECT DISTINCT id, keyword FROM keywords_user WHERE userID=? ORDER BY keyword");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->bind_result($keywordID, $tmp_keyword);
  while ($result->fetch()) {
    $keywords_array[htmlspecialchars($tmp_keyword)] = $cfg_root_path . '/question/list.php?type=%&keyword=' . $keywordID . ';' . urlencode($tmp_keyword);
  }
  $result->close();
  
  return $keywords_array;
}
?>