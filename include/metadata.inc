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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

  function render_metadata($db, $question, $bloom_on, $module, $disabled, &$string, $userObj) {
    global $staff_modules, $faculty;

    $configObject = Config::get_instance();

    $html = <<< META
          <table id="q-metadata-basic" class="form" summary="{$string['qmetasummary']}">
            <tbody>
              <tr>
                <th>{$string['status']}</th>
                <td>
                  <ul id="status_list" class="radio-list horizontal">

META;

    $status_array = QuestionStatus::get_all_statuses($db, $string);
    foreach ($status_array as $status) {
      $curr_status = $question->get_status();
      $checked = (($curr_status == -1 and $status->get_is_default()) or $curr_status == $status->id) ? ' checked="checked"' : '';
      if ($disabled != '' and !$status->get_change_locked()) {
        $disabled_mod = ' disabled="disabled"';
        $disabled_class = ' class="grey"';
      } else {
        $disabled_mod = $disabled_class = '';
      }

      $html .= <<< STATUS
                    <li><input id="status_{$status->id}" name="status" value="{$status->id}"{$checked} type="radio"{$disabled_mod} /><label for="status_{$status->id}"{$disabled_class}>{$status->get_name()}</label></li>

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

    $type = $question->get_type();
    if ($type == 'keyword_based') {
        $disabled = 'disabled = "disabled"';
    } else {
        $disabled = '';
    }

    $html .= <<< META
          <div class="form">
            <div id="keyword-select" class="select-area">
            	<h3>{$string['keywords']}</h3>
              <div id="q-keywords" class="select-group">

META;

    if (count($module) > 0) {
      $keywords = search_utils::get_keywords($db, $module, $userObj->get_user_ID());
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
          if (in_array($keyword['keyword_id'], $question_keywords)) {
            $checked = ' checked="checked"';
            $on = ' on';
          }
          $enc_keyword = htmlentities($keyword['keyword'], ENT_COMPAT, $configObject->get('cfg_page_charset'));
          $html .= <<< META
      						<li><label for="keyword{$i}" class="fullwidth{$on}"><input id="keyword{$i}" name="keywords[]" value="{$keyword['keyword_id']}" type="checkbox"{$checked} $disabled/>{$enc_keyword}</label></li>

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
          $enc_keyword = htmlentities($keyword['keyword'], ENT_COMPAT, $configObject->get('cfg_page_charset'));
          $html .= <<< META
      						<li><label for="keyword{$i}" class="fullwidth{$on}"><input id="keyword{$i}" name="keywords[]" value="{$keyword['keyword_id']}" type="checkbox"{$checked} $disabled/>{$enc_keyword}</label></li>

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
      $team_id = $individual_team['id'];
      $fullname = $individual_team['fullname'];
      $team_idMod = $individual_team['idMod'];
      if (is_null($individual_team['schoolcode'])) {
        $school = $individual_team['school'];
        $schoolhtml = $school;
      } else {
        $school = $individual_team['schoolcode'];
        $schoolhtml = $individual_team['schoolcode'] . ' ' . $individual_team['school'];
      }
      if ($school != $prev_heading) {
        if ($prev_heading != '') {
          $html .= <<< META
              	</ul>

META;
        }
        $html .= <<< HEADING
  							<h4><span class="opaque">{$schoolhtml}</span></h4>
                <ul class="radio-list clearfix">

HEADING;
        if (is_null($individual_team['schoolcode'])) {
          $prev_heading = $individual_team['school'];
        } else {
          $prev_heading = $individual_team['schoolcode'];
        }
      }

      // Selected if: in existing teams for the question OR we have a module ID passed and the user is on that team (or is sysadmin)
      $checked = $on = '';
      if (in_array($team_id, $current_teams) or in_array($team_id, $module)) {
        $checked = ' checked="checked"';
        $on = ' on';
      }

      $enc_name = htmlentities($fullname, ENT_COMPAT, $configObject->get('cfg_page_charset'));
      $html .= <<< META
                	<li><label for="team{$i}" class="fullwidth{$on}"><input id="team{$i}" name="teams[]" value="{$team_idMod}" type="checkbox"{$checked} />{$team_id}: {$enc_name}</label></li>

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

  function keyword_id_to_text($list, $db) {
    $keywordIDs = implode(',', $list);

    $keyword_text = '';
    if ($keywordIDs != '') {
      $result = $db->prepare("SELECT keyword FROM keywords_user WHERE id IN ($keywordIDs)");
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

  function save_keywords($question, $userID, $edit, $db, &$string) {
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
        $query = $db->prepare("INSERT INTO keywords_question VALUES (?, ?)");
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
          $query = $db->prepare("DELETE FROM keywords_question WHERE q_id=? AND keywordID=?");
          $query->bind_param('ii', $question->id, $existing);
          $query->execute();
          $query->close();

          $keyword_changes = true;
        }
      }

      if ($keyword_changes == true) {
        $logger = new Logger($db);
        $logger->track_change($string['editquestion'], $question->id, $userID, keyword_id_to_text($existing_list, $db), keyword_id_to_text($updated_list, $db), 'keywords');

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
