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

require '../include/staff_auth.inc';
require '../include/errors.php';
require_once '../include/mapping.inc';
require_once '../classes/questionbank.class.php';

$q_id = check_var('q_id', 'GET', true, false, true);

if (isset($_GET['type']) and $_GET['type'] == 'objective') {
  $module_code = module_utils::get_moduleid_from_id($_GET['module'], $mysqli);
  $qbank = new QuestionBank($_GET['module'], $module_code, $string, $notice, $mysqli);
  $map_outcomes = true;
} else {
  $map_outcomes = false;
}

if (!isset($_POST['submit'])) {
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['linktopaper']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {background-color:#F1F5FB}
    td {font-size:80%}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    function checkForm() {
      var checkOption = $('input:radio[name=property_id]:checked').val();

      if (typeof checkOption == 'undefined') {
        alert("Please select which paper you would like to add the question to.");
        return false;
      }
      $('#working').show();
    }

    function resizeList() {
      winH = $(window).height() - 150;

      $('#paperlist').css('height', winH + 'px');
    }

    $(function () {
		  resizeList();
			
			$(window).resize(function() {
				resizeList();
			});
      
      $('#cancel').click(function() {
        window.close();
      });

<?php
  if ($map_outcomes) {
?>
      $('#outcomes').val(window.opener.getSelectedOutcomes());
<?php
  }
?>
		});
	</script>
</head>

<body>
<?php
  echo "<form method=\"post\" name=\"theForm\" onsubmit=\"return checkForm()\" action=\"" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "\" autocomplete=\"off\">\n";
?>  

  <table cellpadding="6" cellspacing="0" border="0" width="100%">
  <tr><td style="width:32px; background-color:white; border-bottom:1px solid #CCD9EA"><img src="../artwork/link_to_paper.png" width="32" height="32" alt="<?php echo $string['linktopaper']; ?>" /></td><td class="midblue_header" style="background-color:white; font-size:150%; font-weight:bold; border-bottom:1px solid #CCD9EA"><?php echo $string['linktopaper']; ?></td></tr>
  </table>

  <p style="margin:4px; text-align:justify; font-size:90%"><img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="<?php echo $string['warning']; ?>" /> <?php echo $string['msg1']; ?></p>

  <div style="height:200px; overflow:auto; background-color:white; border:1px solid #CCD9EA; margin:4px" id="paperlist">
  <table cellpadding="0" cellspacing="1" border="0">
<?php
  $result = $mysqli->prepare("SELECT DISTINCT properties.property_id, paper_title, start_date, end_date, paper_type FROM properties, properties_modules WHERE properties.property_id = properties_modules.property_id AND (paper_ownerID = ? OR idMod IN ('" . implode("','",array_keys($staff_modules)) . "')) AND deleted IS NULL ORDER BY paper_title");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($property_id, $paper_title, $start_date, $end_date, $paper_type);
  while ($result->fetch()) {
    if (($paper_type == '2' or $paper_type == '4') and $end_date != '' and date("Y-m-d H:i:s") > $end_date) {
      //echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_padlock.png\" width=\"18\" height=\"18\" alt=\"" . $string['warning'] . "\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } elseif ($start_date < date("Y-m-d H:i:s") and $end_date > date("Y-m-d H:i:s")) {
      echo "<tr><td style=\"width:20px\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"" . $string['warning'] . "\" /></td><td><input type=\"radio\" name=\"property_id\" value=\"$paper_title\" disabled><span style=\"color:#808080\">$paper_title</span></td></tr>\n";
    } else {
      echo "<tr><td style=\"width:20px\">&nbsp;</td><td><input type=\"radio\" name=\"property_id\" value=\"$property_id\" id=\"$property_id\"><label for=\"$property_id\">$paper_title</label></td></tr>\n";
    }
  }
  $result->close();
  
  echo "</table>\n</div>";
  
  echo '<input type="hidden" id="outcomes" name="outcomes" value="" />';
  echo "<div style=\"text-align:center; padding-top:4px;\"><img src=\"../artwork/working.gif\" id=\"working\" width=\"16\" height=\"16\" alt=\"Working\" style=\"display: none\" /> <input type=\"submit\" class=\"ok\" name=\"submit\" value=\"" . $string['addtopaper'] . "\" /><input type=\"button\" class=\"cancel\" name=\"cancel\" id=\"cancel\" value=\"" . $string['cancel'] . "\" /></div>\n</form>\n";
} else {
  $property_id = $_POST['property_id'];
  $properties = PaperProperties::get_paper_properties_by_id($property_id, $mysqli, $string);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $string['linktopaper']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style>
    body {font-size:90%; background-color:EEECDC; text-align:center}
  </style>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      $("#close").click(function() {
        window.close();
      });
      
      $("#gotopaper").click(function() {
        window.opener.location.href = '../paper/details.php?paperID=<?php echo $property_id ?>';
        window.close();
      });
    });
  </script>
    
</head>

<body>
<?php

  $q_id = $_GET['q_id'];
  
  if ($map_outcomes) {
    $yearutils = new yearutils($mysqli);
    $vle_api_cache = array();
    $vle_api_data = MappingUtils::get_vle_api($_GET['module'], $yearutils->get_current_session(), $vle_api_cache, $mysqli);
  }

  // Get the maximum display position for an existing paper.
  $result = $mysqli->prepare("SELECT MAX(display_pos), MAX(screen) FROM papers WHERE paper = ?");
  $result->bind_param('i', $property_id);
  $result->execute();
  $result->bind_result($display_pos, $screen);
  $result->fetch();
  $result->close();
  if ($screen == '') $screen = 1;
  $display_pos++;                     // Add one to put new question right at the end.

  $q_IDs = explode(',', $q_id);
  for ($i=1; $i<count($q_IDs); $i++) {
    $map_guid = array();
    
    Paper_utils::add_question($property_id, $q_IDs[$i], $screen, $display_pos, $mysqli);

    $display_pos++;

    if ($map_outcomes) {
      // Make sure that paper is on the module we're copying from
      $paper_modules = $properties->get_modules();

      if (in_array($_GET['module'], array_keys($paper_modules))) {
        if (isset($_POST['outcomes']) and $_POST['outcomes'] != '') {
          $outcomes = json_decode($_POST['outcomes'], true);

          $mappings = $mysqli->prepare("SELECT question_id, obj_id FROM relationships WHERE question_id = ? AND idMod = ?");
          $mappings->bind_param('ii', $q_IDs[$i], $_GET['module']);
          $mappings->execute();
          $mappings->store_result();
          $mappings->bind_result($map_q_id, $obj_id);
          while ($mappings->fetch()) {
            if (isset($outcomes[$obj_id])) {
              $map_guid[$outcomes[$obj_id]] = true;
            }
          }
          $mappings->close();
        }
      } else {
        echo '<p>' . $string['papernotonmodule'] . '</p>';
      }
    }

    if (count($map_guid) > 0) {
      // Get the mappings for the module in the paper's academic year
      $calendar_year = $properties->get_calendar_year();
      $outcomes = $qbank->get_outcomes($calendar_year, $vle_api_data);
      
      foreach(array_keys($map_guid) as $guid) {
        // Get the IDs of the outcomes for the GUIDs we've been passed
        if (isset($outcomes[$guid])) {
          foreach($outcomes[$guid]['ids'] as $obj_id) {
            // Add new relationship records for the paper and question
            $sql = 'INSERT INTO relationships(idMod, paper_id, question_id, obj_id, calendar_year, vle_api, map_level) VALUES(?, ?, ?, ?, ?, ?, ?)';
            $addRel = $mysqli->prepare($sql);
            $addRel->bind_param('iiiissi', $_GET['module'], $property_id, $q_IDs[$i], $obj_id, $calendar_year, $vle_api_data['api'], $vle_api_data['level']);
            $addRel->execute();
            $addRel->close();
          }
        }
      }
    }
  }

  echo "<p>" . sprintf($string['success'], $properties->get_paper_title()) . "</p>\n";
  echo "<p><input type=\"button\" value=\"" . $string['close'] . "\" class=\"ok\" id=\"close\" /><input type=\"button\" value=\"" . $string['gotopaper'] . "\" class=\"ok\" id=\"gotopaper\" /></p>\n";
}
$mysqli->close();
?>
</body>
</html>