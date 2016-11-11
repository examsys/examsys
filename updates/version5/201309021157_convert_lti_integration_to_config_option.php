<?php

// Config stored in db from 6.2.0 onwards so do not run this update script on that version or version higher.
$version = $configObject->getxml('version');
if (version::is_version_higher($version, '6.2.0') === false and $version !== '6.2.0') {
  // Your code here
  $cfg_web_root = $configObject->get('cfg_web_root');
  if (file_exists($cfg_web_root . 'config/integration/lti_integration.class.php')) {
    $filen = 'config/integration/lti_integration.class.php';
  } else {
    $filen = 'default';
  }

  $new_lines = array("\r\n","// lti_integration variable below is set the relative path & filename of the new integration class or left as blank or default to use the built in functionality.\r\n","\$lti_integration = '$filen';\r\n"); ////Questions\n  \$cfg_interactive_qs = 'html5';\n");
  $target_line = '$cfg_lti_allow_module_create';
  $updater_utils->add_line($string, '$lti_integration', $new_lines, 78, $cfg_web_root, $target_line, 1);
}


/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */
