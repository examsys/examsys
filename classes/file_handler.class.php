<?php
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
 * The file handler interface
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */
abstract class file_handler  {

  /**
   * Language pack component.
   */
  const langcomponent = 'classes/filehandler';

  /**
   * The path to the file.
   * @var string
   */
  public $file;

  /**
   * The name of the file.
   * @var string
   */
  public $filename;

  /**
   * Class strings.
   * @var array
   */
  public $string;

  /**
   * Reading pointer to the file from fopen()
   * @var resource
   */
  protected $read_file_handle;

  /**
   * Writing pointer to the file from fopen()
   * @var resource
   */
  protected $write_file_handle;

  /**
   * Move upload file to tmp dir
   * @param string $from upload file
   * @param string $to temp file location
   * @throws csv_load_exception
   * @return string
   */
  abstract public static function move_upload_to_temp($from, $to);

  /**
   * Opens the file for reading..
   * @throws file_load_exception
   */
  abstract public function load();

  /**
   * Handler specifc fcuntions to create a file.
   * @throws file_write_exception
   */
  abstract protected function create();

  /**
   * Gets a line of data from the file
   *
   * @return array
   * @throws file_load_exception
   */
  abstract public function get_line();

  /**
   * Writes an array as a line in the file.
   * @param array $line
   * @throws file_write_exception
   */
  abstract public function write_line(array $line);

  /**
   * Write file header information to file.
   */
  abstract public function set_headers();

  /**
   * Generate a unique filename
   * @return string
   */
  private static function unique_filename() {
    return bin2hex(random_bytes(40));
  }

  /**
   * Initialise the handler.
   * @param string $file The filename of the fiel to be used
   * @param string $directory The path to the file to be used
   * @throws file_load_exception
   */
  public function __construct($file, $directory = ".") {
    $configObject = Config::get_instance();
    $file = param::clean($file, param::FILENAME);
    // If no directory supplied use temp dir.
    if ($directory === '.') {
      $directory = $configObject->get('cfg_tmpdir');
    }
    $this->filename = basename($file);
    $fullpath = realpath($directory);
    $langpack = new \langpack();
    $this->string = $langpack->get_all_strings(self::langcomponent);
    if ($fullpath === false) {
      throw new file_load_exception($file . $this->string['invalidpath']);
    }
    $this->file = $fullpath . DIRECTORY_SEPARATOR . $this->filename;
  }

  /**
   * Delete file
   * @param string $file file to delete
   */
  public function delete($file) {
    if (file_exists($file)) {
      unlink($file);
    }
  }

  /**
   * Delete a file and close handler.
   */
  public function delete_temp_file() {
    if (is_resource($this->write_file_handle)) {
      fclose($this->write_file_handle);
    }
    $this->delete($this->file);
  }

  /**
   * Sends the file to download.
   */
  public function send_file() {
    if (file_exists($this->file)) {
      $this->set_headers();
      readfile($this->file);
    }
    $this->delete_temp_file();
  }

  /**
   * Create a temp file
   */
  public function create_temp_file() {
    $configObject = Config::get_instance();
    $this->file = $configObject->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . self::unique_filename();
    for ($i = 0; $i < 3; $i++) {
      if (!file_exists($this->file)) {
        break;
      } else {
        $this->file = $configObject->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . self::unique_filename();
      }
    }
    $this->write_file_handle = fopen($this->file, 'w');
    // Use handler specific function to create file.
    $this->create();
  }
}
