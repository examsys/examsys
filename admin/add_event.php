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

require '../include/sysadmin_auth.inc';

if (isset($_GET['default'])) {
  $parts = explode('_', substr($_GET['default'], 1));
  $day = $parts[0];
  if ($day < 10) {
    $day = '0' . $day;
  }
  $month = $parts[1];
  if ($month < 10) {
    $month = '0' . $month;
  }
  $year = $parts[2];
  $default_date = date($year . $month . $day . "H00");
} else {
  $default_date = date('YmdH00');
}
?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['addevent'] ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/event.css" />
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<?php
$texteditorplugin = \plugins\plugins_texteditor::get_editor();
$texteditorplugin->display_header();
$texteditorplugin->get_javascript_config(\plugins\plugins_texteditor::ANNOUNCEMENTS);

if (isset($_POST['submit'])) {
  $title = trim($_POST['title']);
  $message = trim($_POST['message']);
  $thedate = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'];
  $duration = $_POST['duration'];
  $bgcolor = '#' . $_POST['color'];

  $result = $mysqli->prepare("INSERT INTO extra_cal_dates VALUES (NULL, ?, ?, ?, ?, ?, NULL)");
  $result->bind_param('sssis', $title, $message, $thedate, $duration, $bgcolor);
  $result->execute();  
  $result->close();
?>
  <script>
    $(function () {
      window.opener.location.reload();
      window.close();
    });
  </script>
  </head>
  <body>
    
  </body>
  </html>
<?php
  exit();
}

?>
  <script>
    $(function () {
      $('.swatch').click(function() {
        current = $('#color').val();
        $('#' + current).css('border-color', '#F1F5FB');

        newvalue = $(this).attr('id');
        $('#' + newvalue).css('border-color', '#FFBD69');
        $('#color').val(newvalue)
      });
      
      $('#cancel').click(function() {
        window.close();
      });
    });
  </script>
  </head>
<body>
  <div class="dialog_header"><?php echo $string['addevent'] ?></div>
  <form name="theform" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" style="padding:10px" autocomplete="off">
    
  <table style="width:99%">
    <tr>
      <td><?php echo $string['title'] ?></td>
      <td><input type="text" style="width:99.5%" name="title" required /></td>
    </tr>
    <tr>
      <td><?php echo $string['message'] ?></td>
      <td><?php $texteditorplugin->get_textarea('message', 'message', '', plugins\plugins_texteditor::TYPE_STANDARD); ?></td>
    </tr>
    <tr>
      <td><?php echo $string['date'] ?></td>
      <td><?php echo date_utils::timedate_select('f', $default_date, false, date('Y'), date('Y')+2, $string) ?></td>
    </tr>
    <tr>
      <td><?php echo $string['duration'] ?></td>
      <td>
        <select name="duration">
          <option value="5">5 <?php echo $string['mins'] ?></option>
          <option value="10">10 <?php echo $string['mins'] ?></option>
          <option value="20">20 <?php echo $string['mins'] ?></option>
          <option value="30">30 <?php echo $string['mins'] ?></option>
          <option value="60" selected>60 <?php echo $string['mins'] ?></option>
          <option value="90">1.5 <?php echo $string['hours'] ?></option>
          <option value="120">2 <?php echo $string['hours'] ?></option>
          <option value="180">3 <?php echo $string['hours'] ?></option>
          <option value="240">4 <?php echo $string['hours'] ?></option>
          <option value="300">5 <?php echo $string['hours'] ?></option>
          <option value="360">6 <?php echo $string['hours'] ?></option>
          <option value="420">7 <?php echo $string['hours'] ?></option>
          <option value="480">8 <?php echo $string['hours'] ?></option>
          <option value="540">9 <?php echo $string['hours'] ?></option>
          <option value="600">10 <?php echo $string['hours'] ?></option>
          <option value="660">11 <?php echo $string['hours'] ?></option>
          <option value="720">12 <?php echo $string['hours'] ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <td><?php echo $string['colour'] ?></td>
      <td>
        <div class="swatch" id="3A3838" style="background-color:#3A3838"></div>
        <div class="swatch" id="323F4F" style="background-color:#323F4F"></div>
        <div class="swatch" id="2E75B5" style="background-color:#2E75B5"></div>
        <div class="swatch" id="C55A11" style="background-color:#C55A11"></div>
        <div class="swatch" id="7B7B7B" style="background-color:#7B7B7B"></div>
        <div class="swatch" id="BF9000" style="background-color:#BF9000"></div>
        <div class="swatch" id="2F5496" style="background-color:#2F5496; border-color:#FFBD69"></div>
        <div class="swatch" id="538135" style="background-color:#538135"></div>
        <input type="hidden" name="color" id="color" size="10" value="2F5496" />
      </td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:20px; text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok'] ?>" class="ok" /><input type="button" name="cancel" id="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" /></td>
    </tr>
  </table>
    
  </form>
</body>
</html>