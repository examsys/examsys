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
 * Plugin manager functionality
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 */

/**
 * Plugin manager class.
 *
 * This class manages plugins within rogo.
 */
class plugin_manager
{
    /**
     * Whitelist of plugin types supported by rogo.
     * @const PLUGINTYPE_WHITELIST
     */
    public const PLUGINTYPE_WHITELIST = array('mapping', 'SMS', 'texteditor');

    /**
     * Define core plugins.
     * Associate array (plugin namespace, enabled by default flag)
     * @const CORE_PLUGINS
     */
    public const CORE_PLUGINS = array(
        array('namespace' => 'plugins\texteditor\plugin_tinymce_texteditor\plugin_tinymce_texteditor', 'enabled' => true),
        array('namespace' => 'plugins\texteditor\plugin_plain_texteditor\plugin_plain_texteditor', 'enabled' => false)
    );

    /**
     * List available plugins.
     * @return array available plugins (name => namespace)
     */
    public static function listplugins()
    {
        $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . '*';
        $folders = glob($directory, GLOB_ONLYDIR);
        $plugins = array();
        foreach ($folders as $folder) {
            $type = basename($folder);
            if (in_array($type, self::PLUGINTYPE_WHITELIST)) {
                $sub = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . '*';
                $subfolders = glob($sub, GLOB_ONLYDIR);
                foreach ($subfolders as $subfolder) {
                    $plugins[basename($subfolder)] = 'plugins\\' . $type . '\\' . basename($subfolder) . '\\' . basename($subfolder);
                }
            }
        }
        return $plugins;
    }
    /**
     * List available plugin types.
     * @return array available plugin types
     */
    public static function listplugintypes()
    {
        $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . '*';
        $folders = glob($directory, GLOB_ONLYDIR);
        $plugintype = array();
        foreach ($folders as $folder) {
            if (in_array(basename($folder), self::PLUGINTYPE_WHITELIST)) {
                $plugintype[] = 'plugin_' . basename($folder);
            }
        }
        return $plugintype;
    }

    /**
     * Get all enabled plugins
     * @return array list of enabeld plugins
     */
    public static function get_all_enabled_plugins()
    {
        $enabledplugins = array();
        $plugintypes = self::listplugintypes();
        foreach ($plugintypes as $type) {
            $enabled = self::get_plugin_type_enabled($type);
            $enabledplugins = array_merge($enabledplugins, $enabled);
        }
        return $enabledplugins;
    }

    /**
     * Get the enabeld plugin for this type:
     * @param string $type type of plugin
     * @return array list of plugins that are enabled of this type
     */
    public static function get_plugin_type_enabled($type)
    {
        $config = Config::get_instance();
        $enabled = $config->get_setting($type, 'enabled_plugin');
        if (!is_null($enabled)) {
            $enabledarray = $enabled;
            $newenabled = array();
            $changed = false;
            // Check existing enabled plugins and disable those not installed.
            foreach ($enabledarray as $e) {
                if (self::plugin_installed($e)) {
                    $newenabled[] = $e;
                } else {
                    $changed = true;
                }
            }
            if ($changed) {
                $config->set_setting('enabled_plugin', $newenabled, Config::JSON, 'plugin_' . $type);
            }
        } else {
            $newenabled = array();
        }
        return $newenabled;
    }

    /**
     * Is plugin installed.
     * @param string $plugin name of plugin
     * @return bool true is installed false otherise.
     */
    public static function plugin_installed($plugin)
    {
        $config = Config::get_instance();
        $installed = $config->get_setting($plugin, 'installed');
        if ($installed >= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Install all core plugins
     * @param string $db_admin_username db user
     * @param string $db_admin_passwd password fro db user
     */
    public static function install_core_plugins($db_admin_username, $db_admin_passwd)
    {
        foreach (self::CORE_PLUGINS as $core) {
            $pluginns = $core['namespace'];
            $enabled = $core['enabled'];
            $plugin = new $pluginns();
            $plugin->install($db_admin_username, $db_admin_passwd);
            if ($enabled) {
                $plugin->enable_plugin();
            }
        }
    }
}
