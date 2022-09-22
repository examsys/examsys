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

$get_staff = param::optional('staff', false, param::BOOLEAN, param::FETCH_GET);
$get_inactive = param::optional('inactive', false, param::BOOLEAN, param::FETCH_GET);
$get_sysadmin = param::optional('sysadminstaff', false, param::BOOLEAN, param::FETCH_GET);
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
    $search = new UserSearch();
    // max number of results per page
    $limit = param::optional('limit', UserSearch::DEFAULT_LIMIT, param::INT, param::FETCH_GET);
    $search->setLimit($limit);

    // current page of results
    $page = param::optional('page', 1, param::INT, param::FETCH_GET);
    $search->setPage($page);

    // find by module
    if ($moduleID) {
        $search->setModule($moduleID);
    }

    // find by calendar year
    if ($calendar_year !== '%') {
        $search->setYear($calendar_year);
    }

    // find by name
    if (!is_null($search_surname)) {
        $search->setName($search_surname);
    }

    // find by username
    if (!is_null($search_username)) {
        $search->setUsername($search_username);
    }

    // find by student id
    if (!is_null($student_id)) {
        $search->setIDNumber($student_id);
    }

    // filter by roles
    $roles = array();
    if ($get_students) {
        $search->setSearchStudents();
    }
    if ($get_staff) {
        $search->setSearchStaff();
    }
    if ($get_admin) {
        $search->setSearchAdmin();
    }
    if ($get_sysadmin) {
        $search->setSearchSysAdmin();
    }
    if ($get_standardstaff) {
        $search->setSearchStandard();
    }
    if ($get_inactive) {
        $search->setSearchInactive();
    }
    if ($get_external) {
        $search->setSearchExternal();
    }
    if ($get_internal) {
        $search->setSearchInternal();
    }
    if ($get_invigilators) {
        $search->setSearchInvigilators();
    }
    if ($get_graduates) {
        $search->setSearchGraduates();
    }
    if ($get_leavers) {
        $search->setSearchLeavers();
    }
    if ($get_suspended) {
        $search->setSearchSuspended();
    }
    if ($get_locked) {
        $search->setSearchLocked();
    }

    // execute query
    $result = $search->execute();

    $first = $result->first;
    $last = $result->last;
    $pages = $result->pages;
    $stmt = $result->query;

    // bind list query results to variables
    $stmt->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email, $tmp_special_id);
    $stmt->store_result();
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
if (true === $has_result = !is_null($submit) or ! is_null($module_id)) {
    // current page label on breadcrumb
    $links[] = $string['usersearch'];

    // result detail
    if (!is_null($search_surname)) {
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
        <title><?php echo page::title('ExamSys: ' . $string['usermanagement']); ?></title>
        <link rel="stylesheet" type="text/css" href="../css/body.css" />
        <link rel="stylesheet" type="text/css" href="../css/header.css" />
        <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
        <link rel="stylesheet" type="text/css" href="../css/list.css" />
        <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
        <style type="text/css">
            a {color:black}
            .coltitle {background-color:#F1F5FB; color:black}
            #usertable td {padding-left:6px}
            .fn {color:#A5A5A5}
            .uline {line-height: 150%}
            .uline:hover {background-color:#FFE7A2}
            .uline.highlight {background-color:#FFBD69}
            td {padding-left: 0 !important}
            .l {line-height: 160%}
        </style>

        <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
        <script src='../js/require.js'></script>
        <script src='../js/main.min.js'></script>
        <script src="../js/usersearchinit.min.js"></script>
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
                    (<?= number_format($first) ?> <?php echo $string['to']; ?> <?= number_format($last) ?> <?php echo $string['of']; ?> <?= number_format($result->total) ?>):
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
                        <?php if ($i < $pages) :
                            ?>&nbsp;|&nbsp;<?php
                        endif; ?>
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
                                    <?php if ($display[0] == '#') : ?>
                                        <th>&nbsp;</th>
                                    <?php else : ?>
                                        <th class="col"><?= $display ?></th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <?php
                        if (!is_null($submit) and !$search->rolesSelected()) {
                            echo '</table>';
                            echo $notice->info_strip($string['msg1'], 100);
                        } else {
                            ?>
                        <tbody>
                            <?php
                            $x = 0;
                            if (!is_null($submit) and $search->rolesSelected()) {
                                while ($stmt->fetch()) :
                                    ?>
                                    <tr class="l" id="<?= $x ?>" data-userid="<?php echo $tmp_id; ?>" data-lineid="<?php echo $x; ?>" data-menuid="2c" data-roles="<?php echo $tmp_roles; ?>">
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
                                        <td><?= $tmp_surname == '' ? \demo::demo_replace($tmp_surname, $demo, true, ' ') : \demo::demo_replace($tmp_surname, $demo, true, $tmp_surname[0]) ?></td>
                                        <td><?= $tmp_first_names == '' ? \demo::demo_replace($tmp_first_names, $demo, true, ' ') : \demo::demo_replace($tmp_first_names, $demo, true, $tmp_first_names[0]) ?></td>
                                        <td><?= \demo::demo_replace($tmp_username, $demo, false) ?></td>
                                        <td class="fn">
                                            <?php if (false !== mb_strpos($tmp_roles, 'Student')) : ?>
                                                <?= is_null($tmp_student_id) ? $string['unknown'] : \demo::demo_replace_number($tmp_student_id, $demo) ?>
                                            <?php elseif (false !== mb_strpos($tmp_roles, 'Staff')) : ?>
                                                Staff
                                            <?php else : ?>
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
            <?php
            $render = new render($configObject);
            $dataset['name'] = 'dataset';
            $dataset['attributes']['surname'] = $search_surname;
            $dataset['attributes']['username'] = $search_username;
            $dataset['attributes']['sid'] = $student_id;
            $dataset['attributes']['team'] = $team;
            if (!is_null($moduleID)) {
                $dataset['attributes']['module'] = $moduleID;
            } else {
                $dataset['attributes']['module'] = '';
            }
            $dataset['attributes']['year'] = $calendar_year;
            if ($get_students) {
                $dataset['attributes']['students'] = 'on';
            } else {
                $dataset['attributes']['students'] = 'off';
            }
            $dataset['attributes']['email'] = $email;
            $dataset['attributes']['tmp_surname'] = $temporary_surname;
            $dataset['attributes']['tmp_courseid'] = $temporary_courseid;
            $dataset['attributes']['tmp_yearid'] = $temporary_yearid;
            $render->render($dataset, array(), 'dataset.html');
            // JS utils dataset.
            $jsdataset['name'] = 'jsutils';
            $jsdataset['attributes']['xls'] = json_encode($string);
            $render->render($jsdataset, array(), 'dataset.html');
            ?>
    </body>
</html>
