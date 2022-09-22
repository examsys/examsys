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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>ExamSys: <?php echo $string['clearguestaccounts']; ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src='../js/clearguestusersinit.min.js'></script>
    <style type="text/css">
        th {
            background-color: #295AAD;
            color: white;
            text-align: left;
            font-weight: normal
        }

        .l {
            border-bottom: 1px solid #EEEEEE
        }

        .loff {
            border-bottom: 1px solid #EEEEEE;
            color: #808080
        }
    </style>
</head>

<body>
<?php
require '../include/toprightmenu.inc';
echo draw_toprightmenu(243);
?>

<div id="content">

    <div class="head_title">
        <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"/></div>
        <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img
                    src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-"/><a
                    href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
        <div class="page_title"><?php echo $string['clearguestaccounts'] ?></div>
    </div>

    <br/>

    <?php
    if (isset($_POST['submit'])) {
        for ($i = 1; $i <= 100; $i++) {
            if (isset($_POST["clear$i"])) {
                $stmt = $mysqli->prepare('SELECT users.id FROM temp_users, users WHERE temp_users.id = ? AND temp_users.assigned_account = users.username');
                $stmt->bind_param('i', $_POST["clear$i"]);
                $stmt->execute();
                $stmt->bind_result($temp_userID);
                $stmt->fetch();
                $stmt->close();

                // Delete from the temp_users list.
                $stmt = $mysqli->prepare('DELETE FROM temp_users WHERE id = ?');
                $stmt->bind_param('i', $_POST["clear$i"]);
                $stmt->execute();

                // Delete from the log_metadata table just in case a temp user has started but has no records.
                $stmt = $mysqli->prepare('DELETE FROM log_metadata WHERE userID = ?');
                $stmt->bind_param('i', $temp_userID);
                $stmt->execute();
            }
        }
    }
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
        <blockquote>
            <table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; width:100%">
                <tr>
                    <th><?php echo $string['clear'] ?></th>
                    <th><?php echo $string['user'] ?></th>
                    <th><?php echo $string['surname'] ?></th>
                    <th><?php echo $string['firstnames'] ?></th>
                    <th><?php echo $string['title'] ?></th>
                    <th><?php echo $string['studentid'] ?></th>
                    <th><?php echo $string['datereserved'] ?></th>
                    <th><?php echo $string['assessmenttaken'] ?></th>
                </tr>
                <?php
                $used = array();

                $result = $mysqli->prepare("SELECT id, first_names, surname, title, student_id, assigned_account, DATE_FORMAT(reserved,'%d/%m/%Y %H:%i:%s'), reserved FROM temp_users");
                $result->execute();
                $result->bind_result($id, $first_names, $surname, $title, $student_id, $assigned_account, $reserved, $reservedtime);
                while ($result->fetch()) {
                    $assigned_account = str_replace('user', '', $assigned_account);

                    $used[$assigned_account]['id'] = $id;
                    $used[$assigned_account]['first_names'] = $first_names;
                    $used[$assigned_account]['surname'] = $surname;
                    $used[$assigned_account]['title'] = $title;
                    $used[$assigned_account]['student_id'] = $student_id;
                    $used[$assigned_account]['assigned_account'] = $assigned_account;
                    $used[$assigned_account]['reserved'] = $reserved;
                    $used[$assigned_account]['reservedtime'] = $reservedtime;
                }
                $result->close();

                $result = $mysqli->prepare('SELECT DISTINCT paperID, paper_title FROM log2, log_metadata, properties, users WHERE log2.metadataID = log_metadata.id AND log_metadata.userID = users.id AND log_metadata.paperID = properties.property_id AND username = ?');
                $now = time();
                for ($i = 1; $i <= 100; $i++) {
                    if (isset($used[$i]['reserved']) and $used[$i]['reserved'] != '') {
                        $paper_title = '';
                        $tmp_user = "user$i";
                        $result->bind_param('s', $tmp_user);
                        $result->execute();
                        $result->bind_result($q_paper, $paper_title);
                        $result->fetch();

                        if ($used[$i]['surname'] == '') {
                            $used[$i]['surname'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
                        }
                        if ($used[$i]['first_names'] == '') {
                            $used[$i]['first_names'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
                        }
                        if ($used[$i]['title'] == '') {
                            $used[$i]['title'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
                        }
                        if ($used[$i]['student_id'] == '') {
                            $used[$i]['student_id'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
                        }

                        // Check if account allocated in last 15 minutes.
                        $diff = ($now - strtotime($used[$i]['reservedtime'])) / 60;
                        $reservedwarning = false;
                        if ($diff <= 15) {
                            $reservedwarning = true;
                        }

                        echo '<tr><td class="l">';
                        if ($paper_title == '') {
                            echo '<input class="clear" data-reservedwarning="' . $reservedwarning . "\" type=\"checkbox\" name=\"clear$i\" value=\"" . $used[$i]['id'] . '" />';
                        } else {
                            echo "<input class=\"clear\" data-reservedwarning=\"false\" type=\"checkbox\" name=\"clear$i\" value=\"\" disabled />";
                        }
                        echo "</td><td class=\"l\">user$i</td><td class=\"l\">" . $used[$i]['surname'] . '</td><td class="l">' . $used[$i]['first_names'] . '</td><td class="l">' . $used[$i]['title'] . '</td><td class="l">' . $used[$i]['student_id'] . '</td><td class="l">' . $used[$i]['reserved'] . '</td>';
                        if ($paper_title == '') {
                            echo '<td class="loff">' . $string['not taken'] . '</td>';
                        } else {
                            echo "<td class=\"l\"><a href=\"../paper/details.php?paperID=$q_paper\">$paper_title</td>";
                        }
                        echo '</tr>';
                    } else {
                        echo "<tr><td class=\"loff\"><input class=\"clear\" data-reservedwarning=\"false\" type=\"checkbox\" name=\"clear$i\" value=\"\" disabled /></td><td class=\"loff\">user$i</td><td class=\"loff\">guest$i</td><td colspan=\"6\" class=\"loff\" style=\"text-align:center\">" . $string['free'] . '</td></tr>';
                    }
                }
                $result->close();

                $mysqli->close();
                ?>
                <tr>
                    <td colspan="9" style="text-align:center">
                        <input class="ok" type="submit" name="submit" value="<?php echo $string['cleanup'] ?>"/>
                    </td>
                </tr>
            </table>
        </blockquote>
    </form>
</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
