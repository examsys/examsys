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
require '../include/errors.php';

$q_id = check_var('q_id', 'GET', true, false, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['questioninformation'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:80%}
    th {background-color:#295AAD; color:white; text-align:left; font-weight:normal}
    td {vertical-align:top}
    .screen {font-size:90%; color:#808080}
    .num {text-align:right; padding-right:6px}
  </style>
  
  <script>
    function loadPaper(paperID) {
      window.opener.location = "../paper/details.php?paperID=" + paperID;
      window.close();
    }
    
    function loadModule(moduleID) {
      window.opener.location = "../module/index.php?module=" + moduleID;
      window.close();
    }
    
    function openLongitudinal(questionID) {
      window.open("longitudinal_performance.php?q_id=" + questionID);
    }
  </script>
</head>

<body>

<?php
  echo question_info::full_question_information($q_id, $mysqli, $userObject, $string, $notice);
?>


<div style="text-align:center; padding-top:5px">
<form autocomplete="off">
<input type="button" style="width:120px" name="ok" onclick="javascript:window.close();" value="<?php echo $string['close']; ?>" />
</form>
</div>
</body>
</html>
