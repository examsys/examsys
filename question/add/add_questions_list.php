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

require '../../include/staff_auth.inc';
require '../../include/errors.php';

// Get question statuses
$status_array = QuestionStatus::get_all_statuses($mysqli, $string, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../../css/tablesort.css" />
  <style type="text/css">
    body {font-size:80%}
    a {text-decoration:none}

<?php echo QuestionStatus::generate_status_css($status_array); ?>
  </style>
  <script type="text/javascript" src="../../js/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="../../js/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="../../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../../tools/mee/mee/js/mee_src.js"></script>
  <script>
    function populateTicks() {
      var q_array = parent.top.controls.document.getElementById('questions_to_add').value.split(",");
      for (i=0; i<q_array.length; i++) {
        if (q_array[i]!='') {
          var obj = document.getElementById(q_array[i]);
          if (obj != null) {
            obj.checked = true;
          }
        }
      }
    }
    
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          dateFormat: 'uk',
          sortList: [[2,0]] 
        });
      }
      
      populateTicks();
      
      $('.prev').click(function() {
        q_id = $(this).attr('id');
        parent.top.qlist.previewurl.location = '../view_question.php?q_id=' + q_id.substring(4);
      });
      
      
      $(":checkbox").change(function() {
        parent.top.controls.checkStatus(this);
      });

    });
  </script>
</head>
<body>
<?php
  if (isset($_GET['display_pos'])) {
    $display_pos = $_GET['display_pos'];
  } else {
    $display_pos = 1;
  }

  if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
    $ordering = $_GET['ordering'];
  } else {
    $sortby = 'leadin';
    $ordering = 'asc';
  }
  
  $moduleObj = new module();

  echo "<form name=\"theform\" autocomplete=\"off\">\n";
  ?>
  <input type="hidden" name="screen" value="1" />
  <table class="header">
  <?php
  switch($_GET['type']) {
    case 'unused':
      echo "<tr><th colspan=\"6\" style=\"font-size:160%; font-weight:bold\">&nbsp;" . $string['myunusedquestions'] . "</th></tr>\n";
      break;
    case 'all':
      echo "<tr><th colspan=\"6\" style=\"font-size:160%; font-weight:bold\">&nbsp;" . $string['allmyquestions'] . "</th></tr>\n";
      break;
    case 'team':
      echo "<tr><th colspan=\"6\" style=\"font-size:160%\">&nbsp;<strong>" . $string['team'] . ":&nbsp;</strong>" . $moduleObj->get_moduleid_from_id($_GET['teamID'], $mysqli) . "</th></tr>\n";
      break;
    case 'status':
      echo "<tr><th colspan=\"6\" style=\"font-size:160%\">&nbsp;<strong>" . $string['status'] . ":&nbsp;</strong>" . $status_array[$_GET['status']]->get_name() . "</th></tr>\n";
      break;
    case 'keyword':
      $keyword_ids = '';
      if (isset($_POST['keyword_no'])) {
        for ($i=0; $i<$_POST['keyword_no']; $i++) {
          if (isset($_POST["keyword$i"])) {
            if ($keyword_ids == '') {
              $keyword_ids = $_POST["keyword$i"];
            } else {
              $keyword_ids .= ',' . $_POST["keyword$i"];
            }
          }
        }
        if ($keyword_ids == '') {
          echo "</table>\n</body>\n</html>\n";
          exit;
        }
      } else {
        if (isset($_GET['keyword_ids'])) {
          $keyword_ids = $_GET['keyword_ids'];
        } else {
          $keyword_ids = 0;
        }
      }
      echo "<tr><th colspan=\"6\" style=\"font-size:160%; font-weight:bold\">&nbsp;" . $string['bykeyword'] . "</th></tr>\n";
      break;
  }
  echo "</table>\n";

  echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n";
  echo "<thead>\n";
  echo '<tr>';
  $table_order = array('#1'=>18, '#2'=>18, $string['question']=>400, $string['type']=>100, $string['modified']=>100, $string['status']=>120);
  foreach ($table_order as $display => $col_width) {
    if ($display{0} == '#') {
      echo "<th style=\"width:" . $col_width . "px\" class=\"vert_div\"></th>\n";
    } elseif ($display == $string['modified']) {
      echo "<th style=\"width:" . $col_width . "px\" class=\"{sorter: 'datetime'} vert_div\">$display</th>\n";
    } else {
      echo "<th style=\"width:" . $col_width . "px\" class=\"vert_div\">$display</th>\n";
    }
  }
  ?>
  </tr>
  </thead>
  
  <tbody>
  <?php
  $id = 0;
  if ($sortby == 'leadin') $sortby = 'leadin_plain';
  if ($sortby == 'q_type') $sortby = 'CAST(q_type AS CHAR)';

  $retired_in = '-1,' . implode(',', QuestionStatus::get_retired_status_ids($status_array));

  switch($_REQUEST['type']) {
    case 'unused':
      $result = $mysqli->prepare("SELECT questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_long_date')}') AS display_date, locked, status, name FROM papers RIGHT JOIN (questions, question_statuses) ON papers.question = questions.q_id WHERE questions.status = question_statuses.id AND question IS NULL AND questions.ownerID = ? AND status NOT IN ($retired_in) AND deleted IS NULL ORDER BY $sortby $ordering");
      $result->bind_param('i', $userObject->get_user_ID());
      break;
    case 'all':
      $result = $mysqli->prepare("SELECT questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_long_date')}') AS display_date, locked, status, name FROM questions, question_statuses WHERE questions.status = question_statuses.id AND ownerID = ? AND status NOT IN ($retired_in) AND deleted IS NULL ORDER BY $sortby $ordering");
      $result->bind_param('i', $userObject->get_user_ID());
      break;
    case 'team':
      $result = $mysqli->prepare("SELECT questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_long_date')}') AS display_date, locked, status, name FROM (questions, question_statuses, questions_modules) WHERE questions.q_id = questions_modules.q_id AND questions.status = question_statuses.id AND idMod = ? AND deleted IS NULL ORDER BY $sortby $ordering");
      $result->bind_param('i', $_GET['teamID']);
      break;
    case 'status':
      $teams = $userObject->get_staff_modules();
      $module_id_list = implode(',', array_keys($teams));

      $sql = "SELECT questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_long_date')}') AS display_date, locked, status, name "
          . "FROM (questions, question_statuses, questions_modules, modules) "
          . "WHERE questions.q_id = questions_modules.q_id AND questions.status = question_statuses.id AND questions_modules.idMod = modules.id AND status = ? "
          . "AND (ownerID = ?";
      if (!empty($module_id_list)) {
        $sql .= " OR modules.id IN ($module_id_list)";
      }
      $sql .= ") AND deleted IS NULL ORDER BY $sortby $ordering";
      $result = $mysqli->prepare($sql);
      $result->bind_param('si', $_GET['status'], $userObject->get_user_ID());
      break;
    case 'keyword':
      $teams = $userObject->get_staff_modules();

      $retired_in = '-1,' . implode(',', QuestionStatus::get_retired_status_ids($status_array));
      if (count($teams) == 0) {
        $sql = "SELECT
                  questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_long_date')}') AS display_date, locked, status, name
                FROM
                  (questions, question_statuses, keywords_question)
                LEFT JOIN
                  question_exclude
                ON
                  questions.q_id = question_exclude.q_id
                WHERE
                  questions.q_id = keywords_question.q_id
                AND
                  questions.status = question_statuses.id
                AND
                  keywords_question.keywordID IN ($keyword_ids)
                AND
                  ownerID = ?
                AND
                  status NOT IN ($retired_in)
                AND
                  deleted IS NULL
                ORDER BY
                  $sortby $ordering, questions.q_id";
      } else {
        $sql = "SELECT
                  questions.q_id, q_type, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_long_date')}') AS display_date, locked, status, name
                FROM
                  (questions, question_statuses)
                LEFT JOIN
                  question_exclude ON questions.q_id=question_exclude.q_id
                WHERE
                  questions.status = question_statuses.id
                AND
                  questions.q_id IN (SELECT q_id from keywords_question WHERE keywords_question.keywordID IN ($keyword_ids)) AND
                  (ownerID = ? OR questions.q_id IN (SELECT q_id from questions_modules where idMod IN (" . implode(',', array_keys($teams)) . "))) AND
                  status NOT IN ($retired_in) AND deleted IS NULL
                ORDER BY
                  $sortby $ordering, questions.q_id";
      }
      $result = $mysqli->prepare($sql);
      $result->bind_param('i', $userObject->get_user_ID());
    
      break;
  }
  $result->execute();
  $result->bind_result($q_id, $q_type, $leadin, $display_date, $locked, $status, $status_name);
  while ($result->fetch()) {
    $tmp_leadin = QuestionUtils::clean_leadin($leadin);
    if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:#C00000">' . $string['warningnoleadin'] . '</span>';

    $status_class = 'status' . $status;
    echo "<tr class=\"$status_class\"><td>";
    if ($locked != '') echo '<img src="../../artwork/small_padlock.png" width="18" height="18" alt="' . $string['locked'] . '" />';
    echo "</td><td><input type=\"checkbox\" name=\"$q_id\" value=\"$q_id\" /></td><td class=\"prev\" id=\"prev$q_id\">$tmp_leadin</td><td><nobr>&nbsp;" . $string[$q_type] . "</nobr></td><td>" . $display_date . "</td><td>" . $status_name . "</td></tr>\n";
  }
  $result->close();
  $mysqli->close();
?>
</tbody>
</table>
</form>
</body>
</html>