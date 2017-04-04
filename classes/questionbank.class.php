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
* Question bank class.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
class QuestionBank {
  
  private $db;
  private $idMod;
  private $module_id;
  private $string;
  private $notice;
  private $bank_types = null;
  private $stats = null;

  /**
   * Called when the object is unserialised.
   */
  public function __wakeup() {
    // The serialised database object will be invalid,
    // this object should only be serialised during an error report,
    // so adding the current database connect seems like a waste of time.
    $this->db = null;
  }

  public function __construct($idMod, $moduleID, $string, $notice, $db) {
    $this->db     = $db;
    $this->string = $string;
    $this->idMod  = $idMod;
    $this->module_id = $moduleID;
    $this->notice = $notice;
    $this->yearutils = new yearutils($this->db);
  }
  
  public function get_categories($type) {
    if ($this->bank_types == null) {
      $this->load_categories($type);
    }
    
    return $this->bank_types;
  }
  
  public function get_stats($type) {
    return $this->stats;
  }
  
  private function get_keywords() {
    $keywords_array = array();

    $result = $this->db->prepare("SELECT keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID = modules.id AND modules.id = $this->idMod ORDER BY keyword");
    $result->execute();
    $result->bind_result($keyword, $keywordID);
    while ($result->fetch()) {
      $keywords_array[$keywordID] = $keyword;
    }
    $result->close();

    return $keywords_array;
  }

  private function load_categories($type) {
    switch($type) {
      case 'keyword':
        $this->load_stats($type);
        $keywords = $this->get_keywords();
        if (count($keywords) == 0) {    // Stop we have no keywords.
          echo $this->notice->info_strip($this->string['nokeywords'], 100) . "</div>\n</body>\n</html>\n";
          exit;
        }
        foreach ($keywords as $keywordID=>$keyword) {
          $this->bank_types[$keywordID] = $keyword;
        }
        break;
      case 'all':
      case 'type':
        $this->load_stats($type);
        $this->bank_types = array(
          'area' => $this->string['area'],
          'enhancedcalc' => $this->string['enhancedcalc'],
          'dichotomous' => $this->string['dichotomous'],
          'extmatch' => $this->string['extmatch'],
          'blank' => $this->string['blank'],
          'hotspot' => $this->string['hotspot'],
          'info' => $this->string['info'],
          'keyword_based' => $this->string['keyword_based'],
          'labelling' => $this->string['labelling'],
          'likert' => $this->string['likert'],
          'matrix' => $this->string['matrix'],
          'mcq'=> $this->string['mcq'],
          'mrq' => $this->string['mrq'],
          'random' => $this->string['random'],
          'rank' => $this->string['rank'],
          'sct' => $this->string['sct'],
          'textbox' => $this->string['textbox'],
          'true_false' => $this->string['true_false']
        );
        break;
      case 'status':
        $statuses = QuestionStatus::get_all_statuses($this->db, $this->string);
        $this->load_stats($type);
        $this->bank_types = array();
        foreach ($statuses as $status) {
          $status_name = $status->get_name();
          $this->bank_types[$status->id] = $status_name;
        }
        break;
      case 'bloom':
        $this->load_stats($type);
        $this->bank_types = array(
          'knowledge' => $this->string['knowledge'],
          'comprehension' => $this->string['comprehension'],
          'application' => $this->string['application'],
          'analysis' => $this->string['analysis'],
          'synthesis' => $this->string['synthesis'],
          'evaluation' => $this->string['evaluation']
        );
        break;
      case 'performance':
        $this->load_performance_stats();
        $this->bank_types = array(
            'veryeasy' => $this->string['veryeasy'],
            'easy' => $this->string['easy'],
            'moderate' => $this->string['moderate'],
            'hard' => $this->string['hard'],
            'veryhard' => $this->string['veryhard'],
            'highest' => $this->string['highest'],
            'high' => $this->string['high'],
            'intermediate' => $this->string['intermediate'],
            'low' => $this->string['low']
        );
        break;
      case 'objective':
        $this->load_stats($type);
        $this->bank_types = $this->get_outcomes();
        break;
    }
  }
  
  private function load_performance_stats() {
    $this->stats = array('veryeasy'=>0, 'easy'=>0, 'moderate'=>0, 'hard'=>0, 'veryhard'=>0, 'highest'=>0, 'high'=>0, 'intermediate'=>0, 'low'=>0);
    
    $status_array = QuestionStatus::get_all_statuses($this->db, $this->string, true);
    $retired_in = '-1,' . implode(',', QuestionStatus::get_retired_status_ids($status_array));
    
    $sql = 'SELECT DISTINCT p, d, questions.q_id FROM questions, questions_modules, performance_main, performance_details WHERE questions.q_id = questions_modules.q_id AND questions.q_id = performance_main.q_id AND performance_main.id = performance_details.perform_id AND idMod = ? AND deleted IS NULL AND status NOT IN (' . $retired_in . ')';
    $result = $this->db->prepare($sql);
    $result->bind_param('i', $this->idMod);
    $result->execute();
    $result->bind_result($p, $d, $q_id);
    while ($result->fetch()) {
      if ($p >= 80 and $p <= 100) {
        $this->stats['veryeasy']++;
      } elseif ($p >= 60 and $p < 80) {
        $this->stats['easy']++;
      } elseif ($p >= 40 and $p < 60) {
        $this->stats['moderate']++;
      } elseif ($p >= 20 and $p < 40) {
        $this->stats['hard']++;
      } elseif ($p >= 0 and $p < 20) {
        $this->stats['veryhard']++;
      }

      if ($d >= 35 and $d <=100) {
        $this->stats['highest']++;
      } elseif ($d >= 25 and $d < 35) {
        $this->stats['high']++;
      } elseif ($d >= 15 and $d < 25) {
        $this->stats['intermediate']++;
      } elseif ($d >= 0 and $d < 15) {
        $this->stats['low']++;
      }
      
    } 
    $result->close();
  }
  
  private function load_stats($type) {
    $this->stats = array();

    // Un-assigned papers should be limited to the owner.
    if ($this->idMod == 0) {
      $userObject = UserObject::get_instance();
      $ownerSQL = 'questions_modules.idMOD IS NULL AND ownerID = ' . $userObject->get_user_ID();
    } else {
      $ownerSQL = 'questions_modules.idMod =  ' . $this->idMod;
    }

    switch ($type) {
      case 'all':
      case 'type':
        $sql = 'SELECT COUNT(questions.q_id), q_type'
        . ' FROM questions LEFT JOIN questions_modules'
        . ' ON questions.q_id = questions_modules.q_id'
        . ' WHERE ' . $ownerSQL
        . ' AND deleted IS NULL AND status != -1 GROUP BY q_type';
        break;
      case 'status':
        $sql = 'SELECT COUNT(questions.q_id), name'
        . ' FROM (questions, question_statuses) LEFT JOIN questions_modules'
        . ' ON questions.q_id = questions_modules.q_id'
        . ' WHERE questions.status = question_statuses.id'
        . ' AND ' . $ownerSQL
        . ' AND deleted IS NULL GROUP BY status';
        break;
      case 'bloom':
        $sql = 'SELECT COUNT(questions.q_id), bloom'
        . ' FROM questions LEFT JOIN questions_modules'
        . ' ON questions.q_id = questions_modules.q_id'
        . ' WHERE ' . $ownerSQL
        . ' AND deleted IS NULL AND status != -1 GROUP BY bloom';
        break;
      case 'keyword':
        $sql = 'SELECT COUNT(questions.q_id), keywordID'
        . ' FROM (questions, keywords_question, keywords_user) LEFT JOIN questions_modules'
        . ' ON questions.q_id = questions_modules.q_id'
        . ' WHERE keywords_question.keywordID = keywords_user.id'
        . ' AND ' . $ownerSQL
        . ' AND questions.q_id = keywords_question.q_id'
        . ' AND deleted IS NULL AND status != -1 GROUP BY keywordID';
        break;      
      case 'objective':
        $vle_api_data = MappingUtils::get_vle_api($this->idMod, $this->yearutils->get_current_session(), $vle_api_cache, $this->db);
        $all_years = getYearsForModules($vle_api_data['api'], array($this->idMod => $this->module_id), $this->db);
        $all_years = implode("','", $all_years);
        
        $sql = "SELECT COUNT(questions.q_id), relationships.obj_id"
          . " FROM (questions, relationships) LEFT JOIN questions_modules"
          . " ON questions.q_id = questions_modules.q_id"
          . " WHERE questions.q_id = relationships.question_id"
          . " AND $ownerSQL "
          . " AND calendar_year IN ('{$all_years}')"
          . " AND deleted IS NULL AND status != -1 GROUP BY relationships.obj_id";
        break;
    }
    
    $result = $this->db->prepare($sql);
    $result->execute();
    $result->bind_result($number, $type);
    while ($result->fetch()) {
      $this->stats[$type] = $number;
    } 
    $result->close();
  }

  public function get_outcomes($ac_year = 'all', $vle_api_data = null) {
    $outcomes = array();
    $vle_api_cache = array();
    // Get the VLE API we're using currently
    if (is_null($vle_api_data)) {
      $vle_api_data = MappingUtils::get_vle_api($this->idMod, $this->yearutils->get_current_session(), $vle_api_cache, $this->db);
    }

    // Get years for which there are mappings for the current mapping source
    if ($ac_year == 'all') {
      $all_years = getYearsForModules($vle_api_data['api'], array($this->idMod => $this->module_id), $this->db);
    } else {
      $all_years = array($ac_year);
    }

    foreach ($all_years as $ac_year) {
      $obs = getObjectives(array($this->idMod => $this->module_id), $ac_year, '', '', $this->db);

      if (is_array($obs) and isset($obs[$this->module_id])) {
        foreach ($obs[$this->module_id] as $session) {
          if (isset($session['objectives'])) {
            foreach ($session['objectives'] as $objective) {
              if (isset($objective['guid'])) {
                $uid = $objective['guid'];
              } elseif (isset($objective['id'])) {
                $uid = $objective['id'];
              } else {
                $uid = '';
              }

              if ($uid != '') {
                // Build list of IDs but use the latest text
                $ids = (isset($outcomes[$uid])) ? $outcomes[$uid]['ids'] : array();
                $ids[] = $objective['id'];
                $outcomes[$uid] = array('ids' => $ids, 'label' => $objective['content']);
              }
            }
          }
        }
      }
    }

    if (count($outcomes) > 0) {
      uasort($outcomes, function($a, $b) {
        if ($a['label'] == $b['label']) {
          return 0;
        }
        return ($a['label'] < $b['label']) ? -1 : 1;
      });
    }

    // Filter local mappings to remove duplicates
    $last_id = -1;
    $last_text = '';
    if ($vle_api_data['api'] == '') {
      foreach ($outcomes as $id => $outcome) {
        if ($last_id != -1) {
          if ($outcome['label'] == $last_text) {
            $outcomes[$last_id]['ids'][] = $id;
            unset($outcomes[$id]);
          } else {
            $last_id = $id;
            $last_text = $outcome['label'];
          }
        } else {
          $last_id = $id;
        }
      }
    }

    return $outcomes;
  }
}
?>