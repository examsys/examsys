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
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require 'load_config.php';

include_once $cfg_web_root . 'tools/getid3/getid3.php';

/**
 * Function takes a filename with the width and height and returns appropriate HTML to display the media type.
 *
 * @param mixed $filename
 * @param mixed $width
 * @param mixed $height
 * @param mixed $imageid
 *
 */
function display_media($filename, $width, $height, $border_color, $imageid=-1, $locked=false) {

  $configObject = Config::get_instance();

  $mediadirectory = rogo_directory::get_directory('media');
  $html = '';

  $fn_parts = pathinfo($filename);

  if ($imageid > -1) $html .= "<div id=\"media$imageid\" class=\"media\">";
  // Is the file an image or something else (e.g. RasMol)?
  if (!array_key_exists('extension', $fn_parts)) {
    $html .= "<embed src=\"" . $mediadirectory->url($filename) . "\" width=\"$width\" height=\"$height\" border=\"1\" alt=\"Data File\" wmode=\"opaque\"></embed>";
  } elseif (array_key_exists('extension', $fn_parts) and in_array(strtolower($fn_parts['extension']), array('gif', 'jpg', 'jpeg', 'png'))) {
    if ($border_color == '') {
      $html .= "<img src=\"" . $mediadirectory->url($filename) . "\" width=\"$width\" height=\"$height\" alt=\"Image\" />";
    } else {
      $html .= "<img src=\"" . $mediadirectory->url($filename) . "\" width=\"$width\" height=\"$height\" alt=\"Image\" style=\"border: 1px solid $border_color\" />";
    }
  } elseif (in_array($fn_parts['extension'], array('wav', 'wma', 'mid'))) {
    $html .= "<img src=\"audio_icon_32.gif\" width=\"32\" height=\"32\" alt=\"Audio File\" /><a href=\"" . $mediadirectory->url($filename) . "\">Audio Clip</a>";
  } elseif (in_array($fn_parts['extension'], array('doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf'))) {
    $html .= "<iframe src=\"" . $mediadirectory->url($filename) . "\" width=\"$width\" height=\"$height\" align=\"center\">Your browser does not support iframes!</iframe>";
  } elseif ($fn_parts['extension'] == 'flv') {
    if ($width == 0 or $height == 0) {
      $width = 320;
      $height = 260;
    }
    $html .= <<< OBJEMBED
<object type="application/x-shockwave-flash" data="{$configObject->get('cfg_root_path')}/paper/player_flv_maxi.swf" width="{$width}" height="{$height}">
<param name="movie" value="{$configObject->get('cfg_root_path')}/paper/player_flv_maxi.swf" />
<param name="FlashVars" value="flv={$mediadirectory->url($filename, false, false, true)}&amp;buffermessage=&amp;showplayer=always&amp;showvolume=1&amp;showstop=1&amp;showtime=1&amp;margin=1" />
</object>
OBJEMBED;

  } elseif ($fn_parts['extension'] == 'mp3') {     // Embed MP3 using HTML5 audio tag.

    if (strpos($_SERVER['PHP_SELF'],'/edit/') !== false or strpos($_SERVER['PHP_SELF'],'/add/') !== false) {  // Display filename if add or edit script
      $html .= $filename . '&nbsp;';
    }

    $html .= <<< OBJEMBED
<audio src="{$mediadirectory->url($filename)}" controls>
</object>
</audio>
OBJEMBED;
  } elseif ($fn_parts['extension'] == 'avi' or $fn_parts['extension'] == 'wmv') {
    $html .= "<embed src=\"" . $mediadirectory->url($filename) . "\" width=\"$width\" height=\"$height\" border=\"1\" autoplay=\"true\" loop=\"false\" alt=\"Data File\" wmode=\"opaque\"></embed>";
  } else {
    $html .= "<embed src=\"" . $mediadirectory->url($filename) . "\" width=\"$width\" height=\"$height\" border=\"1\" alt=\"Data File\" wmode=\"opaque\"></embed>";
  }
  if ($imageid > -1 and !$locked) {
    $html .= <<< DELETE
<a href="#" class="media-delete" rel="{$imageid}"><img class="align-bottom" src="{$configObject->get('cfg_root_path')}/artwork/red_cross.png" width="16" height="16" alt="Delete" /></a></div>
<label class="hide"><input type="checkbox" id="delete_media{$imageid}" name="delete_media{$imageid}" /> Delete image {$imageid}</label>
<input type="hidden" id="existing_media{$imageid}" name="existing_media{$imageid}" value="$filename" />

DELETE;
  }

  return $html;
}

/**
 * This is function returns a unique filename so that files are not overwritten on the server.
 * The filename is initially seeded with the current UNIX time in seconds plus the original
 * file extension.
 *
 * @param mixed $filename
 *
 */
function unique_filename($filename) {
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
 *
 * @param mixed $fileID
 * @return array containing new media details as 'filename', 'width', and 'height'
 *
 */
function uploadFile($fileID) {

  $file_width = 0;
  $file_height = 0;

  if (!isset($_FILES[$fileID])) {
    return '';
  }

  $bad_file = true;   // Default safe.
  $mediadirectory = rogo_directory::get_directory('media');
  
  $filename = strtolower($_FILES[$fileID]['name']);
  $unique_name = unique_filename($filename);
  $fullpath = $mediadirectory->fullpath($unique_name);

  $permitted = array('.gif', '.jpg', '.jpeg', '.png', '.doc', '.docx', '.ppt', '.pptx', '.xls', '.xlsx', '.pdf', '.avi', '.mpg', '.mpeg', '.mov', '.pdb', '.mp3', '.mid', '.wav', '.ram');
  foreach ($permitted as $extension) {
    if (preg_match("/$extension\$/i", $unique_name)) {
      $bad_file = false;
    }
  }

  if ( $bad_file or $unique_name == 'none' or $unique_name == '') {
    return false;
  }

  if (!move_uploaded_file($_FILES[$fileID]['tmp_name'], $fullpath)) {
    echo uploadError($_FILES[$fileID]['error']);
    return false;
  }

  chmod($fullpath, 0664);
  $getID3 = new getID3;
  $file_info = $getID3->analyze($fullpath);

  switch ($_FILES[$fileID]['type']) {
    case 'application/msword':
    case 'application/vnd.ms-powerpoint':
    case 'application/vnd.ms-excel':
      if (!isset($file_info['fileformat'])) {
        $bad_file = true;
      } elseif ($file_info['fileformat'] != 'msoffice') {
        $bad_file = true;
      }
      $file_width = '100%';
      $file_height = '350';
      break;
    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
    case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
    case 'application/x-mspublisher':
    case 'text/plain':
      $file_width = '100%';
      $file_height = '350';
      break;
    case 'application/pdf':
      if (!isset($file_info['fileformat'])) {
        $bad_file = true;
      } elseif ($file_info['fileformat'] != 'pdf') {
        $bad_file = true;
      }
      $file_width = '100%';
      $file_height = '350';
      break;
    case 'image/gif':
    case 'image/jpg':
    case 'image/jpeg':
    case 'image/pjpeg':
    case 'image/x-png':
    case 'image/png':
      // Image formats
      $identifier_size = GetImageSize($fullpath);
      $file_width = $identifier_size[0];
      $file_height = $identifier_size[1];
      if ($file_width == 0 or $file_height == 0) {
        $bad_file = true;
      }
      break;
    case 'application/x-shockwave-flash':
      //ShockWave
      $identifier_size = GetImageSize($fullpath);
      $file_width = $identifier_size[0];
      $file_height = $identifier_size[1];
      if ($file_width == 0 or $file_height == 0) {
        $bad_file = true;
      }
      break;
    case 'video/mpeg':
    case 'video/x-ms-wmv':
    case 'video/avi':
    case 'video/quicktime':
    case 'video/x-flv':
      $file_width = $file_info['video']['resolution_x'];
      $file_height = $file_info['video']['resolution_y'];
      if ($file_width == 0 or $file_height == 0) {
        $bad_file = true;
      }
      break;
    case 'audio/wav':
    case 'audio/x-wav':
    case 'audio/mid':
    case 'audio/x-pn-realaudio':
    case 'audio/mpeg3':
    case 'audio/x-mpeg-3':
      if (!isset($file_info['playtime_seconds'])) {
        $bad_file = true;
      } elseif ($file_info['playtime_seconds'] == 0) {
        $bad_file = true;
      }
      break;
    default:
      // Can't get mimetype, trying extension types
      $path_parts = pathinfo($fullpath);
      switch (strtolower($path_parts['extension'])) {
        case 'mpg':
        case 'mpeg':
        case 'avi':
        case 'mov':
        case 'flv':
          $file_width = $file_info['video']['resolution_x'];
          $file_height = $file_info['video']['resolution_y'];
          if ($file_width == 0 or $file_height == 0) {
            $bad_file = true;
          }
          break;
        case 'wav':
        case 'mid':
        case 'ram':
        case 'mp3':
          if (!isset($file_info['playtime_seconds'])) {
            $bad_file = true;
          } elseif ($file_info['playtime_seconds'] == 0) {
            $bad_file = true;
          }
          break;
        case 'docx':
        case 'pptx':
        case 'xlsx':
          $file_width = '100%';
          $file_height = '350';
          break;
        default:
          $file_width = 0;
          $file_height = 0;
          break;
      }
      break;
  }

  if ($bad_file) {
    deleteMedia($unique_name);    // Remove the file from the server.
    return array('filename'=>'', 'width'=>0, 'height'=>0, 'rejected_file'=>$unique_name);
  }

  return array('filename'=>$unique_name, 'width'=>$file_width, 'height'=>$file_height, 'rejected_file'=>false);
}

function deleteMedia( $filename ) {
  $mediadirectory = rogo_directory::get_directory('media');
  $file = $mediadirectory->fullpath($filename);

  if ( $filename == '' or !file_exists( $file ) ) {
     return false;
  }

  if ( @!unlink( $file ) ) {
    //display_error("Failed to delete " . $filename, '', false, true);
  }

  return true;
}
?>