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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014. The University of Nottingham
 * @package
 */

require '../../include/staff_auth.inc';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['bykeyword']; ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    body {margin:2px; font-size:80%}
    h1 {font-size:100%; background-color:#DDE7EE; color:#00156E; font-weight:bold; border-bottom:1px solid #C5C5C5; margin-bottom:0px; padding-bottom:0px}
    a:link {color:black}
    a:visited {color:black}
    a:hover {color:black}
     input[type="checkbox"] {margin-right:6px}
    .k {padding-left:2px}
  </style>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../../js/require.js'></script>
  <script src='../../js/main.min.js'></script>
</head>

<body>
<form id="myform" method="post" name="myform" action="add_questions_list.php?type=keyword" target="keywordlist" autocomplete="off">
<?php
  $keyword_no = 0;
$old_moduleID = '';
$stmt = $mysqli->prepare("SELECT moduleid, keyword, keywords_user.id FROM keywords_user, modules WHERE keywords_user.userID = modules.id AND moduleid IN ('" . implode("','", $userObject->get_staff_modules()) . "') ORDER BY moduleid, keyword");
$stmt->execute();
$stmt->bind_result($moduleID, $keyword, $keywordID);
while ($stmt->fetch()) {
    if ($old_moduleID != $moduleID) {
        echo "<table border=\"0\" style=\"padding-top:5px; padding-bottom:2px; width:100%; color:#1E3287; white-space:nowrap\"><tr><td>$moduleID</td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    }
    echo '<div class="k"><label><input class="keyword" type="checkbox" name="keyword' . $keyword_no . "\" value=\"$keywordID\"/>$keyword</label></div>\n";
    $keyword_no++;
    $old_moduleID = $moduleID;
}
  $stmt->close();
echo '<table border="0" style="padding-top:3px; padding-bottom:2px; width:100%; color:#1E3287; white-space:nowrap"><tr><td>' . $string['mykeywords'] . " </td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
$stmt = $mysqli->prepare('SELECT id, keyword FROM keywords_user WHERE userID = ? ORDER BY keyword');
$stmt->bind_param('i', $userObject->get_user_ID());
$stmt->execute();
$stmt->bind_result($keywordID, $keyword);
while ($stmt->fetch()) {
    echo '<div class="k"><label><input class="keyword" type="checkbox" name="keyword' . $keyword_no . "\" value=\"$keywordID\" />$keyword</label></div>\n";
    $keyword_no++;
}
  $stmt->close();
echo "<input type=\"hidden\" name=\"keyword_no\" value=\"$keyword_no\" />";
?>
</form>
<script src="../../js/addkeyowrdsinit.min.js"></script>
</body>
</html>
