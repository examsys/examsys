<?php

if ($updater_utils->check_version('7.0.5')) {
    if (!$updater_utils->has_updated('rogo2764')) {
        $configObject->set_setting('summative_remote', false, Config::BOOLEAN);
        $configObject->set_setting('summative_issuelink', '', Config::URL);
        $updater_utils->record_update('rogo2764');
    }
}
