<?php
if ($updater_utils->check_version("6.4.0")) {
    if (!$updater_utils->has_updated('root_path_set')) {
        // set web root.
        $new_web_root = get_root_path() . '/';
        $new_line = "\$cfg_web_root = '" . $new_web_root . "';\n";
        $updater_utils->replace_line($string, "\$cfg_web_root = get_root_path() . '/';", $new_line, $cfg_web_root);
        // set root path.
        $new_root_path = rtrim('/' . trim(str_replace(normalise_path($_SERVER['DOCUMENT_ROOT']), '', $new_web_root), '/'), '/');
        $new_line = "\$cfg_root_path = '" . $new_root_path . "';\n";
        $updater_utils->replace_line($string, "\$cfg_root_path = rtrim('/' . str_replace(\$_SERVER['DOCUMENT_ROOT'], '', \$cfg_web_root), '/');", $new_line, $cfg_web_root);
        $updater_utils->record_update('root_path_set');
    }
}