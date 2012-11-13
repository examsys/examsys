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

  require_once '../../classes/searchutils.class.php';
  require_once '../../classes/schoolutils.class.php';
  require_once '../../classes/logger.class.php';
  
  function render_metadata($mysqlidb, $question, $bloom_on, $module, $disabled, &$string, $userObj) {
    global $staff_modules, $DISABLEDuserID, $DISABLEDuserroles, $faculty;

    $current_status = ($question->get_status() == '') ? $string['normal'] : $question->get_status();
    
    $html = <<< META
          <table id="q-metadata-basic" class="form" summary="{$string['qmetasummary']}">
            <tbody>
              <tr>
                <th>{$string['status']}</th>
                <td>
                  <ul class="radio-list horizontal">

META;

    $status_array = array($string['normal'], $string['retired'], $string['incomplete'], $string['experimental'], $string['beta']);
    foreach ($status_array as $status) {
      $checked = ($current_status == $status) ? ' checked="checked"' : '';
      if ($disabled != '' and $status != $string['normal'] and $status != $string['retired']) {
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
                <th><label for="bloom">{$string['bloomstaxonomy']}</label></th>
                <td>
  								<select id="bloom" name="bloom">

META;

      $bloom_array = array('', $string['knowledge'], $string['comprehension'], $string['application'], $string['analysis'], $string['synthesis'], $string['evaluation']);
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
    
    
    // Teams - staff_modules
    if (!is_array($module) or count($module) == 0) {
      $module = $question->get_teams();
    }
    $current_teams = $module;

    $teams_array = $userObj->get_staff_accessable_modules();

    // Team keyword lists by module
    if (count($module) == 0) {
      // No teams passed in, use all teams the current user is on.
      $module = $staff_modules;
    }
    
    $html .= <<< META
          <div class="form">
            <div id="keyword-select" class="select-area">
            	<h3>{$string['keywords']}</h3>
              <div id="q-keywords" class="select-group">

META;

    if (count($module) > 0) {
      $keywords = search_utils::get_keywords($mysqlidb, $module, $userObj->get_user_ID());
      $question_keywords = $question->get_keywords();
      
      $i = 0;
      if (count($keywords['team']) > 0) {
        $prev_heading = '';
        foreach ($keywords['team'] as $keyword) {
          if ($keyword['module_id'] != $prev_heading) {
            if ($prev_heading != '') {
            $html .= <<< META
              	</ul>

META;
            }
          
            $html .= <<< META
      					<h4><span class="opaque">{$keyword['module_id']}</span></h4>
                <ul class="radio-list clearfix">

META;
            $prev_heading = $keyword['module_id'];
          }
          
          $checked = $on = ''; 
          if(in_array($keyword['keyword_id'], $question_keywords)) {
            $checked = ' checked="checked"';
            $on = ' on';
          }
          $enc_keyword = htmlentities($keyword['keyword']);
          $html .= <<< META
      						<li><label for="keyword{$i}" class="fullwidth{$on}"><input id="keyword{$i}" name="keywords[]" value="{$keyword['keyword_id']}" type="checkbox"{$checked} />{$enc_keyword}</label></li>

META;
          $i++;
        }
              
        $html .= <<< META
      					</ul>

META;
      }
      
      if (count($keywords['personal']) > 0) {
        $html .= <<< META
                <h4><span class="opaque">{$string['keywords']}</span></h4>
                <ul class="radio-list clearfix">

META;

        foreach ($keywords['personal'] as $keyword) {
          $checked = $on = ''; 
          if(in_array($keyword['keyword_id'], $question_keywords)) {
            $checked = ' checked="checked"';
            $on = ' on';
          }
          $enc_keyword = htmlentities($keyword['keyword']);
          $html .= <<< META
      						<li><label for="keyword{$i}" class="fullwidth{$on}"><input id="keyword{$i}" name="keywords[]" value="{$keyword['keyword_id']}" type="checkbox"{$checked} />{$enc_keyword}</label></li>
          
META;
          $i++;
        }
        
        $html .= <<< META
          			</ul>

META;
      }
      
      $html .= <<< META
              </div>
      			</div>

META;
    }
      
    $html .= <<< META
        		<div id="team-select" class="select-area">
              <h3>{$string['teams']}</h3>
              <div id="q-teams" class="select-group">

META;

    $prev_heading = '';
    $i = 0;
    foreach ($teams_array as $individual_team) {
      $team_name = $individual_team['id'];
      $fullname = $individual_team['fullname'];
      $team_idMod = $individual_team['idMod'];

      if ($individual_team['school'] != $prev_heading) {
        if ($prev_heading != '') {
          $html .= <<< META
              	</ul>

META;
        }
        $html .= <<< HEADING
  							<h4><span class="opaque">{$individual_team['school']}</span></h4>
                <ul class="radio-list clearfix">

HEADING;
        $prev_heading = $individual_team['school'];
      }
      
      // Selected if: in existing teams for the question OR we have a module ID passed and the user is on that team (or is sysadmin)
      $checked = $on = '';
      if (in_array($team_name, $current_teams) or $team_name == $module) {
        $checked = ' checked="checked"';
        $on = ' on';
      }
            
      $enc_name = htmlentities($fullname);
      $html .= <<< META
                	<li><label for="team{$i}" class="fullwidth{$on}"><input id="team{$i}" name="teams[]" value="{$team_idMod}" type="checkbox"{$checked} /> {$team_name}: {$enc_name}</label></li>

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
  
  function save_keywords($question, $userID, $edit, $mysqlidb, &$string) {
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
        $logger->track_change($string['editquestion'], $question->id, $userID, keyword_id_to_text($existing_list, $mysqlidb), keyword_id_to_text($updated_list, $mysqlidb), 'keywords');

        $question->set_keywords($updated_list);
      }
    }
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
