<?php

// Config no longer required in 6.3.0 onwards so do not run this update script on that version or version higher.
$version = $configObject->getxml('version');
if (version::is_version_higher($version, '6.3.0') === false and $version !== '6.3.0') {
    $new_lines = array("//Questions\n  \$cfg_interactive_qs = 'html5';\n");
    $target_line = '$vle_apis';
    $updater_utils->add_line($string, '$cfg_interactive_qs', $new_lines, 99, $cfg_web_root, $target_line, 1);
}

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */
