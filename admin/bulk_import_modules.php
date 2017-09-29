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

ini_set("auto_detect_line_endings", true);

function returnTrueFalse($value) {
  $value = strtolower(trim($value));

  if ($value == 'yes' or $value == 'y' or $value == 'true') {
    return true;
  } else {
    return false;
  }
}
$lang['title'] = $string['bulkmoduleimport'];
$additionaljs = "<script type=\"text/javascript\" src=\"../js/jquery.validate.min.js\"></script>
                <script>
                    $(function () {
                      $('#import_form').validate();
                      
                      $('#cancel').click(function() {
                        history.back();
                      });
                    });
                </script>";
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
<div id="content" class="content">
<?php
  echo $render->render_admin_navigation(array(
    '/' => $string['home'],
    '/admin/index.php' => $string['admintools'],
    '/admin/list_modules.php' => $string['modules'],
    '/users/bulk_import_modules.php' => $string['bulkmoduleimport'],
  ));
?>
<br />
<br />
<?php
  if (isset($_POST['submit'])) {
    $default_academic_year_start = $configObject->get('cfg_academic_year_start');
    if ($_FILES['csvfile']['name'] != 'none' and $_FILES['csvfile']['name'] != '') {
      if (!move_uploaded_file($_FILES['csvfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_module_create.csv"))  {
        echo uploadError($_FILES['csvfile']['error']);
        exit;
      } else {
        ?>
        <br /><br /><br />
        <div align="center">
        <table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #95AEC8; font-size:120%; width:600px">
        <tr>
        <td valign="middle" align="left" style="width:56px; background-color:white"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /><span style="font-size:140%; font-weight:bold" class="dialog_header"><?php echo $string['bulkmoduleimport']; ?></span></td>
        </tr>
        <tr>
        <td align="left" class="dialog_body">
        <ul>

        <?php
        // Get a list of schools held by Rogo.
        $unknown_schoolID = 0;
        $school_list = array();
        $result = $mysqli->prepare("SELECT DISTINCT id, school FROM schools");
        $result->execute();
        $result->bind_result($school_id, $school_name);
        while ($result->fetch()) {
          $school_list[$school_name] = $school_id;
          if ($school_name == 'unknown') {
            $unknown_schoolID = $school_id;
          }
        }
        $result->close();

        $modulesAdded = 0;
        $lines = file( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_module_create.csv");

        $students = array();
        foreach ($lines as $separate_line) {
          if (trim($separate_line) != '') {
            $fields = explode(',', $separate_line);
            
            if (trim($fields[0]) != 'Module ID' and trim($fields[0]) != 'ID') {  // Ignore header line
              $moduleid = trim($fields[0]);
              $fullname = trim($fields[1]);
              if (isset($school_list[trim($fields[2])])) {
                $schoolID = $school_list[trim($fields[2])];
              } else {
                if ($unknown_schoolID == 0) {
                  $result = $mysqli->prepare("SELECT id FROM faculty WHERE name = 'Administrative and Support Units' LIMIT 1");
                  $result->execute();
                  $result->bind_result($facultyID);
                  $result->fetch();
                  $result->close();

                  $unknown_schoolID = SchoolUtils::add_school($facultyID, '', $mysqli);
                }
                $schoolID = $unknown_schoolID;
              }              
              $sms_api          = trim($fields[3]);
              $vle_api          = trim($fields[4]);
              
              $peer             = returnTrueFalse($fields[5]);
              $external         = returnTrueFalse($fields[6]);
              $stdset           = returnTrueFalse($fields[7]);
              $mapping          = returnTrueFalse($fields[8]);
              $active           = returnTrueFalse($fields[9]);
              $selfEnrol        = returnTrueFalse($fields[10]);
              $neg_marking      = returnTrueFalse($fields[11]);
              if (isset($fields[12])) {
                $timed_exams = returnTrueFalse($fields[12]);
              } else {
                $timed_exams = 0;
              }
              if (isset($fields[13])) {
                $exam_q_feedback = returnTrueFalse($fields[13]);
              } else {
                $exam_q_feedback = 0;
              }
              if (isset($fields[14])) {
                $add_team_members = returnTrueFalse($fields[14]);
              } else {
                $add_team_members = 0;
              }
              
              $ebel_grid_template = '';
              
              if (isset($fields[15]) and preg_match ('([0-1][0-9]/[0-3][0-9])', $fields[15]) ) {
                $academic_year_start = trim($fields[15]);
              } else {
                $academic_year_start = $default_academic_year_start;
              }
              
              if (module_utils::module_exists($moduleid, $mysqli)) {
                $updateData = array();
                 
                $checklist = '';
                if ($peer == true) $checklist .= ',peer';
                if ($external == true) $checklist .= ',external';
                if ($stdset == true) $checklist .= ',stdset';
                if ($mapping == true) $checklist .= ',mapping';
                $updateData['checklist'] = substr($checklist, 1);
                $updateData['fullname'] = $fullname;
                $updateData['vle_api'] = $vle_api;
                $updateData['sms'] = $sms_api;
                $updateData['schoolid'] = $schoolID;
                $updateData['active'] = $active;
                $updateData['selfenroll'] = $selfEnrol;
                $updateData['neg_marking'] = $neg_marking;
                $updateData['timed_exams'] = $timed_exams;
                $updateData['exam_q_feedback'] = $exam_q_feedback;
                $updateData['add_team_members'] = $add_team_members;
                $updateData['academic_year_start'] = $academic_year_start;
    
                module_utils::update_module_by_code($moduleid, $updateData, $mysqli);
                echo "<li class=\"existing\">$moduleid - " . $string['alreadyexists'] . "</li>\n";
              } else {
                $success = module_utils::add_modules($moduleid, $fullname, $active, $schoolID, $vle_api, $sms_api, $selfEnrol, $peer, $external, $stdset, $mapping, $neg_marking, $ebel_grid_template, $mysqli, 0, $timed_exams, $exam_q_feedback, 1, $academic_year_start);
                if ($success) {
                  echo "<li class=\"added\">$moduleid - " . $string['added'] . "</li>\n";
                  $modulesAdded++;
                } else {
                  echo "<li class=\"fail\">$moduleid - " . $string['failed'] . "</li>\n";
                }
              }
            }
          }
        }
      }
    }
    unlink( $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . "_module_create.csv");

    echo "</ul>";
    echo "<div style=\"text-align:center\"><input type=\"button\" name=\"ok\" value=\"" . $string['ok'] . "\" onclick=\"window.location='list_modules.php'\" class=\"ok\" /></div>\n";

    $mysqli->close();
    ?>
    </div>
    </td>
    </tr>
    </table>
    </div>
    </td></tr>
    </table>
    <?php
  } else {
?>
<table border="0" cellpadding="4" cellspacing="0" style="width:900px; border:1px solid #95AEC8; margin-left:auto; margin-right:auto">
<tr>
<td class="dialog_header" style="width:56px"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header midblue_header" style="width:844px"><?php echo $string['bulkmoduleimport'] ?></span></td>
</tr>
<tr>
<td align="left" style="padding:10px" class="dialog_body" colspan="2">

<p style="text-align:justify"><?php echo $string['msg1']; ?></p>
<blockquote>Module ID, Name, School, SMS API, Objectives API, Peer Review, External Examiners, Standards Setting, Mapping, Active, Allow Self-enrol, Negative Marking</blockquote>
<div style="text-align:center"><img src="../artwork/bulk_module_import_headings.png" width="891" height="59" alt="screenshot" style="border:1px solid black" /></div>
<br />
<div><?php echo $string['msg2']; ?></div>
<br />
<div align="center">
<form id="import_form" name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
<p><strong><?php echo $string['csvfile']; ?></strong> <input type="file" size="50" name="csvfile" class="required" /></p>
<br />
<p><input type="submit" class="ok" value="<?php echo $string['import'] ?>" name="submit" /><input class="cancel" id="cancel" type="button" value="<?php echo $string['cancel'] ?>" name="cancel" /></p>
</form>
</div>
</td>
</tr>
</table>

<?php
  }
  $render->render_admin_footer();
?>