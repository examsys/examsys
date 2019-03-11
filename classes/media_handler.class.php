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
 * Media handler
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */
class media_handler {

  /**
   * Supported media types.
   * @var array
   */
  const SUPPORTED = array(
    'gif' => questiondata::IMAGE,
    'jpg' => questiondata::IMAGE,
    'jpeg' => questiondata::IMAGE,
    'png' => questiondata::IMAGE,
    'doc' => questiondata::DOC,
    'docx' => questiondata::DOC,
    'ppt' => questiondata::DOC,
    'pptx'=> questiondata::DOC,
    'xls' => questiondata::DOC,
    'xlsx' => questiondata::DOC,
    'pdf' => questiondata::DOC,
    'avi' => questiondata::MOVIE,
    'mpg' => questiondata::MOVIE,
    'mpeg' => questiondata::MOVIE,
    'mov' => questiondata::MOVIE,
    'mp3' => questiondata::MP3,
    'mid' => questiondata::AUDIO,
    'wav' => questiondata::AUDIO,
    'ram' => questiondata::AUDIO,
    'pdb' => questiondata::THREED,
    'ply' => questiondata::THREED,
    'obj' => questiondata::THREED,
    'mtl' => questiondata::THREED,
    'dds' => questiondata::THREED,
    'zip' => questiondata::ARCHIVE,
  );

  /**
   * This is function returns a unique filename so that files are not overwritten on the server.
   * The filename is initially seeded with the current UNIX time in seconds plus the original
   * file extension.
   * @param string $filename
   * @return string
   * @throws directory_not_found
   */
  public static function unique_filename($filename) {
    $mediadirectory = rogo_directory::get_directory('media');

    $ext = substr($filename, strrpos($filename, '.'));
    $fileno = date('U');

    do {
      $tmp_filename = $fileno . $ext;
      $fileno++;
    } while (file_exists($mediadirectory->fullpath($tmp_filename)));

    return $tmp_filename;
  }

  /**
   * Uploads a file onto the server from an HTML form and return its width and height.
   * @param string $fileID
   * @return array|bool containing new media details as 'filename', 'width', and 'height', false on error
   * @throws directory_not_found
   * @throws getid3_exception
   */
  public static function uploadFile($fileID) {

    $file_width = 0;
    $file_height = 0;

    // Check we have a temp file to work with.
    if (!key_exists($fileID, $_FILES)) {
      return false;
    }

    // Check temp file for Undefined, Multiple Files, Corruption Attack
    if (!isset($_FILES[$fileID]['error']) or is_array($_FILES[$fileID]['error'])) {
      return false;
    }

    // Check temp file 'error' property
    switch ($_FILES[$fileID]['error']) {
      case UPLOAD_ERR_OK:
        break;
      default:
        return false;
    }

    // Check temp file 'size' property against max file size limit set by rogo.
    $config = Config::get_instance();
    if ($_FILES[$fileID]['size'] > $config->get_setting('core', 'system_maxmediasize')) {
      return false;
    }

    $bad_file = true;   // Default safe.
    $mediadirectory = rogo_directory::get_directory('media');
    $filename = strtolower($_FILES[$fileID]['name']);
    $unique_name = self::unique_filename($filename);
    $fullpath = $mediadirectory->fullpath($unique_name);

    $permitted = self::get_permitted();

    $filetype = questiondata::FILE;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (key_exists($ext, $permitted)) {
      $bad_file = false;
      $filetype = self::SUPPORTED[$ext];
    }

    if ($bad_file or $unique_name == 'none' or $unique_name == '') {
      return false;
    }

    if (!move_uploaded_file($_FILES[$fileID]['tmp_name'], $fullpath)) {
      echo uploadError($_FILES[$fileID]['error']);
      return false;
    }

    chmod($fullpath, 0664);
    $getID3 = new getID3;
    $file_info = $getID3->analyze($fullpath);

    // File type checks.
    switch ($filetype) {
      case questiondata::DOC:
        $file_width = '100%';
        $file_height = '350';
        break;
      case questiondata::IMAGE:
        $identifier_size = GetImageSize($fullpath);
        $file_width = $identifier_size[0];
        $file_height = $identifier_size[1];
        if ($file_width == 0 or $file_height == 0) {
          $bad_file = true;
        }
        break;
      case questiondata::MOVIE:
        $file_width = $file_info['video']['resolution_x'];
        $file_height = $file_info['video']['resolution_y'];
        if ($file_width == 0 or $file_height == 0) {
          $bad_file = true;
        }
        break;
      case questiondata::AUDIO:
      case questiondata::MP3:
        if (!isset($file_info['playtime_seconds'])) {
          $bad_file = true;
        } elseif ($file_info['playtime_seconds'] == 0) {
          $bad_file = true;
        }
        break;
      case questiondata::ARCHIVE:
        self::process_archive($fullpath);
        break;
      default:
        break;
    }

    // MIME specific checks.
    switch ($_FILES[$fileID]['type']) {
      case 'application/msword':
      case 'application/vnd.ms-powerpoint':
      case 'application/vnd.ms-excel':
        if (!isset($file_info['fileformat']) or $file_info['fileformat'] != 'msoffice') {
          $bad_file = true;
        }
        break;
      case 'application/pdf':
        if (!isset($file_info['fileformat']) or $file_info['fileformat'] != 'pdf') {
          $bad_file = true;
        }
        break;
      default:
        break;
    }

    if ($bad_file) {
      self::deleteMedia($unique_name);    // Remove the file from the server.
      return array('filename' => '', 'width' => 0, 'height' => 0, 'rejected_file' => $unique_name);
    }

    return array('filename' => $unique_name, 'width' => $file_width, 'height' => $file_height, 'rejected_file' => false);
  }

  /**
   * @param string $filename file to be deleted
   * @return bool
   * @throws directory_not_found
   */
  public static function deleteMedia($filename) {
    $mediadirectory = rogo_directory::get_directory('media');
    $filetype = questiondata::FILE;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (key_exists($ext, self::SUPPORTED)) {
      $filetype = self::SUPPORTED[$ext];
    }
    $file = $mediadirectory->fullpath($filename);

    if ($filename == '' or !file_exists($file)) {
      return false;
    }

    @!unlink($file);

    // If media was an achive file, we should look to delete the extracted directory that contained its contents.
    if ($filetype === questiondata::ARCHIVE) {
      $info = pathinfo($file);
      $mediadir = $mediadirectory->location() . $info['filename'];
      if (file_exists($mediadir)) {
        $mediadirectory->clear_subdir($info['filename']);
        rmdir($mediadir);
      }
    }
    return true;
  }

  /**
   * Get list of permitted file types.
   * @return array
   */
  public static function get_permitted() {
    $config = Config::get_instance();
    $permitted = array();
    foreach ($config->get_setting('core', 'system_mediatypes') as $name => $value) {
      if ($value == true) {
        $permitted[$name] = self::SUPPORTED[$name];
      }
    }
    return $permitted;
  }

  /**
   * Extract files in archive ignoring files not supported by Rogo.
   * @param $fullpath path to archive
   * @return bool
   * @throws directory_not_found
   */
  public static function process_archive($fullpath) {
    $config = Config::get_instance();
    $info = pathinfo($fullpath);
    $unique = $info['filename'];
    $tmpdirectory = $config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . $unique;
    $mediadirectory = rogo_directory::get_directory('media');
    $permitted = self::get_permitted();
    $zip = new ZipArchive;
    $res = $zip->open($fullpath);
    if ($res === true) {
      mkdir($mediadirectory->location() . $unique);
      $zip->extractTo($tmpdirectory);
      for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        $filename = $stat['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (key_exists($ext, $permitted)) {
          copy($tmpdirectory . DIRECTORY_SEPARATOR . $filename, $mediadirectory->location() . $unique . DIRECTORY_SEPARATOR . $filename);
        }
        unlink($tmpdirectory . DIRECTORY_SEPARATOR . $filename);
      }
      $zip->close();
      rmdir($tmpdirectory);
    } else {
      return false;
    }
    return true;
  }
}