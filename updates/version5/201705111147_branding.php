<?php

if ($updater_utils->check_version("6.4.0")) {
    if (!$updater_utils->has_updated('rogo-2160')) {
        // New configs.
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('misc_logo_main', 'logo.png', 'string');
        $configObject->set_setting('misc_logo_email', 'alt_logo.png', 'string');
        // Ensure that the new directory folders exist.
        $theme_dir = rogo_directory::get_directory('theme');
        $theme_dir->create();
        $theme_dir->copy_from_default();
        $updater_utils->record_update('rogo-2160');
    }
}