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

require '../../include/staff_auth.inc';
require '../../include/question_types.inc';
require_once '../../classes/questionutils.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['bykeywords']; ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/header.css" />
  <style type="text/css">
    html {height:100%; border-left:1px solid #95AEC8}
    body {font-size:80%}
    .f {padding-left:2px}
    .n {text-align:right; padding-right:2px}
    .mee { display: inline; }
  </style>
  <script type="text/javascript" src="../../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript">
    function orderTable(order_val, direction_val) {
      document.getElementById('order').value = order_val;
      document.getElementById('direction').value = direction_val;
      document.keywordsform.submit();
    }

    function Qpreview(qID) {
      parent.parent.previewurl.location = '../view_question.php?q_id=' + qID;
    }

    function populateTicks() {
      q_array = parent.top.controls.document.getElementById('questions_to_add').value.split(",");
      for (i=0; i<q_array.length; i++) {
        if (q_array[i]!='') {
          var obj = document.getElementById(q_array[i]);
          if (obj != null) {
            obj.checked = true;
          }
        }
      }
    }

    // Get the selected questions and make sure that they are re-checked when we re-order the questions
    $(function () {
      var selected_qs = parent.top.controls.selected_q;
      for (var i = 0; i < selected_qs.length; i++) {
        $('#' + selected_qs[i]).prop('checked', true);
      }
    });

  </script>
</head>

<body onload="populateTicks()">
<?php
  if (isset($_POST['order'])) {
    $order = $_POST['order'];
    $direction = $_POST['direction'];
  } else {
    $order = 'leadin';
    $direction = 'asc';
  }

  echo "<form name=\"theform\" method=\"post\" action=\"\">\n";
  echo "<input type=\"hidden\" name=\"screen\" value=\"1\" />\n";
  echo "<table class=\"header\">\n";
  echo "<tr><th valign=\"top\" colspan=\"5\" style=\"font-size:160%; font-weight:bold\">&nbsp;" . $string['bykeywords'] . "</th></tr>\n";

  if ($order == 'leadin' and $direction == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('leadin','desc'); return false;\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'leadin' and $direction == 'desc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('q_type','desc'); return false;\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'q_type' and $direction == 'desc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;</th></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('last_edited','desc'); return false;\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  } elseif ($order == 'last_edited' and $direction == 'desc') {
    echo "<tr><th colspan=\"2\">&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('leadin','asc'); return false;\">" . $string['question'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('q_type','asc'); return false;\">" . $string['type'] . "</a>&nbsp;</th><th class=\"vert_div\">&nbsp;<a onclick=\"orderTable('last_edited','asc'); return false;\">" . $string['modified'] . "</a>&nbsp;<img src=\"../../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  }

  echo "<tr><th colspan=\"5\" class=\"bevel\"></th></tr>\n";

  if (!isset($_POST['keyword_no'])) {
    echo "</table>\n</body>\n</html>\n";
    exit;
  }

  $keyword_ids = '';
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

  $teams = $userObject->get_staff_modules();

  if (count($teams) == 0) {
    $sql = "SELECT questions.q_id, leadin, leadin_plain, q_type, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_date, locked, parts FROM (questions, keywords_question) LEFT JOIN question_exclude ON questions.q_id=question_exclude.q_id WHERE questions.q_id=keywords_question.q_id AND keywords_question.keywordID IN ($keyword_ids) AND ownerID=? AND status != 'retired' AND deleted IS NULL ORDER BY $order $direction, questions.q_id";
  } else {

    $sql = "SELECT
              questions.q_id, leadin, leadin_plain, q_type, DATE_FORMAT(last_edited,' {$configObject->get('cfg_short_date')}') AS display_date, locked, parts
            FROM
              (questions)
            LEFT JOIN
              question_exclude ON questions.q_id=question_exclude.q_id
            WHERE
              questions.q_id IN (SELECT q_id from keywords_question WHERE keywords_question.keywordID IN ($keyword_ids))  AND
              (ownerID=? OR questions.q_id IN (SELECT q_id from questions_modules where idMod IN (" . implode(',', array_keys($teams)) . "))) AND
              status != 'retired' AND deleted IS NULL
            ORDER BY
              $order $direction, questions.q_id";
  }

  if ($order == 'leadin') $order = 'leadin_plain';
  if ($order == 'q_type') $order = 'CAST(q_type AS CHAR)';
  if ($order == 'created') $order = 'CAST(created AS DATE)';

  $old_id = '';
  $result = $mysqli->prepare($sql);
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->store_result();
  $result->bind_result($q_id, $leadin, $leadin_plain, $q_type, $display_date, $locked, $parts);
  while($result->fetch()) {
    echo "<tr><td style=\"width:20px\">";
    if ($locked != '') echo '<img src="../../artwork/small_padlock.png" width="16" height="16" alt="Locked" />';
    echo "</td><td style=\"width:25px\"><input onclick=\"parent.top.controls.checkStatus(this)\" type=\"checkbox\" id=\"$q_id\" name=\"$q_id\" value=\"$q_id\" /></td>";
    if ($parts == '') {
      echo '<td onclick="Qpreview(' . $q_id . ')">';
    } else {
      echo '<td style="color:red; text-decoration:line-through" onclick="Qpreview(' . $q_id . ')">';
    }
    $leadin = QuestionUtils::clean_leadin($leadin);
    echo $leadin . "</td><td><nobr>" . fullQuestionType($q_type, $string) . "</nobr></td><td style=\"padding-left:5px; padding-right:2px\">$display_date</td></tr>\n";
  }
  $result->close();

?>
</table>
</form>

<?php
echo "<form name=\"keywordsform\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n";
$i = 0;
foreach ($_POST as $key=>$value) {
  if ($key != 'submit' and $key != 'order' and $key != 'direction' and $key != 'keyword_no') {
    echo "<input type=\"hidden\" name=\"keyword{$i}\" value=\"$value\" />\n";
    $i++;
  }
}
$key_no = (isset($_POST['keyword_no'])) ? $_POST['keyword_no'] : 0;
?>
<input type="hidden" name="keyword_no" id="keyword_no" value="<?php echo $key_no ?>" />
<input type="hidden" name="order" id="order" value="" />
<input type="hidden" name="direction" id="direction" value="" />
</form>
</body>
</html>