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
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;: <?php echo $string['computerlabs'] ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .foldername {float:left; width:380px; height:60px; padding-left:22px; font-size:90%}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function selLab(labID, labNo, evt) {
      tmp_ID = $('#oldLabNo').val();
      if (tmp_ID != '') {
        $('#' + tmp_ID).css('background-color', 'white');
        $('#' + tmp_ID).css('color', 'black');
      }
      $('#' + labNo).css('background-color', '#316AC5');
      $('#' + labNo).css('color', 'white');

      $('#menu1a').hide();
      $('#menu1b').show();

      $('#labID').val(labID);
      $('#labNo').val(labNo);
      $('#oldLabNo').val(labNo);

      evt.cancelBubble = true;
    }

    function deselLab() {
      tmp_ID = $('#oldLabNo').val();
      if (tmp_ID != '') {
        $('#' + tmp_ID).css('background-color', 'white');
        $('#' + tmp_ID).css('color', 'black');
      }
      $('#menu1a').show();
      $('#menu1b').hide();

    }
		
    $(function () {
		  $('html').click(function() {
			  deselLab();
      });
		});
  </script>
</head>

<body>
<?php
  require '../include/lab_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(231);
?>
<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['computerlabs'] ?></div>
</div>

<?php
$labs = array();
$campus_sizes = array();

$lab_data = $mysqli->prepare("SELECT labs.id, labs.name, count(address) AS pc_number, campus.name, labs.building, client_identifiers.low_bandwidth
  FROM (client_identifiers, labs, campus)
  WHERE client_identifiers.lab = labs.id AND labs.campus = campus.id
  GROUP BY labs.id, labs.name, campus.name, labs.building, client_identifiers.low_bandwidth
  ORDER BY campus.name, labs.name");
$lab_data->execute();
$lab_data->store_result();
$lab_data->bind_result($id, $name, $pc_number, $campus, $building, $low_bandwidth);
while ($lab_data->fetch()) {
  $labs[] = array('id'=>$id, 'name'=>$name, 'pc_number'=>$pc_number, 'campus'=>$campus, 'building'=>$building, 'low_bandwidth'=>$low_bandwidth);
}
$lab_data->close();

$old_campus = '';
$lab_no = 0;
if (count($labs) > 0) {
  foreach($labs as $lab) {
    if (isset($campus_sizes[$lab['campus']])) {
      $campus_sizes[$lab['campus']]++;
    } else {
      $campus_sizes[$lab['campus']] = 1;
    }
  }

  foreach($labs as $lab) {
    if ($old_campus != $lab['campus']) {
      echo "<table class=\"subsect\" style=\"width:99%\"><tr><td><nobr>" . $lab['campus'] . " (" . $campus_sizes[$lab['campus']] . ")</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n";
    }
    echo '<div class="foldername">';
    echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:66px; cursor:pointer" align="center">';
    echo "  <img onclick=\"selLab('" . $lab['id'] . "','lab$lab_no',event)\" ondblclick=\"viewDetails('" . $lab['id'] . "')\" src=\"../artwork/computer_lab_48.png\" width=\"48\" height=\"48\" alt=\"" . $lab['name'] . "\" /><td>\n";
    echo "  <td style=\"width:290px; cursor:pointer\"><span id=\"lab$lab_no\" onclick=\"selLab('" . $lab['id'] . "', 'lab$lab_no', event)\" ondblclick=\"viewDetails('" . $lab['id'] . "')\">" . $lab['name'] . "</span><br />";
    echo '  <span style="color:#808080">' . $lab['pc_number'];
    if ($lab['pc_number'] == 1) {
      echo ' ' . $string['machine'];
    } else {
      echo ' '. $string['machines'];
    }
    if ($lab['building'] != '') echo ', ' . $lab['building'];
    echo '</span>';
    if ($lab['low_bandwidth'] == 1) {
      echo '<br /><span style="background-color:#C00000; color:white">&nbsp;' . $string['lowbandwidth'] . '&nbsp;</span>';
    }
    echo '</td></tr></table>';
    echo "</div>\n";
    $old_campus = $lab['campus'];
    $lab_no++;
  }
}

$mysqli->close();
?>
</body>
</html>