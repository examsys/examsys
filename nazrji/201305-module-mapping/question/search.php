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
* Displays the results of a question search.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../classes/questionutils.class.php';
set_time_limit(0);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rogō: <?php echo $string['questionsearch'] . " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    .o {color:#A5A5A5}
    .l {padding-left:6px; vertical-align:top}
    .retired {color:#808080}
    .qline {line-height:150%;cursor:pointer;color:#000000;background-color:white; -webkit-user-select:none; -moz-user-select:none;}
    .qline:hover {background-color:#eee}
    .qline.highlight {background-color:#B3C8E8}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript">
    function addQID(qID, clearall) {
      if (clearall) {
        document.PapersMenu.questionID.value = ',' + qID;
      } else {
        document.PapersMenu.questionID.value = document.PapersMenu.questionID.value + ',' + qID;
      }
    }

    function subQID(qID) {
      var tmpq = ',' + qID;
      document.PapersMenu.questionID.value = document.PapersMenu.questionID.value.replace(tmpq, '');
    }

    function clearAll() {
      $('.highlight').removeClass('highlight');
    }

    function selQ(questionID, qType, menuID, evt) {
      document.getElementById('menu2a').style.display = 'none';
      document.getElementById('menu2b').style.display = 'none';
      document.getElementById('menu2c').style.display = 'none';
      document.getElementById('menu' + menuID).style.display = 'block';

      lineID = questionID;

      if (evt.ctrlKey == false) {
        clearAll();
        $('#id' + lineID).addClass('highlight');
        addQID(questionID, true);
      } else {
        if ($('#id' + lineID).hasClass('highlight')) {
          $('#id' + lineID).removeClass('highlight');
          subQID(questionID);
        } else {
          $('#id' + lineID).addClass('highlight');
          addQID(questionID, false);
        }
      }
      document.PapersMenu.qType.value = qType;
      document.PapersMenu.oldQuestionID.value = lineID;

      if (evt != null) {
        evt.cancelBubble = true;
      }

    }

    function qOff() {
      document.getElementById('menu2a').style.display = 'block';
      document.getElementById('menu2b').style.display = 'none';
      document.getElementById('menu2c').style.display = 'none';
      tmp_ID = document.PapersMenu.oldQuestionID.value;
      if (tmp_ID != '') {
        document.getElementById('link_' + tmp_ID).style.backgroundColor = 'white';
      }
    }
  </script>
</head>

<?php
  if (isset($_POST['submit'])) {
    echo "<body onselectstart=\"return false\">\n";

    require '../include/question_search_options.inc';

    echo "<div id=\"content\" class=\"content\">\n";
  } else {
    echo "<body style=\"margin:0px; background-color:white; color:black\">\n";

    require '../include/question_search_options.inc';

    echo "<div id=\"content\" class=\"content\">\n";
    echo "<table class=\"header\">\n";
    echo "<tr><th colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>" . $string['questionsearch'] . "</div></th></tr>";
?>
  <tr>
    <th>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['question']; ?>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['type']; ?>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['modified']; ?>&nbsp;</th>
  </tr>
  <tr>
    <th colspan="4" class="bevel"></th>
  </tr>
  </table>
<?php
  }

if (isset($_POST['submit'])) {
  $error = '';

  if (!isset($_POST['theme']) and !isset($_POST['scenario']) and !isset($_POST['leadin']) and !isset($_POST['options']) and !isset($_POST['keywords'])) {
    $error = $string['notickedfields'];
  }

  if ($_POST['searchterm'] == '' and $_POST['owner'] == '' and  $_POST['status'] == '%' and $_POST['bloom'] == '%' and $_POST['keywordID'] == '' and $_POST['status'] == '%' and $_POST['module'] == '' and $_POST['question_date'] == 'dont remember' and $_POST['qType'] == '' ) {
    $error = $string['narrowyoursearch'];
  }

  if ($error != '') {
    echo "<table class=\"header\" style=\"table-layout:fixed\">\n";
    echo "<tr><th colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>".$string['questionsearch']."</div></th></tr>";
    ?>
    <tr>
    <th>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['question']; ?>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['type']; ?>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['modified']; ?>&nbsp;</th></tr>
    <tr><th colspan="4" class="bevel"></td></tr>
    </table>
    <?php
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border:1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;$error.</td></tr></table>\n";
    exit;
  }

  echo "<table class=\"header fixed\">\n";

  $params = '';
  $variables = array();

  $keywordsSQL = '';
  if ($_POST['keywordID'] != '') {
    $keywordsSQL = 'AND keywordID=?';
    $variables[] = intval($_POST['keywordID']);
    $params .= 'i';
  }

  $searchterm = $_POST['searchterm'];
  if ($searchterm == '') {
    $search_string = '';
  } else {
    if (isset($_POST['theme']) and $_POST['theme']) {
      $themeSQL = ' OR theme LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $themeSQL = '';
    }

    if (isset($_POST['scenario']) and $_POST['scenario']) {
      $scenarioSQL = ' OR scenario_plain LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $scenarioSQL = '';
    }

    if (isset($_POST['leadin']) and $_POST['leadin']) {
      $leadinSQL = ' OR leadin_plain LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $leadinSQL = '';
    }

    if (isset($_POST['options']) and $_POST['options']) {
      $stemsSQL = ' OR option_text LIKE ?';
      $variables[] = '%' . $searchterm . '%';
      $params .= 's';
    } else {
      $stemsSQL = '';
    }

    $search_string = $themeSQL . $scenarioSQL . $leadinSQL . $stemsSQL;
    $search_string = 'AND (' . substr($search_string, 4) . ')';
  }

  if ($_POST['module'] != '') {
    $module_string = ' AND idMod = ?';
    $variables[] = $_POST['module'];
    $params .= 'i';
  } else {
    $module_string = '';
  }

  if ($_POST['owner'] != '' or count($staff_modules) == 0) {
    $user_string = ' AND questions.ownerID=?';
    $variables[] = $_POST['owner'];
    $params .= 'i';
  } else {
    // If no specific owner set lock down by team (apart from SysAdmin).
    if (count($staff_modules) > 0 and $_POST['module'] == '') {
      $user_string = implode(',', array_keys($staff_modules));
      $user_string = " AND (idMod IN ($user_string) OR users.id={$userObject->get_user_ID()})";
    } else {
      $user_string = '';
    }
  }

  if (isset($_POST['status']) and $_POST['status'] != '%') {
    if ($_POST['status'] == 'nonretired') {
      $status_string = " AND questions.status != 'retired'";
    } else {
      $status_string = ' AND questions.status=?';
      $variables[] = $_POST['status'];
      $params .= 's';
    }
  } else {
    $status_string = '';
  }

  if (isset($_POST['locked']) and $_POST['locked'] == '1') {
    $locked_string = '';
  } else {
    $locked_string = " AND locked IS NULL";
  }

  if ($_POST['question_date'] == 'dont remember') {
    $last_edited = '';
  } else {
    switch ($_POST['question_date']) {
      case 'week':
        $from_date = date('YmdHis', mktime(date("H"),date("i"),date("s"),date("m"),date("d")-7,date("Y")));
        $to_date = date("YmdHis");
        break;
      case 'month':
        $from_date = date('YmdHis', mktime(date("H"),date("i"),date("s"),date("m")-1,date("d"),date("Y")));
        $to_date = date("YmdHis");
        break;
      case 'year':
        $from_date = date('YmdHis', mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y")-1));
        $to_date = date("YmdHis");
        break;
      case 'specify':
        $from_date = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . "000000";
        $to_date = $_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . "235959";
        break;
    }
    $last_edited = 'AND last_edited>? AND last_edited<?';
    $variables[] = $from_date;
    $variables[] = $to_date;
    $params .= 'ss';
  }

  if ($_POST['searchtype'] == '%') {
    $q_type = '';
  } else {
    $q_type = 'AND q_type LIKE ?';
    $variables[] = $_POST['searchtype'];
    $params .= 's';
  }

  if ($_POST['bloom'] == '%') {
    $bloom ='';
  } else {
    $bloom = 'AND bloom LIKE ?';
    $variables[] = $_POST['bloom'];
    $params .= 's';
  }

  if ($keywordsSQL == '') {
    $sql = "SELECT DISTINCT title, initials, surname, q_type, questions.q_id, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS last_edited, ownerID, locked, status FROM (questions, users, options) LEFT JOIN questions_modules ON questions.q_id = questions_modules.q_id WHERE questions.q_id = options.o_id AND questions.ownerID=users.id $search_string $module_string $user_string $status_string $locked_string $last_edited $q_type $bloom AND deleted IS NULL ORDER BY leadin_plain";
  } else {
    $sql = "SELECT DISTINCT title, initials, surname, q_type, questions.q_id, leadin, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS last_edited, ownerID, locked, status FROM (questions, users, keywords_question, options) LEFT JOIN questions_modules ON questions.q_id = questions_modules.q_id WHERE questions.q_id = options.o_id AND questions.q_id=keywords_question.q_id $keywordsSQL AND questions.ownerID=users.id $search_string $module_string $user_string $status_string $locked_string $last_edited $q_type $bloom AND deleted IS NULL ORDER BY leadin_plain, questions.q_id";
  }
  $result = $mysqli->prepare($sql);
  if (count($variables) > 0) {
    array_unshift($variables, $params);
    foreach ($variables as $key => $value) {
      $tmp[$key] = &$variables[$key];
    }
    call_user_func_array(array($result,'bind_param'), $tmp);
  }
  $result->execute();
  $result->store_result();
  $result->bind_result($title, $initials, $surname, $q_type, $q_id, $leadin, $last_edited, $ownerID, $locked, $status);

  $hits = $result->num_rows;

  // Empty first line to fix widths
  echo "<tr><th style=\"width: 18px\"></th><th></th><th style=\"width: 150px\"></th><th style=\"width: 80px\"></th></tr>";
  echo "<tr><th colspan=\"4\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>" . $string['questions'] . " (" . number_format($hits) . "):&nbsp;</strong>";
  if (isset($_POST['searchterm']) and $_POST['searchterm'] != '') {
    echo "'" . $_POST['searchterm'] . "'";
  } elseif (isset($_POST['searchtype']) and $_POST['searchtype'] != '%') {
    echo $string[$_POST['searchtype']];
  } else {
    echo $_POST['module'];
  }
  echo "</div></th></tr>";
?>
  <tr>
    <th>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['question']; ?>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['type']; ?>&nbsp;</th>
    <th class="vert_div">&nbsp;<?php echo $string['modified']; ?>&nbsp;</th>
  </tr>
  <tr>
    <th colspan="4" class="bevel"></th>
  </tr>
<?php
  while ($result->fetch()) {
    echo '<tr class="qline';
    if ($status == 'Retired') {
      echo ' retired';
    }
    if ($locked != '') {
      echo "\" id=\"id$q_id\" onclick=\"selQ($q_id,'$q_type','2c',event); return false;\" ondblclick=\"editQ(); return false;\"><td><img src=\"../artwork/small_padlock.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $string['locked'] . "\" /></td>";
    } else {
      echo "\" id=\"id$q_id\" onclick=\"selQ($q_id,'$q_type','2b',event); return false;\" ondblclick=\"editQ(); return false;\"><td></td>";
    }

    $tmp_leadin = QuestionUtils::clean_leadin($leadin);
    if (trim($tmp_leadin) == '') $tmp_leadin = '<span style="color:red">' . $string['noquestionleadin'] . '</span>';

    echo "<td class=\"l\">$tmp_leadin <span class=\"o\">($title $initials $surname)</span></td>";
    echo '<td class="l"><nobr>' . $string[$q_type] . '</nobr></td>';
    echo '<td class="l">' . $last_edited . '</td></tr>';
  }
  $result->close();

  echo "</table>\n";

  if ($hits == 0) {
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border:1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;" . $string['noquestionsfound'] . "</td></tr></table>\n";
  }

  $mysqli->close();
}
?>
</div>
</body>
</html>