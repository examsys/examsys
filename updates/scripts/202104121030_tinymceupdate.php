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

if ($updater_utils->check_version('7.4.0')) {
    if (!$updater_utils->has_updated('rogo3039')) {
        // Install tinymce plugin.
        $defaulttexteditorns = 'plugins\texteditor\plugin_tinymce_texteditor\plugin_tinymce_texteditor';
        $defaulttexteditor = new $defaulttexteditorns();
        $defaulttexteditor->update_plugin_version($defaulttexteditor->get_file_version());
        $tinymcestatus = $configObject->get_setting('plugin_tinymce3_texteditor', 'installed');
        $configObject->set_setting('installed', $tinymcestatus, \Config::BOOLEAN, 'plugin_tinymce_texteditor');
        // Enable.
        $defaulttexteditor->enable_plugin();
        // Delete old text editor.
        $sql = "DELETE FROM config WHERE component = 'plugin_tinymce3_texteditor'";
        $updater_utils->execute_query($sql, false);
        $sql = "DELETE FROM plugins WHERE component = 'plugin_tinymce3_texteditor'";
        $updater_utils->execute_query($sql, false);
        $updater_utils->record_update('rogo3039');
    }
}
