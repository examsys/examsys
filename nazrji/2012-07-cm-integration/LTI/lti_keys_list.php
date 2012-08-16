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
 * Listing of IMS LTI Keys.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>"/>
  <title><?php echo $string['ltikeys'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css"/>
  <link rel="stylesheet" type="text/css" href="../css/header.css"/>
  <style type="text/css">
    .mid {
      padding-left: 30px
    }

    .l {
      cursor: pointer
    }

    .no {
      text-align: right;
      padding-right: 10px
    }

    .deleted {
      color: red;
      text-decoration: line-through
    }
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script language="javascript">
    $(function () {
      $('body').click(deselSch);
    });

    function selSch(divID, evt) {
      tmp_ID = document.myform.divID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }

      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';
      document.myform.divID.value = divID;

      document.getElementById(divID).style.backgroundColor = '#B3C8E8';
      evt.cancelBubble = true;
    }

    function deselSch() {
      tmp_ID = document.myform.divID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
      document.getElementById('menu1b').style.display = 'none';
      document.getElementById('menu1a').style.display = 'block';
    }

    function lon(lineID) {
      if (lineID != document.myform.divID.value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function loff(lineID) {
      if (lineID != document.myform.divID.value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }

    function edit(divID) {
      document.location.href = './edit_LTIkeys.php?LTIkeysid=' + divID;
    }
  </script>
</head>

<body>
<?php
require '../include/lti_keys_options.inc';
?>
<div id="content" class="content">

  <table class="header">
    <tr>
      <th colspan="3">
        <div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;
          &nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-"/>&nbsp;&nbsp;<a
            href="../admin/index.php"><?php echo $string['administrativetools']; ?></a></div>
        <div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['ltikeys']; ?></th>
      <th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#"
                                                                                              onclick="launchHelp(233); return false;"><img
        src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>"
        border="0"/></a></th>
    </tr>
    <tr>
      <th>
        <div class="mid"><?php echo $string['name']; ?>&nbsp;</div>
      </th>
      <th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line"
               border="0"/>&nbsp;<?php echo $string['oauth_consume_key']; ?>&nbsp;</th>
      <th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line"
               border="0"/>&nbsp;<?php echo $string['oauth_secret']; ?>&nbsp;</th>
      <th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line"
               border="0"/>&nbsp;<?php echo $string['oauth_context_id']; ?>&nbsp;</th>
    </tr>
    <tr>
      <th colspan="4" class="bevel"></th>
    </tr>
    <?php
    $id = 0;
    $result = $mysqli->prepare("SELECT id, oauth_consumer_key, secret, `name`, context_id FROM lti_keys WHERE `deleted` IS NULL ORDER BY name");
    $result->execute();
    $result->bind_result($ltis['id'], $ltis['oauth_consumer_key'], $ltis['secret'], $ltis['name'], $ltis['context_id']);
    while ($result->fetch()) {
      $id = $ltis['id'];
      echo "<tr id=\"$id\" onclick=\"selSch($id,event)\" ondblclick=\"edit('$id')\" onmouseover=\"lon($id)\" onmouseout=\"loff($id)\" class=\"l\"><td><div class=\"mid\">" . $ltis['name'] . "</div></td><td>" . $ltis['oauth_consumer_key'] . "</td><td>" . $ltis['secret'] . "</td><td>" . $ltis['context_id'] . "</div></td></tr>\n";
    }
    $result->close();
    $mysqli->close();

    ?>
  </table>
</div>

</body>
</html>