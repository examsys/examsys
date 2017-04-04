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
 * Utility class to get information about Rogo directories.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package core
 */
abstract class rogo_directory {
  /** @var string Path relative to the Rogo root directory used to download files. */
  protected $downloadfile = 'getfile.php';

  /** @var int The file permissions to be used by the directory. */
  protected $filepermissions = 0744;

  /**
   * An array of rogo_directory objects, the key should match the type
   * that would cause it to be loaded.
   *
   * This array should only be accessed via the get_directory() method.
   *
   * @var rogo_directory[]
   */
  protected static $loaded = array();

  /**
   * Is the user required to be authenticated to access files in the directory.
   *
   * @return boolean
   */
  public function authentication_required() {
    return true;
  }

  /**
   * Gets the configured Rogo data directory.
   *
   * @return string A path to the Rogo data directory, including a trailing slash.
   * @throws directory_not_found
   */
  protected function base_directory() {
    if (!empty(InstallUtils::$cfg_rogo_data)) {
      // Rogo is being installed. We should take the settings from InstallUtills
      $rogodata = InstallUtils::$cfg_rogo_data;
    } else {
      $config = Config::get_instance();
      // This will be null if the user has not configured it.
      $rogodata = $config->get('cfg_rogo_data');
    }

    if (!empty($rogodata)) {
      // Data driectory configured and should be used.
      if (!is_writable($rogodata) and !self::is_read_only()) {
        // Huston we have a problem.
        throw new directory_not_found('rogo_data');
      }
      // Ensure the directory has a trailing slash.
      if (substr($rogodata, -1) != DIRECTORY_SEPARATOR) {
        $rogodata .= DIRECTORY_SEPARATOR;
      }
      $path = $rogodata;
    } else {
      // Data directory not configured.
      // We should return the root Rogo path.
      $path = $this->default_base_directory();
    }
    return $path;
  }

  /**
   * Get the amount of time in seconds that files in the directory should be cached for.
   *
   * @return int
   */
  abstract public function cachetime();

  /**
   * Check if the directory has the correct permissions.
   * @return boolean true on correct, false otherwise.
   */
  public function check_permissions() {
    $location = $this->location();
    $readable = is_readable($location);
    $writable = is_writable($location);
    if (self::is_read_only()) {
        if ($readable and !$writable) {
            return true;
        }
    } else {
        if ($readable and $writable) {
            return true;
        }
    }
    return false;
  }
  
  /**
   * Delete the contents of the directory.
   * This should probably only be used by automatic test setup/teardown functions.
   * 
   * @return boolean true if all the contents were deleted, false otherwise.
   */
  public function clear() {
    return $this->recursive_delete($this->location());
  }
  
  /**
   * Copy files from the default location into the configured location.
   * 
   * @return void
   */
  public function copy_from_default() {
    $datadir = $this->base_directory();
    $default_datadir = $this->default_base_directory();
    if ($datadir === $default_datadir) {
      // Directory in the default location do nothing.
      return;
    }
    $location = $this->location();
    $default_location = str_replace($datadir, $default_datadir, $location);
    // Get all the files in the default directory (this is not recursive)
    $files = glob("$default_location*.*");
    foreach ($files as $file) {
      $filename = basename($file);
      copy($default_location . $filename, $location . $filename);
    }
  }

  /**
   * Create the directory if it does not exist.
   */
  public function create() {
    $directory = $this->location();
    if (!file_exists($directory) && !mkdir($directory, $this->filepermissions, true)) {
      throw new directory_not_found('rogo_data');
    }
  }

  /**
   * Returns the default location of the Rogo data directory.
   *
   * @return string
   */
  protected function default_base_directory() {
    return dirname(__DIR__) . DIRECTORY_SEPARATOR;
  }

  /**
   * Deletes the contents of a directory recursively, or delete a file.
   *
   * @param string $location The location to the directory.
   * @return boolean true if all the contents were deleted, false otherwise.
   */
  protected function recursive_delete($location) {
    if (!is_writable($location) or self::is_read_only()) {
      // We cannot do any deletion.
      return false;
    }
    if (!is_dir($location)) {
      // A file has been passed, delete it.
      return unlink($location);
    }
    // A directory has been passed, delete its contents.
    $success = true;
    // If the directory has no trailing slash add one.
    if (substr($location, -1) !== DIRECTORY_SEPARATOR) {
      $location .= DIRECTORY_SEPARATOR;
    }
    $directory = dir($location);
    $entry = $directory->read();
    // Loop through all the entries in the directory.
    while ($entry !== false) {
      if ($entry == '.' || $entry == '..') {
        // We should not try to delete the current and parent directory links.
        $entry = $directory->read();
        continue;
      }
      $deleted = $this->recursive_delete($location . $entry);
      $success = $success && $deleted;
      if ($deleted && is_dir($location . $entry)) {
        // Delete the directory now as its contents have been removed.
        $diredeleted = rmdir($location . $entry);
        $success = $success && $diredeleted;
      }
      // Get the next entry.
      $entry = $directory->read();
    }
    $directory->close();
    return $success;
  }

  /**
   * Get the full OS dependant path to the specified file.
   *
   * @param string $filename
   * @return string
   */
  public function fullpath($filename) {
    return $this->location() . $filename;
  }

  /**
   * Get an instance of a specific type of rogo_directory class.
   * The instanciated class is cached in the loaded array.
   *
   * @param string $type A type of Rogo directory that should be loaded.
   * @return rogo_directory
   * @throws directory_not_found
   */
  public static function get_directory($type) {
    // Check if we have already loaded the directory type.
    if (empty(self::$loaded[$type])) {
      // Try to find the directory class.
      if (!class_exists($type)) {
        throw new directory_not_found($type);
      }
      // Check that it is a valid directory class.
      $directory = new $type();
      if (!($directory instanceof rogo_directory)) {
        throw new directory_not_found($type);
      }
      self::$loaded[$type] = $directory;
    }
    return self::$loaded[$type];
  }

  /**
   * Get the file system location of the directory, including a trailing slash.
   *
   * @return string
   */
  abstract public function location();

  /**
   * Sends the specified file to the users browser then terminates the script.
   *
   * @param string $filename The name of the file in the directory.
   * @param boolean $forcedownload When false the user will be prompted to save the file.
   * @return void
   * @throws file_not_found
   */
  public function send_file($filename, $forcedownload = false) {
    $this->verify_file($filename);
    $fullpath = $this->fullpath($filename);
    // Start sending headers.
    if (!empty($forcedownload)) {
      header('Content-Disposition: attachment; filename="'.$filename.'"');
    } else {
      header('Content-Disposition: inline; filename="'.$filename.'"');
    }

    if ($this->cachetime() > 0) {
      if ($this->authentication_required()) {
        // Only cache on the browser.
        $cachelevel = ' private,';
      } else {
        // Proxies may cache the file.
        $cachelevel = ' public,';
      }
      header('Cache-Control:' . $cachelevel . ' max-age=' . $this->cachetime() . ', no-transform');
      header('Expires: '. gmdate('D, d M Y H:i:s', time() + $this->cachetime()) .' GMT');
      header('Pragma: ');
    } else {
      // No caching anywhere!
      header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
      header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
      header('Pragma: no-cache');
    }
    $fileinfo = new finfo(FILEINFO_MIME);
    header('Content-Type: ' . $fileinfo->file($fullpath));
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($fullpath)) . ' GMT');
    header('Content-Length: ' . filesize($fullpath));
    // Send the file.
    readfile($fullpath);
    // Kill the script.
    exit;
  }

  /**
   * Get the url for a file in the directory.
   *
   * If the filename specified does not exist an exception should be thrown.
   *
   * @param string $filename The name of a file inside the directory.
   * @param boolean $forcedownload Specify if an exception should be thrown if the file does not exist.
   * @param boolean $verifyfile Specify if an exception should be thrown if the file does not exist.
   * @param boolean $escaped Should the url to the file be escaped.
   * @return string
   * @thows file_not_found
   */
  public function url($filename, $forcedownload = false, $verifyfile = false, $escaped = false) {
    if ($verifyfile) {
      self::verify_file($filename);
    }
    $config = Config::get_instance();
    $webroot = $config->get('cfg_root_path');
    // Ensure there is a trailing slash.
    if (substr($webroot, -1) !== '/') {
      $webroot .= '/';
    }
    // Build the parameters for the url.
    $get = '?type=' . get_called_class();
    $get .= '&filename=' . $filename;
    if ($forcedownload) {
      $get .= '&forcedownload=1';
    }
    // Generate and return the url.
    $url = $webroot . $this->downloadfile . $get;
    if ($escaped) {
      $url = htmlentities($url, ENT_HTML5);
    }
    return $url;
  }

  /**
   * Check if the specified file exists.
   *
   * @param string $filename
   * @throws file_not_found
   */
  public function verify_file($filename) {
    $fullpath = $this->fullpath($filename);
    if (empty($filename) || !file_exists($fullpath) || !is_readable($fullpath)) {
      // The file cannot be retrived for the user.
      throw new file_not_found($fullpath);
    }
    // Check real path of file is in the real path of the directory.
    $realfullpath = realpath($fullpath);
    $realdirpath = realpath($this->location());
    if (strpos($realfullpath, $realdirpath) !== 0) {
      throw new file_not_found($fullpath);
    }
  }
  
  /**
   * Check if server is set to be read only
   * @return boolean true if readonly server, false otherwise
   */
  public static function is_read_only() {
    $config = Config::get_instance();
    $readonlyhost = $config->get('cfg_readonly_host');
    if (!empty($readonlyhost)) {
      return $readonlyhost;
    }
    return false;
  }
}
