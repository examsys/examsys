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
 * Bulk module creation
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/admin_auth.inc';
require '../include/toprightmenu.inc';

// Instantiate Twig renderer.
$render = new render($configObject);
$lang['title'] = $string['bulkmoduleimport'];
$additionaljs = '<script type="text/javascript" src="../js/bulkimportinit.min.js"></script>';
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/dialog.css\" />
                <link rel=\"stylesheet\" type=\"text/css\" href=\"../css/breadcrumb.css\" />
                <style type=\"text/css\">
                    p {margin:0; padding:0}
                    h1 {font-size:120%; font-weight:bold}
                    label.error {display:block; color:#f00}
                    li {list-style-type: none}
                    .existing {color:#808080; background-image: url('../artwork/arrow_circle_double.png'); background-repeat:no-repeat; line-height:20px; text-indent:20px}
                    .added {color:black; background-image: url('../artwork/green_plus_16.png'); background-repeat:no-repeat; line-height:20px; text-indent:20px}
                    .failed {color:#C00000; background-image: url('../artwork/red_cross_16.png'); background-repeat:no-repeat; line-height:20px; text-indent:20px}
                </style>";
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
?>
<body>
<?php
  require '../include/admin_module_options.inc';
  echo draw_toprightmenu();
?>
<?php
  $breadcrumb = array(
    $string['home'] => '/',
    $string['admintools'] => '/admin/index.php',
    $string['modules'] => '/admin/list_modules.php',
    $string['bulkmoduleimport'] => '/users/bulk_import_modules.php',
  );
  $render->render_admin_content($breadcrumb, $lang);
  $data['onclick'] = "window.location='list_modules.php'";
  if (isset($_POST['submit'])) {
      $default_academic_year_start = $configObject->get_setting('core', 'system_academic_year_start');
      $tmpfile = $userObject->get_user_ID() . '_module_create.csv';
      try {
          \csv\csv_handler::move_upload_to_temp($_FILES['csvfile'], $configObject->get('cfg_tmpdir') . $tmpfile);
          try {
              $csv = new \csv\csv_handler($tmpfile);
              $import = new \import\import_modules($csv);
              $import->execute();
              $data['exists'] = $import->get_exists();
              $data['added'] = $import->get_added();
              $data['failed'] = $import->get_failed();
          } catch (\csv\csv_load_exception $e) {
              $data['failed'] = array($e->getMessage());
          }
          $csv->delete_temp_file();
      } catch (\csv\csv_load_exception $e) {
          $data['failed'] = array($e->getMessage());
      }
      $render->render($data, $string, 'admin/upload_complete.html');
  } else {
      $data['formaction'] = $_SERVER['PHP_SELF'];
      $data['required'] = \import\import_modules::REQUIRED;
      $data['optional'] = \import\import_modules::OPTIONAL;
      $render->render($data, $string, 'admin/upload.html');
  }
  $render->render_admin_footer();