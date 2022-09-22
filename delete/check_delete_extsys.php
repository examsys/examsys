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
 * Confirm that it is OK to proceed deleting an external system.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$id = param::required('id', param::INT, param::FETCH_GET);

$external = new \external_systems();

if (!$external->external_system_exists($id)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
if ($external->external_system_inuse($id)) {
    $notice->display_notice($string['cannotdelete'], $string['extssysinuse'], '../artwork/exclamation_64.png', 'black', true, false);
    echo '<p><button type="button" onclick="javascript:window.close();">' . $string['cancel'] .  '</button></p>';
    echo "\n</body>\n</html>";
    exit;
}
$render = new render($configObject);
$lang['msg'] = $string['msg'];
$lang['delete'] = $string['delete'];
$lang['cancel'] = $string['cancel'];
$data['action'] = 'do_delete_extsys.php';
$data['id'] = $id;
$render->render($data, $lang, 'admin/check_delete.html');
