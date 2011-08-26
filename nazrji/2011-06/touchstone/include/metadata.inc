<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require_once '../../classes/searchutils.class.php';
  require_once '../../classes/schoolutils.class.php';
  require_once '../../classes/logger.class.php';
  
  function render_metadata($mysqlidb, $question, $bloom_on, $module, $disabled) {
    global $teams, $userID, $userroles, $faculty;

    $current_status = ($question->get_status() == '') ? 'Normal' : $question->get_status();
    
    $html = <<< META
          <table id="q-metadata-basic" class="form" summary="Edit basic question metadata">
            <tbody>
              <tr>
                <th>Status</th>
                <td>
                  <ul class="radio-list horizontal">

META;

    $status_array = array('Normal','Retired','Incomplete','Experimental','Beta');
    foreach ($status_array as $status) {
      $checked = ($current_status == $status) ? ' checked="checked"' : '';
      if ($disabled != '' and $status != 'Normal' and $status != 'Retired') {
        $disabled_mod = ' disabled="disabled"';
        $disabled_class = ' class="disabled"';
      } else {
        $disabled_mod = $disabled_class = '';
      }
      
      $status_str = strtolower($status);
      $html .= <<< STATUS
										<li><input id="status-{$status_str}" name="status" value="{$status}"{$checked} type="radio"{$disabled_mod} /> <label for="status-{$status_str}"{$disabled_class}>{$status}</label></li>

STATUS;
    }
    
    $html .= <<< META
                  </ul>
                </td>
              </tr>

META;

    
    if ($bloom_on) {
      $html .= <<< META
              <tr>
                <th><label for="bloom">Bloom's Taxonomy</label></th>
                <td>
  								<select id="bloom" name="bloom">

META;

      $bloom_array = array('', 'Knowledge', 'Comprehension', 'Application', 'Analysis', 'Synthesis', 'Evaluation');
      $blooms = $question->get_bloom();
      foreach ($bloom_array as $individual_bloom) {
        $selected =  ($individual_bloom == $blooms) ? ' selected="selected"' : '';
        $html .= <<< BLOOM
										<option{$selected} value="{$individual_bloom}">{$individual_bloom}</option>

BLOOM;
      }
      $html .= <<< META
  								</select>
  							</td>
  						</tr>

META;
    }
    
    $html .= <<< META
  					</tbody>
  				</table>

META;
    
    
    // Teams
    $current_teams = $question->get_teams();
    $teams_array = SearchUtils::getTeams($teams, $userroles, $userID, $mysqlidb);
    
    // Team keyword lists by module
    if ($module == '') {
      // No teams passed in, use all teams the current user is on.
      $module = $teams;
    }
    
    $html .= <<< META
          <div class="form">
            <div id="keyword-select" class="select-area">
            <h3>Keywords</h3>
              <div id="q-keywords" class="select-group">

META;

    if ($module != '') {
      $keywords = SearchUtils::get_keywords($mysqlidb, $module, $userID);
      $question_keywords = $question->get_keywords();
      
      $i = 0;
      if (count($keywords['team']) > 0) {
        $prev_heading = '';
        foreach ($keywords['team'] as $keyword) {
          if ($prev_heading != '') {
          $html .= <<< META
              	</ul>

META;
          }
          
          if ($keyword['module_id'] != $prev_heading) {
          $html .= <<< META
      					<h4>{$keyword['module_id']}</h4>
                <ul class="radio-list clearfix">

META;
          }
          
          $checked = $on = ''; 
          if(in_array($keyword['keyword_id'], $question_keywords)) {
            $checked = ' checked="checked"';
            $on = ' on';
          }
          $html .= <<< META
      						<li><label for="keyword{$i}" class="fullwidth{$on}"><input id="keyword{$i}" name="keywords[]" value="{$keyword['keyword_id']}" type="checkbox"{$checked} />{$keyword['keyword']}</label></li>

META;
          $i++;
        }
              
        $html .= <<< META
      					</ul>

META;
      }
      
      if (count($keywords['personal']) > 0) {
        $html .= <<< META
                <h4>Personal Keywords</h4>
                <ul class="radio-list clearfix">

META;

        foreach ($keywords['personal'] as $keyword) {
          $checked = $on = ''; 
          if(in_array($keyword['keyword_id'], $question_keywords)) {
            $checked = ' checked="checked"';
            $on = ' on';
          }
          $html .= <<< META
      						<li><label for="keyword{$i}" class="fullwidth{$on}"><input id="keyword{$i}" name="keywords[]" value="{$keyword['keyword_id']}" type="checkbox"{$checked} />{$keyword['keyword']}</label></li>
          
META;
        }
        
        $html .= <<< META
          			</ul>
              </div>

META;
      }
      
      $html .= <<< META
      			</div>

META;
    }
      
    $html .= <<< META
        		<div id="team-select" class="select-area">
              <h3>Teams</h3>
              <div id="q-teams" class="select-group">

META;

    $prev_heading = '';
    $i = 0;
    foreach ($teams_array as $individual_team) {
      $team_name = $individual_team['id'];
      $fullname = $individual_team['fullname'];
      
      if ($individual_team['school'] != $prev_heading) {
        if ($prev_heading != '') {
          $html .= <<< META
              	</ul>

META;
        }
        $html .= <<< HEADING
  							<h4>{$individual_team['school']}</h4>
                <ul class="radio-list clearfix">

HEADING;
        $prev_heading = $individual_team['school'];
      }
      
      // Selected if: in existing teams for the question OR we have a module ID passed and the user is on that team (or is sysadmin)
      $checked = $on = $disabled_mod = '';
      if (in_array($team_name, $current_teams) or $team_name == $module) {
        $checked = ' checked="checked"';
        $on = ' on';
      }
      if (!in_array($team_name, $teams) and strpos($userroles,'SysAdmin') === false) {
        $disabled_mod = ' disabled="disabled"';
      }
      
      $html .= <<< META
                	<li><label for="team{$i}" class="fullwidth{$on}"><input id="team{$i}" name="teams[]" value="{$team_name}" type="checkbox"{$checked}{$disabled_mod} /> {$team_name}: {$fullname}</label></li>

META;

      $i++;
    }
    

    $html .= <<< META
      					</ul>
              </div>
            </div>
          </div>
    
META;
    
    return $html;
  }
  
  function echoMetadata($blooms, $questionID, $team, $colspan, $mysqlidb, $bloom_on, $current_status, $disabled) {
    global $teams, $userID, $userroles, $faculty;

    if ($current_status == '') $current_status = 'Normal';
    $html = "<tr><td colspan=\"" . ($colspan + 1) . "\"><div class=\"section\">Metadata</div>\n";
    
    // Question Status
    $html .= "<tr>\n<td class=\"field\">Status</td>\n<td colspan=\"$colspan\" style=\"font-size:90%\">";
    $status_array = array('Normal','Retired','Incomplete','Experimental','Beta');
    foreach ($status_array as $status) {
      if ($current_status == $status) {
        if ($disabled != '' and $status != 'Normal' and $status != 'Retired') {
          $html .= '<input type="radio" name="status" value="' . $status . '" checked disabled>&nbsp;<span style="color:#808080">' . $status . '</span>&nbsp;&nbsp;';
        } else {
          $html .= '<input type="radio" name="status" value="' . $status . '" checked>&nbsp;' . $status . '&nbsp;&nbsp;';
        }
      } else {
        if ($disabled != '' and $status != 'Normal' and $status != 'Retired') {
          $html .= '<input type="radio" name="status" value="' . $status . '" disabled>&nbsp;<span style="color:#808080">' . $status . '</span>&nbsp;&nbsp;';
        } else {
          $html .= '<input type="radio" name="status" value="' . $status . '">&nbsp;' . $status . '&nbsp;&nbsp;';
        }
      }
    }
    $html .= "<input type=\"hidden\" name=\"old_status\" value=\"$current_status\" /></td></tr>\n";
    
    // Bloom's Taxonomy
    if ($bloom_on) {
      $html .= "<tr>\n<td class=\"field\">Bloom's Taxonomy</td>\n<td colspan=\"$colspan\"><input type=\"hidden\" name=\"old_bloom\" value=\"$blooms\" /><select name=\"bloom\">\n<option value=\"\"></option>\n";
      $bloom_array = array('Knowledge','Comprehension','Application','Analysis','Synthesis','Evaluation');
      foreach ($bloom_array as $individual_bloom) {
        if ($individual_bloom == $blooms) {
          $html .= "<option value=\"$individual_bloom\" selected>$individual_bloom</option>\n";
        } else {
          $html .= "<option value=\"$individual_bloom\">$individual_bloom</option>\n";
        }
      }
      $html .= "</select></td>\n</tr>\n";
    }
    
    $html .= "<tr><td colspan=\"" . ($colspan+1) . "\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size:90%; width:100%\">";
    $html .= "<tr style=\"font-weight:bold\"><td>Keywords</td><td style=\"padding-left:4px\">Teams</td></tr>";
    
    // Keywords
    $question_keywords = array();
    $or_sql = '';
    if ($questionID != '') {
      $result = $mysqlidb->prepare("SELECT keywordID FROM keywords_question WHERE q_id=$questionID");
      $result->execute();
      $result->store_result();
      $result->bind_result($keywordID);
      while ($result->fetch()) {
        $question_keywords[$keywordID] = $keywordID;
      }
      $result->close();
    }
    
    $keyword_no = 0;
    $keywords = '';
	
    $html .= "<tr>\n<td style=\"vertical-align:top\">";
    $html .= "<div id=\"keywordarea\" style=\"width:300px; height:300px; background-color:white; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">\n";

    // Team keyword lists by module
    if ($team == '') {
      // No teams passed in, use all teams the current user is on.
      $team = implode(';',$teams);
    }
    
    if ($team != '') {
      $old_team = '';
      $sql_team = str_replace(";","','",$team);
      $result = $mysqlidb->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND moduleid IN ('$sql_team') ORDER BY moduleid, keyword");
      $result->execute();
      $result->store_result();
      $result->bind_result($moduleID, $keyword, $keywordID);
      while ($result->fetch()) {
        if ($old_team != $moduleID) {
          $html .= "<table border=\"0\" style=\"padding-top:3px; padding-bottom:2px; width:100%; background-color:white; color:#1E3287\"><tr><td><nobr>" . $moduleID . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
        }
        if (isset($question_keywords[$keywordID])) {
          $html .= "<div style=\"background-color:#B3C8E8\" id=\"divkeyword$keyword_no\"><input type=\"checkbox\" onclick=\"toggle('divkeyword$keyword_no'); updateList();\" id=\"keyword$keyword_no\" name=\"keyword$keyword_no\" value=\"$keywordID\" checked>&nbsp;$keyword</div>\n";
          if ($keywords == '') {
            $keywords = $keywordID;
          } else {
            $keywords .= ';' . $keywordID;
          }
        } else {
          $html .= "<div style=\"background-color:white; color:black\" id=\"divkeyword$keyword_no\"><input type=\"checkbox\" onclick=\"toggle('divkeyword$keyword_no'); updateList();\" id=\"keyword$keyword_no\" name=\"keyword$keyword_no\" value=\"$keywordID\">&nbsp;$keyword</div>\n";
        }
        $old_team = $moduleID;
        $keyword_no++;
      }
      $result->close();
    }
	
    // Personal keyword list
    $html .= "<table border=\"0\" style=\"padding-top:3px; padding-bottom:2px; width:100%; background-color:white; color:#1E3287\"><tr><td><nobr>Personal Keywords</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    $result = $mysqlidb->prepare("SELECT DISTINCT keyword, id FROM keywords_user WHERE userID=$userID ORDER BY keyword");
    $result->execute();
    $result->store_result();
    $result->bind_result($keyword, $keywordID);
    while ($result->fetch()) {
      if (isset($question_keywords[$keywordID])) {
        $html .= "<div style=\"background-color:#B3C8E8\" id=\"divkeyword$keyword_no\"><input type=\"checkbox\" onclick=\"toggle('divkeyword$keyword_no'); updateList();\" id=\"keyword$keyword_no\" name=\"keyword$keyword_no\" value=\"$keywordID\" checked>&nbsp;$keyword</div>\n";
        if ($keywords == '') {
          $keywords = $keywordID;
        } else {
          $keywords .= ';' . $keywordID;
        }
      } else {
        $html .= "<div style=\"background-color:white; color:black\" id=\"divkeyword$keyword_no\"><input type=\"checkbox\" onclick=\"toggle('divkeyword$keyword_no'); updateList();\" id=\"keyword$keyword_no\" name=\"keyword$keyword_no\" value=\"$keywordID\">&nbsp;$keyword</div>\n";
      }
      $keyword_no++;
    }
    $result->close();
    $html .= "</div><input type=\"hidden\" name=\"keyword_no\" id=\"keywordno\" value=\"$keyword_no\" /><textarea rows=\"0\" cols=\"0\" style=\"display:none\" name=\"old_keywords\">$keywords</textarea></td>\n";
    
    // Teams
    $current_teams = explode(';',$team);
    $html .= "<td style=\"padding-left:4px; vertical-align:top\"><div id=\"teamsarea\" style=\"width:500px; height:300px; background-color:white; overflow-y:scroll; border:1px solid #7F9DB9; font-size:90%\">\n";
    
    if (count($current_teams) > 0) {
      $extra_teams = " OR name IN('" . str_replace(";","','",$team) . "')";
      $extra_modules = " OR moduleID IN('" . str_replace(";","','",$team) . "')";
    } else {
      $extra_teams = '';
      $extra_modules = '';
    }
    
    $teams_array = SearchUtils::getTeams($teams, $userroles, $userID, $mysqlidb);

    $found = false;
    foreach($teams_array as $individual_team) {
      if ($individual_team['id'] == $team) $found = true;
    }
    
    $team_no = 0;
    $old_school = '';
    foreach ($teams_array as $individual_team) {
      $team_name = $individual_team['id'];
      $fullname = $individual_team['fullname'];
      
      if ($individual_team['school'] != $old_school) {
        $html .= "<div style=\"font-weight:bold; padding-top:4px\">" . $individual_team['school'] . "</div>\n";
      }
      
      if ((isset($_GET['module']) and $team_name == $_GET['module'] and $team == '') or (isset($_COOKIE['default_team']) and $team_name == $_COOKIE['default_team'] and $team == '')) $match = true;
      
      if (isset($_GET['module']) and $team_name == $_GET['module']) {
        if (in_array($team_name,$teams) or strpos($userroles,'SysAdmin') !== false) {
          $html .= "<div style=\"background-color:#B3C8E8\" id=\"divteam$team_no\"><input type=\"checkbox\" onclick=\"toggle('divteam$team_no'); updateList();\" id=\"team$team_no\" name=\"team$team_no\" value=\"$team_name\" checked>&nbsp;$team_name: $fullname</div>\n";
        } else {
          $html .= "<div style=\"background-color:#B3C8E8\" id=\"divteam$team_no\"><input type=\"checkbox\" name=\"dummyteam$team_no\" value=\"\" checked disabled><input type=\"checkbox\" name=\"team$team_no\" value=\"$team_name\" style=\"display:none\" checked>&nbsp;$team_name: $fullname</div>\n";
        }
      } else {
        $html .= "<div style=\"background-color:white; color:black\" id=\"divteam$team_no\"><input type=\"checkbox\" onclick=\"toggle('divteam$team_no'); updateList();\" id=\"team$team_no\" name=\"team$team_no\" value=\"$team_name\">&nbsp;$team_name: $fullname</div>\n";
      }
      $team_no++;
      $old_school = $individual_team['school'];
    }
    if (count($teams_array) > 0) $html .= "</div><input type=\"hidden\" name=\"team_no\" id=\"teamno\" value=\"$team_no\" /><input type=\"hidden\" name=\"old_teams\" value=\"$team\" />\n</td>\n</tr>\n";
    
    $html .= "</table></td></tr>\n";
    return $html;
  }
  
  function keyword_id_to_text($list, $mysqlidb) {
    $keywordIDs = implode(',',$list);
    
    $keyword_text = '';
    if ($keywordIDs != '') {
      $result = $mysqlidb->prepare("SELECT keyword FROM keywords_user WHERE id IN ($keywordIDs)");
      $result->execute();
      $result->bind_result($keyword);
      while ($result->fetch()) {
        if ($keyword_text == '') {
          $keyword_text = $keyword;
        } else {
          $keyword_text .= ';' . $keyword;
        }
      }
      $result->close();
    }
    
    return $keyword_text;
  }
  
  function getKeywords() {
    $keywords = '';
    $tmp_list = array();
    for ($i=0; $i<$_POST['keyword_no']; $i++) {
      if (isset($_POST["keyword$i"])) {
        $tmp_list[] = $_POST["keyword$i"];
      }
    }
    return $tmp_list;
  }
  
  function save_keywords($question, $userID, $edit, $mysqlidb) {
    $keyword_changes = false;
    $updated_list = (isset($_POST['keywords'])) ? $_POST['keywords'] : array();
    
    if ($edit == true) {
      $existing_list = $question->get_keywords();
    } else {
      $existing_list = array();
    }
    
    // First go through the list of updated keywords looking for new ones to add.
    foreach ($updated_list as $updated) {
      if (!in_array($updated, $existing_list)) {
        $query = $mysqlidb->prepare("INSERT INTO keywords_question VALUES (?, ?)");
        $query->bind_param('ii', $question->id, $updated);
        $query->execute();
        $query->close();
        
        $keyword_changes = true;
      }
    }

    if ($edit == true) {
      // Next go through the list of old keywords looking for ones to delete.
      foreach ($existing_list as $existing) {
        if (!in_array($existing, $updated_list) and $existing != '') {
          $query = $mysqlidb->prepare("DELETE FROM keywords_question WHERE q_id=? AND keywordID=?");
          $query->bind_param('ii', $question->id, $existing);
          $query->execute();
          $query->close();
          
          $keyword_changes = true;
        }
      }
      
      if ($keyword_changes == true) {
        $logger = new Logger($mysqlidb);
        $logger->track_change('Edit Question', $question->id, $userID, keyword_id_to_text($existing_list, $mysqlidb), keyword_id_to_text($updated_list, $mysqlidb), 'keywords');

        $question->set_keywords($updated_list);
      }
    }
  }
  
  function saveKeywords($questionID, $userID, $question_changes, $edit, $mysqlidb) {
    $keyword_changes = false;
    $updated_list = getKeywords();
    if ($edit == true) {
      $existing_list = explode(';',$_POST['old_keywords']);
    } else {
      $existing_list = array();
    }
    
    // First go through the list of updated keywords looking for new ones to add.
    $elements = count($updated_list);
    for ($i=0; $i<$elements; $i++) {
      $keywordID = $updated_list[$i];
      
      if (!in_array($keywordID, $existing_list)) {
        $query = $mysqlidb->prepare("INSERT INTO keywords_question VALUES ($questionID, $keywordID)");
        $query->execute();
        $query->close();
        
        $keyword_changes = true;
      }
    }

    if ($edit == true) {
      // Next go through the list of old keywords looking for ones to delete.
      $elements = count($existing_list);
      for ($i=0; $i<$elements; $i++) {
        $keywordID = $existing_list[$i];
        
        if (!in_array($keywordID, $updated_list) and $keywordID != '') {
          $query = $mysqlidb->prepare("DELETE FROM keywords_question WHERE q_id=$questionID AND keywordID=$keywordID");
          $query->execute();
          $query->close();
          
          $keyword_changes = true;
        }
      }

      if ($keyword_changes == true) {
        record_trackChanges('Edit Question', $questionID, keyword_id_to_text($existing_list, $mysqlidb), keyword_id_to_text($updated_list, $mysqlidb), 'keywords', $userID, $question_changes);
      }
    }
  }
  
  function getTeams() {
    $teams = '';
    $tmp_list = array();
    for ($i=0; $i<$_POST['team_no']; $i++) {
      if (isset($_POST["team$i"])) {
        $tmp_list[] = $_POST["team$i"];
      }
    }
    sort($tmp_list);
    return implode(';', $tmp_list);
  }
  
  function getDefaultTeam() {
    $team = '';
    for ($i=0; $i<$_POST['team_no']; $i++) {
      if (isset($_POST["team$i"]) and $team == '') {
        $team = $_POST["team$i"];
      }
    }
    return $team;
  }
  
?>
