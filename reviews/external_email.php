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
require_once '../include/errors.php';
require_once '../config/external_email_msg.php';

$paperID = check_var('paperID', 'GET', true, false, true);
$mode = check_var('mode', 'GET', true, false, true);
$externalID = check_var('externalID', 'GET', true, false, true);
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$local_time = new DateTimeZone($configObject->get('cfg_timezone'));

if (is_null($properties->get_external_review_deadline())) {
  $notice->display_notice_and_exit($mysqli, $string['error'], $string['noexaminers'], $string['noexaminers'], '/artwork/square_exclamation_48.png', '#C00000', true, true);
}

$external_review_deadline = DateTime::createFromFormat('Y-m-d', $properties->get_external_review_deadline(), $local_time);
$external_review_deadline->setTimezone($local_time);

$display_deadline = $external_review_deadline->format('l jS M Y');
      
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['emailtemplate'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style>
    body {font-size: 90%}
    .email {width:300px; color:#316ac5}
  </style>
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config_externals_email.js"></script>
  <script>
    $(function () {
      
      $('#back').click(function (){
        window.location.href = 'pick_external.php?paperID=<?php echo $paperID ?>&mode=<?php echo $mode ?>&module=<?php echo $_GET['module'] ?>';
      });
      
    });
  </script>
</head>

<body>
<?php
  $external_details = UserUtils::get_user_details($externalID, $mysqli);
  
  $to = $external_details['email'];

  if ($mode == 0) {
    $message = $string['message0'];
    $subject = sprintf($string['subject_msg0'], $configObject->get('cfg_company'));
  } elseif ($mode == 1) {
    $message = $string['message1'];    
    $subject = sprintf($string['subject_msg1'], $configObject->get('cfg_company'));
  } else {
    $message = $string['message2'];
    $subject = sprintf($string['subject_msg2'], $configObject->get('cfg_company'));
  }
  $message = str_replace('$users_name', $userObject->get_first_first_name(), $message);
  $message = str_replace('$support_email', $support_email, $message);
  $message = str_replace('$rogo_url', $url, $message);
  $message = str_replace('$deadline', $display_deadline, $message);
  $message = str_replace('$paper_title', $properties->get_paper_title(), $message);
  $message = str_replace('$external_surname', $external_details['surname'], $message);
  $message = str_replace('$external_first_name', $external_details['first_name'], $message);
  $message = str_replace('$external_title', $external_details['title'], $message);
  $message = str_replace('$logo_path', $logo_path, $message);
  $message = str_replace('$cfg_company', $configObject->get('cfg_company'), $message);
    
  require '../include/toprightmenu.inc';
	echo draw_toprightmenu();
?>
  <div class="head_title" style="font-size:90%">
    <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a>
    <?php
    if (isset($_GET['module']) and $_GET['module'] != '') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
    }    
    echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../paper/details.php?paperID=' . $paperID . '&module=' . $_GET['module'] . '">' . $properties->get_paper_title() . '</a>';
    ?>
    </div>
    <div class="page_title"><?php echo $string['emailtemplate'] ?></div>
  </div>
  
  <br />
<?php
if (isset($_POST['submit'])) {
  $to_list = explode(';', $_POST['toaddress']);
  
  foreach ($to_list as $individual_to) {
    $to = trim($individual_to);
    $subject = trim($_POST['subject']);
    $message = "<html>\n<head><style>\nbody {margin:20px; font-family:Arial,sans-serif; line-height:160%; text-align:justify; color:#3F3F3F; font-size:90%}\na {color:#316ac5}\n</style>\n</head>\n<body>\n" . $_POST['message'] . "</body></html>\n";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=" . $configObject->get('cfg_page_charset') . "\r\n";
    $headers .= 'From: ' . $userObject->get_email() . "\r\n";
    if (trim($_POST['ccaddress']) != '') {
      $headers .= 'CC: ' . trim($_POST['ccaddress']) . "\r\n";
    }
    if (trim($_POST['bccaddress']) != '') {
      $headers .= 'BCC: ' . trim($_POST['ccaddress']) . "\r\n";
    }

    mail($to, $subject, $message, $headers);
  }
  echo "<p>" . $string['emailsent'] . "</p>";
  echo "<p><input type=\"button\" value=\"" . $string['back'] . "\" name=\"back\" id=\"back\" class=\"ok\" /></p>";
} else {
?>
  <form name="templateform" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>" autocomplete="off">

    <table cellpadding="1" cellspacing="0" border="0" style="text-align:left; margin-left:auto; margin-right:auto">
    <tr>
    <td><?php echo $string['to'] ?></td>
    <td><input type="text" size="70" name="toaddress" value="<?php echo $to ?>" class="email" /></td>
    <td style="text-align:right" rowspan="4" valign="top"><img src="../artwork/stamp.png" width="89" height="93" alt="stamp" /></td>
    </tr>
    <tr>
    <td><?php echo $string['cc'] ?></td>
    <td><input type="text" size="70" name="ccaddress" value="<?php echo $userObject->get_email() ?>" class="email" /></td>
    </tr>
    <tr>
    <td><?php echo $string['bcc'] ?></td><td><input type="text" size="70" name="bccaddress" value="" class="email" /></td>
    </tr>
    <tr>
    <td><?php echo $string['subject'] ?></td><td><input type="text" size="70" name="subject" value="<?php echo $subject ?>" /></td>
    </tr>
    <tr>
    <td colspan="3"><textarea class="mceEditor" id="message" name="message" style="width:780px; height:450px"><?php echo htmlspecialchars($message, ENT_NOQUOTES) ?></textarea></p>
    </tr>

    <tr>
    <td colspan="3" style="text-align: center">
    <input type="submit" class="ok" name="submit" value="<?php echo $string['email'] ?>" /><input type="button" name="cancel" id="back" class="cancel" value="<?php echo $string['cancel'] ?>" /></td>
    </tr>
    </table>

  </form>
<?php
}
$mysqli->close();
?>
</body>
</html>