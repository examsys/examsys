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
 * Delete an external system - SysAdmin only.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$id = param::required('id', param::INT, param::FETCH_POST);

$external = new \external_systems();

if (!$external->external_system_exists($id) or $external->external_system_inuse($id)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$external->delete_external_system($id);

$render = new render($configObject);
$lang['title'] = $string['delete'];
$lang['success'] = $string['success'];
$data = array();
$additionaljs = "<script>
    $(function () {
      window.opener.location.reload();
      self.close();
    });
  </script>";
$render->render($data, $lang, 'admin/do_delete.html', $additionaljs);
?>