<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Display system errors
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require '../include/errors.php';
$errorID = check_var('errorID', 'GET', true, false, true);
$row_no = 0;
$result = $mysqli->prepare("SELECT sys_errors.id, auth_user, title, initials, surname, DATE_FORMAT(occurred,'%d/%m/%y&nbsp;%H:%i:%s'), userID, errtype, errstr, errfile, errline, php_self, query_string, request_method, DATE_FORMAT(fixed,'%d/%m/%y&nbsp;%H:%i:%s'), paperID, post_data, variables, backtrace FROM sys_errors LEFT JOIN users ON sys_errors.userID=users.id WHERE sys_errors.id=?");
$result->bind_param('i', $errorID);
$result->execute();
$result->store_result();
$result->bind_result($error_id, $auth_user, $title, $initials, $surname, $occurred, $uID, $errtype, $errstr, $errfile, $errline, $php_self, $query_string, $request_method, $fixed, $paperID, $post_data, $variables, $backtrace);
$row_no = $result->num_rows;
$result->fetch();
$result->close();
if ($row_no == 0) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}


$result = $mysqli->prepare('SELECT id FROM sys_errors WHERE errstr = ? AND errfile = ? AND errline = ?');
$result->bind_param('ssi', $errstr, $errfile, $errline);
$result->execute();
$result->store_result();
$result->bind_result($id);
$similar_errors = $result->num_rows();
$result->close();
$variables = unserialize(base64_decode($variables));

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php printf($string['errordetails'], $error_id); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/error.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script  src="../js/errorinit.min.js"></script>

</head>

<body>

<div style="overflow:auto; height:745px">
<table>
<tr><td class="f"><?php echo $string['date'] ?></td><td><?php echo $occurred; ?></td></tr>
<tr><td class="f"><?php echo $string['staff'] ?></td><td><?php echo $title . ' ' . $initials . ' ' . $surname ?></td></tr>
<tr><td class="f"><?php echo $string['username'] ?></td><td><?php echo $auth_user ?></td></tr>
<tr><td class="f"><?php echo $string['type'] ?></td><td><?php echo $errtype ?></td></tr>
<tr><td class="f"><?php echo $string['description'] ?></td><td><?php echo $errstr ?></td></tr>
<tr><td class="f"><?php echo $string['file'] ?></td><td><?php echo $errfile . ' (line ' . $errline . ')' ?></td></tr>
<tr><td class="f"><?php echo $string['paperid'] ?></td><td><?php echo $paperID ?></td></tr>
<tr><td class="f"><?php echo $string['querystring'] ?></td><td><?php echo $query_string ?></td></tr>
<tr><td class="f"><?php echo $string['post'] ?></td><td><?php echo $post_data ?></td></tr>
<tr><td class="f"><?php echo $string['phpself'] ?></td><td><?php echo $php_self ?></td></tr>
<tr><td class="f"><?php echo $string['requestmethod'] ?></td><td><?php echo $request_method ?></td></tr>
<tr><td class="f" style="vertical-align: top" ><?php echo $string['occurrenceoferror'] ?></td><td><?php echo $similar_errors ?></td></tr>
<tr><td class="f"><?php echo $string['datefixed'] ?></td><td><?php echo ($fixed == '' ? 'n/a' : $fixed); ?></td></tr>
<tr><td class="f" style="vertical-align: top"><?php echo $string['backtrace'] ?></td><td><?php echo $backtrace ?></td></tr>
<tr><td class="f" style="vertical-align: top"><?php echo $string['variables'] ?></td><td><?php if (isset($variables) and !($variables === '' or $variables === false)) {
    ini_set('xdebug.var_display_max_data', '-1');
    var_dump($variables);
                                              } ?></td></tr>
</table>
</div>
<br />
<form id="myform" action="" method="post" name="myform" autocomplete="off">
<div style="text-align:center">
    <input type="button" name="close" id="cancel" value="<?php echo $string['close']; ?>" style="width:100px" class="cancel" />&nbsp;&nbsp;
    <input type="hidden" name="errorID" id="errorID" value="<?php echo $_GET['errorID']; ?>" />
<?php
if ($fixed == '') {
    echo '<input type="submit" name="submit" value="' . $string['fixed'] . '" style="width:100px" />';
} else {
    echo '<input type="submit" name="submit" value="' . $string['fixed'] . '" style="width:100px" disabled />';
}
?>
</div>
</form>

<?php
$mysqli->close();
?>

</body>
</html>
