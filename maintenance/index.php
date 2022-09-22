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
 * @author Rob Ingram
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 2.0
 * @copyright Copyright (c) 2017 The University of Nottingham
 * @package
 */

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
require_once '../include/load_config.php';
$language = LangUtils::getLang($cfg_web_root);
LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));
$configObject = Config::get_instance();
$render = new render($configObject);
$headerdata = array(
  'css' => array(
      '/css/maintenance.css',
  ),
  'scripts' => array(),
);
$data = array('retry_path' => param::required('from', param::LOCAL_URL, param::FETCH_GET));
$render->render($headerdata, $string, 'header.html');
$render->render($data, $string, '/maintenance/maintenance.html');
$render->render_admin_footer();
