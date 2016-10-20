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
* Set of mapping functions used to map questions to objectives
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

/**
 * This is function displayObjectivesMappingForm it displays objectives by session
 *
 * @param int $paper_id
 * @param object $db
 *
 */

function render_objectives_mapping_form($db, $paper_id, &$string) {
  global $vle_api_cache;

  // Get module and session from paper ID.
  $modules_array = array();
  $query = <<<QUERY
SELECT DISTINCT m.moduleid, pm.idMod, p.calendar_year, p.paper_type FROM properties p INNER JOIN properties_modules pm ON p.property_id = pm.property_id INNER JOIN modules m ON pm.idMod = m.id WHERE p.property_id = ?
QUERY;
  $result = $db->prepare($query);
  $result->bind_param('i', $paper_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($moduleId, $idMod, $session, $paper_type);
  while($result->fetch()) {
    $modules_array[$idMod] = $moduleId;
  }
  if ($result->num_rows == 0) {
    return '';
  }
  $result->close();

  if (isset($_GET['q_id'])) {
    $q_id = $_GET['q_id'];
  } else {
    $q_id = '';
  }

  $objsByModuleSession = getObjectives($modules_array, $session, $paper_id, $q_id, $db);

  if (isset($objsByModuleSession['none_of_the_above'])) {
    $none_of_the_above = $objsByModuleSession['none_of_the_above'];
    unset($objsByModuleSession['none_of_the_above']);
  }

  $html = '';

  foreach ($objsByModuleSession as $moduleid => $objsBySession) {
    $tmp = array_flip($modules_array);
    $idmod = $tmp[$moduleid];

    $html .= <<< HTML
        <div class="objheading">{$moduleid} {$string['objectives']}</div>

        <ul class="objectives">

HTML;

    $old_mappings = '';
    $objcount = 0;
    foreach($objsBySession as $id => $class) {
      $heading = '';
      if ($class['class_code'] != '') {
        $heading .= $class['class_code'] . ' - ';
      }
      $heading .= $class['title'];
      if ($class['occurrance'] != '') {
        $heading .= '&nbsp;<span class="note">' . $class['occurrance'] . '</span>';
      }

      $html .= <<< HTML
    			<li class="top">
            <a href="#">$heading</a>
            <ul class="hide">

HTML;
      if (isset($class['objectives'])) {
        foreach ($class['objectives'] as $objectives) {
          if ($objectives['mapped'] != 0) {
            $checked = ' checked="checked"';
            $old_mappings .= $objectives['id'] . ',';
          } else {
            $checked = '';
          }
          $obj_text = strip_tags($objectives['content'], '<b><i><strong><em><sub><sup>');
          $html .= <<< HTML
        				<li><input id="objective{$objcount}" name="{$idmod}_objectives[]" value="{$objectives['id']}" type="checkbox"{$checked} /><label for="objective{$objcount}">{$obj_text}</label></li>

HTML;
          $objcount++;
        }
      } else {
        $html .= <<< HTML
            		<li>{$string['noobjectives']}</li>

HTML;
      }

      $html .= <<< HTML
            </ul>
          </li>

HTML;
    }

    $html .= <<< HTML
        </ul>

HTML;
    $html .= <<< HTML
    		<input name="{$idmod}_session" type="hidden" value="{$session}"/>
    		<input name="{$idmod}_old_mappings" type="hidden" value="{$old_mappings}" />
        <input name="{$idmod}_vle_api" type="hidden" value="{$vle_api_cache[$idmod][$session]['api']}"/>
        <input name="{$idmod}_map_level" type="hidden" value="{$vle_api_cache[$idmod][$session]['level']}"/>

HTML;
  }

  $checked = (isset($none_of_the_above['mapped'])) ? ' checked="checked"' : '';
  $modules_string = implode(',',$modules_array);
  $modules_keys_string = implode(',',array_keys($modules_array));
  $nota_message = sprintf($string['noneabovemsg'], $modules_string);
  $html .= <<< HTML
        <p class="warning" style="margin-left:10px"><input value="-1" id="none_of_the_above" name="none_of_the_above" type="checkbox"{$checked} /><label for="none_of_the_above"><strong>{$string['noneabove']}</strong></label><br />{$nota_message}</p>
        <input id="objective_modules" name="objective_modules" type="hidden" value="{$modules_keys_string}" />

HTML;

  return $html;
}


/**
 * This is function save_objective_mappings it saves mappings to the relationships table in the Rogo database
 *
 * @param mysqli          $db           Database link
 * @param string          $modules      List of modules that are being mapped
 * @param integer         $paper_id     ID of paper that is being mapped
 * @param integer         $question_id  ID of question that is being mapped
 */
function save_objective_mappings($db, $modules, $paper_id, $question_id) {
  if ($paper_id != '' and substr($paper_id,0,5) != 'find:') {
    $modules = explode(',',$modules);
    $old_mappings = '';
    $new_mappings = '';

    foreach ($modules as $idMod) {
      $relationships = array();
      if (isset($_POST[$idMod . '_session'])) {
        $session = $_POST[$idMod . '_session'];
        $old_mappings = $_POST[$idMod . '_old_mappings'];
        $vle_api = $_POST[$idMod . '_vle_api'];
        $map_level = $_POST[$idMod . '_map_level'];
        if (isset($_POST[$idMod . '_objectives'])) {
          foreach ($_POST[$idMod . '_objectives'] as $objective_id) {
            $data = array(
              'idMod' => $idMod,
              'paper_id' => $paper_id,
              'question_id' => $question_id,
              'objective_id' => $objective_id,
              'calendar_year' => $session,
              'vle_api' => $vle_api,
              'map_level' => $map_level);
            $relationships[] = new Relationship($db, $data);
            $new_mappings .= $objective_id . ',';
          }
        }

        //check for none of the above
        if (isset($_POST['none_of_the_above']) and $_POST['none_of_the_above'] == -1 and count($relationships) == 0) {
          $data = array(
            'idMod' => $idMod,
            'paper_id' => $paper_id,
            'question_id' => $question_id,
            'objective_id' => -1,
            'calendar_year' => $session,
            'vle_api' => $vle_api,
            'map_level' => $map_level);
          $relationships[] = new Relationship($db, $data);
          $new_mappings .= '-1,';
        } else {
          $old_mappings .= '-1,';
        }

        if ($new_mappings != $old_mappings) {
          // prepend the delete
          if ($old_mappings != '') {
            $delete = "DELETE FROM relationships WHERE idMod = $idMod AND paper_id = $paper_id AND question_id = $question_id AND calendar_year = '$session' AND vle_api= '$vle_api'";
            if (!$result = $db->query($delete)) {
             echo "Question Mapping Error #1 " . $db->error . "<br/> $delete <br/>";
            }
          }
          foreach ($relationships as $rel) {
            try {
              $rel->save();
            } catch (DatabaseException $ex) {
              echo "Question Mapping Error #1 " . $ex->getMessage();
            }
          }
        }
      }
    }
  }
}

/**
 * This is function getObjectives this function gets objectives to be mapped. It can Retrieve objectives from
 * the internal database or an external application
 *
 * @param string $thisModuleid module code
 * @param string $session A session in the format yyyy/yy
 * @param mixed $paperID
 * @param mixed $questionID
 * @param mixed $db
 * @return array an array of objectives and mappings
 *
 */

$objsBySession_cache = array();
$vle_api_cache = array(); // Cache the vle api to save some queries

/**
 * Get any mapped objectives for a question.
 *
 * @global type $objsBySession_cache
 * @global array $vle_api_cache
 * @global type $configObject
 * @param int $thisModuleid
 * @param int $session The year in which the academic year starts
 * @param int $paperID
 * @param int $questionID
 * @param mysqli $db
 * @param string $scope
 * @return array
 * @throws Exception if the objectives API gives an error.
 */
function getObjectives($thisModuleid, $session, $paperID, $questionID, $db, $scope = 'latest') {
  global $objsBySession_cache, $vle_api_cache,  $configObject;
  $original_thisModuleid = $thisModuleid;
  $objsBySession = array();

  $module_list = $thisModuleid;

  if (!is_array($module_list)) {
    $module_list = array($thisModuleid);
  }

  foreach ($module_list as $thisidMod => $thisModuleid) {
    // Get which API to use to get the learning objectives
    $vle_api_data = MappingUtils::get_vle_api($thisidMod, $session, $vle_api_cache, $db);
    $vle_api = $vle_api_data['api'];
    $map_level = $vle_api_data['level'];

    if ($vle_api != '') {
      // Get objectives from the Curriculum Map via REST request
      if (!isset($objsBySession_cache[$thisModuleid]) or !isset($objsBySession_cache[$thisModuleid][$session])) {
				$vle = CMFactory::GetCMAPI($vle_api);
        $vle->setMappingLevel($map_level);
        $resp = $vle->getObjectives($thisModuleid, $session, $db);
				if ($resp == 'error') {
          throw new Exception('could_not_retrieve_objectives');
        }
        if ($resp != '') {
          $objsBySession_cache[$thisModuleid][$session] = $resp;
				}
      }
      if (isset($objsBySession_cache[$thisModuleid][$session][$thisModuleid])) {
        $objsBySession[$thisModuleid] = $objsBySession_cache[$thisModuleid][$session][$thisModuleid];
      }
    } else {
      // Use Rogō's internally stored objectves
      if ($scope == 'all') {
      	$sql = "SELECT sess_id, identifier, idMod, title, source_url, calendar_year, DATE_FORMAT(occurrence,' {$configObject->get('cfg_short_date')}') AS occurrence FROM sessions WHERE idMod = ? ORDER BY calendar_year, sess_id";
      	$stmt = $db->prepare($sql);
        $stmt->bind_param('i', $thisidMod);
      } elseif ($session == '' and $scope == 'latest') {
        $sql = "SELECT sess_id, identifier, idMod, title, source_url, calendar_year, DATE_FORMAT(occurrence,' {$configObject->get('cfg_short_date')}') AS occurrence FROM sessions WHERE idMod = ? AND calendar_year=(SELECT MAX(calendar_year) FROM objectives) ORDER BY sess_id";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $thisidMod);
      } else {
        $sql = "SELECT sess_id, identifier, idMod, title, source_url, calendar_year, DATE_FORMAT(occurrence,' {$configObject->get('cfg_short_date')}') AS occurrence FROM sessions WHERE idMod = ? AND calendar_year=? ORDER BY sess_id";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('is', $thisidMod, $session);
      }
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($sess_id, $identifier, $idMod, $title, $source_url, $calendar_year, $occurrence);
      while ($stmt->fetch()) {
        $objsBySession[$thisModuleid][$identifier]['class_code'] = '';
        $objsBySession[$thisModuleid][$identifier]['title'] = $title;
        $objsBySession[$thisModuleid][$identifier]['occurrance'] = $occurrence;
        $objsBySession[$thisModuleid][$identifier]['mapped'] = 0;
        $objsBySession[$thisModuleid][$identifier]['identifier'] = $identifier;
        $objsBySession[$thisModuleid][$identifier]['source_url'] = $source_url;
        $objsBySession[$thisModuleid][$identifier]['calendar_year'] = $calendar_year;
        $objsBySession[$thisModuleid][$identifier]['VLE'] = '';
      }

      $stmt->close();
      if (isset($objsBySession[$thisModuleid]) and count($objsBySession[$thisModuleid]) > 0) {
        // Get internal objectives from Rogo.
        foreach ($objsBySession[$thisModuleid] as $identifier => $sess) {
          $sql = "SELECT obj_id, objective, sequence FROM objectives WHERE identifier=? AND calendar_year = ? ORDER BY sequence";
          $stmt = $db->prepare($sql);
          $stmt->bind_param('ss',$identifier,$sess['calendar_year']);
          $stmt->execute();
          $stmt->bind_result($obj_id, $objective, $sequence);
          $i = 0;
          while ($stmt->fetch()) {
            $objsBySession[$thisModuleid][$identifier]['objectives'][$obj_id]['content'] = $objective;
            $objsBySession[$thisModuleid][$identifier]['objectives'][$obj_id]['id'] = $obj_id;
            $objsBySession[$thisModuleid][$identifier]['objectives'][$obj_id]['mapped'] = 0;
            $objsBySession[$thisModuleid][$identifier]['objectives'][$obj_id]['sequence'] = $sequence;
            $i++;
          }
          $stmt->close();
        }
      }
    }
  }

  //START:: get the currently mapped objectives in Rogo.
  $mapped = array();
  if ($questionID != '') {
    $rels = Relationship::find($db, array_keys($original_thisModuleid), $session, $paperID, explode(',', $questionID));
    if ($rels !== false and is_array($rels)) {
      foreach ($rels as $rel) {
        $mapped[$rel->get_objective_id()][] = $rel->get_question_id();
      }
    } else {
      echo "Error: " . $db->error;
    }
  }
  //END:: get the currently mapped objetives in Rogo

  // Get all the objetives
  foreach ($objsBySession as $moduleid => $sessions) {
  	if (!empty($sessions)) {
	    foreach ($sessions as $identifier => $tmp_session) {
	      if (isset($tmp_session['objectives'])) {
	        foreach ($tmp_session['objectives'] as $i => $objective) {
	          if (array_key_exists($objective['id'],$mapped)) {
	            $objsBySession[$moduleid][$identifier]['mapped'] = 1;
	            $objsBySession[$moduleid][$identifier]['objectives'][$i]['mapped'] = $mapped[$objective['id']];
	          } else {
	            $objsBySession[$moduleid][$identifier]['objectives'][$i]['mapped'] = 0;
	          }
	        }
	      }
	    }
  	}
  }

  // Check for none of the above
  if (isset($mapped[-1])) {
    $objsBySession['none_of_the_above']['mapped'] = 1;
  }

  return $objsBySession;
}

/**
 * This is function getObjectivesByMapping it gets a list of objectives with session information.
 *
 * @param array $thisModuleid module code
 * @param string $session A session in the format yyyy/yy
 * @param int $paperID
 * @param int $questionID
 * @param object $db
 * @return array This is the return value description
 *
 */
function getObjectivesByMapping($thisModuleid, $session, $paperID, $questionID, $db) {

  $objsBySession = getObjectives($thisModuleid, $session, $paperID, $questionID, $db);
  $objsByMapping = array();

  if (isset($objsBySession['none_of_the_above']['mapped']) and $objsBySession['none_of_the_above']['mapped'] == 1) {
    $objsByMapping['none_of_the_above']['mapped'] = 1;
  }
  if ($objsBySession !== 'error') {
    foreach($objsBySession as $moduleid => $session) {
      if (!empty($session)) {
        foreach ($session as $identifier => $sessionData) {
          if (is_array($sessionData) and isset($sessionData['objectives'])) {
            foreach ($sessionData['objectives'] as $id => $obj) {
              if ($obj['mapped'] != 0) {
                $objsByMapping[$moduleid][$id] = $obj;
                $objsByMapping[$moduleid][$id]['session'] = $sessionData;
                unset($objsByMapping[$moduleid][$id]['session']['objectives']);
                unset($objsByMapping[$moduleid][$id]['session']['mapped']);
              }
            }
          }
        }
      }
    }
  }

  return $objsByMapping;
}

function copyObjectives($objectives, $idMod, $module, $dest_session, $db) {
	// Get highest identifier - can't rely on time as we may loop in < 1 second
	$result = $db->prepare("SELECT MAX(identifier) AS largest FROM sessions WHERE idMod = ?");
	$result->bind_param('s', $idMod);
	$result->execute();
	$result->bind_result($largest);
	$i = 0;
	while ($result->fetch()) {
		$identifier = $largest + 1;
	}
	if ($identifier < 10) {
		$identifier = intval(time());
	}
	$result->close();

	// Loop through the sessions for the source year
	foreach ($objectives[$module] as $session) {
		$occurrence_parts = explode('/', $session['occurrance']);
		$occurrence_new = date('Y-m-d H:i:s', mktime(9, 0, 0, $occurrence_parts[1], $occurrence_parts[0], $occurrence_parts[2] + 1));

		// Save it
    $stmt = $db->prepare("INSERT INTO sessions(identifier, idMod, title, source_url, calendar_year, occurrence) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sissss', $identifier, $idMod, $session['title'], $session['source_url'], $dest_session, $occurrence_new);
		$stmt->execute();
		$err = $stmt->error;
    $stmt->close();

    if(!empty($err)) {
    	throw new Exception($err);
    }

    // Get starting point for objective ID
    $result = $db->prepare("SELECT MAX(obj_id) AS largest FROM objectives");
    $result->execute();
    $result->bind_result($largest);
    while ($result->fetch()) {
      $obj_id = $largest + 1;
    }
    if ($obj_id < 10) {
      $obj_id = 123;
    }
    $result->close();

		// Loop through the objectives for the original session
    if (isset($session['objectives'])) {
      foreach ($session['objectives'] as $objective) {
        // Save them against the new session identifier
        $stmt = $db->prepare("INSERT INTO objectives(obj_id, objective, idMod, identifier, calendar_year, sequence) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isissi', $obj_id, $objective['content'], $idMod, $identifier, $dest_session, $objective['sequence']);
        $stmt->execute();
        $err = $stmt->error;
        $stmt->close();

        if (!empty($err)) {
          throw new Exception($err);
        }

        $obj_id++;
      }
    }

		$identifier++;
	}
}

/**
 * Get all academic years for which there are mappings for the given modules
 * @param  array   $moduleID    Array of id => module codes
 * @param  mysqli  $db          Database link
 * @return array[string]        Array of academic years
 */
function getYearsForModules($api_name, $moduleID, $db) {
  $cal_years = array();
  $id_mod_in = implode(',', array_keys($moduleID));

  $sql = "SELECT distinct calendar_year FROM relationships WHERE calendar_year IS NOT NULL AND idMod IN ($id_mod_in) AND vle_api = ? ORDER BY calendar_year";
  $result = $db->prepare($sql);
  $result->bind_param('s', $api_name);
  $result->execute();
  $result->bind_result($calendar_year);
  $result->store_result();
  while($result->fetch()) {
    $cal_years[] = $calendar_year;
  }
  $result->close();

  return $cal_years;
}

?>
