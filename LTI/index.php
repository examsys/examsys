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
 * LTI landing page.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_student_auth.inc';
require_once '../include/sidebar_menu.inc';

require_once '../config/index.inc';

function listtreemodules($mysqli, $moduleid, $block_id, $plk, $flat = false, $explode = false) {
  $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');

  $configObject = Config::get_instance();

  $moduleidorig = $moduleid;
  $moduleid = module_utils::get_idMod($moduleid, $mysqli);
  
  $sql = "SELECT DISTINCT crypt_name, paper_type, paper_title, retired, idMod FROM properties, properties_modules WHERE idMod = ? and properties.property_id = properties_modules.property_id AND deleted IS NULL AND paper_type IN ('0','1','3','4') ORDER BY paper_type, paper_title";
  $results2 = $mysqli->prepare($sql);
  $results2->bind_param('i', $moduleid);
  $results2->execute();
  $results2->bind_result($crypt_name, $paper_type, $paper_title, $retired, $moduleID);
  $results2->store_result();
  if ($results2->num_rows() > 0) {
    $rt = $results2->num_rows();
    echo '<div>';
    while ($results2->fetch()) {
      if (strtolower($_SESSION['_lti_context']['resource_link_title']) == strtolower($paper_title)) {
        $checked = ' checked';
      } else {
        $checked = '';
      }
      $extra = "<input type=\"radio\" name=\"paperlinkID\" id=\"paperlinkID-$plk\" value=\"$plk\"$checked><label for=\"paperlinkID-$plk\">";
      $extra1 = "</label>";
      
      echo "<div style=\"padding-left:20px\">$extra<img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" alt=\"" . $paper_type . "\" />&nbsp;" .  $paper_title . "$extra1</div>\n";

      $_SESSION['postlookup'][$plk] = array($crypt_name, $moduleid);
      $plk++;
    }
    echo '</div>';
    $block_id++;
  } else {
    // no papers
  }
  $results2->close();

  return (array($block_id, $plk));
}

$lti = UoN_LTI::get_instance();

if (!$lti->valid) {
  $tempvar = $lti->message;
  if (!isset($string[$tempvar])) {
    $string[$tempvar] = $lti->message;
  }
  $message = $string[$tempvar];
  UserNotices::display_notice($string['LTIFAILURE'], $message, '../artwork/access_denied.png', '#C00000');
  $mysqli->close();
  exit;
}

if (!isset($lti_i)) {
  $lti_i = $lti->load();
}

if (isset($_REQUEST['paperlinkID'])) {
  list($retlookup, $retlookup2) = $_SESSION['postlookup'][$_REQUEST['paperlinkID']];
  unset($_SESSION['postlookup']);
  if ($retlookup > 0) {
    $info = $lti->getResourceKey(1);
    $lti->add_lti_resource($retlookup, 'paper');
  }
}
unset($_SESSION['postlookup']);

$returned = $lti->lookup_lti_resource();

if (!$lti->isInstructor()) {
  //student
  if ($returned === false) {
    // no data selected for this
    UserNotices::display_notice($string['warning'], $string['ltinotconfigured'], '../artwork/access_denied.png', $title_color = '#C00000');
    echo "\n</body>\n</html>\n";
    exit();
  } else {
    //valid data
    $returned2 = $lti->lookup_lti_context();
    if ($returned2 === false) {
        $data = $lti_i->module_code_translate($mysqli, $lti->getCourseName(), $lti->get_context_title());
        if ($data === false) {
            UserNotices::display_notice($string['moduletranslateerror'], sprintf($string['moduletranslatemessage'], $configObject->get('support_email'), $configObject->get('support_email')), '/artwork/access_denied.png');
            echo sprintf($string['moduletranslatecode'], $lti->getCourseName());
            exit();
        }
    } else {
        $data = array(array('', $returned2[0]));
    }
    $yearutils = new yearutils($mysqli);
    $session = $yearutils->get_current_session();

    foreach ($data as $moduleinfo) {
      $returned_check = module_utils::get_full_details_by_name($moduleinfo[1], $mysqli);
      // User not on module, have user details and LTI allows student self reg - enrol student.
      if (!UserUtils::is_user_on_module_by_name($userObject->get_user_ID(), $moduleinfo[1], $session, $mysqli) and $returned_check !== false and $lti_i->allow_module_self_reg()) {
        if ($returned_check['active'] == 1 and $returned_check['selfenroll'] == 1) {
          // Insert new module enrollment
          UserUtils::add_student_to_module_by_name($userObject->get_user_ID(), $moduleinfo[1], 1, $session, $mysqli);
        }
      }
    }
    $_SESSION['lti']['paperlink'] = $returned[0];
    $href = '../paper/user_index.php?id=' . $returned[0];
    header("location: {$href}");
    echo sprintf($string['redirectmessage'], $href);
    exit();

  }
} else {
  //staff

  if ($returned !== false) {
    // goto link

    $returned2 = $lti->lookup_lti_context();
    $mod = $returned2[0];
    // Staff user not on module and LTi allowed to enroll staff users and this is a staff user - enrol.
    if (!$userObject->is_staff_user_on_module($mod) and $lti_i->allow_staff_module_register() and $userObject->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
      UserUtils::add_staff_to_module_by_modulecode($userObject->get_user_ID(), $mod, $mysqli);
    // Staff user not on module and LTi NOT allowed to enroll staff users and this is a staff user - display notice.
    } elseif (!$userObject->is_staff_user_on_module($mod) and !$lti_i->allow_staff_module_register() and $userObject->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
      UserNotices::display_notice($string['NotAddedToModuleTitle'], $string['NotAddedToModule'] . $mod, '../artwork/exclamation_64.png','#C00000');
      echo "\n</body>\n</html>\n";
      exit();
    }
    
    if (!$lti_i->allow_staff_edit_link()) {
      $_SESSION['lti']['paperlink'] = $returned[0];
      $href = '../paper/user_index.php?id=' . $returned[0];
      header("location: {$href}");
      echo sprintf($string['redirectmessage'], $href);
      exit();
    } else {
      // allow editing of the stored link
      //TODO NO SUPPORT YET IMPLIMENTED
    }

  } else {
    // no existing stored link so need to create one
    if (!$userObject->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
      UserNotices::display_notice($string['NoModCreateTitle2'], $string['NoModCreate2'], '../artwork/exclamation_64.png','#C00000');
      echo "\n</body>\n</html>\n";
      exit();
    }
    $returned2 = $lti->lookup_lti_context();

    if ($returned2 === false) {
      $modid = -1;
      //no context
      $data = $lti_i->module_code_translate($mysqli, $lti->getCourseName(), $lti->get_context_title());
      if ($data === false) {
            UserNotices::display_notice($string['moduletranslateerror'], sprintf($string['moduletranslatemessage'], $configObject->get('support_email'), $configObject->get('support_email')), '/artwork/access_denied.png');
            echo sprintf($string['moduletranslatecode'], $lti->getCourseName());
            exit();
      }
      foreach ($data as $moduleinfo) {
        $problem = false;
        // Module exists and staff user is enrolled on it - get module id.
        if (module_utils::module_exists($moduleinfo[1], $mysqli) and $userObject->is_staff_user_on_module($moduleinfo[1])) {
            $modid = module_utils::get_idMod($moduleinfo[1], $mysqli);
        // Module does not exist and LTI allowed to create modules - create module.
        } elseif (!module_utils::module_exists($moduleinfo[1], $mysqli) and $lti_i->allow_module_create() ) {
          if (!$userObject->has_role(array('Staff', 'Admin', 'SysAdmin'))) {
            UserNotices::display_notice($string['NoModCreateTitle2'], $string['NoModCreate2'] . $moduleinfo[1], '../artwork/exclamation_64.png','#C00000');
            echo "\n</body>\n</html>\n";
            exit();
          }
          $peer = 1;
          $external = 1;
          $stdset = 0;
          $mapping = 1;
          $neg_marking = 1;

          $selfEnroll = 0;
          if ($moduleinfo[0] == 'Manual') {
            $selfEnroll = 1;
            $peer = 0;
            $external = 0;
            $stdset = 0;
            $mapping = 0;
            $neg_marking = 1;
          }
          $sms_api = $lti_i->sms_api($moduleinfo);
          if ($sms_api === false) {
            UserNotices::display_notice($string['modulecreateerror'], sprintf($string['modulecreatemessage'], $configObject->get('support_email'), $configObject->get('support_email')), '/artwork/access_denied.png');
            echo sprintf($string['moduletranslatecode'], $moduleinfo[1]);
            exit(); 
          }
          $schoolID = SchoolUtils::get_school_id_by_name($moduleinfo[3], $mysqli);
          $externalid = $lti_i->module_id_translate($lti->getExternalID());
          $modid = module_utils::add_modules($moduleinfo[1], $moduleinfo[5], 1, $schoolID, '', $sms_api, $selfEnroll, $peer, $external, $stdset, $mapping, $neg_marking, 0, $mysqli, 1, 0, 1, 1, 0, '07/01', $externalid);
          if ($modid === false) {
            $problem = true;
          }
        // Module does not exist and LTI NOT allowed to create modules - display notice.
        } elseif (!module_utils::module_exists($moduleinfo[1], $mysqli) and !$lti_i->allow_module_create()) {
          UserNotices::display_notice($string['NoModCreateTitle'], $string['NoModCreate'] . $moduleinfo[1], '../artwork/exclamation_64.png','#C00000');
          echo "\n</body>\n</html>\n";
          exit();
        }
        // User not a staff member on the module and LTI allowed to enrol staff and user is staff and module allows addition of team members - add staff to module.
        if (!$userObject->is_staff_user_on_module($moduleinfo[1]) and $lti_i->allow_staff_module_register() and $userObject->has_role(array('Staff', 'Admin', 'SysAdmin')) and module_utils::is_allowed_add_team_members_by_name($moduleinfo[1],$mysqli) ) {
          UserUtils::add_staff_to_module_by_modulecode($userObject->get_user_ID(), $moduleinfo[1], $mysqli);
          $modid = module_utils::get_idMod($moduleinfo[1], $mysqli);
        // User not a staff memeber on the module and LTI NOT allowed to enrol staff - display notice.
        } elseif (!$userObject->is_staff_user_on_module($moduleinfo[1]) and !$lti_i->allow_staff_module_register()) {
          UserNotices::display_notice($string['NotAddedToModuleTitle'], $string['NotAddedToModule'] . $moduleinfo[1], '../artwork/exclamation_64.png','#C00000');
          echo "\n</body>\n</html>\n";
          exit();
        }
        // Only add context if not a metamodule (i.e. only one module to link to) and no issues have occured.
        if (count($data) == 1 and $problem === false and $modid != -1) {
          $lti->add_lti_context($modid);
        }
      }
    } else {
        $data = array(array('', $returned2[0]));
        foreach ($data as $moduleinfo) {
        // User not a staff member on the module and LTI allowed to enrol staff and user is staff and module allows addition of team members - add staff to module.
        if (!$userObject->is_staff_user_on_module($moduleinfo[1]) and $lti_i->allow_staff_module_register() and $userObject->has_role(array('Staff', 'Admin', 'SysAdmin')) and module_utils::is_allowed_add_team_members_by_name($moduleinfo[1], $mysqli) ) {
          UserUtils::add_staff_to_module_by_modulecode($userObject->get_user_ID(), $moduleinfo[1], $mysqli);
        // User not a staff memeber on the module and LTI NOT allowed to enrol staff - display notice.
        } elseif (!$userObject->is_staff_user_on_module($moduleinfo[1]) and !$lti_i->allow_staff_module_register()) {
          UserNotices::display_notice($string['NotAddedToModuleTitle'], $string['NotAddedToModule'] . $moduleinfo[1], '../artwork/exclamation_64.png','#C00000');
          echo "\n</body>\n</html>\n";
          exit();
        }
      }
    }
    echo <<<END
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset={$configObject->get('cfg_page_charset')}" />

  <title>Rog&#333; {$configObject->get('cfg_install_type')}</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
  body {padding-left:20px; background-color:transparent !important; line-height:140%}
  h1 {font-size:160%; color:#295AAD}
  .info_bar {margin-bottom:8px}
  </style>
   {$configObject->get('cfg_js_root')}
</head>
<body>
<div id="content" class="content">

END;

    $plk = 0;
    $block_id = 0;

    if (isset($error)) {
      foreach ($error as $e) {
        echo $e;
      }
    }

    @ob_flush();
    @ob_start();
    
    // If there is a context and therefore a course already selected display that.
    $modules = '';
    $exit = 0;

    foreach ($data as $moduleinfo) {
      $modules = $modules . ', ' . $moduleinfo[1];
      if ($moduleinfo[1] == '') {
        $exit = 1;
      }
    }
    $modules = substr($modules, 2);
    
    echo '<h1>' . sprintf($string['module'], $modules) . '</h1>';
    $msg = 'First time configuration. Please select the paper you wish to use in this external tool link.';
    echo $notice->info_strip($msg, 100);
    
    echo '<form method="post" autocomplete="off">';
    foreach ($data as $moduleinfo) {
      $moduleid = $moduleinfo[1];

      list($block_id, $plk) = listtreemodules($mysqli, $moduleid, $block_id, $plk, true);
    }
    echo "<br /><div><input type=\"submit\" name=\"submit\" value=\"" . $string['ok'] . "\" class=\"ok\" style=\"margin-left:20px\" /></form></div></form>\n";
    echo '<br />';
    if ($exit == 1) {
      $plk = 0;
      $modules = "Undefined Module. Please contact Support.";
    }
    
    if ($plk == 0) {
      @ob_clean();
      unset($_SESSION['_lti_context']);
      unset($_SESSION['lti']);
      UserNotices::display_notice($string['NoPapers'], $string['NoPapersDesc'], '../artwork/access_denied.png', '#C00000');

      echo sprintf('<p>%s: %s</p>', $string['modulescaption'], $modules);
    }
  }
}
?>
