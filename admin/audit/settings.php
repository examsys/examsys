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
 * Permission access retention settings.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 */

require '../../include/sysadmin_auth.inc';
require '../../include/toprightmenu.inc';

if (param::optional('update', false, param::TEXT, param::FETCH_POST)) {
    $retention = param::required('retention', param::INT, param::FETCH_POST);
    Audit::setRetentionPeriod($retention);
    header('location: list_audit.php', true, 303);
    exit();
}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$additionaljs = '';
$addtionalcss = '<link rel="stylesheet" type="text/css" href="../../css/settings.css"/>';
$breadcrumb = array(
    $string['home'] => '../../index.php',
    $string['administrativetools'] => '../index.php',
    $string['audit'] => 'list_audit.php'
);
$render->render_admin_header($string, $additionaljs, $addtionalcss);
$render->render_admin_options('', '', $string, $toprightmenu, 'admin/options_empty.html');
$render->render_admin_content($breadcrumb, $string);
$data['retention'] = Audit::getRententionPeriod();
$data['action'] = $_SERVER['PHP_SELF'];
$render->render($data, $string, 'admin/audit/settings.html');
$render->render_admin_footer();
