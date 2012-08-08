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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

/**
 * This is function displayObjectivesMappingForm it displays objectives by session
 *
 * @param int $paper_id 
 * @param object $rogodb
 * 
 */
 
require_once $cfg_web_root . 'webServices/RestRequest.class';
require_once $cfg_web_root . 'classes/dateutils.class.php';

function render_objectives_mapping_form($db, $paper_id, &$string) {
  //get module and session form paper id
  $result = $db->prepare("SELECT moduleID, calendar_year, paper_type FROM properties WHERE property_id=?");
  $result->bind_param('i', $paper_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($modules, $session, $paper_type);
  $result->fetch();
  if ($result->num_rows == 0) {
    return 0;
  }
  $result->close();

  if (isset($_GET['q_id'])) {
    $q_id = $_GET['q_id'];
  } else {
    $q_id = '';
  }
  
  $objsByModuleSession = getObjectives($modules, $session, $paper_id, $q_id, $db);

  if (isset($objsByModuleSession['none_of_the_above'])) {
    $none_of_the_above = $objsByModuleSession['none_of_the_above'];
    unset($objsByModuleSession['none_of_the_above']);
  }
  
  $html = '';
  
  foreach ($objsByModuleSession as $moduleid => $objsBySession) {
    $moduleid_forform = str_replace(' ', '_', $moduleid);
    $html .= <<< HTML
        <h2>{$moduleid} {$string['objectives']}</h2>
        
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
        $heading .= '<span class="note">' . $class['occurrance'] . '</span>';
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
          $html .= <<< HTML
        				<li><input id="objective{$objcount}" name="{$moduleid_forform}_objectives[]" value="{$objectives['id']}" type="checkbox"{$checked} /> <label for="objective{$objcount}">{$objectives['content']}</label></li>
  
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
    		<input name="{$moduleid_forform}_session" type="hidden" value="{$session}"/>
    		<input name="{$moduleid_forform}_old_mappings" type="hidden" value="{$old_mappings}" />

HTML;
  }
  
  $checked = (isset($none_of_the_above['mapped'])) ? ' checked="checked"' : '';
  $nota_message = sprintf($string['noneabovemsg'], $modules);
  $html .= <<< HTML
        <p class="warning"><input value="-1" id="none_of_the_above" name="none_of_the_above" type="checkbox"{$checked} /><label for="none_of_the_above"><strong>{$string['noneabove']}</strong></label><br />{$nota_message}</p>
        <input id="objective_modules" name="objective_modules" type="hidden" value="{$modules}" />
        
HTML;
    
  return $html;
}

function displayObjectivesMappingForm($paper_id, $rogodb, $cfg_root_path) {
  //get module and session form paper id
  $result = $rogodb->prepare("SELECT moduleID, calendar_year, paper_type FROM properties WHERE property_id=?");
  $result->bind_param('i', $paper_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($modules, $session, $paper_type);
  $result->fetch();
  if ($result->num_rows == 0) {
    return 0;
  }
  $result->close();

  if (isset($_GET['q_id'])) {
    $q_id = $_GET['q_id'];
  } else {
    $q_id = '';
  }
  
  $objsByModuleSession = getObjectives($modules, $session, $paper_id, $q_id, $rogodb);

  if (isset($objsByModuleSession['none_of_the_above'])) {
    $none_of_the_above = $objsByModuleSession['none_of_the_above'];
    unset($objsByModuleSession['none_of_the_above']);
  }
  
  $html = '';
  foreach ($objsByModuleSession as $moduleid => $objsBySession) {
    $html .= "<h1>$moduleid Objectives</h1>";
    $old_mappings = '';
    $objcount = 0;
    foreach($objsBySession as $id => $class) {
      if (isset($class['objectives']) and count($class['objectives']) > 0) {
        $html .= "<div style=\"cursor:pointer; color:#316AC5; font-weight:bold; margin-top:6px\" onclick=\"showHide('$moduleid$id')\">";
        if($class['mapped'] == 0) {
          $html .= "<img id=\"img_$moduleid$id\" src='$cfg_root_path/artwork/plus.png' />&nbsp;";
          $display = 'none';
        } else {
          $html .= "<img id=\"img_$moduleid$id\" src='$cfg_root_path/artwork/minus.png' />&nbsp;";
          $display = 'block';
        }

        if ($class['class_code'] != '') {
          $html .= $class['class_code'] . ' - ';
        }
        $html .= $class['title'];
        if ($class['occurrance'] != '') {
          $html .= '&nbsp;<span style="color:#808080; font-size:70%; font-weight:normal">' . $class['occurrance'] . '</span>';
        }
        $html .= "</div>\n<div id=\"div_$moduleid$id\" style=\"display:$display; font-size:90%\">\n";
        //write out the objetives for this session
        foreach ($class['objectives'] as $objectives) {
          if ($objectives['mapped'] != 0) {
            $checked = 'checked';
            $old_mappings .= $objectives['id'] . ',';
          } else {
            $checked = '';
          }
          $html .= "\t<div style=\"margin-left:15px\"><input id=\"" . $moduleid . "obj$objcount\" name=\"" . $moduleid . "obj$objcount\" type=\"checkbox\" value=\"" . $objectives['id'] . "\" $checked />&nbsp;" . $objectives['content'] . "</div>\n";
          $objcount++;
        }
        $html .= "</div>\n";
      } else {
        $html .= "<div style=\"color:#808080; font-weight:bold; margin-top:6px\"><img src='$cfg_root_path/artwork/grey_plus.gif' />&nbsp;";
        if($class['class_code'] != '') {
          $html .= $class['class_code'] . ' - ';
        }
        $html .= $class['title'];
        if ($class['occurrance'] != '') {
          $html .= '&nbsp;<span style="color:#808080; font-size:70%; font-weight:normal">' . $class['occurrance'] . '</span>';
        }
        $html .= "</div>\n";
      }
    }
    $html .= "<input id=\"" . $moduleid . "_objectiveCount\" name=\"" . $moduleid . "_objectiveCount\" type=\"hidden\" value=\"$objcount\"/>\n";
    $html .= "<input name=\"" . $moduleid . "_session\" type=\"hidden\" value=\"$session\"/>\n";
    $html .= "<input name=\"" . $moduleid . "_old_mappings\" type=\"hidden\" value=\"$old_mappings\"/>\n";
  }

  //display none of the above !
  $html .= "<hr noshade=\"noshade\" style=\"border:0px; height:1px; text-align:left; background-color:#C0C0C0; color:#C0C0C0; width:40%\" />";
  $html .= "<div style=\"color:#800000\">";
  $checked = '';
  if(isset($none_of_the_above['mapped'])) $checked = ' checked';
  $html .= "<input type=\"checkbox\" value =\"-1\" name=\"none_of_the_above\"$checked /> <strong>None of the Above</strong><br />Check here if the current question does not match any of the above objectives from $modules.";
  $html .= "</div>";


  $html .= "<input id=\"paperID\" name=\"paperID\" type=\"hidden\" value=\"$paper_id\" />\n";
  if (isset($_GET['q_id'])) {
    $html .= "<input id=\"questionID\" name=\"questionID\" type=\"hidden\" value=\"" . $_GET['q_id'] . "\" />\n";
  } else {
    $html .= "<input id=\"questionID\" name=\"questionID\" type=\"hidden\" value=\"\" />\n";
  }
  $html .= "<input id=\"modules\" name=\"modules\" type=\"hidden\" value=\"$modules\" />\n";
  return $html;
}


/**
 * This is function saveObjMappings it saves mappings to the relationships table in the Rogo database
 *
 * @param int $paper_id 
 * @param int $question_id 
 * @param object $rogodb 
 * 
 */
function save_objective_mappings($db, $modules, $paper_id, $question_id) {
  if ($paper_id != '' and substr($paper_id,0,5) != 'find:') {
    $modules = explode(',',$modules);
    $old_mappings = '';
    $new_mappings = '';

    $sql = array();

    foreach ($modules as $moduleid) {
      $moduleid_forform = str_replace(' ', '_', $moduleid);
      if(isset($_POST[$moduleid_forform . '_session'])) {
        $session = $_POST[$moduleid_forform . '_session'];
        $old_mappings = $_POST[$moduleid_forform . '_old_mappings'];
        if(isset($_POST[$moduleid_forform . '_objectives'])) {
          foreach ($_POST[$moduleid_forform . '_objectives'] as $objective_id) {
            $sql[] = "INSERT INTO relationships VALUES (NULL, '$moduleid', $paper_id, $question_id, $objective_id, '$session')";
            $new_mappings .= $objective_id . ',';
          }
        }
    
        //check for none of the above
        if (isset($_POST['none_of_the_above']) and $_POST['none_of_the_above'] == -1 AND count($sql) == 0) {
          $sql[] = "INSERT INTO relationships VALUES (NULL, '$moduleid', $paper_id, $question_id, -1, '$session')";
          $new_mappings .= '-1,';
        } else {
          $old_mappings .= '-1,';
        }
    
        if ($new_mappings != $old_mappings) {
          // prepend the delete
          if ($old_mappings != '') {
            $delete = "DELETE FROM relationships WHERE module_id = '$moduleid' AND paper_id = $paper_id AND question_id = $question_id AND calendar_year = '$session'";
            array_unshift($sql,$delete);
          }
          foreach($sql as $query) {
            if (!$result = $db->query($query)) {
              echo "Question Mapping Error #1 " . $db->error . "<br/> $query <br/>";
            }
          }
        }
      }
    }
  }
}

/**
 * This is function saveObjMappings it saves mappings to the relationships table in the Rogo database
 *
 * @param int $paper_id 
 * @param int $question_id 
 * @param object $rogodb 
 * 
 */
function saveObjMappings($paper_id, $question_id, $rogodb) {
  if (isset($_POST['modules']) and $paper_id != '' and substr($paper_id,0,5) != 'find:') {
    $modules = $_POST['modules'];
    $modules = explode(',',$modules);
    foreach ($modules as $moduleid) {
      if (isset($_POST[$moduleid . '_session'])) {
        $session = $_POST[$moduleid . '_session'];
        $objectiveCount = $_POST[$moduleid . '_objectiveCount'];
        $old_mappings = $_POST[$moduleid . '_old_mappings'];
        $sql = array();
        $new_mappings = '';
        for ($i = 0; $i < $objectiveCount; $i++ ) {
          if (isset($_POST[$moduleid . 'obj' . $i])) {
            $sql[] = "INSERT INTO relationships VALUES (NULL,'$moduleid',$paper_id,$question_id," . $_POST[$moduleid . 'obj' . $i] . ",'$session')";
            $new_mappings .= $_POST[$moduleid . 'obj' . $i] . ',';
          }
        }
        //check for none of the above
        if (isset($_POST['none_of_the_above']) and $_POST['none_of_the_above'] == -1 AND count($sql) == 0) {
          $sql[] = "INSERT INTO relationships VALUES (NULL,'$moduleid',$paper_id,$question_id,-1,'$session')";
          $new_mappings .= '-1,';
        } else {
          $old_mappings .= '-1,';
        }

        if ($new_mappings != $old_mappings) {
          //preappend the delete
          if ($old_mappings != '') {
            $delete = "DELETE FROM relationships WHERE module_id = '$moduleid' AND paper_id = $paper_id AND question_id = $question_id AND calendar_year = '$session'";
            array_unshift($sql,$delete);
          }
          foreach($sql as $query) {
            if (!$result = $rogodb->query($query)) {
              echo "Question Mapping Error #1 " . $rogodb->error . "<br/> $query <br/>";
            }
          }
        }
      }
    }

  }
}


/**
 * This is function getObjectives this function get objevive to be mapped. It can Retrieve objectives from 
 * the internal database or an external application 
 *
 * @param string $thisModuleid module code
 * @param string $session A session in the format yyyy/yy
 * @param mixed $paperID 
 * @param mixed $questionID 
 * @param mixed $rogodb 
 * @return array an array of objectives and mappings
 *
 */
 
$objsBySession_cache = array();
$vle_api_cache = array(); // cashe the vle api to save some queries

function getObjectives($thisModuleid, $session, $paperID, $questionID, $rogodb, $scope='latest') {
  global $objsBySession_cache, $vle_api_cache, $cfg_short_date;
  $original_thisModuleid = $thisModuleid;
  $objsBySession = array();

  $module_list = explode(',',$thisModuleid);
  foreach ($module_list as $thisModuleid) {
    // Get which API to use to get the learning objectives
    if(!isset($vle_api_cache[$thisModuleid])) {
      $stmt = $rogodb->prepare("SELECT vle_api FROM modules WHERE moduleid=?");
      $stmt->bind_param('s',$thisModuleid);
      $stmt->execute();
      $stmt->bind_result($vle_api);
      $stmt->fetch();
      $stmt->close();
      $vle_api_cache[$thisModuleid] = $vle_api;
    } else {
      $vle_api = $vle_api_cache[$thisModuleid];
    }

    if ($vle_api == 'NLE') {
      // Get objectives from the NLE via REST request
      if (!isset($objsBySession_cache[$thisModuleid]) or !isset($objsBySession_cache[$thisModuleid][$session]) or count($objsBySession_cache[$thisModuleid][$session]) == 0) {
        $req = new RestRequest("http://www.nle.nottingham.ac.uk/webServices/getObjectives/$thisModuleid/$session");
        $split_username = explode('_', $_SERVER['PHP_AUTH_USER']);
        $req->setUsername($split_username[0]);
        $req->setPassword($_SERVER['PHP_AUTH_PW']);
        $req->execute();
        $resp = $req->getResponseBody();
        if ($resp != '') {
          $objsBySession_cache[$thisModuleid][$session] = $resp;
        }
      }
      if (isset($objsBySession_cache[$thisModuleid][$session][$thisModuleid])) {
        $objsBySession[$thisModuleid] = $objsBySession_cache[$thisModuleid][$session][$thisModuleid];
      }
    } else {
      // Use TouchStone's internally stored objectves
      if ($scope == 'all') {
      	$sql = "SELECT sess_id, identifier, moduleID, title, source_url, calendar_year, DATE_FORMAT(occurrence,'$cfg_short_date') AS occurrence FROM sessions WHERE moduleID=? ORDER BY calendar_year, sess_id";
      	$stmt = $rogodb->prepare($sql);
        $stmt->bind_param('s', $thisModuleid);
      } elseif ($session == '' and $scope == 'latest') {
        $sql = "SELECT sess_id, identifier, moduleID, title, source_url, calendar_year, DATE_FORMAT(occurrence,'$cfg_short_date') AS occurrence FROM sessions WHERE moduleID=? AND calendar_year=(SELECT MAX(calendar_year) FROM objectives) ORDER BY sess_id";
        $stmt = $rogodb->prepare($sql);
        $stmt->bind_param('s', $thisModuleid);
      } else {
        $sql = "SELECT sess_id, identifier, moduleID, title, source_url, calendar_year, DATE_FORMAT(occurrence,'$cfg_short_date') AS occurrence FROM sessions WHERE moduleID=? AND calendar_year=? ORDER BY sess_id";
        $stmt = $rogodb->prepare($sql);
        $stmt->bind_param('ss', $thisModuleid, $session);
      }
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($sess_id, $identifier, $moduleID, $title, $source_url, $calendar_year, $occurrence);
      while ($stmt->fetch()) {
        $objsBySession[$thisModuleid][$identifier]['class_code'] = '';
        $objsBySession[$thisModuleid][$identifier]['title'] = $title;
        $objsBySession[$thisModuleid][$identifier]['occurrance'] = $occurrence;
        $objsBySession[$thisModuleid][$identifier]['mapped'] = 0;
        $objsBySession[$thisModuleid][$identifier]['identifier'] = $identifier;
        $objsBySession[$thisModuleid][$identifier]['source_url'] = $source_url;
        $objsBySession[$thisModuleid][$identifier]['calendar_year'] = $calendar_year;
        $objsBySession[$thisModuleid][$identifier]['VLE'] = 'TouchStone';
      }
      $stmt->close();
      if (isset($objsBySession[$thisModuleid]) and count($objsBySession[$thisModuleid]) > 0) {
        //GET objs from Touchstone
        foreach ($objsBySession[$thisModuleid] as $identifier => $sess) {
          $sql = "SELECT obj_id,objective,sequence FROM objectives WHERE identifier=? AND calendar_year=? ORDER BY sequence";
          $stmt = $rogodb->prepare($sql);
          $stmt->bind_param('ss',$identifier,$sess['calendar_year']);
          $stmt->execute();
          //$stmt->store_result();
          $stmt->bind_result($obj_id, $objective, $sequence);
          $i = 0;
          while ($row = $stmt->fetch()) {
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

  //START:: get the currently mapped objetives in Rogo.
  $mapped = array();
  if ($questionID != '') {
    $thisModuleid = str_replace(',','|',$original_thisModuleid);
    if ($result = $rogodb->prepare("SELECT obj_id, question_id FROM relationships WHERE module_id REGEXP ? AND paper_id=? AND calendar_year=? AND question_id IN ($questionID)")) {
      $result->bind_param('sis', $thisModuleid, $paperID, $session);
      $result->execute();
      $result->bind_result($obj_id, $question_id);
      while ($row = $result->fetch()) {
        $mapped[$obj_id][] = $question_id;
      }
      $result->close();
    } else {
      echo "Error: " . $rogodb->error;
    }
  }
  //END:: get the currently mapped objetives in Rogo
  
  //get all the objetives
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

  //check for none of the above
  if (isset($mapped[-1])) {
    $objsBySession['none_of_the_above']['mapped'] = 1;
  }

  return $objsBySession;
}

/**
 * This is function getObjectivesByMapping it gets a list of objectives with session information.
 *
 * @param syring $thisModuleid module code
 * @param string $session A session in the format yyyy/yy
 * @param int $paperID 
 * @param int $questionID 
 * @param object $rogodb 
 * @return array This is the return value description
 *
 */
function getObjectivesByMapping($thisModuleid, $session, $paperID, $questionID, $rogodb) {

  $objsBySession = getObjectives($thisModuleid, $session, $paperID, $questionID, $rogodb);
  $objsByMapping = array();

  if (isset($objsBySession['none_of_the_above']['mapped']) and $objsBySession['none_of_the_above']['mapped'] == 1) {
    $objsByMapping['none_of_the_above']['mapped'] = 1;
  }

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
  
  return $objsByMapping;
}

function copyObjectives($objectives, $module, $dest_session, $rogodb) {
	// Get highest identifier - can't rely on time as we may loop in < 1 second
	$result = $rogodb->prepare("SELECT MAX(identifier) AS largest FROM sessions WHERE moduleID = ?");
	$result->bind_param('s', $module);
	$result->execute();
	$result->bind_result($largest);
	$i = 0;
	while ($row = $result->fetch()) {
		$identifier = $largest + 1;
	}
	if($identifier < 10) {
		$identifier = intval(time());
	}
	$result->close();
    
	// Loop through the sessions for the source year
	foreach($objectives[$module] as $session) {
		$occurrence_parts = explode('/', $session['occurrance']);
		$occurrence_new = date('Y-m-d H:i:s', mktime(9, 0, 0, $occurrence_parts[1], $occurrence_parts[0], $occurrence_parts[2] + 1));
		
		// Save it
    $stmt = $rogodb->prepare("INSERT INTO sessions(identifier, moduleID, title, source_url, calendar_year, occurrence) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('ssssss', $identifier, $module, $session['title'], $session['source_url'], $dest_session, $occurrence_new);
		$stmt->execute();
		$err = $stmt->error;
    $stmt->close();
    
    if(!empty($err)) {
    	throw new Exception($err);
    }
    
    // Get starting point for objective ID
    $result = $rogodb->prepare("SELECT MAX(obj_id) AS largest FROM objectives");
    $result->execute();
    $result->bind_result($largest);
    while ($row = $result->fetch()) {
      $obj_id = $largest + 1;
    }
    if ($obj_id < 10) {
      $obj_id = 123;
    }
    $result->close();
    
		// Loop through the objectives for the original session
		foreach ($session['objectives'] as $objective) {
			// Save them against the new session identifier
			$stmt = $rogodb->prepare("INSERT INTO objectives(obj_id, objective, moduleID, identifier, calendar_year, sequence) VALUES (?,?,?,?,?,?)");
			$stmt->bind_param('issssi', $obj_id, $objective['content'], $module, $identifier, $dest_session, $objective['sequence']);
			$stmt->execute();
			$err = $stmt->error;
			$stmt->close();

			if (!empty($err)) {
	    	throw new Exception($err);
	    }
			
			$obj_id++;
    }
    
		$identifier++;
	}
}

?>
