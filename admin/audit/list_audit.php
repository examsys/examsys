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
 * Listing permission access.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 */

require '../../include/sysadmin_auth.inc';
require '../../include/toprightmenu.inc';
$startdate = date('Y-m-d H:i:s', strtotime('-' . Audit::getRententionPeriod() . ' day'));

$limit = param::optional('limit', AuditSearch::DEFAULT_LIMIT, param::INT, param::FETCH_GET);
$page = param::optional('page', 1, param::INT, param::FETCH_GET);
$audit = Audit::getEvents($startdate, $limit, $page);

$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$additionaljs = '<script type="text/javascript" src="js/auditinit.min.js"></script>';
$addtionalcss = '<link rel="stylesheet" type="text/css" href="../../css/list.css"/>
<link rel="stylesheet" type="text/css" href="../../css/audit_list.css"/>';
$breadcrumb = array($string['home'] => '../../index.php', $string['administrativetools'] => '../index.php');
$render->render_admin_header($string, $additionaljs, $addtionalcss);
$render->render_admin_options('settings.php', 'admin_icon_16.gif', $string, $toprightmenu, 'admin/options_link.html');
// Create pages.
if (isset($audit['pages'])) {
    for ($i = 1; $i <= $audit['pages']; $i++) {
        $url = Url::fromGlobals();
        $audit['pageinfo'][$i]['url'] = $url->setQueryValue('page', $i);
        if ($i == $page) {
            $audit['pageinfo'][$i]['selected'] = true;
        } else {
            $audit['pageinfo'][$i]['selected'] = false;
        }
    }
}
$render->render_admin_content($breadcrumb, $string, 'admin/audit/content.html', $audit);
$render->render($audit, $string, 'admin/audit/list.html');
$render->render_admin_footer();
