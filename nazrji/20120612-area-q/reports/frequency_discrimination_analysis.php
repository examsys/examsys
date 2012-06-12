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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/media.inc';
  require '../include/errors.inc';
  
  check_var('paperID', 'GET', true, false);
  
  $stop_words = array('-'=>'-','a'=>'a','about'=>'about','above'=>'above','across'=>'across','after'=>'after','again'=>'again','against'=>'against','all'=>'all','almost'=>'almost','alone'=>'alone','along'=>'along','already'=>'already','also'=>'also','although'=>'although','always'=>'always','among'=>'among','an'=>'an','and'=>'and','another'=>'another','any'=>'any','anybody'=>'anybody','anyone'=>'anyone','anything'=>'anything','anywhere'=>'anywhere','are'=>'are','area'=>'area','areas'=>'areas','around'=>'around','as'=>'as','ask'=>'ask','asked'=>'asked','asking'=>'asking','asks'=>'asks','at'=>'at','away'=>'away','b'=>'b','back'=>'back','backed'=>'backed','backing'=>'backing','backs'=>'backs','be'=>'be','became'=>'became','because'=>'because','become'=>'become','becomes'=>'becomes','been'=>'been','before'=>'before','began'=>'began','behind'=>'behind','being'=>'being','beings'=>'beings','best'=>'best','better'=>'better','between'=>'between','big'=>'big','both'=>'both','but'=>'but','by'=>'by','c'=>'c','came'=>'came','can'=>'can','cannot'=>'cannot','case'=>'case','cases'=>'cases','certain'=>'certain','certainly'=>'certainly','clear'=>'clear','clearly'=>'clearly','come'=>'come','could'=>'could','d'=>'d','did'=>'did','differ'=>'differ','different'=>'different','differently'=>'differently','do'=>'do','does'=>'does','done'=>'done','down'=>'down','downed'=>'downed','downing'=>'downing','downs'=>'downs','during'=>'during','e'=>'e','each'=>'each','early'=>'early','either'=>'either','end'=>'end','ended'=>'ended','ending'=>'ending','ends'=>'ends','enough'=>'enough','even'=>'even','evenly'=>'evenly','ever'=>'ever','every'=>'every','everybody'=>'everybody','everyone'=>'everyone','everything'=>'everything','everywhere'=>'everywhere','f'=>'f','face'=>'face','faces'=>'faces','fact'=>'fact','facts'=>'facts','far'=>'far','felt'=>'felt','few'=>'few','find'=>'find','finds'=>'finds','first'=>'first','for'=>'for','four'=>'four','from'=>'from','full'=>'full','fully'=>'fully','further'=>'further','furthered'=>'furthered','furthering'=>'furthering','furthers'=>'furthers','g'=>'g','gave'=>'gave','general'=>'general','generally'=>'generally','get'=>'get','gets'=>'gets','give'=>'give','given'=>'given','gives'=>'gives','go'=>'go','going'=>'going','good'=>'good','goods'=>'goods','got'=>'got','great'=>'great','greater'=>'greater','greatest'=>'greatest','group'=>'group','grouped'=>'grouped','grouping'=>'grouping','groups'=>'groups','h'=>'h','had'=>'had','has'=>'has','have'=>'have','having'=>'having','he'=>'he','her'=>'her','here'=>'here','herself'=>'herself','high'=>'high','higher'=>'higher','highest'=>'highest','him'=>'him','himself'=>'himself','his'=>'his','how'=>'how','however'=>'however','i'=>'i','if'=>'if','important'=>'important','in'=>'in','interest'=>'interest','interested'=>'interested','interesting'=>'interesting','interests'=>'interests','into'=>'into','is'=>'is','it'=>'it','its'=>'its','itself'=>'itself','j'=>'j','just'=>'just','k'=>'k','keep'=>'keep','keeps'=>'keeps','kind'=>'kind','knew'=>'knew','know'=>'know','known'=>'known','knows'=>'knows','l'=>'l','large'=>'large','largely'=>'largely','last'=>'last','later'=>'later','latest'=>'latest','least'=>'least','less'=>'less','let'=>'let','lets'=>'lets','like'=>'like','likely'=>'likely','long'=>'long','longer'=>'longer','longest'=>'longest','m'=>'m','made'=>'made','make'=>'make','making'=>'making','man'=>'man','many'=>'many','may'=>'may','me'=>'me','member'=>'member','members'=>'members','men'=>'men','might'=>'might','more'=>'more','most'=>'most','mostly'=>'mostly','mr'=>'mr','mrs'=>'mrs','much'=>'much','must'=>'must','my'=>'my','myself'=>'myself','n'=>'n','necessary'=>'necessary','need'=>'need','needed'=>'needed','needing'=>'needing','needs'=>'needs','never'=>'never','new'=>'new','newer'=>'newer','newest'=>'newest','next'=>'next','no'=>'no','nobody'=>'nobody','non'=>'non','noone'=>'noone','not'=>'not','nothing'=>'nothing','now'=>'now','nowhere'=>'nowhere','number'=>'number','numbers'=>'numbers','of'=>'o','of'=>'of','off'=>'off','often'=>'often','old'=>'old','older'=>'older','oldest'=>'oldest','on'=>'on','once'=>'once','one'=>'one','only'=>'only','open'=>'open','opened'=>'opened','opening'=>'opening','opens'=>'opens','or'=>'or','order'=>'order','ordered'=>'ordered','ordering'=>'ordering','orders'=>'orders','other'=>'other','others'=>'others','our'=>'our','out'=>'out','over'=>'over','p'=>'p','part'=>'part','parted'=>'parted','parting'=>'parting','parts'=>'parts','per'=>'per','perhaps'=>'perhaps','place'=>'place','places'=>'places','point'=>'point','pointed'=>'pointed','pointing'=>'pointing','points'=>'points','possible'=>'possible','present'=>'present','presented'=>'presented','presenting'=>'presenting','presents'=>'presents','problem'=>'problem','problems'=>'problems','put'=>'put','puts'=>'puts','q'=>'q','quite'=>'quite','r'=>'r','rather'=>'rather','really'=>'really','right'=>'right','room'=>'room','rooms'=>'rooms','s'=>'s','said'=>'said','same'=>'same','saw'=>'saw','say'=>'say','says'=>'says','second'=>'second','seconds'=>'seconds','see'=>'see','seem'=>'seem','seemed'=>'seemed','seeming'=>'seeming','seems'=>'seems','sees'=>'sees','several'=>'several','shall'=>'shall','she'=>'she','should'=>'should','show'=>'show','showed'=>'showed','showing'=>'showing','shows'=>'shows','side'=>'side','sides'=>'sides','since'=>'since','small'=>'small','smaller'=>'smaller','smallest'=>'smallest','so'=>'so','some'=>'some','somebody'=>'somebody','someone'=>'someone','something'=>'something','somewhere'=>'somewhere','state'=>'state','states'=>'states','still'=>'still','such'=>'such','sure'=>'sure','t'=>'t','take'=>'take','taken'=>'taken','than'=>'than','that'=>'that','the'=>'the','their'=>'their','them'=>'them','then'=>'then','there'=>'there','therefore'=>'therefore','these'=>'these','they'=>'they','thing'=>'thing','things'=>'things','think'=>'think','thinks'=>'thinks','this'=>'this','those'=>'those','though'=>'though','thought'=>'thought','thoughts'=>'thoughts','three'=>'three','through'=>'through','thus'=>'thus','to'=>'to','today'=>'today','together'=>'together','too'=>'too','took'=>'took','toward'=>'toward','turn'=>'turn','turned'=>'turned','turning'=>'turning','turns'=>'turns','two'=>'two','u'=>'u','under'=>'under','until'=>'until','up'=>'up','upon'=>'upon','us'=>'us','use'=>'use','used'=>'used','uses'=>'uses','v'=>'v','very'=>'very','w'=>'w','want'=>'want','wanted'=>'wanted','wanting'=>'wanting','wants'=>'wants','was'=>'was','way'=>'way','ways'=>'ways','we'=>'we','well'=>'well','wells'=>'wells','went'=>'went','were'=>'were','what'=>'what','when'=>'when','where'=>'where','whether'=>'whether','which'=>'which','while'=>'while','who'=>'who','whole'=>'whole','whose'=>'whose','why'=>'why','will'=>'will','with'=>'with','within'=>'within','without'=>'without','work'=>'work','worked'=>'worked','working'=>'working','works'=>'works','would'=>'would','x'=>'x','y'=>'y','year'=>'year','years'=>'years','yet'=>'yet','you'=>'you','young'=>'young','younger'=>'younger','youngest'=>'youngest','your'=>'your','yours'=>'yours','z'=>'z');

  $cohort_percent = $_GET['percent'];
  if ($cohort_percent == 100) $cohort_percent = 27;
  $pstats = array('ve'=>0,'e'=>0,'m'=>0,'h'=>0,'vh'=>0);
  $dstats = array('highest'=>0,'high'=>0,'intermediate'=>0,'low'=>0);

  function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
    $sortarr = array();
    foreach ($marray as $row) {
      $sortarr[] = $row[$column];
    }
    $sortarr = array_map('strtolower',$sortarr);
    $sort_method = SORT_NUMERIC;
    if ($sort_order == 'asc') {
      array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
    } else {
      array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
    }
    return $marray;
  }

  function pStats($value) {
    global $pstats, $string;
		
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
      $html = '<nobr><span style="color:#C00000">p=' . number_format($value,2) . '</span><img src="../artwork/red_flag.png" width="14" height="14" alt="' . $string['warning1'] . '" border="0" class="in-exclusion" /></nobr>';
    } else {
      $html = 'p=' . number_format($value,2);
    }
    return $html;
  }

  function dStats($value) {
    global $dstats, $string;
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
      $html = '<nobr><span style="color:#C00000">d=' . number_format($value,2) . '</span><img src="../artwork/red_flag.png" width="14" height="14" alt="' . $string['warning2'] . '" border="0" class="in-exclusion" /></nobr>';
    } else {
      $html = 'd=' . number_format($value,2);
    }
    return $html;
  }
  
  function calcDiscrimination($no_students, &$top_log_q_id, &$bottom_log_q_id, $i, $keys) {
    global $q_id;
    
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

  function storeData(&$log_array, $qID, $answer, $q_type, $scoring, $display, $mark, $totalpos, $opt_order, $analysis_type) {
    global $stop_words;
    
    if (!isset($log_array[$qID]['mark'])) $log_array[$qID]['mark'] = 0;
    if (!isset($log_array[$qID]['totalpos'])) $log_array[$qID]['totalpos'] = 0;

    switch ($q_type) {
      case 'blank':      
        $tmp_answer_parts = array();
        $tmp_answer_parts = explode('|',$answer);
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
      case 'calculation':
        $tmp_score_method = array();
        $tmp_score_method = explode(',',$display);
        $tolerance = $tmp_score_method[1];
        $tmp_first_split = explode('|', $answer);
        $user_ans_clean = $saved_response_clean = str_replace(',', '', str_replace(' ', '', $tmp_first_split[0]));
        if ($user_ans_clean == $tmp_first_split[1]) {
          if (isset($log_array[$qID][1]['correct'])) {
            $log_array[$qID][1]['correct']++;
          } else {
            $log_array[$qID][1]['correct'] = 1;
          }
        } else {
          if ($user_ans_clean == '') {
            if (isset($log_array[$qID][1]['u'])) {
              $log_array[$qID][1]['u']++;
            } else {
              $log_array[$qID][1]['u'] = 1;
            }
          } elseif (abs($user_ans_clean - $tmp_first_split[1]) <= $tolerance) {
            if (isset($log_array[$qID][1]['tolerance'])) {
              $log_array[$qID][1]['tolerance']++;
            } else {
              $log_array[$qID][1]['tolerance'] = 1;
            }
          } else {
            if (isset($log_array[$qID][1]['incorrect'])) {
              $log_array[$qID][1]['incorrect']++;
            } else {
              $log_array[$qID][1]['incorrect'] = 1;
            }
          }
        }
        break;
      case 'dichotomous':
      case 'true_false':
        for ($i=0; $i<strlen($answer); $i++) {
          $tmp_individual_answer = substr($answer, $i, 1);
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
        $sections = count($tmp_second_split);
        for ($i=2; $i<=count($tmp_second_split);$i+=4) {
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
            $log_array[$qID][$layer]['coords'] = substr($layer_answer,2);
          } else {
            $log_array[$qID][$layer]['coords'] .= ';' . substr($layer_answer,2);
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
        for ($i=0; $i<strlen($answer); $i++) {
          $tmp_individual_answer = substr($answer, $i, 1);
          if (isset($log_array[$qID][$i+1][$tmp_individual_answer])) {
            $log_array[$qID][$i+1][$tmp_individual_answer]++;
          } else {
            $log_array[$qID][$i+1][$tmp_individual_answer] = 1;
          }
        }
        $log_array[$qID]['mark'] += $mark;
        $log_array[$qID]['totalpos'] += $totalpos;
        break;
      case 'extmatch':
        $tmp_answer_parts = array();
        $tmp_answer_parts = explode('|',$answer);
        $i = 0;
        foreach ($tmp_answer_parts as $tmp_individual_answer) {
          $i++;
          $tmp_sub_parts = array();
          $tmp_sub_parts = explode('$',$tmp_individual_answer);
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
        $tmp_answer_parts = explode('|',$answer);
                
        for ($i=0; $i<count($tmp_answer_parts); $i++) {
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
        $tmp_answer_parts = explode(',',$answer);
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

  $paperID = $_GET['paperID'];
  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];
  $d_no = 0;
  $d_total = 0;

  if (isset($_POST['submit'])) {
    // Clear the database of any past exclusions from the current paper.
    if ($result = $mysqli->prepare("DELETE FROM question_exclude WHERE q_paper=?")) {
      $result->bind_param('i', $_GET['paperID']);
      $result->execute();
      $result->close();
    } else {
      display_error("Question_exclude Delete Error", $mysqli->error);
    }

    $old_q_id = 0;
    $old_status = '';
    
    for ($i=1; $i<=$_POST['question_no']; $i++) {
      $current_id = $_POST['id_' . $i];
      if ($current_id != $old_q_id) {
        if (strpos($old_status, '1') !== false) {
          if ($result = $mysqli->prepare("INSERT INTO question_exclude VALUES (NULL, ?, ?, ?, $userID, NOW(), '')")) {
            $result->bind_param('iis', $_GET['paperID'], $old_q_id, $old_status);
            $result->execute();
            $result->close();
          } else {
            display_error("Question_exclude Insert Error 1", $mysqli->error);
          }
        }
        $old_status = '';
      }
      $old_status .= $_POST['status_' . $i];
      $old_q_id = $_POST['id_' . $i];
    }
    if (strpos($old_status, '1') !== false) {
      if ($result = $mysqli->prepare("INSERT INTO question_exclude VALUES (NULL, ?, ?, ?, $userID, NOW(), '')")) {
        $result->bind_param('iis', $_GET['paperID'], $old_q_id, $old_status);
        $result->execute();
        $result->close();
      } else {
        display_error("Question_exclude Insert Error 2", $mysqli->error);
      }
    }
        
    header("location: ../paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder']);
  }

  function excludeButton(&$buttonID, $question_id, $status, $parts, $marks) {
    $buttonID++;
    if (strpos($status,'1') !== false) {
      $html = "<input type=\"hidden\" name=\"status_" . $buttonID . "\" id=\"status_" . $buttonID . "\" value=\"";
      for ($i=0; $i<$marks; $i++) $html .= '1';
      $html .= "\" /><input type=\"hidden\" name=\"id_" . $buttonID . "\" value=\"$question_id\" /><input type=\"hidden\" name=\"marks_" . $buttonID . "\" value=\"$marks\" /><img src=\"../artwork/exclude_on.gif\" id=\"button_" . $buttonID . "\" style=\"cursor:pointer\" onclick=\"toggle('$buttonID',$parts,$marks)\" width=\"23\" height=\"22\" border=\"0\" alt=\"Exclude\" class=\"in-exclusion\" />";
    } else {
      $html = "<input type=\"hidden\" name=\"status_" . $buttonID . "\" id=\"status_" . $buttonID . "\" value=\"";
      for ($i=0; $i<$marks; $i++) $html .= '0';
      $html .= "\" /><input type=\"hidden\" name=\"id_" . $buttonID . "\" value=\"$question_id\" /><input type=\"hidden\" name=\"marks_" . $buttonID . "\" value=\"$marks\" /><img src=\"../artwork/exclude_off.gif\" id=\"button_" . $buttonID . "\" style=\"cursor:pointer\" onclick=\"toggle('$buttonID',$parts,$marks)\" width=\"23\" height=\"22\" border=\"0\" alt=\"Exclude\" class=\"in-exclusion\" />";
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

  function displayQuestion($q_no, $q_id, $theme, $scenario, $leadin, $q_type, $correct, $q_media, $q_media_width, $q_media_height, $options, $o_media, $bottom_log, $top_log, $freq_log, $correct_buf, $candidate_no, $score_method, $display_method, $themecolor, $std) {
    global $ex_no, $d_no, $d_total, $excluded, $user_total, $language;
    if ($theme != '') echo "<tr><td colspan=\"2\"><h1 style=\"color:$themecolor\">$theme</h1></td></tr>\n";
    echo "<tr>\n";
    $tmp_std_array = (!empty($std)) ? explode(',',$std) : array();
    if ($q_type != 'extmatch' and $q_type != 'matrix' and $q_type != 'textbox') {
      if ($q_type == 'info') {
        echo "<td colspan=\"2\" style=\"padding-left:15px\">$leadin\n";
      } else {
        echo "<td class=\"q_no\">$q_no.&nbsp;</td><td><div";
        if ((($q_type == 'dichotomous' or $q_type == 'labelling' or $q_type == 'blank' or $q_type == 'hotspot') and $score_method == 'Mark per Question') or $q_type == 'flash') {
          echo ' id="q_' . ($ex_no+1) . '_1"';
          if (isset($excluded[$q_id])) {
             echo ' class="excluded"';
          }
        }
        echo '>';
        if (trim(str_replace('&nbsp;', '', $scenario)) != '') echo "$scenario<br /><br />\n";
        if ($q_type != 'hotspot' and $q_type != 'timedate' and $q_type != 'calculation' and $q_type != 'flash') echo "$leadin</div>\n";
        if ($q_media != '' and $q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'flash') {
          echo "<p align=\"center\">" . display_media($q_media,$q_media_width,$q_media_height) . "</p>\n";
        }
        if ($q_type != 'hotspot' and $q_type != 'labelling' and $q_type != 'calculation' and $q_type != 'blank' and $q_type != 'flash') echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
      }

      switch ($q_type) {
        case 'blank':
          $blank_details = explode('[blank',$options[0]);
          $array_size = count($blank_details);

          if ($score_method == 'Mark per Question') {
            if (isset($excluded[$q_id]) and substr($excluded[$q_id],0,1) == '1') {
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
              $end_start_tag = strpos($blank_details[$blank_count],']');
              $start_end_tag = strpos($blank_details[$blank_count],'[/blank]');
              $blank_options = substr($blank_details[$blank_count],($end_start_tag+1),($start_end_tag-1));
              $remainder = substr($blank_details[$blank_count], ($start_end_tag+8));
              if (isset($excluded[$q_id])) {
                $tmp_exclude = substr($excluded[$q_id],$blank_count-1,1);
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
                echo '<strong>' . chr($blank_count+64) . '.</strong> <input type="text" size="20" value="' . $tmp_parts[0] . '" />';
              }

              echo $remainder;
            }
            $blank_count++;
          }
          
          echo "<table cellspacing=\"0\" cellpadding=\"4\" border=\"0\" style=\"margin-left:20px\">\n";
          for ($i=1; $i<count($blank_details); $i++) {
            $end_start_tag = strpos($blank_details[$i],']');
            $start_end_tag = strpos($blank_details[$i],'[/blank]');
            $blank_options = substr($blank_details[$i],($end_start_tag+1),($start_end_tag-1));

            $blank_options = explode(',', $blank_options);
            
            $tmp_correct_no = 0;
            $tmp_top_no = 0;
            $tmp_bottom_no = 0;
            
            if ($display_method == 'dropdown') {
              $blank_word = strtolower(trim($blank_options[0]));
              if (isset($freq_log[$q_id][$i+1][$blank_word])) $tmp_correct_no += $freq_log[$q_id][$i+1][$blank_word];
              if (isset($top_log[$q_id][$i+1][$blank_word])) $tmp_top_no += $top_log[$q_id][$i+1][$blank_word];
              if (isset($bottom_log[$q_id][$i+1][$blank_word])) $tmp_bottom_no += $bottom_log[$q_id][$i+1][$blank_word];
              
              $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], $i+1, $blank_word);
            } else {
              foreach ($blank_options as $blank_option) {
                $blank_option = strtolower(trim($blank_option));
                if (isset($freq_log[$q_id][$i+1][$blank_option])) {
                  $tmp_correct_no += $freq_log[$q_id][$i+1][$blank_option];
                }
                if (isset($top_log[$q_id][$i+1][$blank_option])) {
                  $tmp_top_no += $top_log[$q_id][$i+1][$blank_option];
                }
                if (isset($bottom_log[$q_id][$i+1][$blank_option])) {
                  $tmp_bottom_no += $bottom_log[$q_id][$i+1][$blank_option];
                }
              }
              $d = calcDiscrimination($candidate_no, $top_log[$q_id], $bottom_log[$q_id], $i+1, $blank_options);
              
            }
            $t = number_format(($tmp_correct_no/$user_total)*100,0);
            
            $d_no++;
            $d_total += $d;
            $html = '';
            
            $u = number_format(($tmp_top_no/$candidate_no)*100,0);
            $l = number_format(($tmp_bottom_no/$candidate_no)*100,0);
            
            echo "<tr><td>" . chr($i+64) . ".</td>";
            if ($score_method == 'Mark per Option') {
              if (isset($excluded[$q_id])) {
                echo '<td>' . excludeButton($ex_no, $q_id, substr($excluded[$q_id], $i-1,1), 1, 1) . '</td>';
              } else {
                echo '<td>' . excludeButton($ex_no, $q_id, 0, 1, 1) . '</td>';
              }
            }
            echo "<td>" . pStats($tmp_correct_no/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=$t%</td><td>u=$u%</td><td>l=$l%</td>";
            
            if (isset($tmp_std_array[$blank_count-1])) {
              echo '<td class="std">' . $tmp_std_array[$blank_count-1] . '</td>';
            }
            echo "<td id=\"q_" . ($ex_no) . "_1\"";
            if (isset($excluded[$q_id]) and substr($excluded[$q_id], $i-1,1) == '1' and $score_method == 'Mark per Option') echo ' class="excluded"';
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
              echo "<td><a href=\"#\" onclick=\"return manCorrect($q_id, $i)\">Correct</a></td>";
            }
            echo "</tr>";
          }
          echo "</table>\n";
          break;
        case 'calculation':
          if (!isset($freq_log[$q_id][1]['correct'])) $freq_log[$q_id][1]['correct'] = '';
          
          echo "<p>\n<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
          $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],1,'correct');
          if (isset($freq_log[$q_id][1]['correct'])) {
            $t = number_format(($freq_log[$q_id][1]['correct']/$user_total)*100,0);
          } else {
            $t = 0;
          }
          if (isset($top_log[$q_id][1]['correct'])) {
            $u = number_format(($top_log[$q_id][1]['correct']/$candidate_no)*100,0);
          } else {
            $u = 0;
          }
          if (isset($bottom_log[$q_id][1]['correct'])) {
            $l = number_format(($bottom_log[$q_id][1]['correct']/$candidate_no)*100,0);
          } else {
            $l = 0;
          }
          if (isset($excluded[$q_id])) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '';
          }
          
          echo "<tr><td>" . excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1) . "</td><td style=\"width:60px\"><strong>t=" . $t . "%</strong></td><td><strong>u=" . $u . "%</strong></td><td><strong>l=" . $l . "%</strong></td><td><span class=\"std\">" . $std . "</span></td><td id=\"q_" . $ex_no . "_1\"";
          if (isset($excluded[$q_id]) and $excluded[$q_id] == '1') echo ' class="excluded"';
          echo ">$leadin</td></tr>\n";
          echo "<tr><td colspan=\"6\">&nbsp;</td></tr>";
          echo "<tr><td></td><td>" . pStats($freq_log[$q_id][1]['correct']/$user_total) . "</td><td colspan=\"4\">" . dStats($d) . "</td></tr>";
          break;
        case 'dichotomous':
          if ($score_method == 'Mark per Question') {
            if (isset($excluded[$q_id]) and substr($excluded[$q_id],0,1) == '1') {
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
            
            if (isset($excluded[$q_id])) {
              $tmp_exclude = substr($excluded[$q_id],$i-1,1);
            } else {
              $tmp_exclude = '';
            }
            echo "<tr><td>";
            if ($score_method == 'Mark per Option') echo excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1); 
            echo "</td>";
            if ($correct_buf[$i-1] == 't') {
              $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],$i,'t');
              echo "<td>" . pStats($freq_log[$q_id][$i]['t']/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=" . number_format(($freq_log[$q_id][$i]['t']/$user_total)*100,0) . "%</td><td>u=" . number_format(($top_log[$q_id][$i]['t']/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($bottom_log[$q_id][$i]['t']/$candidate_no)*100,0) . "%</td><td><span class=\"std\">" . $tmp_std_array[$std_part]. "</span></td><td><strong>True</strong></td>";
            } else {
              $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],$i,'f');
              echo "<td>" . pStats($freq_log[$q_id][$i]['f']/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=" . number_format(($freq_log[$q_id][$i]['f']/$user_total)*100,0) . "%</td><td>u=" . number_format(($top_log[$q_id][$i]['f']/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($bottom_log[$q_id][$i]['f']/$candidate_no)*100,0) . "%</td><td><span class=\"std\">" . $tmp_std_array[$std_part]. "</span></td><td><strong>False</strong></td>";
            }
            $std_part++;
            echo "<td id=\"q_" . $ex_no . "_1\"";
            if ($score_method == 'Mark per Option' and isset($excluded[$q_id]) and substr($excluded[$q_id],$i-1,1) == '1') echo ' class="excluded"';
            echo ">$individual_option</td></tr>\n";
          }
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
          //if (!isset($tmp_std_array[$std_part])) $tmp_std_array[$std_part] = '';
          
          if (isset($excluded[$q_id]) and substr($excluded[$q_id],0,1) == '1') {
            echo "<tr><td colspan=\"4\">" . excludeButton($ex_no, $q_id, '11', 2, 2) . "</td></tr>\n";
          } else {
            echo "<tr><td colspan=\"4\">" . excludeButton($ex_no, $q_id, '00', 2, 2) . "</td></tr>\n";
          }

          echo "<tr><td>t=" . number_format(($freq_log[$q_id][1]['t']/$user_total)*100,0) . "%</td><td>u=" . number_format(($top_log[$q_id][1]['t']/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($bottom_log[$q_id][1]['t']/$candidate_no)*100,0) . "%</td><td id=\"q_" . $ex_no . "_1\"";
          if (isset($excluded[$q_id]) and substr($excluded[$q_id],0,1) == '1') echo ' class="excluded"';
          echo '>';
          if ($correct_buf[0] == 't') {
            $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],1,'t');
            $p = $freq_log[$q_id][1]['t'] / $user_total;
            echo '<strong>True</strong>';
          } else {
            echo 'True';
          }
          echo "</td></tr>\n";
          echo "<tr><td>t=" . number_format(($freq_log[$q_id][1]['f']/$user_total)*100,0) . "%</td><td>u=" . number_format(($top_log[$q_id][1]['f']/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($bottom_log[$q_id][1]['f']/$candidate_no)*100,0) . "%</td><td id=\"q_" . $ex_no . "_2\"";
          if (isset($excluded[$q_id]) and substr($excluded[$q_id],0,1) == '1') echo ' class="excluded"';
          echo '>';
          if ($correct_buf[0] == 'f') {
            $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],1,'f');
            $p = $freq_log[$q_id][1]['f'] / $user_total;
            echo '<strong>False</strong>';
          } else {
            echo 'False';
          }
          echo "</td></tr>\n";
          echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
          echo "<tr><td>" . pStats($p) . "</td><td colspan=\"3\">" . dStats($d) . "</td></tr>\n";
          break;
        case 'labelling':
          if ($score_method == 'Mark per Question') {
            if (isset($excluded[$q_id])) {
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
?>
    <div align="center">
    <script language="JavaScript">
      function swfLoaded<?php echo $q_no; ?>(message) {
        var num = message.substring(5,message.length);
        setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $q_media; ?>', '<?php echo trim($correct); ?>', '');
      }
      write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $q_no; ?>" width="<?php echo ($q_media_width + 250); ?>" height="<?php echo $tmp_height; ?>" align="middle">');
      write_string('<param name="allowScriptAccess" value="always" />');
      write_string('<param name="movie" value="/reports/label_analysis.swf" />');
      write_string('<param name="quality" value="high" />');
      write_string('<param name="bgcolor" value="#ffffff" />');
      write_string('<embed src="/reports/label_analysis.swf" quality="high" bgcolor="#ffffff" width="<?php echo ($q_media_width + 250); ?>" height="<?php echo $tmp_height; ?>" swliveconnect="true" id="flash<?php echo $q_no; ?>" name="flash<?php echo $q_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      write_string('</object>');
    </script>
    </div>
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
                if ($score_method == 'Mark per Option') {
                  if (isset($excluded[$q_id])) {
                    echo "<td>" . excludeButton($ex_no, $q_id, substr($excluded[$q_id],$i-1,1), 1, 1) . "</td><td>" . pStats($tmp_correct_no/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=" . number_format(($tmp_correct_no/$user_total)*100,0) . "%</td><td>u=" . number_format(($tmp_top_no/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td id=\"q_" . $ex_no . "_1\"";
                  } else {
                    echo "<td>" . excludeButton($ex_no, $q_id, '', 1, 1) . "</td><td>" . pStats($tmp_correct_no/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=" . number_format(($tmp_correct_no/$user_total)*100,0) . "%</td><td>u=" . number_format(($tmp_top_no/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td id=\"q_" . $ex_no . "_1\"";
                  }
                  if (isset($excluded[$q_id]) and substr($excluded[$q_id],$i-1,1) == '1') echo ' class="excluded"';
                } else {
                  echo "<td></td><td>" . pStats($tmp_correct_no/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=" . number_format(($tmp_correct_no/$user_total)*100,0) . "%</td><td>u=" . number_format(($tmp_top_no/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td";
                }
                echo ">";
                if (strpos(strtolower($individual_option),'.jpg') !== false or strpos(strtolower($individual_option),'.jpeg') !== false or strpos(strtolower($individual_option),'.gif') !== false or strpos(strtolower($individual_option),'.png') !== false) {
                  $image_parts = explode('~', $individual_option);
                  echo "<img src=\"../media/" . $image_parts[0] . "\" width=\"" . $image_parts[1] . "\" height=\"" . $image_parts[2] . "\" alt=\"\" border=\"1\" />";
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
          if (isset($excluded[$q_id])) {
            echo excludeButton($ex_no, $q_id, 1, 1,1);
          } else {
            echo excludeButton($ex_no, $q_id, 0, 1, 1);
          }
          echo $leadin;
          ?>
            <script language="JavaScript">
            var isInternetExplorer = navigator.appName.indexOf("Microsoft") != -1;
            function flash<?php echo $q_no; ?>_DoFSCommand(command, args) {
              var flash<?php echo $q_no; ?>Obj = isInternetExplorer ? document.all.flash<?php echo $q_no; ?> : document.flash<?php echo $q_no; ?>;
              document.questions.q<?php echo $q_no; ?>.value = args;
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
          <script language="JavaScript">
            write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" id="flash<?php echo $q_no; ?>" width="<?php echo $q_media_width; ?>" height="<?php echo $q_media_height; ?>" align="middle">');
            write_string('<param name="allowScriptAccess" value="sameDomain" />');
            write_string('<param name="movie" value="../media/<?php echo $q_media; ?>" />');
            write_string('<param name="quality" value="high" />');
            write_string('<param name="bgcolor" value="#ffffff" />');
            <?php
              if ($scenario != '') {
                echo 'write_string(\'<param name="FlashVars" value="' . $scenario . '">\')';
              }
              echo 'write_string(\'<embed src="../media/' . $q_media . '"';
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
            if (isset($excluded[$q_id])) {
              echo excludeButton($ex_no, $q_id, str_repeat('1', count($layers)), 1, count($layers));
            } else {
              echo excludeButton($ex_no, $q_id, str_repeat('0', count($layers)), 1, count($layers));
            }
          }
          
          $layers = explode('|', $correct);
          $coords = '';
          for ($i = 1; $i <= count($layers); $i++) {
            $coords .= $freq_log[$q_id][$i]['coords'] . '|';
          }
          $coords = rtrim($coords, '|');
          
          $tmp_correct = str_replace("'", "\'", trim($correct));
          $tmp_correct = str_replace("&nbsp;", " ", $tmp_correct);
          $tmp_correct = preg_replace('/\r\n/', '', $tmp_correct); 
          ?>
          <div align="center">
          <script language="JavaScript">
      			function swfLoaded<?php echo $q_no; ?>(message) {
      				var num = message.substring(5,message.length);
      				setUpFlash(num, message, '<?php echo $language; ?>', '<?php echo $q_media; ?>', '<?php echo $tmp_correct; ?>', '<?php echo $coords; ?>','0');
      			}
      			write_string('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="https://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="flash<?php echo $q_no; ?>" width="<?php echo ($q_media_width + 302); ?>" height="<?php echo ($q_media_height + 25); ?>" align="middle">');
      			write_string('<param name="allowScriptAccess" value="always" />');
      			write_string('<param name="movie" value="/reports/hotspot_analysis.swf" />');
      			write_string('<param name="quality" value="high" />');
      			write_string('<param name="bgcolor" value="#ffffff" />');
      			write_string('<embed src="/reports/hotspot_analysis.swf" quality="high" bgcolor="#ffffff" width="<?php echo ($q_media_width + 302); ?>" height="<?php echo ($q_media_height + 25); ?>" swliveconnect="true" id="flash<?php echo $q_no; ?>" name="flash<?php echo $q_no; ?>" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer" />');
      			write_string('</object>');
          </script>
          </div>
          <?php
          
          echo "<p><table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
          for ($i = 1; $i <= count($layers); $i++) {
            echo "<tr><td>" . chr($i + 64) . ".</td>";
            $label = substr($layers[$i - 1], 0, strpos($layers[$i - 1], '~'));
            
            $std_rating = (isset($std_parts[$i - 1])) ? $std_parts[$i - 1] : '';
          
            $d = calcDiscrimination($candidate_no,$top_log[$q_id],$bottom_log[$q_id],$i,1);
            $tmp_correct_no = (isset($freq_log[$q_id][$i][1])) ? $freq_log[$q_id][$i][1] : 0;
            $tmp_top_no = (isset($top_log[$q_id][$i][1])) ? $top_log[$q_id][$i][1] : 0;
            $tmp_bottom_no = (isset($bottom_log[$q_id][$i][1])) ? $bottom_log[$q_id][$i][1] : 0;
            if (isset($excluded[$q_id])) {
              echo "<td>";
              if ($score_method == 'Mark per Option') echo excludeButton($ex_no, $q_id, substr($excluded[$q_id],$i-1,1), 1, 1);
              echo "</td><td>" . pStats($tmp_correct_no/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=" . number_format(($tmp_correct_no/$user_total)*100,0) . "%</td><td>u=" . number_format(($tmp_top_no/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td";
              if ($score_method == 'Mark per Option') echo " id=\"q_" . $ex_no . "_1\"";
            } else {
              echo "<td>";
              if ($score_method == 'Mark per Option') echo excludeButton($ex_no, $q_id, '', 1, 1);
              echo "</td><td>" . pStats($tmp_correct_no/$user_total) . "</td><td>" . dStats($d) . "</td><td>t=" . number_format(($tmp_correct_no/$user_total)*100,0) . "%</td><td>u=" . number_format(($tmp_top_no/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</td><td><span class=\"std\">$std_rating</span></td><td";
              if ($score_method == 'Mark per Option') echo " id=\"q_" . $ex_no . "_1\"";
            }
            if ($score_method == 'Mark per Option' and isset($excluded[$q_id]) and substr($excluded[$q_id],$i-1,1) == '1') echo ' class="excluded"';
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
            if (isset($freq_log[$q_id][1][$i])) {
              $t = number_format(($freq_log[$q_id][1][$i]/$user_total)*100,0);
            } else {
              $t = 0;
            }
            echo "<td style=\"text-align:center\">t=" . $t . "%</td>";
          }
          echo "</tr>\n";
          break;
        case 'mcq':
          if (isset($excluded[$q_id])) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '';
          }
          echo "<tr><td colspan=\"3\">" . excludeButton($ex_no, $q_id, $tmp_exclude, count($options), 1) . "</td></tr>\n";
          $i = 0;
          foreach ($options as $individual_option) {
            $i++;
            if (isset($freq_log[$q_id][1][$i])) {
              $t = number_format(($freq_log[$q_id][1][$i]/$user_total)*100,0);
            } else {
              $t = 0;
            }
            if (isset($top_log[$q_id][1][$i])) {
              $u = number_format(($top_log[$q_id][1][$i]/$candidate_no)*100,0);
            } else {
              $u = 0;
            }
            if (isset($bottom_log[$q_id][1][$i])) {
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
            if (isset($excluded[$q_id]) and $excluded[$q_id] == '1') echo ' class="excluded"';
            echo ">";
            if ($individual_option != '') echo "$individual_option\n";
            if (is_array($o_media[$i - 1])) {
              echo '<br />';
              echo display_media($o_media[$i - 1][0], $o_media[$i - 1][1], $o_media[$i - 1][2]);
            }
            echo "</td></tr>\n";
          }
          echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
          echo "<tr><td>" . pStats($tmp_correct_no/$user_total) . "</td><td colspan=\"2\">" . dStats($d) . "</td></tr>\n";
          break;
        case 'mrq':
          $tmp_parts = 0;
          $i=0;
          foreach ($options as $individual_option) {
            $i++;
            if ($correct_buf[$i-1] == 'y') $tmp_parts++;
          }
          if (isset($excluded[$q_id])) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '';
          }
          echo "<tr><td colspan=\"3\">" . excludeButton($ex_no, $q_id, $tmp_exclude, count($options), $tmp_parts) . "</td></tr>\n";
          $i = 0;
          $tmp_parts = 0;
          $std_part = 0;
          foreach ($options as $individual_option) {
            $i++;
            if (!isset($log[$q_id][$i]['y'])) $log[$q_id][$i]['y'] = 0;
            if (isset($freq_log[$q_id][$i]['y'])) {
              $t = number_format(($freq_log[$q_id][$i]['y']/$user_total)*100,0);
            } else {
              $t = 0;
            }
            if (isset($top_log[$q_id][$i]['y'])) {
              $u = number_format(($top_log[$q_id][$i]['y']/$candidate_no)*100,0);
            } else {
              $u = 0;
            }
            if (isset($bottom_log[$q_id][$i]['y'])) {
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
              if (isset($excluded[$q_id]) and strpos($excluded[$q_id],'1') !== false) echo ' class="excluded"';
              $std_part++;
            } else {
              echo "<tr><td class=\"grey\">t=" . $t . "%</td><td class=\"grey\">u=" .$t . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td><td id=\"q_" . $ex_no . "_" . $i . "\"";
              if (isset($excluded[$q_id]) and strpos($excluded[$q_id],'1') !== false) echo ' class="excluded"';
            }
            echo ">$individual_option";
            if (is_array($o_media[$i - 1])) {
              echo '<br />';
              echo display_media($o_media[$i - 1][0], $o_media[$i - 1][1], $o_media[$i - 1][2]);
            }
            echo "</td></tr>\n";
          }
          if (empty($top_log[$q_id]['totalpos']) or empty($bottom_log[$q_id]['totalpos'])) {
            $d = 0;
          } else {
            $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
          }
          echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
          $tmp_pstat = (isset($freq_log[$q_id]['mark']) and isset($freq_log[$q_id]['totalpos']) and $freq_log[$q_id]['totalpos'] > 0) ? pStats($freq_log[$q_id]['mark']/$freq_log[$q_id]['totalpos']) : 'p=0.00';
          echo "<tr><td>" . $tmp_pstat . "</td><td colspan=\"2\">" . dStats($d) . "</td></tr>\n";
          break;
        case 'rank':
          $rank_no = 0;
          foreach ($correct_buf as $individual_correct) {
            if ($individual_correct > $rank_no and $individual_correct != 0) $rank_no = $individual_correct;
          }
          $i = 0;
          if ($score_method == 'BonusMark') {
            $no_marks_available = $rank_no + 1;
          } elseif ($score_method == 'AllItemsCorrect') {
            $no_marks_available = 1;
          } else {
            $no_marks_available = $rank_no;
          }
          if (isset($excluded[$q_id])) {
            $tmp_exclude = $excluded[$q_id];
          } else {
            $tmp_exclude = '';
          }
          echo "<tr><td colspan=\"4\">" . excludeButton($ex_no, $q_id, $tmp_exclude, count($options), $no_marks_available) . "</td></tr>\n";
          foreach ($options as $individual_option) {
            echo "<tr><td id=\"q_" . $ex_no . "_" . ($i+1) . "\" colspan=\"6\"";
            if (isset($excluded[$q_id]) and strpos($excluded[$q_id],'1') !== false) echo ' class="excluded"';
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
          $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
          $std_val = (isset($tmp_std_array[$i])) ? $tmp_std_array[$i] : '';
          $tmp_correct_no = (isset($top_log[$q_id]['all_correct'])) ? $top_log[$q_id]['all_correct'] : 0;
          $tmp_bottom_no = (isset($bottom_log[$q_id]['all_correct'])) ? $bottom_log[$q_id]['all_correct'] : 0;
          echo "<tr><td><strong>u=" . number_format(($tmp_correct_no/$candidate_no)*100,0) . "%</strong></td><td><strong>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</strong></td><td><span class=\"std\">" . $std_val . "</span></td><td style=\"font-weight:bold\">All items correct</td></tr>\n";
          echo "<tr><td>" . pStats($freq_log[$q_id]['mark']/$freq_log[$q_id]['totalpos']) . "</td><td colspan=\"3\">" . dStats($d) . "</td></tr>\n";
          break;
        case 'sct':
          $tmp_exclude = (isset($excluded[$q_id])) ? $excluded[$q_id] : '';
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
            echo "<tr class=\"grey{$correct_class}\"><td>t=" . number_format(($tmp_correct_no/$user_total)*100,0) . "%</td><td>u=" . number_format(($tmp_top_no/$candidate_no)*100,0) . "%</td><td>l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</td><td></td>";
            echo "<td id=\"q_" . $ex_no . "_" . $i . "\"";
            if ($tmp_exclude == '1') echo ' class="excluded"';
            echo ">$individual_option</td></tr>\n";
          }
          echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
          if ($top_log[$q_id]['totalpos'] > 0 and $bottom_log[$q_id]['totalpos'] > 0) {
            $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
          } else {
            $d = 0;
          }
          $tmp_pstat = ($freq_log[$q_id]['totalpos'] > 0) ? pStats($freq_log[$q_id]['mark']/$freq_log[$q_id]['totalpos']) : 'p=' . 0;
          echo "<tr><td>" . $tmp_pstat . "</td><td colspan=\"3\">" . dStats($d) . "</td></tr>\n";
          break;
      }
      if ($q_type != 'info' and $q_type != 'blank' and $q_type != 'flash') echo "</table></p>\n";
    } elseif ($q_type == 'textbox') {
      echo "<td class=\"q_no\">$q_no.&nbsp;</td><td><p ";
      if (isset($excluded[$q_id]) and substr($excluded[$q_id], 0, 1) == '1') echo ' class="excluded"';
      if (isset($excluded[$q_id])) {
        $tmp_exclude = $excluded[$q_id];
      } else {
        $tmp_exclude = '';
      }
      echo "id=\"q_" . ($ex_no + 1) . "_1\">" . excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1) . "&nbsp;$leadin</p>";
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
          
      echo "<tr><td colspan=\"2\"><strong>Top Group:</strong></td><td colspan=\"2\"><strong>Bottom Group:</strong></td></tr>\n";
      echo "<tr><td colspan=\"2\">(mean word count = " . round($top_log[$q_id]['word_count'] / $candidate_no) . ")</td><td colspan=\"2\">(mean word count = " . round($bottom_log[$q_id]['word_count'] / $candidate_no) . ")</td></tr>";
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
      
      if ($top_log[$q_id]['totalpos'] == 0 or $bottom_log[$q_id]['totalpos'] == 0) {
        $d = 0;
      } else {
        $d = ($top_log[$q_id]['mark'] / $top_log[$q_id]['totalpos']) - ($bottom_log[$q_id]['mark'] / $bottom_log[$q_id]['totalpos']);
      }
      echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
      if (isset($freq_log[$q_id]['unmarked']) and $freq_log[$q_id]['unmarked'] > 0) {
        echo "<tr><td>p=<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" /></td><td>d=<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" /></td><td colspan=\"2\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" />&nbsp;" . $freq_log[$q_id]['unmarked'] . " unmarked scripts</td></tr>\n";
      } else {
        if ($freq_log[$q_id]['totalpos'] == 0) {
          $p = 0;
        } else {
          $p = $freq_log[$q_id]['mark'] / $freq_log[$q_id]['totalpos'];
        }
        echo "<tr><td>" . pStats($p) . "</td><td colspan=\"3\">" . dStats($d)  . "</td></tr>\n";
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
        if (isset($excluded[$q_id])) {
          echo ' class="excluded"';
        }
      }
      echo ">$leadin</div>";

      if ($score_method == 'Mark per Question') {
        if (isset($excluded[$q_id])) {
          echo excludeButton($ex_no, $q_id, str_repeat('1', count($options)), 1, count($options));
        } else {
          echo excludeButton($ex_no, $q_id, str_repeat('0', count($options)), 1, count($options));
        }
      }
      
      echo "<p>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\" class=\"matrix\">\n";
      $cols = 5;
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
              if (isset($excluded[$q_id])) {
                $tmp_exclude = substr($excluded[$q_id],$i-1,1);
              } else {
                $tmp_exclude = '';
              }
              $tmp_correct_no = (isset($freq_log[$q_id][$i][$correct_answer])) ? $freq_log[$q_id][$i][$correct_answer] : 0;
              $tmp_top_no = (isset($top_log[$q_id][$i][$correct_answer])) ? $top_log[$q_id][$i][$correct_answer] : 0;
              $tmp_bottom_no = (isset($bottom_log[$q_id][$i][$correct_answer])) ? $bottom_log[$q_id][$i][$correct_answer] : 0;
              if ($score_method == 'Mark per Option') {
                echo '<td>' .  excludeButton($ex_no, $q_id, $tmp_exclude, 1, 1) . '</td>';
              }
              echo "<td style=\"font-weight:bold\">" . pStats($tmp_correct_no/$user_total) . "</td>";
              echo "<td style=\"font-weight:bold\">" . dStats($d) . "</td>";
              echo "<td style=\"font-weight:bold\">t=" . number_format(($tmp_correct_no/$user_total)*100,0) . "%</td>";
              echo "<td style=\"font-weight:bold\">u=" . number_format(($tmp_top_no/$candidate_no)*100,0) . "%</td>";
              echo "<td style=\"font-weight:bold\">l=" . number_format(($tmp_bottom_no/$candidate_no)*100,0) . "%</td>";
              
              if (isset($tmp_std_array[$i-1])) {
                echo '<td class="std"><strong>' . $tmp_std_array[$i-1] . '</strong></td>';
              }

              echo "<td ";
              if (isset($excluded[$q_id]) and substr($excluded[$q_id],$i-1,1) == '1') echo ' class="excluded"';
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
        if (isset($excluded[$q_id]) and substr($excluded[$q_id],0,1) == '1') echo ' class="excluded"';
      }
      echo ">$leadin</div>\n";
      if ($score_method == 'Mark per Question') {
        if (isset($excluded[$q_id])) {
          echo excludeButton($ex_no, $q_id, str_repeat('1', $total_scenarios), 1, $total_scenarios);
        } else {
          echo excludeButton($ex_no, $q_id, str_repeat('0', $total_scenarios), 1, $total_scenarios);
        }
      }
      echo "<ol class=\"extmatch\">";
      if ($tmp_media_array[0] != '') {
        echo "<p align=\"center\">" . display_media($tmp_media_array[0],$tmp_media_width_array[0],$tmp_media_height_array[0]) . "</p>\n";
      }
      $std_part = 0;
      $section = 0;
      for ($i=1; $i<=$total_scenarios; $i++) {
        $tmp_correct_no = 0;
        $correct_stems = 0;
        echo "<li>\n";
        if (isset($tmp_media_array[$i]) and $tmp_media_array[$i] != '') {
          echo "<p>" . display_media($tmp_media_array[$i],$tmp_media_width_array[$i],$tmp_media_height_array[$i]) . "</p>\n";
        }
        if (isset($tmp_ext_scenarios[$i-1])) echo "<div>" . $tmp_ext_scenarios[$i-1] . "</div>\n";
        
        $option_no = 1;
        foreach ($options as $individual_option) {
          $specific_answers = array();
          $specific_answers = explode('$', $tmp_answers_array[$i-1]);
          $answer_match = false;
          for ($x=0; $x<count($specific_answers); $x++) {
            if ($option_no == $specific_answers[$x]) $answer_match = true;
          }
          if ($answer_match == true) $correct_stems++;
          $option_no++;
        }
		
        if (isset($excluded[$q_id])) {
          $tmp_exclude = substr($excluded[$q_id],$section,1);
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
          for ($x=0; $x<count($specific_answers); $x++) {
            if ($option_no == $specific_answers[$x]) $answer_match = true;
          }
          if ($answer_match == true) {
            if (isset($top_log[$q_id][$i][$option_no])) {
              $t = $top_log[$q_id][$i][$option_no]/$candidate_no;
            } else {
              $t = 0;
            }
            if (isset($bottom_log[$q_id][$i][$option_no])) {
              $l = $bottom_log[$q_id][$i][$option_no]/$candidate_no;
            } else {
              $l = 0;
            }
            $sub_d += $t - $l;
            $sub_d_no++;
            if (isset($freq_log[$q_id][$i][$option_no])) {
              $t = number_format(($freq_log[$q_id][$i][$option_no]/$user_total)*100,0);
            } else {
              $t = 0;
            }
            if (isset($top_log[$q_id][$i][$option_no])) {
              $u = number_format(($top_log[$q_id][$i][$option_no]/$candidate_no)*100,0);
            } else {
              $u = 0;
            }
            if (isset($bottom_log[$q_id][$i][$option_no])) {
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
            if ($score_method == 'Mark per Option' and isset($excluded[$q_id]) and substr($excluded[$q_id],$section,1) == '1') echo ' excluded';
            echo "\"";
            if ($score_method == 'Mark per Option') echo " id=\"q_" . $ex_no . "_" . $option_no . "\"";
            echo ">" . chr($option_no+64) . ". $individual_option</td></tr>\n";
            $correct_stems++;
            if (isset($freq_log[$q_id][$i][$option_no])) $tmp_correct_no += $freq_log[$q_id][$i][$option_no];
            $std_part++;
          } else {
            if (isset($freq_log[$q_id][$i][$option_no])) {
              $t = number_format(($freq_log[$q_id][$i][$option_no]/$user_total)*100,0);
            } else {
              $t = 0;
            }
            if (isset($top_log[$q_id][$i][$option_no])) {
              $u = number_format(($top_log[$q_id][$i][$option_no]/$candidate_no)*100,0);
            } else {
              $u = 0;
            }
            if (isset($bottom_log[$q_id][$i][$option_no])) {
              $l = number_format(($bottom_log[$q_id][$i][$option_no]/$candidate_no)*100,0);
            } else {
              $l = 0;
            }
		  
            echo "<tr><td class=\"grey\">t=" . $t . "%</td><td class=\"grey\">u=" . $u . "%</td><td class=\"grey\">l=" . $l . "%</td><td></td><td";
            if ($score_method == 'Mark per Option' and isset($excluded[$q_id]) and substr($excluded[$q_id],$section,1) == '1') echo ' class="excluded"';
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
        echo "<tr><td>" . pStats($tmp_correct_no/($correct_stems * $user_total)) . "</td><td colspan=\"3\">" . dStats($d) . "</td></tr>";
        if ($i < $total_scenarios) echo "<tr><td colspan=\"4\">&nbsp;</td></tr>";
        echo "</table></div></li>\n";
      }
      echo "</ol>\n";
    }
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['frequencydiscrimination'] . " $cfg_install_type"; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%; background-color:white; color:black; margin:0px}
h1 {margin-left:15px; font-size:18pt}
p {margin-left:0px; margin-right:0px}
.figures {text-align:right}
.q_no {text-align:right; vertical-align:top; width:50px}
.grey {color:#808080}
.extmatch li {padding-bottom:14px; vertical-align:text-bottom; list-style-type:lower-roman}
.correct {color:#000; font-weight:bold}
.excluded {color:red; text-decoration:line-through}
.excluded img { border: 2px solid red}
.excluded img.in-exclusion {border:0}
td p:first-child {margin-top:0}
.matrix {border:1px solid #808080; border-collapse:collapse}
.matrix td {border:1px solid #808080}
.std {display:block;background-color:#f27000;color:white;width:35px;text-align:center}
.scr_no {margin-left:25px}
.screenbrk {
  color:#15428B;
  font-weight:bold;
  font-size:90%;
  height:70px;
  width:100%;
  border-top: 1px solid #B5C4DF;
  background: -moz-linear-gradient(top, #E4EEFC, #FFFFFF);
  background: -webkit-linear-gradient(top, #E4EEFC, #FFFFFF);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#E4EEFC', endColorstr='#FFFFFF');
}
</style>

<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
<script type="text/javascript" src="../js/ie_fix.js"></script>
<script type="text/javascript" src="../js/flash_include.js"></script>
<script language="JavaScript">
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
  
  function manCorrect(q_id, part_no) {
    window.open("blank_remark.php?q_id=" + q_id + "&blank=" + part_no + "&paperID=<?php echo $_GET['paperID']; ?>&startdate=<?php echo $_GET['startdate']; ?>&enddate=<?php echo $_GET['enddate']; ?>","remark","width=500,height="+(screen.height-80)+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
    
    return false;
  }
</script>
</head>

<body>
<form name="theform" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>" method="post">
<table class="header" style="font-size:90%">
<?php
  // Get any questions to exclude.
  $excluded = array();
  $result = $mysqli->prepare("SELECT q_id, parts FROM question_exclude WHERE q_paper=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $parts);
  while ($result->fetch()) {
    $excluded[$q_id] = $parts;
  }
  $result->close();

  // Get some paper properties
  $result = $mysqli->prepare("SELECT paper_title, paper_type, labelcolor, themecolor, marking, pass_mark, moduleID FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title, $paper_type, $labelcolor, $themecolor, $marking, $pass_mark, $moduleID);
  $result->fetch();
  $result->close();
  
  // Get the standards setting
  if (substr($marking,0,1) == '2') {
    $tmp_parts = explode(',', $marking);
    
    $std_set_array = array();
  
    $result = $mysqli->prepare("SELECT questionID, rating FROM standards_setting WHERE setterID=? AND std_set=?");
    $result->bind_param('is', $tmp_parts[1], $tmp_parts[2]);
    $result->execute();
    $result->bind_result($questionID, $rating);
    while ($result->fetch()) {
      $std_set_array[$questionID] = $rating;
    }
    $result->close();
  }
  
  // Get all the users on the module(s) the paper is on.
  if ($moduleID != '') {
    $users_on_modules = '';
    $mod_query = $mysqli->prepare("SELECT userID, moduleid FROM student_modules WHERE moduleid IN ('" . str_replace(",","','",$moduleID) . "')");
    $mod_query->execute();
    $mod_query->bind_result($tmp_userID, $tmp_moduleid);
    $mod_query->store_result();
    while ($mod_query->fetch()) {
	  if (isset($_GET['repmodule']) and $_GET['repmodule'] != '' and $tmp_moduleid != $_GET['repmodule']) {
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
    $student_modules_sql = " AND log$paper_type.userID IN ($users_on_modules)";
  } 
  
  if ($_GET['studentsonly'] == 1) {
    $roles_sql = " AND (users.roles='Student' OR users.roles='graduate')";
  } else {
    $roles_sql = '';
  }
  // Calculate top and bottom cohorts.
  $student_list = '';
  if ($paper_type == '0') {
    //$result = $mysqli->prepare("(SELECT username, sum(mark) AS total_mark, log_metadata.started FROM (log0, users, log_metadata) WHERE log0.userID=log_metadata.userID AND log0.started=log_metadata.started AND log0.q_paper=log_metadata.paperID AND log0.userID=users.id AND (users.roles='Student' OR users.roles='graduate') AND q_paper=? AND grade LIKE ? AND log0.started>=? AND log0.started<=? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%' AND student_grade NOT LIKE '%nhs%' $student_modules_sql GROUP BY username, q_paper, log0.started) UNION ALL (SELECT username, sum(mark) AS total_mark, log_metadata.started FROM (log1, users, log_metadata) WHERE log1.userID=log_metadata.userID AND log1.started=log_metadata.started AND log1.q_paper=log_metadata.paperID AND log1.userID=users.id AND (users.roles='Student' OR users.roles='graduate') AND q_paper=? AND log1.started>=? AND log1.started<=? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE '%staff%' AND student_grade NOT LIKE '%nhs%' " . str_replace('log0', 'log1', $student_modules_sql) . " GROUP BY username, q_paper, log1.started) ORDER BY total_mark ASC, username");
    $result = $mysqli->prepare("(SELECT username, sum(mark) AS total_mark, log_metadata.started FROM (log0, users, log_metadata) WHERE log0.userID=log_metadata.userID AND log0.started=log_metadata.started AND log0.q_paper=log_metadata.paperID AND log0.userID=users.id AND (users.roles='Student' OR users.roles='graduate') AND q_paper=? AND grade LIKE ? AND log0.started>=? AND log0.started<=? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%' AND student_grade NOT LIKE '%nhs%' $student_modules_sql GROUP BY username, q_paper, log0.started) UNION ALL (SELECT username, sum(mark) AS total_mark, log_metadata.started FROM (log1, users, log_metadata) WHERE log1.userID=log_metadata.userID AND log1.started=log_metadata.started AND log1.q_paper=log_metadata.paperID AND log1.userID=users.id AND (users.roles='Student' OR users.roles='graduate') AND q_paper=? AND log1.started>=? AND log1.started<=? " . str_replace('log0', 'log1', $student_modules_sql) . " GROUP BY username, q_paper, log1.started) ORDER BY total_mark ASC, username");
    $result->bind_param('isssiss', $paperID, $_GET['repcourse'], $startdate, $enddate, $paperID, $startdate, $enddate);
  } else {
    //$result = $mysqli->prepare("SELECT username, sum(mark) AS total_mark, log_metadata.started FROM (log$paper_type, users, log_metadata) WHERE log$paper_type.userID=log_metadata.userID AND log$paper_type.started=log_metadata.started AND log$paper_type.q_paper=log_metadata.paperID $roles_sql AND log$paper_type.userID=users.id AND q_paper=? AND grade LIKE ? AND DATE_ADD(log$paper_type.started, INTERVAL 2 MINUTE)>=? AND log$paper_type.started<=? AND student_grade NOT LIKE 'university%' AND student_grade NOT LIKE 'Staff%' AND student_grade NOT LIKE '%nhs%' $student_modules_sql GROUP BY username, q_paper, log$paper_type.started ORDER BY total_mark ASC, username");
    $result = $mysqli->prepare("SELECT username, sum(mark) AS total_mark, log_metadata.started FROM (log$paper_type, users, log_metadata) WHERE log$paper_type.userID=log_metadata.userID AND log$paper_type.started=log_metadata.started AND log$paper_type.q_paper=log_metadata.paperID $roles_sql AND log$paper_type.userID=users.id AND q_paper=? AND grade LIKE ? AND DATE_ADD(log$paper_type.started, INTERVAL 2 MINUTE)>=? AND log$paper_type.started<=? $student_modules_sql GROUP BY username, q_paper, log$paper_type.started ORDER BY total_mark ASC, username");
    $result->bind_param('isss',$paperID, $_GET['repcourse'], $startdate, $enddate);
  }
  $result->execute();
  $result->bind_result($username, $total_mark, $started);
  $result->store_result();

  $student_no = 0;
  $bottom_cohort = array();
  $user_no = round(($result->num_rows/100)*$cohort_percent);
  $user_total = $result->num_rows;
  while ($result->fetch()) {
    if ($student_no < $user_no) {
      $bottom_cohort[$started][$username] = '';
    } elseif ($student_no >= ($user_total - $user_no)) {
      $top_cohort[$started][$username] = '';
    }
    $student_no++;
  }
  $result->close();
    
  // Capture the log data first.
  $freq_array = array();
  $bottom_log_array = array();
  $top_log_array = array();
  if ($paper_type == '0') {
    $result = $mysqli->prepare("(SELECT username, log0.userID, log0.q_id, user_answer, q_type, score_method, display_method, mark, totalpos, option_order, started FROM log0, questions, users WHERE log0.q_id=questions.q_id AND q_paper=? AND grade LIKE ? AND users.id=log0.userID AND (users.roles='Student' OR users.roles='graduate') AND started>=? AND started<=? $student_modules_sql) UNION ALL (SELECT username, log1.userID, log1.q_id, user_answer, q_type, score_method, display_method, mark, totalpos, option_order, started FROM log1, questions,  users WHERE log1.q_id=questions.q_id AND q_paper=? AND users.id=log1.userID AND (users.roles='Student' OR users.roles='graduate') AND started>=? AND started<=? " . str_replace('log0', 'log1', $student_modules_sql) . ")");
    $result->bind_param('isssiss', $paperID, $_GET['repcourse'], $startdate, $enddate, $paperID, $startdate, $enddate);
  } else {
    $result = $mysqli->prepare("SELECT username, log$paper_type.userID, log$paper_type.q_id, user_answer, q_type, score_method, display_method, mark, totalpos, option_order, started FROM log$paper_type, questions, users WHERE log$paper_type.q_id=questions.q_id AND q_paper=? AND grade LIKE ? AND users.id=log$paper_type.userID AND (users.roles='Student' OR users.roles='graduate') AND DATE_ADD(started, INTERVAL 2 MINUTE)>=? AND started<=? $student_modules_sql");
    $result->bind_param('isss', $paperID, $_GET['repcourse'], $startdate, $enddate);
  }
  $result->execute();
  $result->bind_result($username, $tmp_userID, $question_ID, $tmp_answer, $q_type, $score_method, $display_method, $mark, $totalpos, $option_order, $started);
  
  while ($result->fetch()) {
    storeData($freq_array, $question_ID, $tmp_answer, $q_type, $score_method, $display_method, $mark, $totalpos, $option_order, 'all');
    if (isset($bottom_cohort[$started][$username])) {
      storeData($bottom_log_array, $question_ID, $tmp_answer, $q_type, $score_method, $display_method, $mark, $totalpos, $option_order, 'bottom');
    }
    if (isset($top_cohort[$started][$username])) {
      storeData($top_log_array, $question_ID, $tmp_answer, $q_type, $score_method, $display_method, $mark, $totalpos, $option_order, 'top');
    }
  }
  $result->close();
  
  $folder = '';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    $folder = $_GET['folder'];
    $result = $mysqli->prepare("SELECT name FROM folders WHERE id=? LIMIT 1");
    $result->bind_param('i', $folder);
    $result->execute();
    $result->bind_result($folder_name);
    $result->fetch();
    $result->close();
  }

  if ($user_total == 0) {
    // No one has taken the paper yet.
    echo '<tr><th>';
    
    echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
    if ($folder != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
    }
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';
    
    echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">" . $string['reporttitle'] . "</span></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n";
    echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border: 1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;This paper has not been attempted by anyone.</td></tr></table>\n";  
  } elseif ($user_no == 0) {
    // Not enough data for relevant cohort at selected percentage
    echo '<tr><th>';
    
    echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
    if ($folder != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
    } elseif (isset($_GET['module']) and $_GET['module'] != '') {
      echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
    }
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';
    
    echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">" . $string['reporttitle'] . "</span></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n";
    echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border: 1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;Not enough data to calculate upper and lower groups. Please select a higher percentage.</td></tr></table>\n";  
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
    $result = $mysqli->prepare("SELECT screen, q_id, q_type, theme, scenario, leadin, option_text, o_media, o_media_width, o_media_height, score_method, display_method, q_media, q_media_width, q_media_height, correct, std FROM (papers, questions, options) WHERE  papers.paper=? AND papers.question=questions.q_id AND questions.q_id = options.o_id $qids_instring ORDER BY screen, display_pos, id_num");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->bind_result($screen, $q_id, $q_type, $theme, $scenario, $leadin, $option_text, $o_media, $o_media_width, $o_media_height, $score_method, $display_method, $q_media, $q_media_width, $q_media_height, $correct, $std);
    $result->store_result();
    while ($result->fetch()) {
      if ($display_header == true) {
        echo '<tr><th>';
        echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
        if ($folder != '') {
          echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a>';
        } elseif (isset($_GET['module']) and $_GET['module'] != '') {
          echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a>';
        }
        echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $paper_title . '</a></div>';
        
        echo "<span style=\"margin-left:10px; font-size:200%; color:black; font-weight:bold\">" . $string['reporttitle'] . "</span></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
        echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";

        echo '<br /><div align="center"><table cellpadding="4" cellspacing="0" border="0" width="95%" style="background-color:#E4EEFC; border:1px solid #B5C4DF">';
        echo '<tr><td style="text-align:left"><table cellpadding="2" cellspacing="0" border="0">';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['totalcandidatenumber'] . '</td><td style="width:500px">' . number_format($user_total) . '</td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="Warning" class="in-exclusion" /> ' . $string['warning'] . ' ' . $string['p_warning'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right"><nobr>' . $string['groupsizes'] . '</nobr></td><td>' . $cohort_percent . '% (' . $user_no . ' ' . $string['pergroup'] . ')</td><td rowspan="7" style="vertical-align:top"><img src="../artwork/red_flag.png" width="14" height="14" alt="Warning" class="in-exclusion" /> ' . $string['warning'] . ' ' . $string['d_warning'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">' . $string['boldstems'] . '</td><td>' . $string['correctanswers'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">p=</td><td>' . $string['p_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">d=</td><td>' . $string['d_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">t=</td><td>' . $string['t_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">u=</td><td>' . $string['u_definition'] . '</td></tr>';
        echo '<tr><td style="margin:0px; font-weight:bold; text-align:right">l=</td><td>' . $string['l_definition'] . '</td></tr>';
        echo '</table></td></tr>';
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
        
        displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $o_media_buffer, $bottom_log_array, $top_log_array, $freq_array, $correct_buffer, $user_no, $old_score_method, $old_display_method, $old_themecolor, $old_std);
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
          if (array_key_exists($label_no,$tmp_second_split)) {
            if (substr($tmp_second_split[$label_no],0,1) != '|') {
              $options_buffer[] = trim(substr($tmp_second_split[$label_no],0,strpos($tmp_second_split[$label_no],'|'))) . '|' . $tmp_second_split[$label_no-2] . '|' . ($tmp_second_split[$label_no-1] - 25);
              if ($tmp_second_split[$label_no-2] >= 220) {
                $correct_buffer[] = $tmp_second_split[$label_no-2] . 'x' . ($tmp_second_split[$label_no-1] - 25);
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
    $mysqli->close();
    $question_no++;
    if ($old_q_type == 'info') $question_no--;
    
    if (isset($std_set_array[$old_q_id])) $old_std = $std_set_array[$old_q_id];
    
    displayQuestion($question_no, $old_q_id, $old_theme, $old_scenario, $old_leadin, $old_q_type, $old_correct, $old_q_media, $old_q_media_width, $old_q_media_height, $options_buffer, $o_media_buffer, $bottom_log_array, $top_log_array, $freq_array, $correct_buffer, $user_no, $old_score_method, $old_display_method, $old_themecolor, $old_std);
  ?>
  </table>
  <br />
  
  <table border="0" style="padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287"><tr><td><?php echo $string['summary']; ?></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table>

  <table cellpadding="0" cellspacing="0" border="0" style="width:650px; font-size:100%; margin-left:40px">
  <tr><td colspan="2" style="padding-left:4px"><?php echo $string['msg']; ?></td></tr>
  <tr>
  <td style="vertical-align:top">
  <table cellpadding="4" cellspacing="0" border="0" style="font-size:100%">
  <tr style="font-weight:bold"><td><?php echo $string['difficulty']; ?></td><td style="text-align:center">p</td><td><?php echo $string['noofitems']; ?></td><td></td></tr>
  <tr><td><?php echo $string['veryeasy']; ?></td><td>&gt; 0.8</td><td style="text-align:right"><?php echo $pstats['ve']; ?></td><td></td></tr>
  <tr><td><?php echo $string['easy']; ?></td><td>0.6-0.8</td><td style="text-align:right"><?php echo $pstats['e']; ?></td><td></td></tr>
  <tr><td><?php echo $string['moderate']; ?></td><td>0.4-0.6</td><td style="text-align:right"><?php echo $pstats['m']; ?></td><td></td></tr>
  <tr><td><?php echo $string['hard']; ?></td><td>0.2-0.4</td><td style="text-align:right"><?php echo $pstats['h']; ?></td><td></td></tr>
  <tr style="color:#C00000"><td><?php echo $string['veryhard']; ?></td><td>&lt; 0.2</td><td style="text-align:right"><?php echo $pstats['vh']; ?></td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="<?php echo $string['warning1']; ?>" border="0" class="in-exclusion" /></td></tr>
  <tr><td><?php echo $string['mean']; ?></td><td style="text-align:right"><?php echo number_format($pstats['total']/$pstats['no'],2); ?></td><td></td><td></td></tr>
  </table>
  </td>
  <td style="vertical-align:top">
  <table cellpadding="4" cellspacing="0" border="0" style="font-size:100%">
  <tr style="font-weight:bold"><td><?php echo $string['discrimination']; ?></td><td style="text-align:center">d</td><td><?php echo $string['noofitems']; ?></td><td></td></tr>
  <tr><td><?php echo $string['highest']; ?></td><td>&gt;= 0.35</td><td style="text-align:right"><?php echo $dstats['highest']; ?></td><td></td></tr>
  <tr><td><?php echo $string['high']; ?></td><td>0.25-0.35</td><td style="text-align:right"><?php echo $dstats['high']; ?></td><td></td></tr>
  <tr><td><?php echo $string['intermediate']; ?></td><td>0.15-0.25</td><td style="text-align:right"><?php echo $dstats['intermediate']; ?></td><td></td></tr>
  <tr style="color:#C00000"><td><?php echo $string['low']; ?></td><td>&lt; 0.15</td><td style="text-align:right"><?php echo $dstats['low']; ?></td><td><img src="../artwork/red_flag.png" width="14" height="14" alt="<?php echo $string['warning2']; ?>" border="0" class="in-exclusion" /></td></tr>
  <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
  <tr><td><?php echo $string['mean']; ?></td><td style="text-align:right"><?php echo number_format($dstats['total']/$dstats['no'],2); ?></td><td></td><td></td></tr>
  </table>
  </td>
  </tr>
  </table>

  <input type="hidden" name="question_no" value="<?php echo $ex_no; ?>" />
  <div align="center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" style="width:150px" />&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="history.back()" style="width:80px" /></div>
  </form>
<?php
}
?>
</body>
</html>