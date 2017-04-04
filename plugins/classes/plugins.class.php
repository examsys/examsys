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

namespace plugins;

/**
* Plugin functionality
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Abstract plugin class.
 * 
 * This class should be extend by classes for plugins
 */
abstract class plugins {
    /**
     * The database connection.
     * @var mysqli
     */
    protected $db;
    /**
     * The config object.
     * @var object
     */
    protected $config;
    /**
     * Name of the plugin.
     * @var string
     */
    protected $plugin;
    /**
     * Version of the plugin from file.
     * @var string
     */
    protected $version;
    /**
     * Installed version of the plugin.
     * @var string
     */
    protected $installedversion;
    /**
     * Rogo version dependency of the plugin.
     * @var string
     */
    protected $requires;
    /**
     * Type of the plugin.
     * @var string
     */
    protected $plugin_type;
     /**
     * Language pack component.
     * @var string
     */
    protected $langcomponent;
    /**
     * Path to plugin.
     * @var string
     */
    private $path;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup() {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    /**
     * Get install path of plugin
     * @return string path
     */
    public function get_path() {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . $this->plugin_type . DIRECTORY_SEPARATOR . $this->plugin;
    }
    /**
     * Remove plugin version
     * @return bool true on success
     */
    private function delete_plugin_version() {
        $deletesql = $this->db->prepare("DELETE FROM plugins WHERE component = ?");
        $deletesql->bind_param('s', $this->plugin);
        $deletesql->execute();
        $deletesql->close();
        if ($this->db->errno != 0) {
            return false;
        }
        return true;
    }
    /**
     * Constructor
     */
    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->config = \Config::get_instance();
        // Get path to plugin.
        $this->path = $this->get_path();
        // Load version info.
        require $this->path . DIRECTORY_SEPARATOR . 'version.php';
    }
    /**
     * Install plugin.
     * @param string $dbuser user to run db command
     * @param string $dbpasswd password for user
     * @return string installation success or otherwise the error response
     */
    public function install($dbuser, $dbpasswd) {
        // Disable at start of process, enable at end if successful.
        $this->config->set_setting('installed', 0, \Config::BOOLEAN, $this->plugin);
        // Check plugin dependencies.
        $check = $this->check_plugin_dependencies();
        if ($check !== true) {
            return $check;
        } else {
            // Attempt to install or update plugin.
            $pluginpath = $this->path . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR;
            $currentversion = $this->get_plugin_version();
            if ($currentversion === false) {
                // 1. Install plugin.
                try {
                    $installfile = $pluginpath . 'install.sql';
                    if (file_exists($installfile)) {
                        if (!\DBUtils::run_sql($installfile, $dbuser, $dbpasswd)) {
                            throw new \Exception("DBUtils::run_sql install.sql failed.");
                        }
                    }
                } catch (\Exception $e) {
                    return 'SCHEMA_FAIL';
                }
            } else {
                // 1. Update plugin.
                try {
                    // Supports accumlative patches.
                    // Only runs patches for versions higher than currently installed version.
                    if (\version::is_version_higher($this->version, $currentversion)) {
                        // Get update files available.
                        $updatefiles = glob($pluginpath . 'update*');
                        $fileversion = array();
                        foreach ($updatefiles as $file) {
                            $v = ltrim(rtrim(basename($file), '.sql'), 'update');
                            // Check version format is correct.
                            if (\version::check_version_format($v)) {
                              $fileversion[] = $v;
                            }
                        }
                        if (count($fileversion) > 0) {
                            // Run each update file in numeric ascending order.
                            $fileversion = \version::sort_version($fileversion);
                            foreach ($fileversion as $version) {
                                if (!\version::is_version_higher($version, $currentversion) or \version::is_version_higher($version, $this->version)) {
                                    // Skip updates from previously installed versions and from future versions.
                                    continue;
                                }
                                $updatefile = $pluginpath . 'update' . $version . '.sql';
                                if (file_exists($updatefile)) {
                                    if (!\DBUtils::run_sql($updatefile, $dbuser, $dbpasswd)) {
                                        throw new \Exception("DBUtils::run_sql update" . $version . ".sql failed.");
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    return 'UPDATE_FAIL';
                }
            }
            // 2. Update version.
            $this->update_plugin_version($this->version);
            // 3. Flag plugin as installed.
            $this->config->set_setting('installed', 1, \Config::BOOLEAN, $this->plugin);
        }
        return 'OK';
    }
    /**
     * Unistall a plugin
     * Removes database schema of plugin and sets appropiate flags in config
     * Does not remove the code
     * @param string $dbuser user to run db command
     * @param string $dbpasswd password for user
     * @return string uninstall success or otherwise the error response
     */
    public function uninstall($dbuser, $dbpasswd){
        $currentversion = $this->get_plugin_version();
        // Only uninstall if code and db versions match.
        if ($currentversion == $this->version) {
            // Attempt to remove the plugin database schema
            $pluginpath = $this->path . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR;
            try {
                $uninstallfile = $pluginpath . 'uninstall.sql';
                if (file_exists($uninstallfile)) {
                    if (!\DBUtils::run_sql($uninstallfile, $dbuser, $dbpasswd)) {
                        throw new \Exception("DBUtils::run_sql uninstall.sql failed.");
                    }
                }
            } catch (\Exception $e) {
                return 'DROP_SCHEMA_FAIL';
            }
            // Disable plugin.
            $this->config->set_setting('installed', 0, \Config::BOOLEAN, $this->plugin);
            // Delete plugin.
            $this->delete_plugin_version();
            return 'OK';
        }
        return 'UNINSTALL_VERSION_ERROR';
    }
    /**
     * Check plugin dependencies
     * @return bool|string true if dependencies met, error string otherwise
     */
    public function check_plugin_dependencies() {
        $new_plugin_version = $this->version;
        if (!\version::check_version_format($new_plugin_version)) {
            // Cannot install plugin with incorrect version format.
            return 'INCORRECT_VERSION';
        }
        $current_rogo_version = $this->config->get('rogo_version');
        $current_plugin_version = $this->get_plugin_version();
        $plugin_requires_rogo = $this->requires;
        if (!\version::is_version_higher($plugin_requires_rogo, $current_rogo_version)) {
            if (\version::is_version_higher($current_plugin_version, $new_plugin_version)) {
                // Cannot install current version higher.
                return 'CURRENT_VERSION_HIGHER';
            } elseif ($current_plugin_version == $new_plugin_version) {
                // Cannot install already installed.
                // Not fatal so renable
                $this->config->set_setting('installed', 1, \Config::BOOLEAN, $this->plugin);
                return 'ALREADY_INSTALLED';
            }
        } else {
            // Cannot install requires higher rogo version.
            return 'REQUIRES_ROGO_HIGHER';
        }
        return true;
    }
    /**
     * Update version of the plugin
     * @param string $version the plugin version
     * @return bool true on success
     */
    public function update_plugin_version($version) {
        $currentversion = $this->get_plugin_version();
        // If not installed, install.
        if ($currentversion === false) {
            $insertsql = $this->db->prepare("INSERT INTO plugins (component, version, type) VALUES (?, ?, ?)");
            $insertsql->bind_param('sss', $this->plugin, $version, $this->plugin_type);
            $insertsql->execute();
            $insertsql->close();
            if ($this->db->errno != 0) {
                return false;
            }
            $this->installedversion = $version;
        }
        // Installed so check if we are upgrading.
        if (\version::is_version_higher($currentversion, $version)) {
            // Do not support rolling back plugins.
            return false;
        } elseif (\version::is_version_higher($version, $currentversion)) {
            $updatesql = $this->db->prepare("UPDATE plugins SET version = ? WHERE component = ?");
            $updatesql->bind_param('ss', $version, $this->plugin);
            $updatesql->execute();
            $updatesql->close();
            if ($this->db->errno != 0) {
                return false;
            }
            $this->installedversion = $version;
        }
        return true;
    }
    /**
     * Get version of plugin already install
     * @param string $plugin name of plugin
     * @return string|bool version of plugin or false
     */
    public function get_plugin_version() {
        // If already set no need to query db.
        if (empty($this->installedversion)) {
            $sql = $this->db->prepare("SELECT version FROM plugins WHERE component = ?");
            $sql->bind_param('s', $this->plugin);
            $sql->execute();
            $sql->bind_result($this->installedversion);
            $sql->store_result();
            $sql->fetch();
            if ($sql->num_rows == 0) {
                $sql->close();
                return false;
            }
            $sql->close();
        }
        return $this->installedversion;
    }
    /**
     * Get version of plugin from version file
     * @return string value of version
     */
    public function get_file_version() {
        return $this->version;
    }
    /**
     * Get rogo required version from version file
     * @return string value of required version
     */
    public function get_file_requires() {
        return $this->requires;
    }
    /**
     * Get installed version of plugin
     * @return string value of installed version
     */
    public function get_installed_version() {
        return $this->installedversion;
    }
    /**
     * Get lang component of the plugin.
     * @return string value of lang component
     */
    public function get_lang_component() {
        return $this->langcomponent;
    }
    /**
     * Enable the plugin.
     */
    abstract public function enable_plugin();
}