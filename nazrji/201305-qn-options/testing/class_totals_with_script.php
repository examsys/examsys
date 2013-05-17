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
 * This script is designed to compare marks between the Class Totals report and students' actual exam scripts (finish.php).
 * It works by:
 *   1. Get summative exam papers in the require date range.
 *   2. For each paper call class_totals.php and parse for student IDs and marks.
 *   3. For each student call finish.php and compare the mark.
 *   4. Echo errors for any which do not match.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';

$papers = array();
$result = $mysqli->prepare("SELECT property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y') FROM properties WHERE paper_type = '2' AND start_date < NOW() AND deleted IS NULL ORDER BY property_id");
$result->execute();
$result->bind_result($paperID, $title, $display_start_date);
while ($result->fetch()) {
  $papers[] = array('paperID'=>$paperID, 'title'=>$title, 'display_start_date'=>$display_start_date);
}
$result->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

  <title>Testing: Class Totals</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css"/>
  <link rel="stylesheet" type="text/css" href="../css/header.css"/>
  <style type="text/css">
    body {
      font-size: 90%;
    }

    dt {
      font-weight: bold;
    }
    dd {
      padding-bottom: 16px;
    }
    input[type=text] {
      width: 400px;
    }
    .error {
      color: red;
      font-weight: bold;
    }
  </style>

  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script src="../js/jquery.validate.min.js" type="text/javascript"></script>
  <script type="text/javascript">
    $(function () {
      $('#results').hide();
      $('#start').click(function (e) {
        e.preventDefault();
        $('#the_form').validate();
        if ($('#the_form').valid()) {
          var period = $('#period').val();
          var paper = $('#paper').val();
          var passwd = $('#passwd').val();
          var username = $('#username').val();
          $.post('class_totals_with_script_ajax.php',
                  {
                    period:period,
                    paper:paper,
                    username:username,
                    passwd:passwd
                  });
          $('#results').show();
          $('#form').hide();
        }
      });
      $('#status').click(function() {
        var period = $('#period').val();
        var paper = $('#paper').val();
        window.location.href='class_totals_with_script_status.php?period=' + period + '&paper=' + paper;
      });
    })
  </script>
</head>
<body>
<h1>Class Totals Internal Analysis</h1>


<div id="form">
  <form id="the_form" action="./" method="post">
  <dl class="form">
    <dt><label for="period">Select time period:</label></dt>
    <dd>
      <select id="period" name="period">
        <option value="">-- All papers --</option>
        <option value="week">Last week</option>
        <option value="month">Last month</option>
        <option value="year">Last year</option>
        <option value="2year">Last 2 years</option>
        <option value="3year">Last 3 years</option>
      </select>
    </dd>
    <dt><label for="paper">OR Select a paper</label></dt>
    <dd>
      <select id="paper">
        <option value="">-- All papers --</option>
<?php
foreach ($papers as $paper):
?>
        <option value="<?php echo $paper['paperID'] ?>"><?php echo '[' . $paper['paperID'] . '] ' . $paper['title'] ?> (<?php echo $paper['display_start_date'] ?>)</option>
<?php
endforeach;
?>
      </select>
    </dd>
    <dt>Username:</dt>
    <dd><input type="text" id="username" name="username" class="required" /></dd>
    <dt>Password:</dt>
    <dd><input type="password" id="passwd" name="passwd" class="required" /></dd>
  </dl>
    <button id="start">Start Analysis</button>
  </form>
</div>
<div id="results">
  <p>Analysis started.</p>
  <button id="status">View the current status</button>
</div>
</body>
</html>
