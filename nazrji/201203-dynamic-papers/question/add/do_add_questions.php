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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';

function clone_scalar($var) {
  return $var;
}

$close_window = true;

if ($_POST['questions_to_add'] != '') {
  $questions = explode(',', $_POST['questions_to_add']);
  if (isset($_GET['display_pos'])) {
    // Adding questions to paper
    $display_pos = $_GET['display_pos'];
    foreach ($questions as $item) {
      $result = $mysqli->prepare("INSERT INTO papers VALUES (NULL,?,?,?,?)");
      $result->bind_param('iiii', $_GET['paperID'], $item, $_POST['screen'], $display_pos);
      $result->execute();
      $result->close();
      $display_pos++;

      // Create a track changes record to say new question added.
      $tmp_paperID = intval($_GET['paperID']);
      $trackChange = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Alter Paper',?,$userID,'',?,NOW(),'Add Question')");
      $trackChange->bind_param('is', $tmp_paperID, $item);
      $trackChange->execute();
      $trackChange->close();
    }
    $paperID = (isset($_GET['paperID'])) ? $_GET['paperID'] : '';
    $type = (isset($_GET['type'])) ? $_GET['type'] : '';
    $scrOfY = (isset($_GET['scrOfY'])) ? $_GET['scrOfY'] : '';
    $module = (isset($_GET['module'])) ? $_GET['module'] : '';
    $folder = (isset($_GET['folder'])) ? $_GET['folder'] : '';
    $redirect_url = "../../paper/details.php?paperID=$paperID&type=$type&module=$module&folder=$folder&scrOfY=$scrOfY";
  } else {
    // Adding questions to dynamic paper

    $session = $_POST['session'];
    $module = $_POST['module'];
    $i = $j = 0;
    $do_insert = false;
    foreach ($questions as $question) {
      ${'tmp_question'.$i} = $question;

      if ($_POST['objectives'] != '') {
        $mapped_objs = explode(',', $_POST['objectives']);

        $ins_query = 'INSERT INTO relationships(module_id, question_id, obj_id, calendar_year) VALUES ';
        $params = array('bind_param', '');
        foreach ($mapped_objs as $objective) {
          $check = $mysqli->prepare("SELECT rel_id FROM relationships WHERE module_id=? AND question_id=? AND obj_id=? AND calendar_year=? AND paper_id IS NULL");
          $check->bind_param('siis', $module, $question, $objective, $session);
          $check->execute();
          $check->store_result();
          $check->fetch();
          if ($check->num_rows == 0) {
            $do_insert = true;
            // Build up insert statement
            ${'tmp_objective'.$j} = $objective;
            $ins_query .= "(?, ?, ?, ?),";
            $params[1] .= 'siis';
            $params = array_merge($params, array(&$module, &${'tmp_question'.$i}, &${'tmp_objective'.$j}, &$session));
            $j++;
          }
          $check->close();
          $redirect_url = "../../mapping/dynamic_papers_by_session.php?module=$module&session=$session";
        }
        if ($do_insert) {
          $ins_query = rtrim($ins_query, ',');
          $result = $mysqli->prepare($ins_query);
          $params[0] = $result;
          call_user_func_array('mysqli_stmt_bind_param', $params);
          $result->execute();
          $result->close();
        }
      } else {
        $redirect_url = "../../mapping/map_question.php?module=$module&questions={$_POST['questions_to_add']}&session=$session";
        $close_window = false;
      }
      $i++;
    }
  }
}
$mysqli->close();
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Add new Question</title>
  <script src="../../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script type="text/javascript">
<?php
if ($close_window) {
?>
    $(function () {
      top.window.opener.location.href='<?php echo $redirect_url ?>';
      top.window.close();
    });
<?php
} else {
?>
    $(function () {
      top.window.location.href='<?php echo $redirect_url ?>';
    });
<?php
}
?>
  </script>
</head>
<body>
</body>
</html>