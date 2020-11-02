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
class media_handler
{

    /**
     * Supported media types.
     * @var array
     */
    public const SUPPORTED = array(
    'gif' => questiondata::IMAGE,
    'jpg' => questiondata::IMAGE,
    'jpeg' => questiondata::IMAGE,
    'png' => questiondata::IMAGE,
    'doc' => questiondata::DOC,
    'docx' => questiondata::DOC,
    'ppt' => questiondata::DOC,
    'pptx' => questiondata::DOC,
    'xls' => questiondata::DOC,
    'xlsx' => questiondata::DOC,
    'pdf' => questiondata::DOC,
    'avi' => questiondata::MOVIE,
    'mpg' => questiondata::MOVIE,
    'mpeg' => questiondata::MOVIE,
    'mov' => questiondata::MOVIE,
    'mp3' => questiondata::HTML5AUDIO,
    'mp4' => questiondata::HTML5VIDEO,
    'mid' => questiondata::AUDIO,
    'wav' => questiondata::HTML5AUDIO,
    'ram' => questiondata::AUDIO,
    'pdb' => questiondata::THREED,
    'ply' => questiondata::THREED,
    'obj' => questiondata::THREED,
    'mtl' => questiondata::THREED,
    'dds' => questiondata::THREED,
    'zip' => questiondata::ARCHIVE,
    );

    /**
     * Clean the alternate text so it is suitable for display
     * @param ?string $alt the alternate text
     * @return string
     */
    private static function cleanAltText(?string $alt): string
    {
        return \param::clean($alt, \param::TEXT);
    }

    /**
     * This is function returns a unique filename so that files are not overwritten on the server.
     * The filename is initially seeded with the current UNIX time in seconds plus the original
     * file extension.
     * @param string $filename
     * @return string
     * @throws directory_not_found
     */
    public static function unique_filename($filename)
    {
        $mediadirectory = rogo_directory::get_directory('media');

        $ext = mb_substr($filename, mb_strrpos($filename, '.'));
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
     * @param ?string $alt - alternate text for media
     * @return array|bool containing new media details as 'filename', 'width', 'height', 'alt',
     *         'owner' and 'rejection state' or false on error
     * @throws directory_not_found
     * @throws getid3_exception
     */
    public static function uploadFile(string $fileID, ?string $alt = '')
    {
        $userObj = UserObject::get_instance();
        $owner = $userObj->get_user_ID();

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
        $filename = mb_strtolower($_FILES[$fileID]['name']);
        $unique_name = self::unique_filename($filename);
        $fullpath = $mediadirectory->fullpath($unique_name);

        $permitted = self::get_permitted();

        $filetype = questiondata::FILE;
        $ext = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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
        $getID3 = new getID3();
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
            case questiondata::HTML5VIDEO:
                $file_width = $file_info['video']['resolution_x'];
                $file_height = $file_info['video']['resolution_y'];
                if ($file_width == 0 or $file_height == 0) {
                    $bad_file = true;
                }
                break;
            case questiondata::AUDIO:
            case questiondata::HTML5AUDIO:
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
            // Remove the file from the server.
            self::deleteMedia($unique_name);
            return array(
                'filename' => '',
                'width' => 0,
                'height' => 0,
                'alt' => '',
                'owner' => -1,
                'rejected_file' => $unique_name
            );
        }

        return array(
            'filename' => $unique_name,
            'width' => $file_width,
            'height' => $file_height,
            'alt' => $alt,
            'owner' => $owner,
            'rejected_file' => false
        );
    }

    /**
     * @param string $filename file to be deleted
     * @return bool
     * @throws directory_not_found
     */
    public static function deleteMedia($filename)
    {
        $mediadirectory = rogo_directory::get_directory('media');
        $filetype = questiondata::FILE;
        $ext = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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
    public static function get_permitted()
    {
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
    public static function process_archive($fullpath)
    {
        $config = Config::get_instance();
        $info = pathinfo($fullpath);
        $unique = $info['filename'];
        $tmpdirectory = $config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . $unique;
        $mediadirectory = rogo_directory::get_directory('media');
        $permitted = self::get_permitted();
        $zip = new ZipArchive();
        $res = $zip->open($fullpath);
        if ($res === true) {
            mkdir($mediadirectory->location() . $unique);
            $zip->extractTo($tmpdirectory);
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $filename = $stat['name'];
                $ext = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
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

    /**
     * Insert media into database
     * @param string $source the file
     * @param int $width width of media
     * @param int $height height of media
     * @param ?string $alt alternate text for media
     * @param int $owner owner of the media
     * @return int
     */
    public static function insertMedia(string $source, int $width, int $height, ?string $alt, int $owner): int
    {
        $alt = self::cleanAltText($alt);
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('ssssi'),
            array(
                &$source,
                &$width,
                &$height,
                &$alt,
                &$owner
            )
        );
        $sql = $db->prepare('INSERT INTO media (source, width, height, alt, ownerid) VALUES (?, ?, ?, ?, ?)');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $id = -1;
        } else {
            $id = $db->insert_id;
        }
        $sql->close();
        return $id;
    }

    /**
     * Insert question-media link into database
     * @param int $mediaid the media identifier
     * @param int $questionid the question identifier
     * @param int $num the medias order number in the question (used by extmatch)
     * @return bool
     */
    public static function linkQuestionToMedia(int $mediaid, int $questionid, int $num): bool
    {
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('iii'),
            array(
                &$mediaid,
                &$questionid,
                &$num
            )
        );
        $ok = true;
        $sql = $db->prepare('INSERT INTO questions_media (mediaid, qid, num) VALUES (?, ?, ?)');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $ok = false;
        }
        $sql->close();
        return $ok;
    }

    /**
     * Update media in database
     * @param int $id media indentifier
     * @param string $source the file
     * @param int $width width of media
     * @param int $height height of media
     * @param string $alt alternate text for media
     * @param int $owner owner of the media
     * @return bool
     */
    public static function updateMedia(int $id, string $source, int $width, int $height, string $alt, int $owner): bool
    {
        $alt = self::cleanAltText($alt);
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('ssssii'),
            array(
                &$source,
                &$width,
                &$height,
                &$alt,
                &$owner,
                &$id
            )
        );
        $ok = true;
        $sql = $db->prepare('UPDATE media SET source = ?, width = ?, height = ?, alt = ?, ownerid = ? WHERE id = ?');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $ok = false;
        }
        $sql->close();
        return $ok;
    }

    /**
     * Update media alternate text in database
     * @param int $id media indentifier
     * @param string $alt alternate text for media
     * @return bool
     */
    public static function updateMediaAltText(int $id, string $alt): bool
    {
        $alt = self::cleanAltText($alt);
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('si'),
            array(
                &$alt,
                &$id
            )
        );
        $ok = true;
        $sql = $db->prepare('UPDATE media SET alt = ? WHERE id = ?');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $ok = false;
        }
        $sql->close();
        return $ok;
    }

    /**
     * Remove media from database
     * @param int $id media indentifier
     * @return bool
     */
    public static function removeMedia(int $id): bool
    {
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('i'),
            array(
                &$id
            )
        );
        $ok = true;
        $sql = $db->prepare('DELETE FROM media WHERE id = ?');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $ok = false;
        }
        $sql->close();
        return $ok;
    }

    /**
     * Remove questions media link from database
     * @param int $id media indentifier
     * @param int $questionid question indentifier
     * @return bool
     */
    public static function unlinkQuestionFromMedia(int $id, int $questionid): bool
    {
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('ii'),
            array(
                &$id,
                &$questionid
            )
        );
        $ok = true;
        $sql = $db->prepare('DELETE FROM questions_media WHERE mediaid = ? AND qid = ?');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $ok = false;
        }
        $sql->close();
        return $ok;
    }

    /**
     * Insert option-media link into database
     * @param int $mediaid the media identifier
     * @param int $optionid the option identifier
     * @return bool
     */
    public static function linkOptionToMedia(int $mediaid, int $optionid): bool
    {
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('ii'),
            array(
                &$mediaid,
                &$optionid
            )
        );
        $ok = true;
        $sql = $db->prepare('INSERT INTO options_media (mediaid, oid) VALUES (?, ?)');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $ok = false;
        }
        $sql->close();
        return $ok;
    }

    /**
     * Remove options media link from database
     * @param int $id media indentifier
     * @param int $optionid option indentifier
     * @return bool
     */
    public static function unlinkOptionFromMedia(int $id, int $optionid): bool
    {
        $db = Config::get_instance()->db;
        $params = array_merge(
            array('ii'),
            array(
                &$id,
                &$optionid
            )
        );
        $ok = true;
        $sql = $db->prepare('DELETE FROM options_media WHERE mediaid = ? AND oid = ?');
        call_user_func_array(array($sql, 'bind_param'), $params);
        $sql->execute();
        if ($db->error) {
            $ok = false;
        }
        $sql->close();
        return $ok;
    }
}
