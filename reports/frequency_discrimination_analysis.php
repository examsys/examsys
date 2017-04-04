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
* The Frequency & Discimination Analysis is used to look at the number of students that have selected each option in a question
* and how well it disciminates between the upper and lower 27% of students.  These values help to identify how well the question
* is working.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/media.inc';
require_once '../include/errors.php';
require_once '../include/sort.inc';
require_once '../include/errors.php';

require_once '../plugins/questions/enhancedcalc/enhancedcalc.class.php';

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.php';
require_once '../lang/' . $language . '/question/edit/area.php';
require_once '../lang/' . $language . '/paper/hotspot_answer.php';
require_once '../lang/' . $language . '/paper/hotspot_question.php';
require_once '../lang/' . $language . '/paper/label_answer.php';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

$paperID    = check_var('paperID', 'GET', true, false, true);
$startdate  = check_var('startdate', 'GET', true, false, true);
$enddate    = check_var('enddate', 'GET', true, false, true);

set_time_limit(0);

// Get some paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
$gradebook = new gradebook($mysqli);
$graded = $gradebook->paper_graded($paperID);
    
$stop_words = array('-'=>'-','a'=>'a','about'=>'about','above'=>'above','across'=>'across','after'=>'after','again'=>'again','against'=>'against','all'=>'all','almost'=>'almost','alone'=>'alone','along'=>'along','already'=>'already','also'=>'also','although'=>'although','always'=>'always','among'=>'among','an'=>'an','and'=>'and','another'=>'another','any'=>'any','anybody'=>'anybody','anyone'=>'anyone','anything'=>'anything','anywhere'=>'anywhere','are'=>'are','area'=>'area','areas'=>'areas','around'=>'around','as'=>'as','ask'=>'ask','asked'=>'asked','asking'=>'asking','asks'=>'asks','at'=>'at','away'=>'away','b'=>'b','back'=>'back','backed'=>'backed','backing'=>'backing','backs'=>'backs','be'=>'be','became'=>'became','because'=>'because','become'=>'become','becomes'=>'becomes','been'=>'been','before'=>'before','began'=>'began','behind'=>'behind','being'=>'being','beings'=>'beings','best'=>'best','better'=>'better','between'=>'between','big'=>'big','both'=>'both','but'=>'but','by'=>'by','c'=>'c','came'=>'came','can'=>'can','cannot'=>'cannot','case'=>'case','cases'=>'cases','certain'=>'certain','certainly'=>'certainly','clear'=>'clear','clearly'=>'clearly','come'=>'come','could'=>'could','d'=>'d','did'=>'did','differ'=>'differ','different'=>'different','differently'=>'differently','do'=>'do','does'=>'does','done'=>'done','down'=>'down','downed'=>'downed','downing'=>'downing','downs'=>'downs','during'=>'during','e'=>'e','each'=>'each','early'=>'early','either'=>'either','end'=>'end','ended'=>'ended','ending'=>'ending','ends'=>'ends','enough'=>'enough','even'=>'even','evenly'=>'evenly','ever'=>'ever','every'=>'every','everybody'=>'everybody','everyone'=>'everyone','everything'=>'everything','everywhere'=>'everywhere','f'=>'f','face'=>'face','faces'=>'faces','fact'=>'fact','facts'=>'facts','far'=>'far','felt'=>'felt','few'=>'few','find'=>'find','finds'=>'finds','first'=>'first','for'=>'for','four'=>'four','from'=>'from','full'=>'full','fully'=>'fully','further'=>'further','furthered'=>'furthered','furthering'=>'furthering','furthers'=>'furthers','g'=>'g','gave'=>'gave','general'=>'general','generally'=>'generally','get'=>'get','gets'=>'gets','give'=>'give','given'=>'given','gives'=>'gives','go'=>'go','going'=>'going','good'=>'good','goods'=>'goods','got'=>'got','great'=>'great','greater'=>'greater','greatest'=>'greatest','group'=>'group','grouped'=>'grouped','grouping'=>'grouping','groups'=>'groups','h'=>'h','had'=>'had','has'=>'has','have'=>'have','having'=>'having','he'=>'he','her'=>'her','here'=>'here','herself'=>'herself','high'=>'high','higher'=>'higher','highest'=>'highest','him'=>'him','himself'=>'himself','his'=>'his','how'=>'how','however'=>'however','i'=>'i','if'=>'if','important'=>'important','in'=>'in','interest'=>'interest','interested'=>'interested','interesting'=>'interesting','interests'=>'interests','into'=>'into','is'=>'is','it'=>'it','its'=>'its','itself'=>'itself','j'=>'j','just'=>'just','k'=>'k','keep'=>'keep','keeps'=>'keeps','kind'=>'kind','knew'=>'knew','know'=>'know','known'=>'known','knows'=>'knows','l'=>'l','large'=>'large','largely'=>'largely','last'=>'last','later'=>'later','latest'=>'latest','least'=>'least','less'=>'less','let'=>'let','lets'=>'lets','like'=>'like','likely'=>'likely','long'=>'long','longer'=>'longer','longest'=>'longest','m'=>'m','made'=>'made','make'=>'make','making'=>'making','man'=>'man','many'=>'many','may'=>'may','me'=>'me','member'=>'member','members'=>'members','men'=>'men','might'=>'might','more'=>'more','most'=>'most','mostly'=>'mostly','mr'=>'mr','mrs'=>'mrs','much'=>'much','must'=>'must','my'=>'my','myself'=>'myself','n'=>'n','necessary'=>'necessary','need'=>'need','needed'=>'needed','needing'=>'needing','needs'=>'needs','never'=>'never','new'=>'new','newer'=>'newer','newest'=>'newest','next'=>'next','no'=>'no','nobody'=>'nobody','non'=>'non','noone'=>'noone','not'=>'not','nothing'=>'nothing','now'=>'now','nowhere'=>'nowhere','number'=>'number','numbers'=>'numbers','of'=>'o','of'=>'of','off'=>'off','often'=>'often','old'=>'old','older'=>'older','oldest'=>'oldest','on'=>'on','once'=>'once','one'=>'one','only'=>'only','open'=>'open','opened'=>'opened','opening'=>'opening','opens'=>'opens','or'=>'or','order'=>'order','ordered'=>'ordered','ordering'=>'ordering','orders'=>'orders','other'=>'other','others'=>'others','our'=>'our','out'=>'out','over'=>'over','p'=>'p','part'=>'part','parted'=>'parted','parting'=>'parting','parts'=>'parts','per'=>'per','perhaps'=>'perhaps','place'=>'place','places'=>'places','point'=>'point','pointed'=>'pointed','pointing'=>'pointing','points'=>'points','possible'=>'possible','present'=>'present','presented'=>'presented','presenting'=>'presenting','presents'=>'presents','problem'=>'problem','problems'=>'problems','put'=>'put','puts'=>'puts','q'=>'q','quite'=>'quite','r'=>'r','rather'=>'rather','really'=>'really','right'=>'right','room'=>'room','rooms'=>'rooms','s'=>'s','said'=>'said','same'=>'same','saw'=>'saw','say'=>'say','says'=>'says','second'=>'second','seconds'=>'seconds','see'=>'see','seem'=>'seem','seemed'=>'seemed','seeming'=>'seeming','seems'=>'seems','sees'=>'sees','several'=>'several','shall'=>'shall','she'=>'she','should'=>'should','show'=>'show','showed'=>'showed','showing'=>'showing','shows'=>'shows','side'=>'side','sides'=>'sides','since'=>'since','small'=>'small','smaller'=>'smaller','smallest'=>'smallest','so'=>'so','some'=>'some','somebody'=>'somebody','someone'=>'someone','something'=>'something','somewhere'=>'somewhere','state'=>'state','states'=>'states','still'=>'still','such'=>'such','sure'=>'sure','t'=>'t','take'=>'take','taken'=>'taken','than'=>'than','that'=>'that','the'=>'the','their'=>'their','them'=>'them','then'=>'then','there'=>'there','therefore'=>'therefore','these'=>'these','they'=>'they','thing'=>'thing','things'=>'things','think'=>'think','thinks'=>'thinks','this'=>'this','those'=>'those','though'=>'though','thought'=>'thought','thoughts'=>'thoughts','three'=>'three','through'=>'through','thus'=>'thus','to'=>'to','today'=>'today','together'=>'together','too'=>'too','took'=>'took','toward'=>'toward','turn'=>'turn','turned'=>'turned','turning'=>'turning','turns'=>'turns','two'=>'two','u'=>'u','under'=>'under','until'=>'until','up'=>'up','upon'=>'upon','us'=>'us','use'=>'use','used'=>'used','uses'=>'uses','v'=>'v','very'=>'very','w'=>'w','want'=>'want','wanted'=>'wanted','wanting'=>'wanting','wants'=>'wants','was'=>'was','way'=>'way','ways'=>'ways','we'=>'we','well'=>'well','wells'=>'wells','went'=>'went','were'=>'were','what'=>'what','when'=>'when','where'=>'where','whether'=>'whether','which'=>'which','while'=>'while','who'=>'who','whole'=>'whole','whose'=>'whose','why'=>'why','will'=>'will','with'=>'with','within'=>'within','without'=>'without','work'=>'work','worked'=>'worked','working'=>'working','works'=>'works','would'=>'would','x'=>'x','y'=>'y','year'=>'year','years'=>'years','yet'=>'yet','you'=>'you','young'=>'young','younger'=>'younger','youngest'=>'youngest','your'=>'your','yours'=>'yours','z'=>'z');
$pstats_array = array();
$dstats_array = array();

$cohort_percent = $_GET['percent'];
if ($cohort_percent == 100) $cohort_percent = 27;
$pstats = array('ve'=>0,'e'=>0,'m'=>0,'h'=>0,'vh'=>0);
$dstats = array('highest'=>0,'high'=>0,'intermediate'=>0,'low'=>0);

function pStats($value, $qid, $part_no) {
  global $pstats, $string, $pstats_array;

  $html = '';

  if ($value >= 0.8) {
      $pstats['ve']++;
    } elseif ($value >= 0.6 and $value < 0.8) {
      $pstats['e']++;
    } elseif ($value >= 0.4 and $value < 0.6) {
      $pstats['m']++;
    } elseif ($value >= 0.2 and $value < 0.4) {
      $pstats['h']++;
    } else {
      $pstats['vh']++;
    }
    if (isset($pstats['total'])) {
      $pstats['total'] += $value;
    } else {
    $pstats['total'] = $value;
  }
  if (isset($pstats['no'])) {
    $pstats['no']++;
    } else {
    $pstats['no'] = 1;
  }

  if ($value < 0.2) {
    $html = '<nobr><span style="color:#C00000">p=' . number_format($value,2) . '</span>&nbsp;<img src="../artwork/red_flag.png" width="14" height="14" alt="' . $string['warning1'] . '" class="in-exclusion" /></nobr>';
  } else {
    $html = 'p=' . number_format($value,2);
  }


  $pstats_array[$qid][$part_no] = round($value, 2) * 100;

  return $html;
}

function dStats($value, $qid, $part_no) {
  global $dstats, $string, $dstats_array;

  if ($value >= 0.35) {
    $dstats['highest']++;
  } elseif ($value >= 0.25 and $value < 0.35) {
    $dstats['high']++;
  } elseif ($value >= 0.15 and $value < 0.25) {
    $dstats['intermediate']++;
  } else {
    $dstats['low']++;
  }
  if (isset($dstats['total'])) {
      $dstats['total'] += $value;
    } else {
    $dstats['total'] = 1;
  }
  if (isset($dstats['no'])) {
      $dstats['no']++;
    } else {
    $dstats['no'] = 1;
  }
  if ($value < 0.15) {
    $html = '<nobr><span style="color:#C00000">d=' . number_format($value,2) . '</span>&nbsp;<img src="../artwork/red_flag.png" width="14" height="14" alt="' . $string['warning2'] . '" class="in-exclusion" /></nobr>';
  } else {
    $html = 'd=' . number_format($value,2);
  }

  $dstats_array[$qid][$part_no] = round($value, 2) * 100;

  return $html;
}

/**
 * Calculate the 'Pearson product-moment correlation coefficient'
 * https://rogo-eassessment-docs.atlassian.net/wiki/pages/viewpage.action?pageId=1049586
 * https://en.wikipedia.org/wiki/Pearson_product-moment_correlation_coefficient
 *
 * @param int $no_students The number of users in each cohort
 * @param array $top_log_q_id The answers for a single question given by the high performing users.
 * @param array $bottom_log_q_id The answers for a single question given by the low performing users.
 * @param mixed $i The identifier for the part of the question being analysed.
 * @param mixed $keys The correct answer, or an array of correct answers for the part.
 * @return string
 */
function calcDiscrimination($no_students, &$top_log_q_id, &$bottom_log_q_id, $i, $keys) {
  $top_key_value = 0;
  $bottom_key_value = 0;

  if (!is_array($keys)) $keys = array($keys);

  foreach($keys as $key) {
    if (isset($top_log_q_id[$i][$key])) {
      $top_key_value += $top_log_q_id[$i][$key];
    }
    if (isset($bottom_log_q_id[$i][$key])) {
      $bottom_key_value += $bottom_log_q_id[$i][$key];
    }
  }

  $top_ratio = $top_key_value / $no_students;
  $bottem_ratio = $bottom_key_value / $no_students;

  return number_format($top_ratio - $bottem_ratio,2);
}

function storeData(&$log_array, $qID, $answer, $q_type, $display, $settings, $mark, $totalpos, $stop_words, $analysis_type) {
	$configObject = Config::get_instance();

  if (!isset($log_array[$qID]['mark'])) $log_array[$qID]['mark'] = 0;
  if (!isset($log_array[$qID]['totalpos'])) $log_array[$qID]['totalpos'] = 0;

  switch ($q_type) {
    case 'area':
      if ($mark == $totalpos) {
        if (isset($log_array[$qID]['correct'])) {
          $log_array[$qID][1]['correct']++;
        } else {
          $log_array[$qID][1]['correct'] = 1;
        }
      } elseif ($mark < $totalpos and $mark > 0) {
        if (isset($log_array[$qID]['partial'])) {
          $log_array[$qID][1]['partial']++;
        } else {
          $log_array[$qID][1]['partial'] = 1;
        }
      } else {
        if (isset($log_array[$qID]['incorrect'])) {
          $log_array[$qID][1]['incorrect']++;
        } else {
          $log_array[$qID][1]['incorrect'] = 1;
        }
      }
      $log_array[$qID]['mark'] += $mark;
      $log_array[$qID]['totalpos'] += $totalpos;
      break;
    case 'blank':
      $tmp_answer_parts = array();
      $tmp_answer_parts = json_decode($answer);
      $i = 0;
      foreach ($tmp_answer_parts as $tmp_individual_answer) {
        $tmp_individual_answer = strtolower(trim($tmp_individual_answer));
        $i++;
        if ($tmp_individual_answer == 'u') {
          if (isset($log_array[$qID][$i]['u'])) {
            $log_array[$qID][$i]['u']++;
          } else {
            $log_array[$qID][$i]['u'] = 1;
          }
        } else {
          if (isset($log_array[$qID][$i][$tmp_individual_answer])) {
            $log_array[$qID][$i][$tmp_individual_answer]++;
          } else {
            $log_array[$qID][$i][$tmp_individual_answer] = 1;
          }
        }
      }
      break;
    case 'enhancedcalc':
      $configObj = Config::get_instance();
      $calc = new enhancedcalc($configObj);
      $calc->set_settings($settings);
      $calc->set_useranswer($answer);
			
      if ($calc->is_user_ans_correct() or $calc->is_user_ans_within_fullmark_tolerance()) {
        if (isset($log_array[$qID][1]['correct'])) {
          $log_array[$qID][1]['correct']++;
        } else {
          $log_array[$qID][1]['correct'] = 1;
        }
      } 
 
      $log_array[$qID]['mark'] += $mark;
      $log_array[$qID]['totalpos'] += $totalpos;
      break;
    case 'dichotomous':
    case 'true_false':
      $count_answer = strlen($answer);
      for ($i=0; $i<$count_answer; $i++) {
        $tmp_individual_answer = $answer{$i};
        if (isset($log_array[$qID][$i+1][$tmp_individual_answer])) {
          $log_array[$qID][$i+1][$tmp_individual_answer]++;
        } else {
          $log_array[$qID][$i+1][$tmp_individual_answer] = 1;
        }
      }
      break;
    case 'labelling':
      $tmp_first_split = explode(';', $answer);
      $tmp_second_split = explode('$', $tmp_first_split[1]);
      $count_tmp_second_split = count($tmp_second_split);
      for ($i=2; $i<=$count_tmp_second_split;$i+=4) {
        $x_coord = $tmp_second_split[$i-2];
        $y_coord = $tmp_second_split[$i-1];
        $tmp_individual_answer = trim($tmp_second_split[$i]);
        $element = $x_coord . 'x' . $y_coord;
        if (isset($log_array[$qID][$element][$tmp_individual_answer])) {
          $log_array[$qID][$element][$tmp_individual_answer]++;
        } else {
          $log_array[$qID][$element][$tmp_individual_answer] = 1;
        }
      }
      break;
    case 'hotspot':
      $layer_answers = explode('|', $answer);

      $layer = 1;
      foreach ($layer_answers as $layer_answer) {
        if (substr($layer_answer,0,1) == '1') {
          if (isset($log_array[$qID][$layer]['1'])) {
            $log_array[$qID][$layer]['1']++;
          } else {
            $log_array[$qID][$layer]['1'] = 1;
          }
        } elseif (substr($layer_answer,0,1) == '0') {
          if (isset($log_array[$qID][$layer]['0'])) {
            $log_array[$qID][$layer]['0']++;
          } else {
            $log_array[$qID][$layer]['0'] = 1;
          }
        } else {
          if (isset($log_array[$qID][$layer]['u'])) {
            $log_array[$qID][$layer]['u']++;
          } else {
            $log_array[$qID][$layer]['u'] = 1;
          }
        }
				if (!isset($log_array[$qID][$layer]['coords'])) {
					$log_array[$qID][$layer]['coords'] = $layer_answer;
				} else {
					$log_array[$qID][$layer]['coords'] .= ';' . $layer_answer;
				}				
        $layer++;
      }
      break;
    case 'mcq':
      if (isset($log_array[$qID][1][$answer])) {
        $log_array[$qID][1][$answer]++;
      } else {
        $log_array[$qID][1][$answer] = 1;
      }
      break;
    case 'mrq':
		  if ($answer == 'a') {
			  if (isset($log_array[$qID]['a'])) {
					$log_array[$qID]['a']++;
				} else {
					$log_array[$qID]['a'] = 1;
				}
			} else {
				$count_answer = strlen($answer);
				for ($i=0; $i<$count_answer; $i++) {
					$tmp_individual_answer = $answer{$i};
					if (isset($log_array[$qID][$i+1][$tmp_individual_answer])) {
						$log_array[$qID][$i+1][$tmp_individual_answer]++;
					} else {
						$log_array[$qID][$i+1][$tmp_individual_answer] = 1;
					}
				}
			}
      $log_array[$qID]['mark'] += $mark;
      $log_array[$qID]['totalpos'] += $totalpos;
      break;
    case 'extmatch':
      $tmp_answer_parts = array();
      $tmp_answer_parts = explode('|', $answer);
      $i = 0;
      foreach ($tmp_answer_parts as $tmp_individual_answer) {
        $i++;
        $tmp_sub_parts = array();
        $tmp_sub_parts = explode('$', $tmp_individual_answer);
        foreach ($tmp_sub_parts as $tmp_individual_part) {
          if ($tmp_individual_answer == 'u') {
            if (isset($log_array[$qID][$i]['u'])) {
              $log_array[$qID][$i]['u']++;
            } else {
              $log_array[$qID][$i]['u'] = 1;
            }
          } else {
            if (isset($log_array[$qID][$i][$tmp_individual_part])) {
              $log_array[$qID][$i][$tmp_individual_part]++;
            } else {
              $log_array[$qID][$i][$tmp_individual_part] = 1;
            }
          }
        }
      }
      break;
    case 'matrix':
      $tmp_answer_parts = explode('|', $answer);
      $count_tmp_answer_parts = count($tmp_answer_parts);
      for ($i=0; $i<$count_tmp_answer_parts; $i++) {
        $tmp_individual_answer = $tmp_answer_parts[$i];

        if ($tmp_individual_answer == 'u' or $tmp_individual_answer == '') {
          if (isset($log_array[$qID][$i+1]['u'])) {
            $log_array[$qID][$i+1]['u']++;
          } else {
            $log_array[$qID][$i+1]['u'] = 1;
          }
        } else {
          if (isset($log_array[$qID][$i+1][$tmp_individual_answer])) {
            $log_array[$qID][$i+1][$tmp_individual_answer]++;
          } else {
            $log_array[$qID][$i+1][$tmp_individual_answer] = 1;
          }
        }
      }
      break;
    case 'rank':
      $tmp_answer_parts = array();
      $tmp_answer_parts = explode(',', $answer);
      $i = 0;
      foreach ($tmp_answer_parts as $tmp_individual_answer) {
        if (isset($log_array[$qID][$i][$tmp_individual_answer])) {
          $log_array[$qID][$i][$tmp_individual_answer]++;
        } else {
          $log_array[$qID][$i][$tmp_individual_answer] = 1;
        }
        $i++;
      }
      if ($mark == $totalpos) {
        if (isset($log_array[$qID]['all_correct'])) {
          $log_array[$qID]['all_correct']++;
        } else {
          $log_array[$qID]['all_correct'] = 1;
        }
      }
      $log_array[$qID]['mark'] += $mark;
      $log_array[$qID]['totalpos'] += $totalpos;
      break;
    case 'sct':
      if (isset($log_array[$qID][1][$answer])){
        $log_array[$qID][1][$answer]++;
      } else {
        $log_array[$qID][1][$answer] = 1;
      }
      $log_array[$qID]['mark'] += $mark;
      $log_array[$qID]['totalpos'] += $totalpos;
      break;
    case 'textbox':
      if ($analysis_type == 'top' or $analysis_type == 'bottom') {
        $user_words = str_word_count($answer,1);
        foreach ($user_words as $word) {
          $word = strtolower($word);
          if (!isset($stop_words[$word])) {
            if (isset($log_array[$qID]['words'][$word])) {
              $log_array[$qID]['words'][$word]++;
            } else {
              $log_array[$qID]['words'][$word] = 1;
            }
          }
        }
      }

      if (isset($user_words)) {
        if (isset($log_array[$qID]['word_count'])){
          $log_array[$qID]['word_count'] += count($user_words);
        } else {
          $log_array[$qID]['word_count'] = count($user_words);
        }
      }
      $log_array[$qID]['mark'] += $mark;
      if (is_null($mark)) {
        if (isset($log_array[$qID]['unmarked'])) {
          $log_array[$qID]['unmarked']++;
        } else {
          $log_array[$qID]['unmarked'] = 1;
        }
      }
      $log_array[$qID]['totalpos'] += $totalpos;
      break;
    case 'likert':
      if (isset($log_array[$qID][1][$answer])) {
        $log_array[$qID][1][$answer]++;
      } else {
        $log_array[$qID][1][$answer] = 1;
      }
      break;
  }
}

$d_no = 0;
$d_total = 0;

if (isset($_POST['submit'])) {
  $old_exclusions = new Exclusion($paperID, $mysqli);
  $old_exclusions->load();

  // Clear the database of any past exclusions from the current paper.
  $old_exclusions->clear_all_exclusions();

  $old_q_id = 0;
  $old_status = '';
  $excluded = false;

  $new_exclusions = new Exclusion($paperID, $mysqli);
  for ($i=1; $i<=$_POST['question_no']; $i++) {
    $current_id = $_POST['id_' . $i];
    if ($current_id != $old_q_id) {
      if (strpos($old_status, '1') !== false) {
        $new_exclusions->add_exclusion($old_q_id, $old_status);
      }
      $old_status = '';
    }
    $old_status .= $_POST['status_' . $i];
    $old_q_id = $_POST['id_' . $i];
  }
  if (strpos($old_status, '1') !== false) {
    $new_exclusions->add_exclusion($old_q_id, $old_status);
  }

  $new_exclusions->load();
  
  if ($old_exclusions->excluded !== $new_exclusions->excluded) {
    $propertyObj->set_recache_marks(1);
    $propertyObj->save();
  }
  
  header("location: ../paper/details.php?paperID=" . $paperID . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder']);
  exit();
}

function excludeButton(&$buttonID, $question_id, $status, $parts, $marks) {
  $buttonID++;
  if (strpos($status,'1') !== false) {
    $html = "<input type=\"hidden\" name=\"status_" . $buttonID . "\" id=\"status_" . $buttonID . "\" value=\"";
    for ($i=0; $i<$marks; $i++) $html .= '1';
    $html .= "\" /><input type=\"hidden\" name=\"id_" . $buttonID . "\" value=\"$question_id\" /><input type=\"hidden\" name=\"marks_" . $buttonID . "\" value=\"$marks\" /><img src=\"../artwork/exclude_on.gif\" id=\"button_" . $buttonID . "\" style=\"cursor:pointer\" onclick=\"toggle('$buttonID',$parts,$marks)\" width=\"23\" height=\"22\" alt=\"Exclude\" class=\"in-exclusion\" />";
  } else {
    $html = "<input type=\"hidden\" name=\"status_" . $buttonID . "\" id=\"status_" . $buttonID . "\" value=\"";
    for ($i=0; $i<$marks; $i++) $html .= '0';
    $html .= "\" /><input type=\"hidden\" name=\"id_" . $buttonID . "\" value=\"$question_id\" /><input type=\"hidden\" name=\"marks_" . $buttonID . "\" value=\"$marks\" /><img src=\"../artwork/exclude_off.gif\" id=\"button_" . $buttonID . "\" style=\"cursor:pointer\" onclick=\"toggle('$buttonID',$parts,$marks)\" width=\"23\" height=\"22\" alt=\"Exclude\" class=\"in-exclusion\" />";
  }

  return $html;
}

function count_labels($correct) {
  $label_no = 0;

  $tmp_first_split = explode(';', $correct);
  $tmp_second_split = explode('|', $tmp_first_split[11]);
  foreach ($tmp_second_split as $ind_label) {
    $label_parts = explode('$', $ind_label);
    if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
      $label_no++;
    }
  }

  return $label_no;
}

function displayQuestion($exclusions, $q_no, $q_id, $theme, $scenario, $leadin, $q_type, $correct, $q_media, $q_media_width, $q_media_height, $options, $o_media, $bottom_log, $top_log, $freq_log, $correct_buf, $candidate_no, $score_method, $display_method, $themecolor, $std) {
  global $ex_no, $d_no, $d_total, $user_total, $language, $string;

	$configObject = Config::get_instance();
  $mediadirectory = rogo_directory::get_directory('media');

  if ($theme != '') echo "<tr><td colspan=\"2\"><h1 style=\"color:$themecolor\">$theme</h1></td></tr>\n";
  echo "<tr>\n";
  $tmp_std_array = (!empty($std)) ? explode(',', $std) : array();

  $parts = count($tmp_std_array);
  for ($i=0; $i<$parts; $i++) {
    $tmp_std_array[$i] = str_replace('exclude_', '', $tmp_std_array[$i]);
  }

  if ($q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'textbox') {
    if ($q_type == 'info') {
      echo "<td colspan=\"2\" style=\"padding-left:15px\">$leadin\n";
    } else {
      echo "<td class=\"q_no\">$q_no.&nbsp;</td><td><div";
      if ((($q_type == 'dichotomous' or $q_type == 'labelling' or $q_type == 'blank' or $q_type == 'hotspot') and $score_method == 'Mark per Question') or $q_type == 'flash') {
        echo ' id="q_' . ($ex_no+1) . '_1"';
        if ($exclusions->is_question_excluded($q_id)) {
          echo ' class="excluded"';
        }
      }
      echo '>';
      if (trim(str_replace('&nbsp;', '', $scenario)) != '') echo "$scenario<br /><br />\n";
      if ($q_type != 'hotspot' and $q_type != 'timedate' and $q_type != 'enhancedcalc' and $q_type != 'flash' and $q_type != 'area') echo "$leadin</div>\n";
      if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'flash' and $q_type != 'area') {
        echo "<p align=\"center\">" . display_media($q_media, $q_media_width, $q_media_height, '') . "</p>\n";
      }
      if ($q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'enhancedcalc' and $q_type != 'blank' and $q_type != 'flash' and $q_type != 'area') echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
    }

    switch ($q_type) {
      case 'random':
        echo "<div class=\"q_warning\">" . $string['randomwarning'] . "</div>\n";
        break;
      case 'keyword_based':
        echo "<div class=\"q_warning\">" . $string['keywordwarning'] . "</div>\n";
        break;
      case 'area':
        echo "<div id=\"q_" . ($ex_no+1) . "_1\"";
        if ($exclusions->is_question_excluded($q_id)) {
          echo ' class="excluded"';
        }
        echo ">$leadin\n";
        if ($exclusions->is_question_excluded($q_id)) {
          echo excludeButton($ex_no, $q_id, '1', 1, 1);
        } else {
          echo excludeButton($ex_no, $q_id, '0', 1, 1);
        }
        echo "</div><p>" . display_media($q_media, $q_media_width, $q_media_height, '#7F9DB9') . "</p>\n";
        if (!isset($freq_log[$q_id][1]['correct'])) $freq_log[$q_id][1]['correct'] = 0;
        if (!isset($freq_log[$q_id][1]['partial'])) $freq_log[$q_id][1]['partial'] = 0;
        if (!isset($freq_log[$q_id][1]['incorrect'])) $freq_log[$q_id][1]['incorrect'] = 0;

        if (!isset($top_log[$q_id][1]['correct'])) $top_log[$q_id][1]['correct'] = 0;
        if (!isset($top_log[$q_id][1]['partial'])) $top_log[$q_id][1]['partial'] = 0;
        if (!isset($top_log[$q_id][1]['incorrect'])) $top_log[$q_id][1]['incorrect'] = 0;

        if (!isset($bottom_log[$q_id][1]['correct'])) $bottom_log[$q_id][1]['correct'] = 0;
        if (!isset($bottom_log[$q_id][1]['partial'])) $bottom_log[$q_id][1]['partial'] = 0;
        if (!isset($bottom_log[$q_id][1]['incorrect'])) $bottom_log[$q_id][1]['incorrect'] = 0;

        echo "<table>\n";
        $t = ($user_total != 0) ? number_format(($freq_log[$q_id][1]['correct']/$user_total)*100,0) : 0;
        $u = ($candidate_no != 0) ? number_format(($top_log[$q_id][1]['correct']/$candidate_no)*100,0) : 0;
        $l = ($candidate_no != 0) ? number_format(($bottom_log[$q_id][1]['correct']/$candidate_no)*100,0) : 0;
        echo "<tr style=\"font-weight:bold\"><td>t=$t%</td><td>u=$u%</td><td>l=$l%</td><td>". $string['FullMarks'] . "</td></tr>\n";

        $partial_t = ($user_total != 0) ? number_format(($freq_log[$q_id][1]['partial']/$user_total)*100,0) : 0;
        $partial_u = ($candidate_no != 0) ? number_format(($top_log[$q_id][1]['partial']/$candidate_no)*100,0) : 0;
        $partial_l = ($candidate_no != 0) ? number_format(($bottom_log[$q_id][1]['partial']/$candidate_no)*100,0) : 0;
        echo "<tr><td>t=$partial_t%</td><td>u=$partial_u%</td><td>l=$partial_l%</td><td>". $string['PartialMarks'] . "</td></tr>\n";

        $incorrect_t = ($user_total != 0) ? number_format(($freq_log[$q_id][1]['incorrect']/$user_total)*100,0) : 0;
        $incorrect_u = ($candidate_no != 0) ? number_format(($top_log[$q_id][1]['incorrect']/$candidate_no)*100,0) : 0;
        $incorrect_l = ($candidate_no != 0) ? number_format(($bottom_log[$q_id][1]['incorrect']/$candidate_no)*100,0) : 0;
        echo "<tr><td>t=$incorrect_t%</td><td>u=$incorrect_u%</td><td>l=$incorrect_l%</td><td>". $string['Incorrect'] . "</td></tr>\n";
        echo "</table>\n";

        echo "<table>\n";
        if (!isset($freq_log[$q_id]) or !isset($freq_log[$q_id]['totalpos']) or $freq_log[$q_id]['totalpos'] == 0) {
          $p = 0;
        } else {
          $p = $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'];
        }

        $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], 1, 'correct');

        echo "<tr><td>" . pStats($p, $q_id, 1) . "</td><td colspan=\"3\">" . dStats($d, $q_id, 1)  . "</td></tr>\n";
        break;
      case 'blank':
        echo '<br />';
        $blank_details = explode('[blank', $options[0]);
        $array_size = count($blank_details);

        if ($score_method == 'Mark per Question') {
          if ($exclusions->is_question_part_excluded($q_id, 0)) {
            echo excludeButton($ex_no, $q_id, str_repeat('1', ($array_size - 1)), 1, ($array_size - 1));
          } else {
            echo excludeButton($ex_no, $q_id, str_repeat('0', ($array_size - 1)), 1, ($array_size - 1));
          }
        }

        $options[0] = preg_replace("| mark=\"([0-9]{1,3})\"|", "", $options[0]);
        $options[0] = preg_replace("| size=\"([0-9]{1,3})\"|", "", $options[0]);
        
        $blank_count = 0;
        echo $blank_details[0];
        while ($blank_count < $array_size) {
          if (strpos($blank_details[$blank_count],'[/blank]') !== false) {
            $end_start_tag = strpos($blank_details[$blank_count], ']');
            $start_end_tag = strpos($blank_details[$blank_count], '[/blank]');
            $cut_length = $start_end_tag - $end_start_tag - 1;
            $blank_options = substr($blank_details[$blank_count], ($end_start_tag + 1), $cut_length);
            $remainder = substr($blank_details[$blank_count], ($start_end_tag + 8));
            if ($exclusions->is_question_excluded($q_id)) {
              $tmp_exclude = $exclusions->get_exclusion_part_by_qid($q_id, $blank_count - 1);
            } else {
              $tmp_exclude = '';
            }

            if ($display_method == 'dropdown') {
              $options_array = explode(',', $blank_options);
              $i = 0;
              foreach ($options_array as $individual_blank_option) {
                $individual_blank_option = trim($individual_blank_option);
                if (!isset($log[$q_id][$blank_count+1][$individual_blank_option])) $log[$q_id][$blank_count+1][$individual_blank_option] = 0;
                if ($i == 0) {
                  echo ' <strong>' . chr($blank_count+64) . '.</strong> <select><option value="">' . $individual_blank_option . '</option></select>';
                }
                $i++;
              }
            } else {
              $tmp_parts = explode(',', $blank_options);
              echo ' <strong>' . chr($blank_count+64) . '.</strong> <input type="text" size="20" value="' . $tmp_parts[0] . '" />';
            }

            echo $remainder;
          }
          $blank_count++;
        }

        echo "<table cellspacing=\"0\" cellpadding=\"4\" border=\"0\" style=\"margin-left:20px\">\n";
        for ($i=1; $i<count($blank_details); $i++) {
          $end_start_tag = strpos($blank_details[$i],']');
          $start_end_tag = strpos($blank_details[$i],'[/blank]');
          $cut_length = $start_end_tag - $end_start_tag - 1;
          $blank_options = substr($blank_details[$i], ($end_start_tag+1), $cut_length);

          $blank_options = explode(',', $blank_options);

          $tmp_correct_no = 0;
          $tmp_top_no = 0;
          $tmp_bottom_no = 0;

          if ($display_method == 'dropdown') {
            $blank_word = strtolower(trim($blank_options[0]));
            if (isset($freq_log[$q_id][$i][$blank_word])) $tmp_correct_no += $freq_log[$q_id][$i][$blank_word];
            if (isset($top_log[$q_id][$i][$blank_word])) $tmp_top_no += $top_log[$q_id][$i][$blank_word];
            if (isset($bottom_log[$q_id][$i][$blank_word])) $tmp_bottom_no += $bottom_log[$q_id][$i][$blank_word];

            $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], $i, $blank_word);
          } else {
            $unique_blank_options = array_intersect_key($blank_options, array_unique(array_map('strtolower', $blank_options)));
            $unique_blank_options = array_map('strtolower', $unique_blank_options);
            
            // Merge the same option on its own and with spaces (e.g. 'cat' and ' cat').
            $new_blank_options = array();
            foreach ($unique_blank_options as $blank_option) {
              $new_blank_options[] = strtolower(trim($blank_option));
            }
            $unique_blank_options = array_unique($new_blank_options);

            foreach ($unique_blank_options as $blank_option) {
              if (isset($freq_log[$q_id][$i][$blank_option])) {
                $tmp_correct_no += $freq_log[$q_id][$i][$blank_option];
              }
              if (isset($top_log[$q_id][$i][$blank_option])) {
                $tmp_top_no += $top_log[$q_id][$i][$blank_option];
              }
              if (isset($bottom_log[$q_id][$i][$blank_option])) {
                $tmp_bottom_no += $bottom_log[$q_id][$i][$blank_option];
              }
            }
            $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], $i, $unique_blank_options);

          }
          $t = ($user_total != 0) ? number_format(($tmp_correct_no/$user_total)*100,0) : 0;
          
          $d_no++;
          $d_total += $d;
          $html = '';

          $u = ($candidate_no != 0) ? number_format(($tmp_top_no / $candidate_no) * 100, 0) : 0;
          $l = ($candidate_no != 0) ? number_format(($tmp_bottom_no / $candidate_no) * 100, 0) : 0;

          echo "<tr><td>" . chr($i+64) . ".</td>";
          if ($score_method == 'Mark per Option') {
            if ($exclusions->is_question_excluded($q_id)) {
              $tmp_part = $exclusions->get_exclusion_part_by_qid($q_id, $i-1);
              echo '<td>' . excludeButton($ex_no, $q_id, $tmp_part, 1, 1) . '</td>';
            } else {
              echo '<td>' . excludeButton($ex_no, $q_id, 0, 1, 1) . '</td>';
            }
          }
          $p = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;
          echo "<td>" . pStats($p, $q_id, $i) . "</td><td>" . dStats($d, $q_id, $i) . "</td><td>t=$t%</td><td>u=$u%</td><td>l=$l%</td>";

          if (isset($tmp_std_array[$i-1])) {
            echo '<td class="std">' . $tmp_std_array[$i-1] . '</td>';
          }
          echo "<td id=\"q_" . ($ex_no) . "_1\"";
          if ($exclusions->is_question_excluded($q_id) and $exclusions->get_exclusion_part_by_qid($q_id, $i-1) == '1' and $score_method == 'Mark per Option') echo ' class="excluded"';
          echo ">";
          if ($display_method == 'dropdown') {
            $html = $blank_options[0];
          } else {
            foreach ($blank_options as $blank_option) {
              if ($html == '') {
                $html = $blank_option;
              } else {
                $html .= ', ' . $blank_option;
              }
            }
          }
          echo "$html</td>";

          if ($display_method == 'textboxes') {
            echo "<td><input type=\"button\" onclick=\"blankCorrect($q_id, $i)\" value=\"" . $string['Correct'] . "\" /></td>";
          }
          echo "</tr>";
        }
        echo "</table>\n";
        break;
      case 'dichotomous':
        if ($score_method == 'Mark per Question') {
          if ($exclusions->is_question_excluded($q_id)) {
            echo excludeButton($ex_no, $q_id, str_repeat('1', count($options)), 1, count($options));
          } else {
            echo excludeButton($ex_no, $q_id, str_repeat('0', count($options)), 1, count($options));
          }
        }
        $i = 0;
        $std_part = 0;
        foreach ($options as $individual_option) {
          $i++;
          if (!isset($log[$q_id][$i]['t'])) $log[$q_id][$i]['t'] = 0;
          if (!isset($log[$q_id][$i]['f'])) $log[$q_id][$i]['f'] = 0;
          if (!isset($freq_log[$q_id][$i]['t'])) $freq_log[$q_id][$i]['t'] = 0;
          if (!isset($freq_log[$q_id][$i]['f'])) $freq_log[$q_id][$i]['f'] = 0;
          if (!isset($bottom_log[$q_id][$i]['t'])) $bottom_log[$q_id][$i]['t'] = 0;
          if (!isset($bottom_log[$q_id][$i]['f'])) $bottom_log[$q_id][$i]['f'] = 0;
          if (!isset($top_log[$q_id][$i]['t'])) $top_log[$q_id][$i]['t'] = 0;
          if (!isset($top_log[$q_id][$i]['f'])) $top_log[$q_id][$i]['f'] = 0;
          if (!isset($tmp_std_array[$std_part])) $tmp_std_array[$std_part] = '';

          if ($exclusions->is_question_excluded($q_id)) {
            $tmp_exclude = $exclusions->get_exclusion_part_by_qid($q_id, $i-1);
          } else {
            $tmp_exclude = '';
          }
          echo "<tr><td>";
          if ($score_method == 'Mark per Option') echo excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1);
          echo "</td>";
          if ($correct_buf[$i-1] == 't') {
            $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],$i,'t');
            $p = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][$i]['t']/$user_total : 0;
            $ptop = (isset($top_log[$q_id]) and $candidate_no != 0) ? $top_log[$q_id][$i]['t']/$candidate_no : 0;
            $pbottom = (isset($bottom_log[$q_id]) and $candidate_no != 0) ? $bottom_log[$q_id][$i]['t']/$candidate_no : 0;
            $text = $string['True'];
          } else {
            $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],$i,'f');
            $p = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][$i]['f']/$user_total : 0;
            $ptop = (isset($top_log[$q_id]) and $candidate_no != 0) ? $top_log[$q_id][$i]['f']/$candidate_no : 0;
            $pbottom = (isset($bottom_log[$q_id]) and $candidate_no != 0) ? $bottom_log[$q_id][$i]['f']/$candidate_no : 0;
            $text = $string['False'];
          }
          echo "<td>" . pStats($p, $q_id, $i) . "</td><td>" . dStats($d, $q_id, $i) . "</td><td>t=" . number_format($p*100,0) . "%</td><td>u=" . number_format($ptop*100,0) . "%</td><td>l=" . number_format($pbottom*100,0) . "%</td><td><span class=\"std\">" . $tmp_std_array[$std_part] . "</span></td><td><strong>" . $text . "</strong></td>";
          $std_part++;
          echo "<td id=\"q_" . $ex_no . "_1\"";
          if ($score_method == 'Mark per Option' and $exclusions->is_question_excluded($q_id) and $exclusions->get_exclusion_part_by_qid($q_id, $i-1) == '1') echo ' class="excluded"';
          echo ">$individual_option</td></tr>\n";
        }
        break;
      case 'enhancedcalc':
        if (!isset($freq_log[$q_id][1]['correct'])) $freq_log[$q_id][1]['correct'] = '';

	
        $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], 1, 'correct');
				
        if (isset($freq_log[$q_id][1]['correct']) and $user_total != 0) {
          $t = number_format(($freq_log[$q_id][1]['correct'] / $user_total)*100, 0);
        } else {
          $t = 0;
        }
        if (isset($top_log[$q_id][1]['correct']) and $candidate_no != 0) {
          $u = number_format(($top_log[$q_id][1]['correct'] / $candidate_no)*100, 0);
        } else {
          $u = 0;
        }
        if (isset($bottom_log[$q_id][1]['correct']) and $candidate_no != 0) {
          $l = number_format(($bottom_log[$q_id][1]['correct'] / $candidate_no)*100, 0);
        } else {
          $l = 0;
        }
        if ($exclusions->is_question_excluded($q_id)) {
          $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);;
        } else {
          $tmp_exclude = '';
        }

        echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
        echo "<tr><td>" . excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1) . "</td><td style=\"width:60px\"><strong>t=" . $t . "%</strong></td><td><strong>u=" . $u . "%</strong></td><td><strong>l=" . $l . "%</strong></td><td><span class=\"std\">" . $std . "</span></td><td id=\"q_" . $ex_no . "_1\"";
        if ($exclusions->is_question_excluded($q_id)) echo ' class="excluded"';
        echo ">$leadin</td>";
        echo "<td><input type=\"button\" onclick=\"return clacCorrect($q_id, $i)\" value=\"" . $string['Correct'] . "\" /></td>";
        echo "</tr>\n";
        echo "<tr><td colspan=\"7\">&nbsp;</td></tr>";
        $p = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][1]['correct']/$user_total : 0;
        echo "<tr><td></td><td>" . pStats($p, $q_id, 1) . "</td><td colspan=\"5\">" . dStats($d, $q_id, 1) . "</td></tr>";
        break;
      case 'true_false':
        if (!isset($log[$q_id][1]['t'])) $log[$q_id][1]['t'] = 0;
        if (!isset($log[$q_id][1]['f'])) $log[$q_id][1]['f'] = 0;
        if (!isset($freq_log[$q_id][1]['t'])) $freq_log[$q_id][1]['t'] = 0;
        if (!isset($freq_log[$q_id][1]['f'])) $freq_log[$q_id][1]['f'] = 0;
        if (!isset($bottom_log[$q_id][1]['t'])) $bottom_log[$q_id][1]['t'] = 0;
        if (!isset($bottom_log[$q_id][1]['f'])) $bottom_log[$q_id][1]['f'] = 0;
        if (!isset($top_log[$q_id][1]['t'])) $top_log[$q_id][1]['t'] = 0;
        if (!isset($top_log[$q_id][1]['f'])) $top_log[$q_id][1]['f'] = 0;

        if ($exclusions->is_question_excluded($q_id)) {
          echo "<tr><td colspan=\"4\">" . excludeButton($ex_no, $q_id, '11', 2, 2) . "</td></tr>\n";
        } else {
          echo "<tr><td colspan=\"4\">" . excludeButton($ex_no, $q_id, '00', 2, 2) . "</td></tr>\n";
        }

        $ptrue = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][1]['t']/$user_total : 0;
        $ptoptrue = (isset($top_log[$q_id]) and $candidate_no != 0) ? $top_log[$q_id][1]['t']/$candidate_no : 0;
        $pbottomtrue = (isset($bottom_log[$q_id]) and $candidate_no != 0) ? $bottom_log[$q_id][1]['t']/$candidate_no : 0;
        echo "<tr><td>t=" . number_format($ptrue*100,0) . "%</td><td>u=" . number_format($ptoptrue*100,0) . "%</td><td>l=" . number_format($pbottomtrue*100,0) . "%</td>";
        $temp_td = "<td id=\"q_" . $ex_no . "_1\"";
        if ($exclusions->is_question_excluded($q_id)) $temp_td .= ' class="excluded"';
        $temp_td .=  '>';
        if ($correct_buf[0] == 't') {
          $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],1,'t');
          $p = $ptrue;
          echo '<td><span class="std">' . $std . '</span></td>' . $temp_td . '<strong>' . $string['True'] . '</strong>';
        } else {
          echo '<td></td>' . $temp_td . $string['True'];
        }
        echo "</td></tr>\n";
        $pfalse = (isset($freq_log[$q_id]) and $user_total != 0) ? $freq_log[$q_id][1]['f']/$user_total : 0;
        $ptopfalse = (isset($top_log[$q_id]) and $candidate_no != 0) ? $top_log[$q_id][1]['f']/$candidate_no : 0;
        $pbottomfalse = (isset($bottom_log[$q_id]) and $candidate_no != 0) ? $bottom_log[$q_id][1]['f']/$candidate_no : 0;
        echo "<tr><td>t=" . number_format($pfalse*100,0) . "%</td><td>u=" . number_format($ptopfalse*100,0) . "%</td><td>l=" . number_format($pbottomfalse*100,0) . "%</td>";
        $temp_td = "<td id=\"q_" . $ex_no . "_2\"";
        if ($exclusions->is_question_excluded($q_id)) $temp_td .= ' class="excluded"';
        $temp_td .= '>';
        if ($correct_buf[0] == 'f') {
          $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],1,'f');
          $p = $pfalse;
          echo '<td><span class="std">' . $std . '</span></td>' . $temp_td . '<strong>' . $string['False'] . '</strong>';
        } else {
          echo '<td></td>' . $temp_td . $string['False'];
        }
        echo "</td></tr>\n";
        echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
        echo "<tr><td>" . pStats($p, $q_id, 1) . "</td><td colspan=\"3\">" . dStats($d, $q_id, 1) . "</td></tr>\n";
        break;
      case 'labelling':
        if ($score_method == 'Mark per Question') {
          if ($exclusions->is_question_excluded($q_id)) {
            echo excludeButton($ex_no, $q_id, str_repeat('1', count_labels($correct)), 1, count_labels($correct));
          } else {
            echo excludeButton($ex_no, $q_id, str_repeat('0', count_labels($correct)), 1, count_labels($correct));
          }
        }
        $std_part = 0;
        $max_col1 = 0;
        $max_col2 = 0;
        $tmp_first_split = explode(';', $correct);
        $tmp_second_split = explode('|', $tmp_first_split[11]);
        foreach ($tmp_second_split as $ind_label) {
          $label_parts = explode('$', $ind_label);
          if (isset($label_parts[4]) and trim($label_parts[4]) != '') {
            if ($label_parts[0] < 10) {
              $max_col1 = $label_parts[0];
            } else {
              $max_col2 = $label_parts[0];
            }
          }
        }
        $max_col2-=10;

        $max_label = max($max_col1, $max_col2);

        $tmp_height = $q_media_height;
        if ($tmp_height < ($max_label * 55)) $tmp_height = ($max_label * 55);

		//<!-- ======================== HTML5 part rep disc ================= -->
		echo "<canvas id='canvas" . $q_no . "' width='" . ($q_media_width + 220) . "' height='" . $tmp_height . "'></canvas>\n";
		echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
		echo "<script>\n";
		echo "setUpQuestion(" . $q_no . ", 'flash" . $q_no . "', '" . $language . "', '" . $q_media . "', '" . trim($correct) . "', '', '','#FFC0C0','labelling','analysis');\n";
		echo "</script>\n";
		//<!-- ==================================================== -->
	?>
  <br />
<?php

        echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
        $i = 1;
        foreach ($correct_buf as $individual_coord) {
          echo "<tr><td>" . chr($i + 64) . ".</td>";
          $option_no = 1;
          foreach ($options as $individual_option) {
            $first_part = explode('|',$individual_option);
            $individual_option = trim($first_part[0]);

            $tmp_parts = explode('~', $individual_option);
            $text_only = $tmp_parts[0];

            if ($individual_coord == $first_part[1] . 'x' . $first_part[2]) {
              $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], $individual_coord, $text_only);
              if (isset($tmp_std_array[$std_part])) {
                $std_rating = $tmp_std_array[$std_part];
              } else {
                $std_rating = '';
              }
              $tmp_correct_no = (isset($freq_log[$q_id][$individual_coord][$text_only])) ? $freq_log[$q_id][$individual_coord][$text_only] : 0;
              $tmp_top_no = (isset($top_log[$q_id][$individual_coord][$text_only])) ? $top_log[$q_id][$individual_coord][$text_only] : 0;
              $tmp_bottom_no = (isset($bottom_log[$q_id][$individual_coord][$text_only])) ? $bottom_log[$q_id][$individual_coord][$text_only] : 0;
              $p = ($user_total != 0) ? $tmp_correct_no/$user_total : 0;
              $ptop = ($candidate_no != 0) ? $tmp_top_no/$candidate_no : 0;
              $pbottom = ($candidate_no != 0) ? $tmp_bottom_no/$candidate_no : 0;
              if ($score_method == 'Mark per Option') {
                if ($exclusions->is_question_excluded($q_id)) {
                  $tmp_exclude = $exclusions->get_exclusion_part_by_qid($q_id, $i-1);
                  echo "<td>" . excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1) . "</td><td>" . pStats($p, $q_id, $i) . "</td><td>" . dStats($d, $q_id, $i) . "</td><td>t=" . number_format($p*100,0) . "%</td><td>u=" . number_format(($ptop)*100,0) . "%</td><td>l=" . number_format($pbottom*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td id=\"q_" . $ex_no . "_1\"";
                } else {
                  echo "<td>" . excludeButton($ex_no, $q_id, '', 1, 1) . "</td><td>" . pStats($p, $q_id, $i) . "</td><td>" . dStats($d, $q_id, $i) . "</td><td>t=" . number_format($p*100,0) . "%</td><td>u=" . number_format(($ptop)*100,0) . "%</td><td>l=" . number_format($pbottom*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td id=\"q_" . $ex_no . "_1\"";
                }
                if ($exclusions->is_question_excluded($q_id) and $exclusions->get_exclusion_part_by_qid($q_id, $i-1) == '1') echo ' class="excluded"';
              } else {
                echo "<td></td><td>" . pStats($p, $q_id, $i) . "</td><td>" . dStats($d, $q_id, $i) . "</td><td>t=" . number_format($p*100,0) . "%</td><td>u=" . number_format(($ptop)*100,0) . "%</td><td>l=" . number_format($pbottom*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td";
              }
              echo ">";
              if (strpos(strtolower($individual_option),'.jpg') !== false or strpos(strtolower($individual_option),'.jpeg') !== false or strpos(strtolower($individual_option),'.gif') !== false or strpos(strtolower($individual_option),'.png') !== false) {
                $image_parts = explode('~', $individual_option);
                echo "<img src=\"" . $mediadirectory->url($image_parts[0]) . "\" width=\"" . $image_parts[1] . "\" height=\"" . $image_parts[2] . "\" alt=\"\" border=\"1\" />";
              } else {
                echo "<strong>$individual_option</strong>";
              }
              echo "</td></tr>\n";
              $std_part++;
            }
            $option_no++;
          }
          $i++;
        }
        break;
      case 'flash':
        if ($exclusions->is_question_excluded($q_id)) {
          echo excludeButton($ex_no, $q_id, 1, 1,1);
        } else {
          echo excludeButton($ex_no, $q_id, 0, 1, 1);
        }
        echo $leadin;
        ?>
          <script>
          var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
          function flash<?php echo $q_no; ?>_DoFSCommand(command, args) {
            var flash<?php echo $q_no; ?>Obj = isInternetExplorer ? document.all.flash<?php echo $q_no; ?> : document.flash<?php echo $q_no; ?>;
             //document.questions.q<?php echo $q_no; ?>.value = args;
          }
          if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
            document.write('<script language=\"VBScript\"\>\n');
            document.write('On Error Resume Next\n');
            document.write('Sub flash<?php echo $q_no; ?>_FSCommand(ByVal command, ByVal args)\n');
            document.write('	Call flash<?php echo $q_no; ?>_DoFSCommand(command, args)\n');
            document.write('End Sub\n');
            document.write('</script\>\n');
          }
        </script>
        <div style="text-align:center">
        <script>
          write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab" id="flash<?php echo $q_no; ?>" width="<?php echo $q_media_width; ?>" height="<?php echo $q_media_height; ?>" align="middle">');
          write_string('<param name="allowScriptAccess" value="sameDomain" />');
          write_string('<param name="movie" value="<?php echo $mediadirectory->url($q_media); ?>" />');
          write_string('<param name="quality" value="high" />');
          write_string('<param name="bgcolor" value="#ffffff" />');
          <?php
            if ($scenario != '') {
              echo 'write_string(\'<param name="FlashVars" value="' . $scenario . '">\')';
            }
            echo 'write_string(\'<embed src="' . $mediadirectory->url($q_media) . '"';
            if ($scenario != '') {
              echo ' FlashVars="' . $scenario . '"';
            }
            echo ' quality="high" bgcolor="#ffffff" width="' . $q_media_width . '" height="' . $q_media_height . '" swLiveConnect=true id="flash' . $q_no . '" name="flash' . $q_no . '" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />\');';
          ?>
          write_string('</object>');
        </script>
        </div>
        <?php
        break;
      case 'hotspot':
        $layers = explode('|', $correct);
        $std_parts = explode(',', $std);

        if ($score_method == 'Mark per Question') {
          if ($exclusions->is_question_excluded($q_id)) {
            echo excludeButton($ex_no, $q_id, str_repeat('1', count($layers)), 1, count($layers));
          } else {
            echo excludeButton($ex_no, $q_id, str_repeat('0', count($layers)), 1, count($layers));
          }
        }

        $layers = explode('|', $correct);
        $coords = '';
        for ($i = 1; $i <= count($layers); $i++) {
          if (isset($freq_log[$q_id][$i]['coords'])) {
            $coords .= $freq_log[$q_id][$i]['coords'] . '|';
          } else {
            $coords .= '|';
          }
        }
        $coords = rtrim($coords, '|');

        $tmp_correct = str_replace("'", "\'", trim($correct));
        $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
        $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct);

		$configObject          = Config::get_instance();
		//<!-- ======================== HTML5 part rep disc ================= -->
		echo "<canvas id='canvas" . $q_no . "' width='" . ($q_media_width + 302) . "' height='" . ($q_media_height + 25) . "'></canvas>\n";
		echo "<br /><div style='width:100%;text-align: left;' id='canvasbox'></div>\n";
		echo "<script>\n";
		echo "setUpQuestion(" . $q_no . ", 'flash" . $q_no . "', '" . $language . "', '" . $q_media . "', '" . $tmp_correct . "', '" . $coords . "', '0','#FFC0C0','hotspot','analysis');\n";
		echo "</script>\n";
		//<!-- ==================================================== -->

        echo "<p><table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
        for ($i = 1; $i <= count($layers); $i++) {
          echo "<tr><td>" . chr($i + 64) . ".</td>";
          $label = substr($layers[$i - 1], 0, strpos($layers[$i - 1], '~'));

          $std_rating = (isset($std_parts[$i - 1])) ? $std_parts[$i - 1] : '';

          $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],$i,1);
          $tmp_correct_no = (isset($freq_log[$q_id][$i][1])) ? $freq_log[$q_id][$i][1] : 0;
          $tmp_top_no = (isset($top_log[$q_id][$i][1])) ? $top_log[$q_id][$i][1] : 0;
          $tmp_bottom_no = (isset($bottom_log[$q_id][$i][1])) ? $bottom_log[$q_id][$i][1] : 0;
          $p = ($user_total != 0) ? $tmp_correct_no/$user_total : 0;
          $ptop = ($candidate_no != 0) ? $tmp_top_no/$candidate_no : 0;
          $pbottom = ($candidate_no != 0) ? $tmp_bottom_no/$candidate_no : 0;
          if ($exclusions->is_question_excluded($q_id)) {
            echo "<td>";
            if ($score_method == 'Mark per Option') echo excludeButton($ex_no, $q_id, $exclusions->get_exclusion_part_by_qid($q_id, $i-1), 1, 1);
            echo "</td><td>" . pStats($p, $q_id, $i) . "</td><td>" . dStats($d, $q_id, $i) . "</td><td>t=" . number_format($p*100,0) . "%</td><td>u=" . number_format($ptop*100,0) . "%</td><td>l=" . number_format($pbottom*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td";
            if ($score_method == 'Mark per Option') echo " id=\"q_" . $ex_no . "_1\"";
          } else {
            echo "<td>";
            if ($score_method == 'Mark per Option') echo excludeButton($ex_no, $q_id, '', 1, 1);
            echo "</td><td>" . pStats($p, $q_id, $i) . "</td><td>" . dStats($d, $q_id, $i) . "</td><td>t=" . number_format($p*100,0) . "%</td><td>u=" . number_format($ptop*100,0) . "%</td><td>l=" . number_format($pbottom*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td";
            if ($score_method == 'Mark per Option') echo " id=\"q_" . $ex_no . "_1\"";
          }
          if ($score_method == 'Mark per Option' and $exclusions->is_question_excluded($q_id) and $exclusions->get_exclusion_part_by_qid($q_id, $i-1) == '1') echo ' class="excluded"';
          echo "><strong>$label</strong></td></tr>\n";
        }
        break;
      case 'likert':
        $scale = explode('|', $display_method);
        echo "<tr>\n";
        for ($i=0; $i<count($scale)-1; $i++) {
          echo "<td>" . $scale[$i] . "</td>";
        }
        echo "</tr>\n";
        echo "<tr>\n";
        for ($i=1; $i<count($scale); $i++) {
          if (isset($freq_log[$q_id][1][$i]) and $user_total != 0) {
            $t = number_format(($freq_log[$q_id][1][$i]/$user_total)*100,0);
          } else {
            $t = 0;
          }
          echo "<td style=\"text-align:center\">t=" . $t . "%</td>";
        }
        echo "</tr>\n";
        break;
      case 'mcq':
        if ($exclusions->is_question_excluded($q_id)) {
          $tmp_exclude =  $exclusions->get_exclusions_by_qid($q_id);
        } else {
          $tmp_exclude = '';
        }
        echo "<tr><td colspan=\"3\">" . excludeButton($ex_no, $q_id, $tmp_exclude, count($options), 1) . "</td></tr>\n";
        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          if (isset($freq_log[$q_id][1][$i]) and $user_total != 0) {
            $t = number_format(($freq_log[$q_id][1][$i]/$user_total)*100,0);
          } else {
            $t = 0;
          }
          if (isset($top_log[$q_id][1][$i]) and $candidate_no != 0) {
            $u = number_format(($top_log[$q_id][1][$i]/$candidate_no)*100,0);
          } else {
            $u = 0;
          }
          if (isset($bottom_log[$q_id][1][$i]) and $candidate_no != 0) {
            $l = number_format(($bottom_log[$q_id][1][$i]/$candidate_no)*100,0);
          } else {
            $l = 0;
          }
          if ($correct == $i) {
            $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],1,$i);
            $tmp_correct_no = (isset($freq_log[$q_id][1][$i])) ? $freq_log[$q_id][1][$i] : 0;
            echo "<tr style=\"font-weight:bold\"><td>t=" . $t . "%</td><td>u=" . $u . "%</td><td>l=" . $l . "%</td><td><span class=\"std\">$std</span></td>";
          } else {
            echo "<tr><td class=\"grey\">t=" . $t . "%</td><td class=\"grey\">u=" . $u . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td>";
          }
          echo "<td id=\"q_" . $ex_no . "_" . $i . "\"";
          if ($exclusions->is_question_excluded($q_id)) echo ' class="excluded"';
          echo ">";
          if ($individual_option != '') echo "$individual_option\n";
          if (is_array($o_media[$i - 1])) {
            echo '<br />';
            echo display_media($o_media[$i - 1][0], $o_media[$i - 1][1], $o_media[$i - 1][2], '');
          }
          echo "</td></tr>\n";
        }
				
				if (isset($freq_log[$q_id][1]['a']) and $user_total != 0) {
					$t = number_format(($freq_log[$q_id][1]['a']/$user_total)*100,0);
				} else {
					$t = 0;
				}
				if (isset($top_log[$q_id][1]['a']) and $candidate_no != 0) {
					$u = number_format(($top_log[$q_id][1]['a']/$candidate_no)*100,0);
				} else {
					$u = 0;
				}
				if (isset($bottom_log[$q_id][1]['a']) and $candidate_no != 0) {
					$l = number_format(($bottom_log[$q_id][1]['a']/$candidate_no)*100,0);
				} else {
					$l = 0;
				}
				echo "<tr><td class=\"grey\">t=" . $t . "%</td><td class=\"grey\">u=" . $u . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td><td style=\"color:#C00000\">&lt;abstain&gt;</td></tr>\n";
					
        echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
        $p = ($user_total != 0) ? $tmp_correct_no/$user_total : 0;
        echo "<tr><td>" . pStats($p, $q_id, 1) . "</td><td colspan=\"2\">" . dStats($d, $q_id, 1) . "</td></tr>\n";
        break;
      case 'mrq':
        if ($exclusions->is_question_excluded($q_id)) {
          $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);
        } else {
          $tmp_exclude = '';
        }
        echo "<tr><td colspan=\"3\">" . excludeButton($ex_no, $q_id, $tmp_exclude, count($options), count($options)) . "</td></tr>\n";
        $i = 0;
        $std_part = 0;
        foreach ($options as $individual_option) {
          $i++;
          if (!isset($log[$q_id][$i]['y'])) $log[$q_id][$i]['y'] = 0;
          if (isset($freq_log[$q_id][$i]['y']) and $user_total != 0) {
            $t = number_format(($freq_log[$q_id][$i]['y']/$user_total)*100,0);
          } else {
            $t = 0;
          }
          if (isset($top_log[$q_id][$i]['y']) and $candidate_no != 0) {
            $u = number_format(($top_log[$q_id][$i]['y']/$candidate_no)*100,0);
          } else {
            $u = 0;
          }
          if (isset($bottom_log[$q_id][$i]['y']) and $candidate_no != 0) {
            $l = number_format(($bottom_log[$q_id][$i]['y']/$candidate_no)*100,0);
          } else {
            $l = 0;
          }
          if ($correct_buf[$i-1] == 'y') {
            if (isset($tmp_std_array[$i-1])) {
              $tmp_std = $tmp_std_array[$i-1];
            } else {
              $tmp_std = '';
            }

            echo "<tr style=\"font-weight:bold\"><td>t=" . $t . "%</td><td>u=" . $u . "%</td><td>l=" . $l . "%</td><td><span class=\"std\">" . $tmp_std . "</span></td><td id=\"q_" . $ex_no . "_" . $i . "\"";
            if ($exclusions->is_question_excluded($q_id)) echo ' class="excluded"';
            $std_part++;
          } else {
            echo "<tr><td class=\"grey\">t=" . $t . "%</td><td class=\"grey\">u=" . $t . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td><td id=\"q_" . $ex_no . "_" . $i . "\"";
            if ($exclusions->is_question_excluded($q_id)) echo ' class="excluded"';
          }
          echo ">$individual_option";
          if (is_array($o_media[$i - 1])) {
            echo '<br />';
            echo display_media($o_media[$i - 1][0], $o_media[$i - 1][1], $o_media[$i - 1][2], '');
          }
          echo "</td></tr>\n";
        }
				
				// Abstain
        if (isset($freq_log[$q_id]['a']) and $user_total != 0) {
					$t = number_format(($freq_log[$q_id]['a']/$user_total)*100,0);
        } else {
					$t = 0;
				}
				if (isset($top_log[$q_id]['a']) and $candidate_no != 0) {
					$u = number_format(($top_log[$q_id]['a']/$candidate_no)*100,0);
        } else {
					$u = 0;
				}
        if (isset($bottom_log[$q_id]['a']) and $candidate_no != 0) {
					$l = number_format(($bottom_log[$q_id]['a']/$candidate_no)*100,0);
				} else {
					$l = 0;
				}
        echo "<tr><td class=\"grey\">t=" . $t . "%</td><td class=\"grey\">u=" . $t . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td><td id=\"q_" . $ex_no . "_abstain\"><span style=\"color:#C00000\">&lt;" . $string['abstain'] . "&gt;</span></td></tr>\n";
				
        if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
          $d = 0;
        } else {
          $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
        }
        echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
        $tmp_pstat = (isset($freq_log[$q_id]['mark']) and isset($freq_log[$q_id]['totalpos']) and $freq_log[$q_id]['totalpos'] > 0) ? $freq_log[$q_id]['mark']/$freq_log[$q_id]['totalpos'] : 0;
        echo "<tr><td>" . pStats($tmp_pstat, $q_id, 1) . "</td><td colspan=\"2\">" . dStats($d, $q_id, 1) . "</td></tr>\n";
        break;
      case 'rank':
        $rank_no = 0;
        foreach ($correct_buf as $individual_correct) {
          if ($individual_correct > $rank_no and $individual_correct != 0) $rank_no = $individual_correct;
        }
        $i = 0;
        if ($exclusions->is_question_excluded($q_id)) {
          $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);
        } else {
          $tmp_exclude = '';
        }
        echo "<tr><td colspan=\"4\">" . excludeButton($ex_no, $q_id, $tmp_exclude, count($options), count($options) + 1) . "</td></tr>\n";
        foreach ($options as $individual_option) {
          echo "<tr><td id=\"q_" . $ex_no . "_" . ($i+1) . "\" colspan=\"6\"";
          if ($exclusions->is_question_excluded($q_id)) echo ' class="excluded"';
          echo ">$individual_option</td></tr>\n";
          for ($rank_position=1; $rank_position<=$rank_no; $rank_position++) {
            if (isset($top_log[$q_id][$i][$rank_position])) {
              $u = number_format(($top_log[$q_id][$i][$rank_position]/$candidate_no)*100,0);
            } else {
              $u = 0;
            }
            if (isset($bottom_log[$q_id][$i][$rank_position])) {
              $l = number_format(($bottom_log[$q_id][$i][$rank_position]/$candidate_no)*100,0);
            } else {
              $l = 0;
            }

            if (!isset($log[$q_id][$i][$rank_position])) $log[$q_id][$i][$rank_position] = 0;
            if ($correct_buf[$i] == $rank_position) {
              if (isset($tmp_std_array[$i])) {
                $tmp_std = $tmp_std_array[$i];
              } elseif (isset($tmp_std_array[0]) and !isset($tmp_std)) {
                // This is the first displayed option in a ranking with the Mark per question marking method.
                $tmp_std = $tmp_std_array[0];
              } else {
                $tmp_std = '';
              }

              echo "<tr><td><strong>u=" . $u . "%</strong></td><td><strong>l=" . $l . "%</strong></td><td><span class=\"std\">" . $tmp_std . "</span></td><td style=\"font-weight:bold\">$rank_position";

              if ($rank_position == 1) {
                echo 'st';
              } elseif ($rank_position == 2) {
                echo 'nd';
              } elseif ($rank_position == 3) {
                echo 'rd';
              } else {
                echo 'th';
              }
              echo "</td><td>&nbsp;</td></tr>\n";
            } else {
              echo "<tr><td class=\"grey\">u=" . $u . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td><td>$rank_position";
              if ($rank_position == 1) {
                echo 'st';
              } elseif ($rank_position == 2) {
                echo 'nd';
              } elseif ($rank_position == 3) {
                echo 'rd';
              } else {
                echo 'th';
              }
              echo "</td><td style=\"width:50%\">&nbsp;</td></tr>\n";
            }
          }
          echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
          $i++;
        }
        if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
          $d = 0;
        } else {
          $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
        }
        $std_val = (isset($tmp_std_array[$i])) ? $tmp_std_array[$i] : '';
        $tmp_correct_no = (isset($top_log[$q_id]['all_correct'])) ? $top_log[$q_id]['all_correct'] : 0;
        $tmp_bottom_no = (isset($bottom_log[$q_id]['all_correct'])) ? $bottom_log[$q_id]['all_correct'] : 0;
        echo "<tr><td><strong>u=" . number_format(($tmp_correct_no/$candidate_no)*100,0) . "%</strong></td><td><strong>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</strong></td><td><span class=\"std\">" . $std_val . "</span></td><td style=\"font-weight:bold\">". $string['AllItemsCorrect'] . "</td></tr>\n";
        $p = (isset($freq_log[$q_id]) and $freq_log[$q_id]['totalpos'] != 0) ? $freq_log[$q_id]['mark']/$freq_log[$q_id]['totalpos'] : 0;
        echo "<tr><td>" . pStats($p, $q_id, 1) . "</td><td colspan=\"3\">" . dStats($d, $q_id, 1) . "</td></tr>\n";
        break;
      case 'sct':
        if ($exclusions->is_question_excluded($q_id)) {
          $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);
        } else {
          $tmp_exclude = '';
        }
        echo "<tr><td colspan=\"3\">" . excludeButton($ex_no, $q_id, $tmp_exclude, count($options), 1) . "</td></tr>\n";
        $i = 0;
        foreach ($options as $individual_option) {
          $i++;
          $tmp_correct_no = (isset($freq_log[$q_id][1][$i])) ? $freq_log[$q_id][1][$i] : 0;
          $tmp_top_no = (isset($top_log[$q_id][1][$i])) ? $top_log[$q_id][1][$i] : 0;
          $tmp_bottom_no = (isset($bottom_log[$q_id][1][$i])) ? $bottom_log[$q_id][1][$i] : 0;

          $max_correct = 0;
          $correct_answer_no = 0;
          $answer_no = 1;
          foreach ($correct_buf as $tmp_correct) {
            if ($tmp_correct > $max_correct) {
              $max_correct = $tmp_correct;
              $correct_answer_no = $answer_no;
            }
            $answer_no++;
          }

          $correct_class = ($correct_answer_no  == $i) ? ' correct' : '';
          $percent_correct = ($user_total != 0) ? $tmp_correct_no/$user_total : 0;
          $percent_top = ($candidate_no != 0) ? $tmp_top_no/$candidate_no : 0;
          $percent_bottom = ($candidate_no != 0) ? $tmp_bottom_no/$candidate_no : 0;
          echo "<tr class=\"grey{$correct_class}\"><td>t=" . number_format($percent_correct*100,0) . "%</td><td>u=" . number_format($percent_top*100,0) . "%</td><td>l=" . number_format($percent_bottom*100,0) . "%</td><td></td>";
          echo "<td id=\"q_" . $ex_no . "_" . $i . "\"";
          if ($exclusions->is_question_excluded($q_id)) echo ' class="excluded"';
          echo ">$individual_option</td></tr>\n";
        }
        echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
        if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
          $d = 0;
        } else {
          $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
        }
        $p = (isset($freq_log[$q_id]) and $freq_log[$q_id]['totalpos'] > 0) ? $freq_log[$q_id]['mark']/$freq_log[$q_id]['totalpos'] : 0;

        echo "<tr><td>" . pStats($p, $q_id, 1) . "</td><td colspan=\"3\">" . dStats($d, $q_id, 1) . "</td></tr>\n";
        break;
    }
    if ($q_type != 'info' and $q_type != 'blank' and $q_type != 'flash') echo "</table></p>\n";
  } elseif ($q_type == 'textbox') {
    echo "<td class=\"q_no\">$q_no.&nbsp;</td><td><div ";
    if ($exclusions->is_question_excluded($q_id)) {
      echo ' class="excluded"';
      $tmp_exclude = $exclusions->get_exclusions_by_qid($q_id);
    } else {
      $tmp_exclude = '';
    }
    echo "id=\"q_" . ($ex_no + 1) . "_1\">" . excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1) . "&nbsp;$leadin</div>";
    echo "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">";

    $sortby = 'used';
    $ordering = 'ASC';

    $top_words = array();
    if (isset($top_log[$q_id]['words'])) {
      $i = 0;
      foreach ($top_log[$q_id]['words'] as $word=>$used) {
        $top_words[$i]['word'] = $word;
        $top_words[$i]['used'] = $used;
        $i++;
      }
    }
    $top_words = array_csort($top_words,$sortby,$ordering);

    $bottom_words = array();
    if (isset($bottom_log[$q_id]['words'])) {
      $i = 0;
      foreach ($bottom_log[$q_id]['words'] as $word=>$used) {
        $bottom_words[$i]['word'] = $word;
        $bottom_words[$i]['used'] = $used;
        $i++;
      }
    }
    $bottom_words = array_csort($bottom_words,$sortby,$ordering);

    echo "<tr><td colspan=\"2\"><strong>" . $string['TopGroup'] . ":</strong></td><td colspan=\"2\"><strong>" . $string['BottomGroup'] . ":</strong></td></tr>\n";
    $mean_word_count_top = (isset($top_log[$q_id]) and $candidate_no != 0) ? $top_log[$q_id]['word_count'] / $candidate_no : 0;
    $mean_word_count_bottom = (isset($bottom_log[$q_id]) and $candidate_no != 0) ? $bottom_log[$q_id]['word_count'] / $candidate_no : 0;
    echo "<tr><td colspan=\"2\">(" . $string['meanWordCount'] . " = " . round($mean_word_count_top) . ")</td><td colspan=\"2\">(" . $string['meanWordCount'] . " = " . round($mean_word_count_bottom) . ")</td></tr>";
    for ($i=0; $i<40; $i++) {
      if (isset($top_words[$i]['word']) or isset($bottom_words[$i]['word'])) {
        echo "<tr>";
        if (isset($top_words[$i]['word'])) {
          echo "<td>" . $top_words[$i]['used'] . "</td><td>" . $top_words[$i]['word'] . "</td>";
        } else {
          echo "<td></td><td></td>";
        }
        if (isset($bottom_words[$i]['word'])) {
          echo "<td>" . $bottom_words[$i]['used'] . "</td><td>" . $bottom_words[$i]['word'] . "</td>";
        } else {
          echo "<td></td><td></td>";
        }
        echo "</tr>";
      }
    }

    if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
      $d = 0;
    } else {
      $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
    }
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    if (isset($freq_log[$q_id]['unmarked']) and $freq_log[$q_id]['unmarked'] > 0) {
      echo "<tr><td>p=<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"Warning\" /></td><td>d=<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"Warning\" /></td><td colspan=\"2\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"Warning\" />&nbsp;" . sprintf($string['unmarkedscripts'], $freq_log[$q_id]['unmarked']) . "</td></tr>\n";
    } else {
      if (empty($freq_log[$q_id]['totalpos'])) {
        $p = 0;
      } else {
        $p = $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'];
      }
      echo "<tr><td>" . pStats($p, $q_id, 1) . "</td><td colspan=\"3\">" . dStats($d, $q_id, 1)  . "</td></tr>\n";
    }
    echo "</table></td></tr>\n";
  } elseif ($q_type == 'matrix') {
    $tmp_media_array = explode('|',$q_media);
    $tmp_media_width_array = explode('|',$q_media_width);
    $tmp_media_height_array = explode('|',$q_media_height);
    $tmp_ext_scenarios = explode('|',$scenario);
    $tmp_answers_array = explode('|',$correct_buf[0]);

    echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td><div";
    if ($score_method == 'Mark per Question') {
      echo ' id="q_' . ($ex_no + 1) . '_1"';
      if ($exclusions->is_question_excluded($q_id)) {
        echo ' class="excluded"';
      }
    }
    echo ">$leadin</div>";

    if ($score_method == 'Mark per Question') {
      if ($exclusions->is_question_excluded($q_id)) {
        echo excludeButton($ex_no, $q_id, str_repeat('1', count($options)), 1, count($options));
      } else {
        echo excludeButton($ex_no, $q_id, str_repeat('0', count($options)), 1, count($options));
      }
    }

    echo "<p>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\" class=\"matrix\">\n";
    $cols = 6;
    if ($score_method == 'Mark per Option') $cols++;
    $std_on = false;
    for ($i=0; $i<count($options); $i++) {
      if (isset($tmp_std_array[$i])) $std_on = true;
    }
    if ($std_on) $cols++;

    echo "<tr><td colspan=\"$cols\">&nbsp;</td><td>&nbsp;</td>";
    for ($i=0; $i<count($options); $i++) {
      echo '<td>' . $options[$i] . '</td>';
    }
    echo "</tr>\n";
    for ($i=1; $i<=(substr_count($scenario,'|')+1); $i++) {
      if ($tmp_ext_scenarios[$i-1] != '') {
        echo "<tr>\n";
        $option_no = 1;
        foreach ($options as $individual_option) {
          if ($option_no == 1) {
            $correct_answer = $tmp_answers_array[$i-1];
            $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], $i, $correct_answer);
            if ($exclusions->is_question_excluded($q_id)) {
              $tmp_exclude = $exclusions->get_exclusion_part_by_qid($q_id, $i-1);
            } else {
              $tmp_exclude = '';
            }
            $tmp_correct_no = (isset($freq_log[$q_id][$i][$correct_answer])) ? $freq_log[$q_id][$i][$correct_answer] : 0;
            $tmp_top_no = (isset($top_log[$q_id][$i][$correct_answer])) ? $top_log[$q_id][$i][$correct_answer] : 0;
            $tmp_bottom_no = (isset($bottom_log[$q_id][$i][$correct_answer])) ? $bottom_log[$q_id][$i][$correct_answer] : 0;
            if ($score_method == 'Mark per Option') {
              echo '<td>' .  excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1) . '</td>';
            }
            $p = ($user_total != 0) ? $tmp_correct_no / $user_total : 0;
            $ptop = ($candidate_no != 0) ? $tmp_top_no / $candidate_no : 0;
            $pbottom = ($candidate_no != 0) ? $tmp_bottom_no / $candidate_no : 0;
            echo "<td>" . chr($i+64) . ".</td>";
            echo "<td style=\"font-weight:bold\">" . pStats($p, $q_id, $i) . "</td>";
            echo "<td style=\"font-weight:bold\">" . dStats($d, $q_id, $i) . "</td>";
            echo "<td style=\"font-weight:bold\">t=" . number_format($p*100,0) . "%</td>";
            echo "<td style=\"font-weight:bold\">u=" . number_format($ptop*100,0) . "%</td>";
            echo "<td style=\"font-weight:bold\">l=" . number_format($pbottom*100,0) . "%</td>";

            if (isset($tmp_std_array[$i-1])) {
              echo '<td class="std"><strong>' . $tmp_std_array[$i-1] . '</strong></td>';
            }

            echo "<td ";
            if ($exclusions->is_question_excluded($q_id) and $exclusions->get_exclusion_part_by_qid($q_id, $i-1) == '1' and $score_method != 'Mark per Question') echo ' class="excluded"';
            if ($score_method == 'Mark per Option') echo "id=\"q_" . ($ex_no) . "_1\"";
            echo ">" . $tmp_ext_scenarios[$i-1] . "</td>";
          }

          if ($tmp_answers_array[$i-1] == $option_no) {
            echo "<td style=\"text-align:center; background-color:#C0FFC0\"><input type=\"radio\" name=\"q" . $q_id . "_" . $i . "\" checked /></td>";
          } else {
            echo "<td style=\"text-align:center\"><input type=\"radio\" name=\"q" . $q_id . "_" . $i . "\" /></td>";
          }
          $option_no++;
        }
        echo "</tr>\n";
      }
    }
    echo "</table>\n</td></tr>\n";
  } elseif ($q_type == 'extmatch') {
    $matching_scenarios = array();
    $matching_scenarios = explode('|', $scenario);
    $tmp_media_array = explode('|',$q_media);
    $tmp_media_width_array = explode('|',$q_media_width);
    $tmp_media_height_array = explode('|',$q_media_height);
    $tmp_ext_scenarios = explode('|',$scenario);
    $tmp_answers_array = explode('|',$correct_buf[0]);

    $tmp_text_no = 0;
    for ($part_id=0; $part_id<10; $part_id++) {
      if (isset($matching_scenarios[$part_id]) and trim(strip_tags($matching_scenarios[$part_id])) != '') $tmp_text_no++;
    }
    $tmp_media_no = 0;
    for ($part_id=1; $part_id<=10; $part_id++) {
      if (isset($tmp_media_array[$part_id]) and $tmp_media_array[$part_id] != '') $tmp_media_no++;
    }
    $total_scenarios = max($tmp_text_no, $tmp_media_no);

    echo "<tr><td class=\"q_no\">$q_no.&nbsp;</td><td><div";
    if ($score_method == 'Mark per Question') {
      echo " id=\"q_" . ($ex_no + 1) . "_1\"";
      if ($exclusions->is_question_excluded($q_id)) echo ' class="excluded"';
    }
    echo ">$leadin</div>\n";
    if ($score_method == 'Mark per Question') {
      if ($exclusions->is_question_excluded($q_id)) {
        echo excludeButton($ex_no, $q_id, str_repeat('1', $total_scenarios), 1, $total_scenarios);
      } else {
        echo excludeButton($ex_no, $q_id, str_repeat('0', $total_scenarios), 1, $total_scenarios);
      }
    }
    echo "<ol class=\"extmatch\">";
    if ($tmp_media_array[0] != '') {
      echo "<p align=\"center\">" . display_media($tmp_media_array[0],$tmp_media_width_array[0],$tmp_media_height_array[0], '') . "</p>\n";
    }
    $std_part = 0;
    $section = 0;
    for ($i=1; $i<=$total_scenarios; $i++) {
      $tmp_correct_no = 0;
      $correct_stems = 0;
      echo "<li>\n";
      if (isset($tmp_media_array[$i]) and $tmp_media_array[$i] != '') {
        echo "<p>" . display_media($tmp_media_array[$i], $tmp_media_width_array[$i], $tmp_media_height_array[$i], '') . "</p>\n";
      }
      if (isset($tmp_ext_scenarios[$i-1])) echo "<div>" . $tmp_ext_scenarios[$i-1] . "</div>\n";

      $option_no = 1;
      foreach ($options as $individual_option) {
        $specific_answers = array();
        $specific_answers = explode('$', $tmp_answers_array[$i-1]);
        $answer_match = false;
        $count_specific_answers = count($specific_answers);
        for ($x=0; $x<$count_specific_answers; $x++) {
          if ($option_no == $specific_answers[$x]) $answer_match = true;
        }
        if ($answer_match == true) $correct_stems++;
        $option_no++;
      }

      if ($exclusions->is_question_excluded($q_id)) {
        $tmp_exclude = $exclusions->get_exclusion_part_by_qid($q_id, $section);
      } else {
        $tmp_exclude = '';
      }
      if ($score_method == 'Mark per Option') echo "<div>" . excludeButton($ex_no,$q_id,$tmp_exclude, count($options), $correct_stems) . "</div>";
      echo "<div><table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
      $sub_d = 0;
      $sub_d_no = 0;
      $option_no = 1;
      $correct_stems = 0;
      foreach ($options as $individual_option) {
        $specific_answers = explode('$', $tmp_answers_array[$i-1]);
        $answer_match = false;
        $count_specific_answers = count($specific_answers);
        for ($x=0; $x<$count_specific_answers; $x++) {
          if ($option_no == $specific_answers[$x]) $answer_match = true;
        }
        if ($answer_match == true) {
          if (isset($top_log[$q_id][$i][$option_no]) and $candidate_no != 0) {
            $t = $top_log[$q_id][$i][$option_no]/$candidate_no;
          } else {
            $t = 0;
          }
          if (isset($bottom_log[$q_id][$i][$option_no]) and $candidate_no != 0) {
            $l = $bottom_log[$q_id][$i][$option_no]/$candidate_no;
          } else {
            $l = 0;
          }
          $sub_d += $t - $l;
          $sub_d_no++;
          if (isset($freq_log[$q_id][$i][$option_no]) and $user_total != 0) {
            $t = number_format(($freq_log[$q_id][$i][$option_no]/$user_total)*100,0);
          } else {
            $t = 0;
          }
          if (isset($top_log[$q_id][$i][$option_no]) and $candidate_no != 0) {
            $u = number_format(($top_log[$q_id][$i][$option_no]/$candidate_no)*100,0);
          } else {
            $u = 0;
          }
          if (isset($bottom_log[$q_id][$i][$option_no]) and $candidate_no != 0) {
            $l = number_format(($bottom_log[$q_id][$i][$option_no]/$candidate_no)*100,0);
          } else {
            $l = 0;
          }
          if (isset($tmp_std_array[$std_part])) {
            $tmp_std = $tmp_std_array[$std_part];
          } else {
            $tmp_std = '';
          }
          echo "<tr style=\"font-weight:bold\"><td>t=" . $t . "%</td><td>u=" . $u . "%</td><td>l=" . $l . "%</td><td><span class=\"std\">" . $tmp_std . "</span></td><td class=\"correct";
          if ($score_method == 'Mark per Option' and $exclusions->is_question_excluded($q_id) and $exclusions->get_exclusion_part_by_qid($q_id, $section) == '1') echo ' excluded';
          echo "\"";
          if ($score_method == 'Mark per Option') echo " id=\"q_" . $ex_no . "_" . $option_no . "\"";
          echo ">" . chr($option_no+64) . ". $individual_option</td></tr>\n";
          $correct_stems++;
          if (isset($freq_log[$q_id][$i][$option_no])) $tmp_correct_no += $freq_log[$q_id][$i][$option_no];
          $std_part++;
        } else {
          if (isset($freq_log[$q_id][$i][$option_no]) and $user_total != 0) {
            $t = number_format(($freq_log[$q_id][$i][$option_no]/$user_total)*100,0);
          } else {
            $t = 0;
          }
          if (isset($top_log[$q_id][$i][$option_no]) and $candidate_no != 0) {
            $u = number_format(($top_log[$q_id][$i][$option_no]/$candidate_no)*100,0);
          } else {
            $u = 0;
          }
          if (isset($bottom_log[$q_id][$i][$option_no]) and $candidate_no != 0) {
            $l = number_format(($bottom_log[$q_id][$i][$option_no]/$candidate_no)*100,0);
          } else {
            $l = 0;
          }

          echo "<tr><td class=\"grey\">t=" . $t . "%</td><td class=\"grey\">u=" . $u . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td><td";
          if ($score_method == 'Mark per Option' and $exclusions->is_question_excluded($q_id) and $exclusions->get_exclusion_part_by_qid($q_id, $section) == '1') echo ' class="excluded"';
          if ($score_method == 'Mark per Option') echo " id=\"q_" . $ex_no . "_" . $option_no . "\"";
          echo ">" . chr($option_no+64) . ". $individual_option</td></tr>\n";
        }
        $option_no++;
      }
      $section = $std_part;
      $d = ($sub_d/$sub_d_no);
      $d_no++;
      $d_total += $d;
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>";
      echo "<tr><td>" . pStats($tmp_correct_no/($correct_stems * $user_total), $q_id, $i) . "</td><td colspan=\"3\">" . dStats($d, $q_id, $i) . "</td></tr>";
      if ($i < $total_scenarios) echo "<tr><td colspan=\"4\">&nbsp;</td></tr>";
      echo "</table></div></li>\n";
    }
    echo "</ol>\n";
  }
  echo "</td></tr>\n";
  echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php echo $string['frequencydiscrimination'] . " " . $configObject->get('cfg_install_type') ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/key.css" />
<link rel="stylesheet" type="text/css" href="../css/finish.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <style type="text/css">
    body {margin-bottom: 10px}
    h1 {margin-left: 15px; font-size: 18pt}
    p {margin-left: 0; margin-right: 0}
    .figures {text-align: right}
    .q_no {text-align: right; vertical-align: top; width: 50px}
    .extmatch li {padding-bottom: 14px; vertical-align: text-bottom; list-style-type: lower-roman}
    .correct {color: #000; font-weight: bold}
    .excluded {color:red; text-decoration: line-through}
    .excluded img {border: 2px solid red}
    .excluded img.in-exclusion {border: 0}
    .in-exclusion:hover {background-color: #FFE7A2}
    td p:first-child {margin-top: 0}
    .matrix {border:1px solid #808080; border-collapse: collapse}
    .matrix td {border:1px solid #808080}
    .mee {display: inline}
    .subsect_table {margin-left: 6px; margin-bottom: 10px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
<?php
  if ($propertyObj->get_latex_needed() == 1) {
    echo "<script type=\"text/javascript\" src=\"../js/jquery-migrate-1.2.1.min.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"../tools/mee/mee/js/mee_src.js\"></script>\n";
  }

  $render = new render($configObject);
  $render->render_html5_js(json_encode($jstring));

?>

  <script>
    function toggle(qID, parts, marks) {
      for (i=1; i<=parts; i++) {
        if ($('#status_' + qID).val().substr(0,1) == '1') {
          $('#q_' + qID + '_' + i).removeClass('excluded');
        } else {
          $('#q_' + qID + '_' + i).addClass('excluded');
        }
      }

      var new_value = '';
      if ($('#status_' + qID).val().substr(0,1) == '1') {
        for (i=1; i<=marks; i++) {
          new_value += '0';
        }
        $('#status_' + qID).val(new_value);
        $('#button_' + qID).attr('src', '../artwork/exclude_off.gif');
      } else {
        for (i=1; i<=marks; i++) {
          new_value += '1';
        }
        $('#status_' + qID).val(new_value);
        $('#button_' + qID).attr('src', '../artwork/exclude_on.gif');
      }
    }

    function blankCorrect(q_id, part_no) {
      window.open("blank_remark.php?q_id=" + q_id + "&blank=" + part_no + "&paperID=<?php echo $_GET['paperID']; ?>&startdate=<?php echo $_GET['startdate']; ?>&enddate=<?php echo $_GET['enddate']; ?>","remark","width=500,height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");

      return false;
    }

    function clacCorrect(q_id) {
      window.open("enhanced_calc_remark.php?q_id=" + q_id + "&paperID=<?php echo $_GET['paperID']; ?>&startdate=<?php echo $_GET['startdate']; ?>&enddate=<?php echo $_GET['enddate']; ?>","remark","width=850,height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");

      return false;
    }
  </script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(154);
?>
<div id="content">
<form name="theform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post" autocomplete="off">
<?php
  // Get some paper properties
  $paper_title = $propertyObj->get_paper_title();
  $paper_type = $propertyObj->get_paper_type();
  $labelcolor = $propertyObj->get_labelcolor();
  $themecolor = $propertyObj->get_themecolor();
  $marking = $propertyObj->get_marking();
  $pass_mark = $propertyObj->get_pass_mark();

  $moduleIDs = Paper_utils::get_modules($paperID, $mysqli);
  
  $exclusions = new Exclusion($paperID, $mysqli);
  $exclusions->load();

  // Get the standards setting
  if ($marking{0} == '2') {
    $tmp_parts = explode(',', $marking);

    $standard_setting = new StandardSetting($mysqli);
    $std_set_array = $standard_setting->get_ratings_by_question($tmp_parts[1]);
  }

  // Get all the users on the module(s) the paper is on.
  $users_on_modules = '';
  if (is_array($moduleIDs)) {
    $users_on_modules = '';
    $moduleIDs_in = "'" . implode(',', array_keys($moduleIDs)) . "'";
    $mod_query = $mysqli->prepare("SELECT idMod, userID, moduleid FROM modules_student, modules WHERE modules.id = modules_student.idMod AND idMod IN ($moduleIDs_in)");
    $mod_query->execute();
    $mod_query->bind_result($idMod, $tmp_userID, $tmp_moduleid);
    $mod_query->store_result();
    while ($mod_query->fetch()) {
	  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '' and $idMod != $_GET['repmodule']) {
	    continue; //this user is not on the module set in repmodule so dont put them in the array
	  }
	  if ($users_on_modules == '') {
        $users_on_modules = "'" . $tmp_userID . "'";
      } else {
        $users_on_modules .= ",'" . $tmp_userID . "'";
      }
    }
    $mod_query->close();
  }
  $student_modules_sql = '';
  if ($users_on_modules != '' and isset($_GET['repmodule']) and $_GET['repmodule'] != '') {
    $student_modules_sql = " AND log_metadata.userID IN ($users_on_modules)";
  }

  if ($_GET['studentsonly'] == 1) {
    $roles_sql = " AND (users.roles = 'Student' OR users.roles = 'graduate')";
  } else {
    $roles_sql = '';
  }
  // Calculate top and bottom cohorts.
  $student_list = '';
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT username, sum(mark) AS total_mark, started FROM (log0, users, log_metadata) WHERE log0.metadataID = log_metadata.id AND log_metadata.userID = users.id AND (users.roles='Student' OR users.roles='graduate') AND paperID = ? AND grade LIKE ? AND started >= ? AND started <= ? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%' AND student_grade NOT LIKE '%nhs%' $student_modules_sql GROUP BY username, paperID, started) UNION ALL (SELECT username, sum(mark) AS total_mark, log_metadata.started FROM (log1, users, log_metadata) WHERE log1.metadataID = log_metadata.id AND log_metadata.userID = users.id AND (users.roles='Student' OR users.roles='graduate') AND paperID = ? AND started >= ? AND started <= ? " . str_replace('log0', 'log1', $student_modules_sql) . " GROUP BY username, started) ORDER BY total_mark ASC, username");
    $result->bind_param('isssiss', $paperID, $_GET['repcourse'], $startdate, $enddate, $paperID, $startdate, $enddate);
  } else {
    $result = $mysqli->prepare("SELECT username, sum(mark) AS total_mark, started FROM (log$paper_type, users, log_metadata) WHERE log$paper_type.metadataID = log_metadata.id $roles_sql AND log_metadata.userID = users.id AND paperID = ? AND grade LIKE ? AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? $student_modules_sql GROUP BY username, started ORDER BY total_mark ASC, username");
    $result->bind_param('isss', $paperID, $_GET['repcourse'], $startdate, $enddate);
  }
  $result->execute();
  $result->bind_result($username, $total_mark, $started);
  $result->store_result();

  $student_no = 0;
  $bottom_cohort = array();
  $user_no = round(($result->num_rows / 100) * $cohort_percent);
  $user_total = $result->num_rows;
	if ($user_total == 1) {
	  // If a single user load them into top and bottom cohorts.
		$result->fetch();
		$bottom_cohort[$started][$username] = '';
		$top_cohort[$started][$username] = '';
		$student_no = 1;
		$user_no = 1;
		$cohort_percent = 100;
	} else {
		while ($result->fetch()) {
			if ($student_no < $user_no) {
				$bottom_cohort[$started][$username] = '';
			} elseif ($student_no >= ($user_total - $user_no)) {
				$top_cohort[$started][$username] = '';
			}
			$student_no++;
		}
	}
  $result->close();
	
  // Capture the log data first.
  $freq_array       = array();
  $bottom_log_array = array();
  $top_log_array    = array();

  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT username, log_metadata.userID, log0.q_id, user_answer, q_type, score_method, display_method, settings, mark, totalpos, option_order, started FROM log0, log_metadata, questions, users WHERE log0.metadataID = log_metadata.id AND log0.q_id = questions.q_id AND paperID = ? AND grade LIKE ? AND users.id = log_metadata.userID AND (users.roles='Student' OR users.roles='graduate') AND started >= ? AND started <= ? $student_modules_sql) UNION ALL (SELECT username, log_metadata.userID, log1.q_id, user_answer, q_type, score_method, display_method, settings, mark, totalpos, option_order, started FROM log1, log_metadata, questions,  users WHERE log1.metadataID = log_metadata.id AND log1.q_id=questions.q_id AND paperID = ? AND grade LIKE ? AND users.id = log_metadata.userID AND (users.roles='Student' OR users.roles='graduate') AND started >= ? AND started <= ? " . str_replace('log0', 'log1', $student_modules_sql) . ")");
    $result->bind_param('isssisss', $paperID, $_GET['repcourse'], $startdate, $enddate, $paperID, $_GET['repcourse'], $startdate, $enddate);
  } else {
    $result = $mysqli->prepare("SELECT username, log_metadata.userID, log$paper_type.q_id, user_answer, q_type, score_method, display_method, settings, mark, totalpos, option_order, started FROM log$paper_type, log_metadata, questions, users WHERE log$paper_type.metadataID = log_metadata.id AND log$paper_type.q_id = questions.q_id AND paperID = ? AND grade LIKE ? AND users.id = log_metadata.userID AND (users.roles='Student' OR users.roles='graduate') AND DATE_ADD(started, INTERVAL 2 MINUTE) >= ? AND started <= ? $student_modules_sql");
    $result->bind_param('isss', $paperID, $_GET['repcourse'], $startdate, $enddate);
  }
  $result->execute();
  $result->bind_result($username, $tmp_userID, $question_ID, $tmp_answer, $q_type, $score_method, $display_method, $settings, $mark, $totalpos, $option_order, $started);

  while ($result->fetch()) {
    storeData($freq_array, $question_ID, $tmp_answer, $q_type, $display_method, $settings, $mark, $totalpos, $stop_words, 'all');
    if (isset($bottom_cohort[$started][$username])) {
      storeData($bottom_log_array, $question_ID, $tmp_answer, $q_type, $display_method, $settings, $mark, $totalpos, $stop_words, 'bottom');
    }
    if (isset($top_cohort[$started][$username])) {
      storeData($top_log_array, $question_ID, $tmp_answer, $q_type, $display_method, $settings, $mark, $totalpos, $stop_words, 'top');
    }
  }
  $result->close();
  
  if ($user_total == 0) {
    // No one has taken the paper yet.
    echo '<div class="head_title">';

    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
    echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
    if (isset($_GET['folder']) and $_GET['folder'] != '') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';

    echo "<div class=\"page_title\">" . $string['reporttitle'] . "</div>\n";
    echo "</div>\n";
		echo $notice->info_strip($string['PaperNotAttempted'], 100);
        
  } else {
  	// Capture the paper makeup.
    $display_header = true;
    $question_no = 0;
    $old_q_id = 0;
    $old_screen = 1;
    $options_buffer = array();
    $correct_buffer = array();
    $o_media_buffer = array();
    $qids_instring = '';
    if (isset($_GET['q_ids']) and $_GET['q_ids'] != '') {
      $qids_instring = ' AND q_id IN(' . $_GET['q_ids']. ')';
	  }

    $sql = <<<SQL
SELECT screen, q_id, q_type, theme, scenario, leadin, option_text, o_media,
 o_media_width, o_media_height, score_method, display_method, q_media, q_media_width,
 q_media_height, correct, '' AS std
FROM (papers, questions) LEFT JOIN options ON questions.q_id = options.o_id
WHERE papers.paper = ? AND papers.question=questions.q_id $qids_instring
ORDER BY screen, display_pos, id_num
SQL;
    $result = $mysqli->prepare($sql);
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($screen, $q_id, $q_type, $theme, $scenario, $leadin, $option_text, $o_media, $o_media_width, $o_media_height, $score_method, $display_method, $q_media, $q_media_width, $q_media_height, $correct, $std);
    $result->store_result();
    while ($result->fetch()) {
      if ($display_header == true) {
        echo '<div class="head_title">';
        echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
        echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
        if (isset($_GET['folder']) and $_GET['folder'] != '') {
          echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../folder/index.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
        } elseif (isset($_GET['module']) and $_GET['module'] != '') {
          echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
        }
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';

        echo "<div class=\"page_title\">" . $string['reporttitle'] . "</div>\n";
        echo "</div>\n";
        if ($graded) {
            echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">";
            echo "<tr><td><div class=\"yellowwarn\"><img src=\"../artwork/paper_locked_padlock.png\" width=\"32\" height=\"32\" alt=\"Published\" /></div></td><td style=\"vertical-align:middle\"><div class=\"yellowwarn\">" . $string['paperpublishedwarning'] . " <a href=\"#\" class=\"blacklink\" onclick=\"launchHelp(189); return false;\">". $string['paperlockedclick'] ."</a></div></td></tr>\n";
            echo "</table>";
        }
        echo '<br /><div class="key">';
        echo '<table cellpadding="2" cellspacing="0" border="0">';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['totalcandidatenumber'] . '</td><td style="width:375px">' . number_format($user_total) . '</td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="Warning" class="in-exclusion" /> ' . $string['warning'] . ' ' . $string['p_warning'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right"><nobr>' . $string['groupsizes'] . '</nobr></td><td>' . $cohort_percent . '% (' . $user_no . ' ' . $string['pergroup'] . ')</td><td rowspan="7" style="vertical-align:top"><img src="../artwork/red_flag.png" width="14" height="14" alt="Warning" class="in-exclusion" /> ' . $string['warning'] . ' ' . $string['d_warning'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['boldstems'] . '</td><td>' . $string['correctanswers'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">p =</td><td>' . $string['p_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">d =</td><td>' . $string['d_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">t =</td><td>' . $string['t_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">u =</td><td>' . $string['u_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">l =</td><td>' . $string['l_definition'] . '</td></tr>';
        echo '</table></div><br />';

        echo '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
        $display_header = false;
      }
      if ($question_no == 0) {
        $old_labelcolor = $labelcolor;
        $old_themecolor = $themecolor;
      }
      if ($old_q_id != $q_id and $old_q_id > 0) {   // New question.
        $question_no++;
        if ($old_q_type == 'info') $question_no--;

        if (isset($std_set_array[$old_q_id])) $old_std = $std_set_array[$old_q_id];

        displayQuestion($exclusions, $question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $o_media_buffer, $bottom_log_array, $top_log_array, $freq_array, $correct_buffer, $user_no, $old_score_method, $old_display_method, $old_themecolor, $old_std);
        $options_buffer = array();
        $correct_buffer = array();
        $o_media_buffer = array();
        if ($old_screen != $screen) {
          echo '<tr><td colspan="2">';
          echo '<div class="screenbrk"><span class="scr_no">' . $string['screen'] . '&nbsp;' . $screen . '</span></div>';
          echo '</td></tr>';
        }
      }
      if ($q_type == 'labelling') {
        $tmp_first_split = explode(';', $correct);
        $tmp_second_split = explode('$', $tmp_first_split[11]);
        for ($label_no = 4; $label_no <= 200; $label_no += 4) {
          if (isset($tmp_second_split[$label_no])) {
            if (substr($tmp_second_split[$label_no],0,1) != '|') {
              // The label has text, i.e. is not blank.
              // The stored answer coordinates are sometimes floating points, rather than integers.
              // We need to round them to ensure they will always matach the coordiantes that are 
              // stored in the user answers. If we do not do this frequency analysis for the question type
              // will sometimes not work. See ROGO-1822.
              $x_coordinate = round($tmp_second_split[$label_no-2]);
              $y_coordiante = round($tmp_second_split[$label_no-1] - 25);
              $options_buffer[] = trim(substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'))) . '|' . $x_coordinate . '|' . $y_coordiante;
              if ($x_coordinate >= 220) {
                $correct_buffer[] = $x_coordinate . 'x' . $y_coordiante;
              }
            }
          }
        }
        $o_media_buffer[] = '';
      } elseif ($q_type == 'blank') {
        $not_used = preg_match("/mark=\"([0-9]{1,3})\"/",$option_text,$results);
        $blank_details = explode('[blank',$option_text);
        $no_answers = count($blank_details) - 1;
        for ($i=1; $i<=$no_answers; $i++) {
          $blank_details[$i] = preg_replace("| mark=\"([0-9]{1,3})\"|","",$blank_details[$i]);
          $blank_details[$i] = preg_replace("| size=\"([0-9]{1,3})\"|","",$blank_details[$i]);

          $blank_details[$i] = substr($blank_details[$i],(strpos($blank_details[$i],']') + 1));
          $blank_details[$i] = substr($blank_details[$i],0,strpos($blank_details[$i],'[/blank]'));
          $answer_list = explode(',',$blank_details[$i]);
          $answer_list[0] = str_replace("[/blank]",'',$answer_list[0]);
          if ($score_method == 'textboxes') {
            foreach ($answer_list as $individual_answer) {
              $correct_buffer[] = html_entity_decode(trim($individual_answer));
            }
          } else {
            $correct_buffer[] = html_entity_decode(trim($answer_list[0]));
          }
        }
        $options_buffer[] = $option_text;
        $o_media_buffer[] = '';
      } else {
        $options_buffer[] = $option_text;
        $correct_buffer[] = $correct;
        if ($o_media != '') {
          $o_media_buffer[] = array($o_media, $o_media_width, $o_media_height);
        } else {
          $o_media_buffer[] = '';
        }
      }
      $old_q_id = $q_id;
      $old_theme = $theme;
      $old_scenario = $scenario;
      $old_leadin = $leadin;
      $old_q_type = $q_type;
      $old_q_media = $q_media;
      $old_q_media_width = $q_media_width;
      $old_q_media_height = $q_media_height;
      $old_correct = $correct;
      $old_score_method = $score_method;
      $old_display_method = $display_method;
      $old_std = $std;
      $old_screen = $screen;
    }
    $result->close();

    $question_no++;
    if ($old_q_type == 'info') $question_no--;

    if (isset($std_set_array[$old_q_id])) $old_std = $std_set_array[$old_q_id];

    displayQuestion($exclusions, $question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $o_media_buffer, $bottom_log_array, $top_log_array, $freq_array, $correct_buffer, $user_no, $old_score_method, $old_display_method, $old_themecolor, $old_std);
  ?>
  </table>
  <br />

  <div class="subsect_table"><div class="subsect_title"><?php echo $string['summary'] ?></div><div class="subsect_hr"><hr noshade="noshade" /></div></div>
  
  
  <table cellpadding="0" cellspacing="0" style="width:650px; margin-left:40px">
  <tr><td colspan="2" style="padding-left:4px"><?php echo $string['msg']; ?></td></tr>
  <tr>
  <td style="vertical-align:top">
  <table cellpadding="4" cellspacing="0">
  <tr style="font-weight:bold"><td><?php echo $string['difficulty']; ?></td><td style="text-align:center">p</td><td><?php echo $string['noofitems']; ?></td><td></td></tr>
  <tr><td><?php echo $string['veryeasy']; ?></td><td>&gt; 0.8</td><td style="text-align:right"><?php echo $pstats['ve']; ?></td><td></td></tr>
  <tr><td><?php echo $string['easy']; ?></td><td>0.6-0.8</td><td style="text-align:right"><?php echo $pstats['e']; ?></td><td></td></tr>
  <tr><td><?php echo $string['moderate']; ?></td><td>0.4-0.6</td><td style="text-align:right"><?php echo $pstats['m']; ?></td><td></td></tr>
  <tr><td><?php echo $string['hard']; ?></td><td>0.2-0.4</td><td style="text-align:right"><?php echo $pstats['h']; ?></td><td></td></tr>
  <tr style="color:#C00000"><td><?php echo $string['veryhard']; ?></td><td>&lt; 0.2</td><td style="text-align:right"><?php echo $pstats['vh']; ?></td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="<?php echo $string['warning1']; ?>" class="in-exclusion" /></td></tr>
  <tr><td><?php echo $string['mean']; ?></td><td style="text-align:right"><?php
  if (isset($pstats['no'])) {
    echo number_format($pstats['total'] / $pstats['no'], 2); 
  }
  ?></td><td></td><td></td></tr>
  </table>
  </td>
  <td style="vertical-align:top">
  <table cellpadding="4" cellspacing="0" >
  <tr style="font-weight:bold"><td><?php echo $string['discrimination']; ?></td><td style="text-align:center">d</td><td><?php echo $string['noofitems']; ?></td><td></td></tr>
  <tr><td><?php echo $string['highest']; ?></td><td>&gt;= 0.35</td><td style="text-align:right"><?php echo $dstats['highest']; ?></td><td></td></tr>
  <tr><td><?php echo $string['high']; ?></td><td>0.25-0.35</td><td style="text-align:right"><?php echo $dstats['high']; ?></td><td></td></tr>
  <tr><td><?php echo $string['intermediate']; ?></td><td>0.15-0.25</td><td style="text-align:right"><?php echo $dstats['intermediate']; ?></td><td></td></tr>
  <tr style="color:#C00000"><td><?php echo $string['low']; ?></td><td>&lt; 0.15</td><td style="text-align:right"><?php echo $dstats['low']; ?></td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="<?php echo $string['warning2']; ?>" class="in-exclusion" /></td></tr>
  <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
  <tr><td><?php echo $string['mean']; ?></td><td style="text-align:right"><?php
  if (isset($dstats['no'])) {
    echo number_format($dstats['total'] / $dstats['no'], 2); 
  }
  ?></td><td></td><td></td></tr>
  </table>
  </td>
  </tr>
  </table>

  <?php

    // Clear previous performance stats
    $id_list = array();
    $result = $mysqli->prepare("SELECT id FROM performance_main WHERE paperID = ?");
    
    if ($mysqli->error) {
      echo $string['showerror'] . "<br >";
    }
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($id);
    while ($result->fetch()) {
      $id_list[] = $id;
    }
    $result->close();

    // Remove records from performance_main
    $date_started = substr($started, 0, 10);
    $remove = $mysqli->prepare("DELETE FROM performance_main WHERE paperID = ? AND taken = ?");
    $remove->bind_param('is', $paperID, $date_started);
    $remove->execute();
    $remove->close();

    // Remove records from performance_details
    if (count($id_list) > 0) {
      $remove = $mysqli->prepare("DELETE FROM performance_details WHERE perform_id IN (" . implode(',', $id_list) . ")");
      $remove->execute();
      $remove->close();
    }

    if ($_GET['percent'] == 100) {
      $tmp_percent = 27;  // The default for U/L analysis
    } else {
      $tmp_percent = $_GET['percent'];
    }

    if (isset($pstats['no'])) {
      // Write records into performance_main
      //----------------------------------------------------------------------------------------------
      $sql = '';
      $params = '';
      $variables = array();
      $tmp = array();
      foreach ($dstats_array as $qid=>$question_data) {
        if ($sql == '') {
          $sql = 'INSERT INTO performance_main VALUES (NULL, ?, ?, ?, ?, ?)';
        } else {
          $sql .= ', (NULL, ?, ?, ?, ?, ?)';
        }
        $params .= 'iiiis';
        $variables[] = $qid;
        $variables[] = $paperID;
        $variables[] = $tmp_percent;
        $variables[] = $user_total;
        $variables[] = $date_started;
      }

      $record = $mysqli->prepare($sql);

      array_unshift($variables, $params);
      foreach ($variables as $key => $value) {
        $tmp[$key] = &$variables[$key];
      }
      call_user_func_array(array($record,'bind_param'), $tmp);

      $record->execute();
      $record->close();

      // Write records into performance_details
      //----------------------------------------------------------------------------------------------
      $q_rec_ids = array();
      // First a quick query to get the IDs from performance_main to use in performance_details
      $result = $mysqli->prepare("SELECT id, q_id FROM performance_main WHERE paperID = ? AND taken = ?");
      $result->bind_param('is', $paperID, $date_started);
      $result->execute();
      $result->bind_result($id, $tmp_q_id);
      while ($result->fetch()) {
        $q_rec_ids[$tmp_q_id] = $id;
      }
      $result->close();

      $sql = '';
      $params = '';
      $variables = array();
      $tmp = array();
      foreach ($dstats_array as $qid=>$question_data) {
        foreach ($question_data as $part_no=>$d_value) {
          if ($sql == '') {
            $sql = 'INSERT INTO performance_details VALUES (?, ?, ?, ?)';
          } else {
            $sql .= ', (?, ?, ?, ?)';
          }
          $params .= 'iiii';
          $variables[] = $q_rec_ids[$qid];
          $variables[] = $part_no;
          $variables[] = $pstats_array[$qid][$part_no];
          $variables[] = $d_value;
        }
      }

      $record = $mysqli->prepare($sql);

      array_unshift($variables, $params);
      foreach ($variables as $key => $value) {
        $tmp[$key] = &$variables[$key];
      }
      call_user_func_array(array($record,'bind_param'), $tmp);

      $record->execute();
      $record->close();
    }
  ?>

  <input type="hidden" name="question_no" value="<?php echo $ex_no; ?>" />
  <div align="center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" <?php if ($graded) { echo 'disabled="disabled"'; } else { echo 'class="ok"'; }?>/><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="history.back()" class="cancel" /></div>
  </form>
</div>
<?php
}
$mysqli->close();
?>
</body>
</html>
