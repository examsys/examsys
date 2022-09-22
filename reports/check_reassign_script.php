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
 * Script is used to change the userID from a reservered temp_user account to a real user account.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
$paperID = check_var('paperID', 'GET', true, false, true);
$userID  = check_var('userID', 'GET', true, false, true);
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

/**
 * Get the modules that a user is enrolled on in the year that the paper the exam was run in.
 *
 * @param int $userID The id of the user that modules should be retrieved for.
 * @param object $mysqlidb The database connection object.
 * @param PaperProperties $properties The paper properties for the exam that the guest user is on.
 * @return array An array of module codes the user is enrolled on
 */
function getModules($userID, $mysqlidb, $properties)
{

    $modules = array();
    // The session we get student modules for should be based on that of the year the paper is set for.
    $session = $properties->get_calendar_year();
    $result = $mysqlidb->prepare('SELECT idmod FROM modules_student WHERE calendar_year = ? AND userID = ?');
    $result->bind_param('si', $session, $userID);
    $result->execute();
    $result->bind_result($moduleid);
    $result->store_result();
    while ($result->fetch()) {
        $modules[] = module_utils::get_moduleid_from_id($moduleid, $mysqlidb);
    }
    $result->close();
    return $modules;
}


// Get all the details from 'temp_users' for given userID.
$row_no = 0;
$result = $mysqli->prepare('SELECT temp_users.id, temp_users.title, temp_users.first_names, temp_users.surname, student_id, assigned_account, username FROM users, temp_users WHERE users.id = ? AND users.username = temp_users.assigned_account');
$result->bind_param('i', $userID);
$result->execute();
$result->bind_result($temp_account_id, $temp_title, $temp_first_names, $temp_surname, $temp_student_id, $assigned_account, $temp_username);
$result->store_result();
$row_no = $result->num_rows;
$result->fetch();
$result->close();
if ($row_no == 0) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['submit'])) {
    $temp_title       = $_POST['title'];
    $temp_first_names = $_POST['first_names'];
    $temp_surname     = $_POST['surname'];
    $temp_student_id  = $_POST['student_id'];
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['Reassign Script to User']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; margin:4px}
    h1 {color: #C00000}
    .uline {height:54px; cursor:pointer; background-repeat:no-repeat; background-position: 2px center; vertical-align:middle}
    .uline:hover {background-color:#FFE7A2}
    .name {margin-left:60px; position:relative; top:50%; transform: translateY(-50%)}
    .ineligible {background-color:#FFD9D9}
    .ineligible:hover {background-color:#D99594}
  </style>

    <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src="../js/reassignuserinit.min.js"></script>
</head>

<body>
<?php
// Check first if the exam is in progress.
if ($properties->is_live()) {
    echo '<blockquote><h1>' . $string['warning'] . '</h1><p>' . $string['msg2'] . '</p><br /><p style="text-align:center"><input type="button" value="' . $string['ok'] . "\" id=\"cancel\" class=\"ok\" /></p></blockquote>\n</body>\n</html>\n";
    exit;
}
$target_userID = '';
$target_student = array();
// Look up the temporary information in 'users'.
if ($temp_student_id != '') {
    // Try student number lookup.
    $result = $mysqli->prepare('SELECT id, surname, first_names, title, gender, student_id FROM users, sid WHERE users.id = sid.userID AND student_id = ?');
    $result->bind_param('s', $temp_student_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($target_userID, $target_surname, $target_first_names, $target_title, $gender, $student_id);
    while ($result->fetch()) {
        $target_student[$target_userID]['surname']      = $target_surname;
        $target_student[$target_userID]['first_names']  = $target_first_names;
        $target_student[$target_userID]['title']        = $target_title;
        $target_student[$target_userID]['gender']       = $gender;
        $target_student[$target_userID]['student_id']   = $student_id;
        $target_student[$target_userID]['modules']      = getModules($target_userID, $mysqli, $properties);
    }
    $result->close();
}
if ($target_userID == '') {
    // If no student number try the other details.
    $first_names = trim($temp_first_names) . '%';
    $temp_surname = trim($temp_surname);
    $temp_title = trim($temp_title);
    $sql = "
    SELECT DISTINCT
        u.id, u.surname, u.first_names, u.title, u.gender, sid.student_id 
    FROM
        users u
        JOIN user_roles ur ON ur.userid = u.id
        JOIN roles r ON r.id = ur.roleid
        LEFT JOIN sid ON u.id = sid.userID 
    WHERE 
        surname = ? AND first_names LIKE ?
        AND r.name IN ('Staff', 'Student')
    ";
    $result = $mysqli->prepare($sql);
    $result->bind_param('ss', $temp_surname, $first_names);
    $result->execute();
    $result->store_result();
    $result->bind_result($target_userID, $target_surname, $target_first_names, $target_title, $gender, $student_id);
    while ($result->fetch()) {
        $target_student[$target_userID]['surname']      = $target_surname;
        $target_student[$target_userID]['first_names']  = $target_first_names;
        $target_student[$target_userID]['title']        = $target_title;
        $target_student[$target_userID]['gender']       = $gender;
        $target_student[$target_userID]['student_id']   = $student_id;
        $target_student[$target_userID]['modules']      = getModules($target_userID, $mysqli, $properties);
    }
    $result->close();
}

echo '<p>' . str_replace('user', 'Temporary Account ', $temp_username) . ' ' . $string['msg3'] . ":</p>\n<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$userID&paperID=$paperID\" autocomplete=\"off\">\n<table border=\"0\" style=\"width:100%\">\n";
echo '<tr><th>' . $string['Title'] . '</th><th>' . $string['Last Name'] . '</th><th>' . $string['First Names'] . '</th><th>' . $string['Student ID'] . "</th><th></th></tr>\n";
echo "<tr><td><input type=\"text\" name=\"title\" value=\"$temp_title\" size=\"5\" /></td><td><input type=\"text\" name=\"surname\" value=\"$temp_surname\" size=\"15\" /></td><td><input type=\"text\" name=\"first_names\" value=\"$temp_first_names\" size=\"15\" /></td><td><input type=\"text\" name=\"student_id\" value=\"$temp_student_id\" size=\"6\" /></td><td><input type=\"submit\" name=\"submit\" value=\"" . $string['search'] . "\" style=\"width:80px\" /></tr>\n";
echo "</table>\n</form>\n";
if (count($target_student) == 0) {
    echo '<div>' . $string['msg4'] . ".</div>\n";
} else {
    // Get the modules for the paper so we can check if the students are enrolled on any of them.
    $paper_modules = $properties->get_modules();
    echo '<br /><div>' . $string['Reassign answers'] . ' ' . str_replace('user', 'Temporary Account ', $temp_username) . ' ' . $string['to following user'] . ":</div>\n<div id=\"userlist\" style=\"height:300px; border:1px solid #7F9DB9; overflow-y:scroll\">\n";
    foreach ($target_student as $individualID => $individual) {
        // Check if the student eligible to take the paper, if they are not we should highlight this to the user.
          $modules_student_is_on = array_intersect($paper_modules, $individual['modules']);
        if (empty($modules_student_is_on)) {
            // Student is not enrolled on the paper's modules.
            $div_class = 'uline ineligible';
            $eligible = false;
        } else {
            // Student is enrolled on at least one of the paper's modules.
            $div_class = 'uline';
            $eligible = true;
        }

        if ($individual['title'] == 'Mr') {
            $user_icon = 'user_male_48.png';
        } elseif ($individual['title'] == 'Dr') {
            if ($individual['gender'] == 'female') {
                $user_icon = 'user_female_48.png';
            } else {
                $user_icon = 'user_male_48.png';
            }
        } elseif ($individual['title'] == 'Mx') {
            $user_icon = 'user_mx_48.png';
        } else {
            $user_icon = 'user_female_48.png';
        }
        echo "<div class=\"$div_class reassign\" style=\"background-image:url('../artwork/$user_icon')\" data-userid=\"$userID\" data-targetid=\"$individualID\" data-username=\"$temp_username\" id=\"$individualID\"><div class=\"name\">" . $individual['title'] . ' ' . $individual['surname'] . ', <span style="color:#808080">' . $individual['first_names'] . '</span><br />(' . $individual['student_id'] . ')<br />';
        if ($eligible) {
            echo implode(', ', $modules_student_is_on);
        } else {
            echo $string['user_not_on_paper_modules'];
        }
        echo '</div></div>';
    }
    echo "</div>\n";
}
?>
<br />
<div style="text-align:center"><input type="button" id="cancel" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" /></div>

</body>
</html>
