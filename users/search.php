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
 * The results screen of a search for a user(s).
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
require '../include/staff_auth.inc';
require_once '../include/errors.php';

$demo = $userObject->has_role('Demo');

$sortby = param::optional('sortby', 'surname', param::ALPHA, param::FETCH_GET);
$ordering = param::optional('ordering', 'asc', param::ALPHA, param::FETCH_GET);

$moduleID = param::optional('module', null, param::INT, param::FETCH_GET);
$calendar_year = param::optional('calendar_year', '%', param::INT, param::FETCH_GET);

$get_staff = param::optional('staff', true, param::BOOLEAN, param::FETCH_GET);
$get_inactive = param::optional('inactive', true, param::BOOLEAN, param::FETCH_GET);
$get_sysadmin = param::optional('sysadminstaff', true, param::BOOLEAN, param::FETCH_GET);
$get_admin = param::optional('adminstaff', false, param::BOOLEAN, param::FETCH_GET);
$get_invigilators = param::optional('invigilators', false, param::BOOLEAN, param::FETCH_GET);
$get_standardstaff = param::optional('standardsstaff', false, param::BOOLEAN, param::FETCH_GET);
$get_external = param::optional('externals', false, param::BOOLEAN, param::FETCH_GET);
$get_internal = param::optional('internals', false, param::BOOLEAN, param::FETCH_GET);
$get_students = param::optional('students', false, param::BOOLEAN, param::FETCH_GET);
$get_graduates = param::optional('graduates', false, param::BOOLEAN, param::FETCH_GET);
$get_leavers = param::optional('leavers', false, param::BOOLEAN, param::FETCH_GET);
$get_suspended = param::optional('suspended', false, param::BOOLEAN, param::FETCH_GET);
$get_locked = param::optional('locked', false, param::BOOLEAN, param::FETCH_GET);

$student_id = param::optional('student_id', null, param::INT, param::FETCH_GET);
$search_surname = param::optional('search_surname', null, param::TEXT, param::FETCH_GET);
$search_username = param::optional('search_username', null, param::TEXT, param::FETCH_GET);

$submit = param::optional('submit', null, param::ALPHA, param::FETCH_GET);

// Define page variables.
$pages = 1;
$first = 0;
$last = 0;
$counter = 0;

if (!is_null($submit)) {
    // max number of results per page
    $limit_default = 100;
    $limit = param::optional('limit', $limit_default, param::INT, param::FETCH_GET);
    if ($limit < 1) {
        $limit = $limit_default;
    }

    // current page of results
    $page = param::optional('page', 1, param::INT, param::FETCH_GET);
    if ($page < 1) {
        $page = 1;
    }

    // query offset
    $offset = $page * $limit - $limit;

    // accumulaters for query builder
    $conditions = array();
    $parameters = array();
    $types = array();

    // find by module
    if ($moduleID) {
        $studentmodules = " AND modules_student.idMod = $moduleID";
        $staffconditions = " AND modules_staff.idMod = $moduleID";
    } else {
        $studentmodules = '';
        $staffconditions = '';
    }

    // find by calendar year
    if ($calendar_year !== '%') {
        $studentyear = " AND calendar_year = $calendar_year";
    } else {
        $studentyear = '';
    }

    $studentconditions = $studentmodules . $studentyear;

    // find by name
    if (!is_null($search_surname)) {
        $tmp_surname = str_replace("*", "%", trim($search_surname));

        // lookup titles
        $tmp_titles = explode(',', $string['title_types']);
        foreach ($tmp_titles as $tmp_title) {
            if (substr_count(strtolower($tmp_surname), strtolower($tmp_title . ' ')) > 0) {
                $conditions[] = 'title = ?';
                $parameters[] = $tmp_title;
                $types[] = 'ss';
            }
            $tmp_surname = preg_replace("/(" . $tmp_title . " )/i", "", $tmp_surname);
        }

        // find initials
        $sections = preg_split('[,.]', $tmp_surname);
        if (count($sections) > 1) {    // Search for initials.
            if (strlen($sections[0]) < strlen($sections[1])) {
                $tmp_initials = $mysqli->real_escape_string(trim($sections[0]));
                $tmp_surname = trim($sections[1]);
            } else {
                $tmp_initials = $mysqli->real_escape_string(trim($sections[1]));
                $tmp_surname = trim($sections[0]);
            }
            $conditions[] = 'initials LIKE ?';
            $parameters[] = $tmp_initials . '%';
            $types[] = 'ss';
        }

        // find remaining names
        $tmp_surname = explode(' ', $tmp_surname);
        $condition = array();
        foreach ($tmp_surname as $name) {
            $name = $mysqli->real_escape_string(str_replace('*', '%', $name));
            if (false === array_key_exists($name, $condition)) {
                $condition[$name] = 'surname LIKE ? OR first_names LIKE ?';
                $types[] = 'ssss';
                array_push($parameters, $name, $name);
            }
        }
        if (count($condition) > 0) {
            $conditions[] = sprintf('(%s)', implode(' OR ', $condition));
        }
    }

    // find by username
    if (!is_null($search_username) and trim($search_username) !== '') {
        $tmp_username = $mysqli->real_escape_string(str_replace('*', '%', trim($search_username)));
        $conditions[] = 'users.username LIKE ?';
        $parameters[] = $tmp_username;
        $types[] = 'ss';
    }

    // find by student id
    if (!is_null($student_id) and $student_id !== '') {
        $tmp_studentid = $mysqli->real_escape_string(trim($student_id));
        $conditions[] = 'student_id = ?';
        $parameters[] = $tmp_studentid;
        $types[] = 'ii';
    }

    // filter by roles
    $roles = array();
    if ($get_students or ( !is_null($student_id) and $student_id !== '')) {
        $roles[] = "roles = 'Student'";
    }
    if ($get_staff) {
        $roles[] = "roles = 'Staff'";
    }
    if ($get_admin) {
        $roles[] = "roles = 'Staff,Admin'";
    }
    if ($get_sysadmin) {
        $roles[] = "roles = 'Staff,SysAdmin'";
    }
    if ($get_standardstaff) {
        $roles[] = "roles = 'Staff,Standards Setter'";
    }
    if ($get_inactive) {
        $roles[] = "roles = 'Inactive Staff'";
    }
    if ($get_external) {
        $roles[] = "(roles = 'External Examiner')";
    }
    if ($get_internal) {
        $roles[] = "(roles = 'Internal Reviewer')";
    }
    if ($get_invigilators) {
        $roles[] = "roles = 'Invigilator'";
    }
    if ($get_graduates) {
        $roles[] = "roles = 'graduate'";
    }
    if ($get_leavers) {
        $roles[] = "roles = 'left'";
    }
    if ($get_suspended) {
        $roles[] = "roles = 'Suspended'";
    }
    if ($get_locked) {
        $roles[] = "roles = 'Locked'";
    }
    if (count($roles) > 0) {
        $conditions[] = sprintf('(%s)', implode(' OR ', $roles));
    }
    if (!$get_leavers) {
        $conditions[] = "grade <> 'left'";
    }

    // execute query
    if (count($roles) > 0) {
        // Fields.
        $sql_counter = 'SELECT COUNT(DISTINCT users.id) AS counter';
        $sql_fields = 'SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email, special_id';
        // Student template.
        $sql_student_template = " FROM users
          LEFT JOIN modules_student ON users.id = modules_student.userID
          LEFT JOIN sid ON users.id = sid.userID
          LEFT JOIN special_needs ON users.id = special_needs.userID 
          LEFT JOIN modules ON modules_student.idMod = modules.id
          WHERE user_deleted IS NULL" . $studentconditions . " AND " . implode(' AND ', $conditions);
        // Staff template.
        // Need to get only staff,student roles to aviud duplication.
        $conditions = str_replace('Student', 'Staff,Student', $conditions);
        $sql_staff_template = " FROM users
          LEFT JOIN modules_staff ON users.id = modules_staff.memberID 
          LEFT JOIN sid ON users.id = sid.userID
          LEFT JOIN special_needs ON users.id = special_needs.userID 
          LEFT JOIN modules ON modules_staff.idMod = modules.id
          WHERE user_deleted IS NULL" . $staffconditions . " AND " . implode(' AND ', $conditions);
        // UNION the templates and order,sort,limit,offset.
        $sql_count = sprintf('%s%s UNION %s%s', $sql_counter, $sql_student_template, $sql_counter, $sql_staff_template);
        $sql_list = sprintf('%s%s UNION %s%s ORDER BY %s %s LIMIT %d OFFSET %d',
          $sql_fields,
          $sql_student_template,
          $sql_fields,
          $sql_staff_template,
          $sortby,
          $ordering,
          $limit,
          $offset
        );

        // arguments to bind to queries
        $arguments = array(implode('', $types));
        foreach ($parameters as &$param) {
            $arguments[] = &$param;
        }
        // As query is a union we need to duplicate the arguments.
        foreach ($parameters as &$param) {
            $arguments[] = &$param;
        }
        // prepare counter query
        if (false === $stmt = $mysqli->prepare($sql_count)) {
            throw new \RuntimeException($mysqli->error);
        }

        // bind parameters to counter query
        if (count($parameters) > 0) {
            if (false === call_user_func_array(array($stmt, 'bind_param'), $arguments)) {
                throw new \RuntimeException($stmt->error);
            }
        }

        // execute counter query
        if (false === $stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        // fetch total items count
        $stmt->bind_result($count);
        while ($stmt->fetch()) {
          $counter += $count;
        }
        $stmt->close();

        // calculate first and last items in thispage
        $first = $offset + 1;
        $last = min(array($offset + $limit, $counter));
        if ($last == 0) {
            $first = 0;
        }
        // calculate total number pages
        $pages = ceil($counter / $limit);

        // prepare list query
        if (false === $stmt = $mysqli->prepare($sql_list)) {
            throw new \RuntimeException($mysqli->error);
        }

        // bind parameters to list query
        if (count($parameters) > 0) {
            if (false === call_user_func_array(array($stmt, 'bind_param'), $arguments)) {
                throw new \RuntimeException($stmt->error);
            }
        }

        // execute list query
        if (false === $stmt->execute()) {
            throw new \RuntimeException($stmt->error);
        }

        // bind list query results to variables
        $stmt->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email, $tmp_special_id);
        $stmt->store_result();
    }
}

$module_id = param::optional('moduleID', null, param::INT, param::FETCH_GET);
$paper_id = param::optional('paperID', null, param::INT, param::FETCH_GET);
$team = param::optional('team', null, param::ALPHANUM, param::FETCH_GET);
$email = param::optional('email', null, param::EMAIL, param::FETCH_GET);
$temporary_surname = param::optional('tmp_surname', null, param::ALPHA, param::FETCH_GET);
$temporary_courseid = param::optional('tmp_courseID', null, param::INT, param::FETCH_GET);
$temporary_yearid = param::optional('tmp_yearID', null, param::INT, param::FETCH_GET);

$render = new render($configObject);

// links on breadcrumb
$links = array(
    '/' => $string['home'],
    '/users/search.php' => $string['usermanagement'],
);
if ($moduleID) {
    $href = '/../module/index.php?module=' . $moduleID;
    $links[$href] = module_utils::get_moduleid_from_id($moduleID, $mysqli);
}

// search has result
if (true === $has_result = !is_null($submit) or ! is_null($paper_id) or ! is_null($module_id)) {
    // current page label on breadcrumb
    $links[] = $string['usersearch'];

    // result detail
    if ($paper_id) {
        $result_detail = implode(', ', array_values($paper_modules)) . ' (' . $paper_calendar_year . ')';
    } elseif (!is_null($search_surname)) {
        $result_detail = $search_surname;
    } elseif (!is_null($moduleID) and $moduleID !== '%') {
        $result_detail = module_utils::get_moduleid_from_id($moduleID, $mysqli);
        if (!is_null($calendar_year) and $calendar_year !== '%' and $get_students) {
            $result_detail .= ' (' . $calendar_year . ')';
        }
    } elseif (!is_null($search_username)) {
        $result_detail = $search_username;
    } elseif (!is_null($student_id)) {
        $result_detail = $student_id;
    } elseif ($calendar_year > 0) {
        $result_detail = $calendar_year;
    } else {
        $result_detail = '';
    }

    $table_order = array('#1', '#2', $string['title'], $string['surname'], $string['firstname'], $string['username'], $string['studentid'], $string['year'], $string['course']);
    $photodirectory = rogo_directory::get_directory('user_photo');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
        <title><?php echo page::title('Rog&#333;: ' . $string['usermanagement']); ?></title>
        <link rel="stylesheet" type="text/css" href="../css/body.css" />
        <link rel="stylesheet" type="text/css" href="../css/header.css" />
        <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
        <link rel="stylesheet" type="text/css" href="../css/list.css" />
        <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
        <style type="text/css">
            a {color:black}
            .coltitle {cursor:hand; background-color:#F1F5FB; color:black}
            #usertable td {padding-left:6px}
            .fn {color:#A5A5A5}
            .uline {line-height: 150%}
            .uline:hover {background-color:#FFE7A2}
            .uline.highlight {background-color:#FFBD69}
            td {padding-left: 0 !important}
            .l {line-height: 160%}
        </style>

        <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="../js/staff_help.js"></script>
        <script type="text/javascript" src="../js/toprightmenu.js"></script>
        <script>
            function addUserID(ID, clearall) {
                if (clearall) {
                    $('#userID').val(',' + ID);
                } else {
                    cur_value = $('#userID').val() + ',' + ID;
                    $('#userID').val(cur_value);
                }
            }

            function subUserID(ID) {
                var tmpuserID = ',' + ID;
                new_value = $('#userID').val().replace(tmpuserID, '');
                $('#userID').val(new_value);
            }

            function clearAll() {
                $('.highlight').removeClass('highlight');
            }

            function selUser(userID, lineID, menuID, roles, evt) {
                $('#menu2a').hide();
                $('#menu' + menuID).show();

                if (evt.ctrlKey == false && evt.metaKey == false) {
                    clearAll();
                    $('#' + lineID).addClass('highlight');
                    addUserID(userID, true);
                } else {
                    if ($('#' + lineID).hasClass('highlight')) {
                        $('#' + lineID).removeClass('highlight');
                        subUserID(userID);
                    } else {
                        $('#' + lineID).addClass('highlight');
                        addUserID(userID, false);
                    }
                }
                $('#roles').val(roles);
                checkRoles();

                evt.stopPropagation();
            }

            function userOff() {
                $('#menu2a').show();
                $('#menu2b').hide();
                $('#menu2c').hide();

                clearAll();
            }

            function profile(userID) {
                document.location.href = 'details.php?search_surname=<?php echo $search_surname; ?>'
                        + '&search_username=<?php echo $search_username ?>&student_id=<?php echo $student_id; ?>'
                        + '&moduleID=<?php echo $team; ?><?php if (!is_null($moduleID)) echo '&module=' . $moduleID; ?>'
                        + '&calendar_year=<?php echo $calendar_year ?>&students=<?php if ($get_students) echo 'on'; ?>'
                        + '&submit=Search&userID=' + userID + '&email=<?php echo $email; ?>'
                        + '&tmp_surname=<?php echo $temporary_surname; ?>&tmp_courseID=<?php echo $temporary_courseid; ?>'
                        + '&tmp_yearID=<?php echo $temporary_yearid; ?>';
            }

            $(function () {
                if ($("#maindata").find("tr").size() > 1) {
                    $("#maindata").tablesorter({
                        // sort on the third column, order asc
                        sortList: [[3, 0]]
                    });
                }

                $(document).click(function () {
                    $('#menudiv').hide();
                });
            });
        </script>
    </head>
    <body>
        <?php
        // left hand side menu
        include '../include/user_search_options.php';
        // hidden topright menu
        require '../include/toprightmenu.inc';
        echo draw_toprightmenu(92);
        ?>

        <div id="content" class="content">
            <?php echo $render->render_admin_navigation($links); ?>

            <div class="head_title">
                <div class="page_title">
                    <?php echo $string['usersearch']; ?>
                    <?php if ($has_result) : ?>
                    (<?= number_format($first) ?> <?php echo $string['to']; ?> <?= number_format($last) ?> <?php echo $string['of']; ?> <?= number_format($counter) ?>):
                        <span style="font-weight: normal">
                            <?= $result_detail ?>
                        </span>
                    <?php endif; ?>
                </div>

            <?php if ($pages > 1) : ?>
                <?php $url = Url::fromGlobals(); ?>
                <div style="margin-left: 10px;">
                    <?php for ($i = 1; $i <= $pages; $i++) : ?>
                        <?php if ($i == $page) : ?>
                            <strong>
                        <?php else : ?>
                            <a href="<?= $url->setQueryValue('page', $i); ?>">
                        <?php endif; ?>
                        <?= $i ?>
                        <?php if ($i == $page) : ?>
                            </strong>
                        <?php else : ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($i < $pages) : ?>&nbsp;|&nbsp;<?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            </div>

            <?php if ($has_result) : ?>
                <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>?sortby=<?php echo $sortby; ?>&order=<?php echo $ordering; ?>" autocomplete="off">
                    <table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0" style="width:100%">
                        <thead>
                            <tr>
                                <?php foreach ($table_order as $display) : ?>
                                    <?php if ($display{0} == '#') : ?>
                                        <th>&nbsp;</th>
                                    <?php else : ?>
                                        <th class="col"><?= $display ?></th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <?php
                            if (!is_null($submit) and empty($roles)) {
                                echo '</table>';
                                echo $notice->info_strip($string['msg1'], 100);
                            } else {
                        ?>
                        <tbody>
                            <?php
                            $x = 0;
                            if (!is_null($submit) and count($roles) > 0) {
                                while ($stmt->fetch()) :
                                    ?>
                                    <tr class="l" id="<?= $x ?>" onclick="selUser('<?= $tmp_id ?>', <?= $x ?>, '2c', '<?= $tmp_roles ?>', event); return false;" ondblclick="profile('<?= $tmp_id ?>'); return false;">
                                        <td>
                                            <?php if (false !== $photoname = UserUtils::student_photo_exist($tmp_username)) : ?>
                                                <img src="../artwork/photo.png" width="16" height="16" alt="Photo" />
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tmp_special_id) : ?>
                                                <img src="../artwork/accessibility_16.png" width="16" height="16" />
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (array_key_exists(mb_strtolower($tmp_title), $string)) : ?>
                                                <?= $string[mb_strtolower($tmp_title)] ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $tmp_surname == '' ? \demo::demo_replace($tmp_surname, $demo, true, ' ') : \demo::demo_replace($tmp_surname, $demo, true, $tmp_surname{0}) ?></td>
                                        <td><?= $tmp_first_names == '' ? \demo::demo_replace($tmp_first_names, $demo, true, ' ') : \demo::demo_replace($tmp_first_names, $demo, true, $tmp_first_names{0}) ?></td>
                                        <td><?= \demo::demo_replace($tmp_username, $demo, false) ?></td>
                                        <td class="fn">
                                            <?php if (false !== strpos($tmp_roles, 'Student')) : ?>
                                                <?= is_null($tmp_student_id) ? $string['unknown'] : \demo::demo_replace_number($tmp_student_id, $demo) ?>
                                            <?php elseif (false !== strpos($tmp_roles, 'Staff')) : ?>
                                                Staff
                                            <?php else: ?>
                                                <?= $string['na'] ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $tmp_yearofstudy ?>
                                        </td>
                                        <td>
                                            <?= $tmp_grade ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $x++;
                                endwhile;
                                $stmt->close();
                                $mysqli->close();
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                        }
                    ?>
                </form>
            <?php endif; ?>
    </body>
</html>