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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/
require '../include/staff_auth.inc';
require_once '../include/sort.inc';
require_once '../lang/' . $language . '/include/question_types.php';
require_once '../classes/questionbank.class.php';
require_once '../include/errors.php';

$type = check_var('type', 'GET', true, false, true);

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState();

$_SESSION['nav_page'] = $_SERVER['SCRIPT_NAME'];
$_SESSION['nav_query'] = $_SERVER['QUERY_STRING'];

// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);

$statusSQL  = '';
if (isset($_GET['status'])) {
  $statusSQL = " AND status = " . $_GET['status'];
}
if (isset($_GET['userid'])) {
  $userid = $_GET['userid'];
} else {
  $userid = '';
}
if (isset($_GET['keyword'])) {
  $keyword = $_GET['keyword'];
} else {
  $keyword = '';
}
if (isset($_GET['module'])) {
  $module = $_GET['module'];
  if ($module != '0') {
    if (!isset($module_details)) {
      $module_details = module_utils::get_full_details_by_ID($module, $mysqli);
    }
    $module_code = $module_details['moduleid'];
    if (!$module_code) {
      $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
      $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
  } else {
    $module_code = 'Unassigned';
  }
} else {
  $module = '';
}

$qbank = new QuestionBank($module, $module_code, $string, $notice, $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['questionbank'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/tablesort.css" />
  <link rel="stylesheet" type="text/css" href="../css/question_list.css" />
  <link rel="stylesheet" type="text/css" href="../css/adhocwindow.css" ?>
  <style type="text/css">
    label {padding-top:2px}
  <?php echo QuestionStatus::generate_status_css($status_array); ?>
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/sidebar.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/adhocwindow.js"></script>
  <script>
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          dateFormat: '<?php echo $configObject->get('cfg_tablesorter_date_time'); ?>',
          sortList: [[0,0]]
        });
      }
      
      $('.q').dblclick(function() {
        ed();
      });

    });

    $(function () {
      check_checkboxes();
      
      $(document).click(function() {
        hideMenus(this);
      });
    });
  </script>
</head>

<body onselectstart="return false">
<?php
  require '../include/question_list_options.inc';
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>

<div id="content">
<?php
  $question_no = 0;
  $display_no = 0;
  $bank_type = '';
  $module_sql = '';

  if ($keyword != '%' and $keyword != '') {
    $bank_type = ": '" . keyword_utils::name_from_ID($keyword, $mysqli) . "'";
 } elseif (isset($_GET['bloom'])) {
    $bank_type = ': ' . $_GET['type'];
  } elseif ($_GET['type'] == 'performance') {
    $types = array('veryeasy' => 'Very Easy', 'easy' => 'Easy', 'moderate' => 'Moderate', 'hard' => 'Hard', 'veryhard' => 'Very Hard', 'highest' => 'Highest', 'high' => 'High', 'intermediate'  => 'Intermediate', 'low'  => 'Low');
    $bank_type = ': ' . $types[$_GET['subtype']];
  } elseif ($module != '') {
    $bank_type = ": $module_code";
  } elseif ($_GET['type'] != '%') {
    $bank_type = ': ' . $string[$_GET['type']];
  }
  
  echo "<div class=\"head_title\">\n";
  echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>\n";
  
  echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
  if (isset($_GET['module'])) {
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . $module_code . '</a>';
    
    if ($_GET['type'] == 'type') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../question/bank.php?type=type&module=' . $_GET['module'] . '">' . $string['questiontype'] . '</a>'; 
    } elseif ($_GET['type'] == 'bloom') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../question/bank.php?type=bloom&module=' . $_GET['module'] . '">' . $string['bloomstaxonomy'] . '</a>'; 
    } elseif ($_GET['type'] == 'status') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../question/bank.php?type=status&module=' . $_GET['module'] . '">' . $string['status'] . '</a>'; 
    } elseif ($_GET['type'] == 'keyword') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../question/bank.php?type=keyword&module=' . $_GET['module'] . '">' . $string['keyword'] . '</a>'; 
    } elseif ($_GET['type'] == 'performance') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../question/bank.php?type=performance&module=' . $_GET['module'] . '">' . $string['performance'] . '</a>'; 
    }
  }
  echo "</div><div class=\"page_title\">" . $string['questionbank'] . "&nbsp;<span id=\"q_count\"></span><span style=\"font-weight:normal\">$bank_type</span></div>";
  echo "</div>\n";
  if ($module != 0 and strpos($module_details['checklist'], 'mapping') === false and $_GET['type'] == 'objective') {
    echo $notice->info_strip($string['modulenomappings'], 100) . "\n</div>\n</body>\n</html>";
    exit;
  }
 
  $staff_modules_sql = '';
  if ($module != '') {
    $module_sql = "questions_modules.idMod = " . $_GET['module'];
  } else {
    if (count($staff_modules) > 0) {
      $staff_modules_list = implode(',', array_keys($staff_modules));
      $staff_modules_sql = " AND ((questions_modules.idMod IN ($staff_modules_list)";
      $staff_modules_sql .= ") OR users.id=" . $userObject->get_user_ID() . ") ";
    } else {
      // Reset to just look for current owners paper if not on any teams.
      $staff_modules_sql .= "AND users.id=" . $userObject->get_user_ID() . " ";
    }
  }

  if ($module_sql != '') {
    $module_sql = 'AND (' . $module_sql .')';
  }

  if ($keyword != '%' and $keyword != '') {
    $keyword = ' AND keywordID=' . $keyword;
  } else {
    $keyword = '';
  }

  $display_no = 0;

  $retired_in = '-1,' . implode(',', QuestionStatus::get_retired_status_ids($status_array));
	
	$questions = array();
		
	if (isset($_GET['sortby'])) {
		$sortby = $_GET['sortby'];
	} else {
	  if (isset($state['sortby'])) {
			$sortby = $state['sortby'];
		} else {
			$sortby = 'leadin';
		}
	}
	
	if (isset($_GET['ordering'])) {
		$ordering = $_GET['ordering'];
	} else {
	  if (isset($state['ordering'])) {
			$ordering = $state['ordering'];
		} else {
			$ordering = 'asc';
		}
	}
	
	if ($sortby == 'modified') {
		$tmp_sortby = 'last_edited';
	} else {
		$tmp_sortby = $sortby;
	}
	if ($tmp_sortby == 'q_type' and isset($_GET['type'])) {
	  $tmp_sortby = 'leadin';
	}
  //SL 05-02-2015 changed to look at leadin column instead of leadin_plain column
  if ($_GET['module'] == '0') {
    $sql = "SELECT DISTINCT NULL AS extra_field, NULL AS p, NULL AS d, questions.q_id, theme, leadin AS leadin, q_type, last_edited, DATE_FORMAT(last_edited, '{$configObject->get('cfg_long_date')}') AS modified, locked, status, bloom FROM (users, questions) LEFT JOIN questions_modules ON questions.q_id = questions_modules.q_id WHERE users.id = questions.ownerID AND ownerID = " . $userObject->get_user_ID() . " AND idMod IS NULL GROUP BY q_id";
  } elseif ($_GET['type'] == 'performance') {
    $sql = "SELECT DISTINCT NULL AS extra_field, p, d, questions.q_id, theme, leadin AS leadin, q_type, last_edited, DATE_FORMAT(last_edited, '{$configObject->get('cfg_long_date')}') AS modified, locked, status, bloom FROM (questions, performance_main, performance_details, questions_modules) WHERE questions.q_id = performance_main.q_id AND performance_main.id = performance_details.perform_id AND questions.q_id = questions_modules.q_id AND idMod = $module";
  } elseif ($_GET['type'] == 'keyword') {
    $sql = "SELECT DISTINCT keyword AS extra_field, keywordID AS p, NULL AS d, questions.q_id, theme, leadin AS leadin, q_type, last_edited, DATE_FORMAT(last_edited, '{$configObject->get('cfg_long_date')}') AS modified, locked, status, bloom FROM (questions, questions_modules, keywords_question, keywords_user) WHERE questions.q_id = keywords_question.q_id AND keywords_question.keywordID = keywords_user.id AND questions.q_id = questions_modules.q_id AND idMod = $module AND deleted IS NULL AND status NOT IN ($retired_in)";
  } elseif ($_GET['type'] == 'bloom') {
    $sql = "SELECT DISTINCT bloom AS extra_field, NULL AS p, NULL AS d, questions.q_id, theme, leadin AS leadin, q_type, last_edited, DATE_FORMAT(last_edited, '{$configObject->get('cfg_long_date')}') AS modified, locked, status, bloom FROM (questions, questions_modules) WHERE questions.q_id = questions_modules.q_id $module_sql $staff_modules_sql $statusSQL AND deleted IS NULL AND status NOT IN ($retired_in)";
  } elseif ($_GET['type'] == 'objective') {
    $yearutils = new yearutils($mysqli);
    $vle_api_cache = array();
    $vle_api_data = MappingUtils::get_vle_api($module, $yearutils->get_current_session(), $vle_api_cache, $mysqli);
    $sql = "SELECT DISTINCT GROUP_CONCAT(obj_id SEPARATOR ' ') AS extra_field, NULL AS p, NULL AS d, questions.q_id, theme, leadin AS leadin, q_type, last_edited, DATE_FORMAT(last_edited, '{$configObject->get('cfg_long_date')}') AS modified, locked, status, bloom FROM (questions, questions_modules, relationships) WHERE questions.q_id = questions_modules.q_id AND questions.q_id = relationships.question_id AND relationships.vle_api = '{$vle_api_data['api']}' AND relationships.map_level = '{$vle_api_data['level']}' $module_sql $staff_modules_sql $statusSQL AND deleted IS NULL AND status NOT IN ($retired_in) GROUP BY question_id";
  } else {
    $sql = "SELECT DISTINCT NULL AS extra_field, NULL AS p, NULL AS d, questions.q_id, theme, leadin AS leadin, q_type, last_edited, DATE_FORMAT(last_edited, '{$configObject->get('cfg_long_date')}') AS modified, locked, status, bloom FROM (questions, questions_modules) WHERE questions.q_id = questions_modules.q_id $module_sql $staff_modules_sql $statusSQL $keyword AND deleted IS NULL";
    if ($_GET['type'] != 'status') {
      $sql .= " AND status NOT IN ($retired_in)";
    }
  }
  
  $search_results = $mysqli->prepare($sql);
  $search_results->execute();
  $search_results->bind_result($extra_field, $p, $d, $q_id, $theme, $leadin, $q_type, $last_edited, $modified, $locked, $status, $bloom);
  $search_results->store_result();

  if ($type == 'keyword') {
    $table_order = array($string['question']=>800, $string['type']=>100, 'Keyword'=>100, $string['modified']=>70, $string['status']=>70);
  } elseif ($type == 'bloom') {
    $table_order = array($string['question']=>800, $string['type']=>100, 'Bloom\'s Taxonomy'=>100, $string['modified']=>70, $string['status']=>70);
  } elseif ($type == 'performance') {
    $table_order = array($string['question']=>800, $string['type']=>100, 'P'=>50, 'D'=>50, $string['modified']=>70, $string['status']=>70);
  } else {
    $table_order = array($string['question']=>800, $string['type']=>100, $string['modified']=>70, $string['status']=>70);
  }

  if (isset($_GET['type']) and $_GET['type'] == 'all' and $search_results->num_rows == 0) {
    echo $notice->info_strip($string['noquestions'], 100) . "\n</div>\n</body>\n</html>";
    exit;
  }
  
  $params = '';
	if (isset($_GET['type'])) $params .= '&type=' . $_GET['type'];
	if (isset($_GET['module'])) $params .= '&module=' . $_GET['module'];
	if (isset($_GET['keyword'])) $params .= '&keyword=' . $_GET['keyword'];
	
  echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n";
  echo "<thead>\n";
  foreach ($table_order as $display => $col_width) {
    if ($display == $string['modified']) {
      echo "<th class=\"{sorter: 'datetime'} vert_div\">$display</th>";
    } else {
      echo "<th class=\"vert_div\">$display</th>";
    }
  }
  ?>
	</tr>
	</thead>

  <tbody>
  <?php
  while ($search_results->fetch()) {
    echo '<tr class="q';

    if ($_GET['type'] == 'type' or $_GET['type'] == 'all') {
      echo ' ' . $q_type;
    } elseif ($_GET['type'] == 'status') {
      echo ' ' . $status;
    } elseif ($_GET['type'] == 'keyword') {
      echo ' ' . $p;
    } elseif ($_GET['type'] == 'bloom' and $bloom != '') {
      echo ' ' . strtolower($bloom);
    } elseif ($_GET['type'] == 'performance') {
        if ($p >= 80 and $p <= 100) {
          echo ' veryeasy';     // Very Easy
        } elseif ($p >= 60 and $p < 80) {
          echo ' easy';      // Easy
        } elseif ($p >= 40 and $p < 60) {
          echo ' moderate';      // Moderate
        } elseif ($p >= 20 and $p < 40) {
          echo ' hard';      // Hard
        } elseif ($p >= 0 and $p < 20) {
          echo ' veryhard';     // Very Hard
        }

        if ($d >= 35) {
          echo ' highest';
        } elseif ($d >= 25 and $d < 35) {
          echo ' high';
        } elseif ($d >= 15 and $d < 25) {
          echo ' intermediate';
        } elseif ($d >= 0 and $d < 15) {
          echo ' low';
        }
    } elseif ($_GET['type'] == 'objective' and $extra_field != '') {
      echo ' ' . $extra_field;
    } 
    if ($locked != '') {
      echo ' lock';
    }
    echo '"';
    
    echo " id=\"l" . $q_id . "_" . $display_no . "\">";
    
    if ($q_type == 'sct') {
        $parts = explode('~', $leadin);
        $leadin = $parts[0];
    }
    $leadin = str_replace('&nbsp;', ' ', $leadin);
    $leadin = str_replace("\n", '', $leadin);
    $leadin = str_replace("\r", '', $leadin);
    if (trim($leadin) == '') $leadin = '<span style="color:#C00000">' . $string['noquestionleadin'] . '</span>';

    if ($locked == '') {
      echo '<td class="u"';
    } else {
      echo '<td class="l"';
    }
    if (strlen($leadin) > $configObject->get('cfg_search_leadin_length')) {
      echo ' onmouseover="showAdHocWindow(event,\''.htmlspecialchars(QuestionUtils::clean_leadin($leadin, 0)).'\');" ';
      echo ' onmouseleave="hideAdHocWindow();" ';
    }
    echo '>';
    if (trim($theme) != '') {
      echo '<span class="t">' . $theme . '</span><br />&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    //sl change this from echo $leadin to below
	
    echo QuestionUtils::clean_leadin($leadin, $configObject->get('cfg_search_leadin_length')) . '</td>';
    echo '<td class="nobr">' . $string[$q_type] . '</td>';
    if ($type == 'keyword' or $type == 'bloom') {
      echo '<td>' . $extra_field . '</td>';    
    } elseif ($type == 'performance') {
      echo '<td>' . ($p / 100) . '</td>';    
      echo '<td>' . ($d / 100) . '</td>';    
    }
    echo '<td>' . $modified . '</td>';
    echo "<td>" . $status_array[$status]->get_name() . "</td></tr>\n";

    $display_no++;
  }
	$search_results->close();

	if (isset($_GET['sortby'])) {
		$stateutil->setState($userObject->get_user_ID(), 'sortby', $_GET['sortby'], $_SERVER['PHP_SELF'], $mysqli);
	}
	if (isset($_GET['ordering'])) {
		$stateutil->setState($userObject->get_user_ID(), 'ordering', $_GET['ordering'], $_SERVER['PHP_SELF'], $mysqli);
	}
  
  $mysqli->close();
?>
</tbody>
</table>
</div>

</body>
</html>
