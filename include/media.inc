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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

/**
 * Function takes a filename with the width and height and returns appropriate HTML to display the media type.
 *
 * @param mixed $filename 
 * @param mixed $width 
 * @param mixed $height 
 * @param mixed $imageid 
 *
 */
function display_media($filename, $width, $height, $imageid=-1) {
  $html = '';
  
  $fn_parts = pathinfo($filename);
  
  if ($imageid > -1) $html .= "<div id=\"media$imageid\" class=\"media\">";
  // Is the file an image or something else (e.g. RasMol)?
  if (in_array($fn_parts['extension'], array('gif', 'jpg', 'jpeg', 'png'))) {
    $html .= "<img src=\"/media/$filename\" width=\"$width\" height=\"$height\" border=\"0\" alt=\"Image\" style=\"vertical-align:text-top\" />";
  } elseif (in_array($fn_parts['extension'], array('wav', 'wma', 'mid'))) {
    $html .= "<img src=\"audio_icon_32.gif\" width=\"32\" height=\"32\" alt=\"Audio File\" /><a href=\"/media/$filename\">Audio Clip</a>";
  } elseif (in_array($fn_parts['extension'], array('doc', 'ppt', 'xls', 'pdf'))) {
    $html .= "<iframe src=\"/media/$filename\" width=\"$width\" height=\"$height\" align=\"center\">Your browser does not support iframes!</iframe>";
  } elseif ($fn_parts['extension'] == 'flv') {
    if ($width == 0 or $height == 0) {
      $width = 320;
      $height = 260;
    }
    $html .= <<< OBJEMBED
<object type="application/x-shockwave-flash" data="/paper/player_flv_maxi.swf" width="{$width}" height="{$height}">
<param name="movie" value="/paper/player_flv_maxi.swf" />
<param name="FlashVars" value="flv=/media/{$filename}&amp;buffermessage=&amp;showplayer=always&amp;showvolume=1&amp;showstop=1&amp;showtime=1&amp;margin=1" />
</object>  
OBJEMBED;
    
  } elseif ($fn_parts['extension'] == 'mp3') {     //Embed MP3 using a Flash plugin
    
    if (strpos($_SERVER['PHP_SELF'],'/edit/') !== false or strpos($_SERVER['PHP_SELF'],'/add/') !== false) {  // Display filename if add or edit script
      $html .= $filename . '&nbsp;';
    }
    
    $html .= <<< OBJEMBED
<object type="application/x-shockwave-flash" data="/paper/player_mp3_maxi.swf" width="200" height="20">
<param name="wmode" value="transparent" />
<param name="movie" value="/paper/player_mp3_maxi.swf" />
<param name="FlashVars" value="mp3=/media/{$filename}&amp;showstop=1&amp;showvolume=1&amp;bgcolor1=ffa50b&amp;bgcolor2=d07600" />
</object>  
OBJEMBED;
  } elseif ($fn_parts['extension'] == 'avi' or $fn_parts['extension'] == 'wmv') {
    $html .= "<embed src=\"/media/$filename\" width=\"$width\" height=\"$height\" border=\"1\" autoplay=\"true\" loop=\"false\" alt=\"Data File\" wmode=\"opaque\"></embed>";
  } else {
    $html .= "<embed src=\"/media/$filename\" width=\"$width\" height=\"$height\" border=\"1\" alt=\"Data File\" wmode=\"opaque\"></embed>";
  }
  if ($imageid > -1) {
    $html .= <<< DELETE
<a href="#" class="media-delete" rel="{$imageid}"><img class="align-bottom" src="/artwork/red_cross.png" width="16" height="16" alt="Delete" /></a></div>
<label class="hide"><input type="checkbox" id="delete_media{$imageid}" name="delete_media{$imageid}" /> Delete image {$imageid}</label>
DELETE;
  }

  return $html;
}

/**
 * This is function returns a unique filename so that files are not overwritten on the server.
 * Only a-z, 0-9 and a few other characters are kept in the filename.
 *
 * @param mixed $filename 
 *
 */
function unique_filename($filename, $strip = TRUE) {
  global $cfg_web_root;
  if ($filename == '') return '';
  $i = 1;
  if($strip == TRUE) {
    $filename = strtolower($filename);
    $filename = preg_replace("/[^a-z0-9_\-\(\)\.]/","",$filename);
  }

  while(file_exists($cfg_web_root . "media/$filename")) {
    if(!isset($ext)) {
      $tmp = explode('.',$filename);
      $n = count($tmp);
      $ext = $tmp[$n - 1];
      $tmp[$n - 1] = '';
      $name = implode('',$tmp);
    }
    $filename = $name . "$i." . $ext;
    $i++;
  }
  return $filename;
}

/**
 * Uploads a file onto the server from an HTML form and return its width and height.
 *
 * @param mixed $fileID
 * @return array containing new media details as 'filename', 'width', and 'height'
 *
 */
function uploadFile($fileID) {
  global $cfg_web_root;
  
  $file_width = 0;
  $file_height = 0;
  
  if(!isset($_FILES[$fileID])) {
    return '';
  }
  
  $unique_name = unique_filename($_FILES[$fileID]['name']);
  if ($unique_name != 'none' and $unique_name != '') {
    if (!move_uploaded_file($_FILES[$fileID]['tmp_name'], $cfg_web_root . "media/$unique_name"))  {
      echo uploadError($_FILES[$fileID]['error']);
      return false;
    } else {
      chmod($cfg_web_root . "media/$unique_name", 0664);
      switch ($_FILES[$fileID]['type']) {
        case 'application/msword':
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
        case 'application/vnd.ms-powerpoint':
        case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
        case 'application/vnd.ms-excel':
        case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
        case 'application/x-mspublisher':
        case 'application/pdf':
        case 'text/plain':
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
          $identifier_size = GetImageSize($cfg_web_root . "media/$unique_name");
          $file_width = $identifier_size[0];
          $file_height = $identifier_size[1];
          break;
        case 'application/x-shockwave-flash':
          //ShockWave
          $identifier_size = GetImageSize($cfg_web_root . "media/$unique_name");
          $file_width = $identifier_size[0];
          $file_height = $identifier_size[1];
          break;
        case 'video/mpeg':
        case 'video/x-ms-wmv':
        case 'video/avi':
        case 'video/quicktime':
        case 'video/x-flv':
          $getID3 = new getID3;
          $file_info = $getID3->analyze($cfg_web_root . "media/$unique_name");
          $file_width = $file_info['video']['resolution_x'];
          $file_height = $file_info['video']['resolution_y'];
          break;
        default:
          // Can't get mimetype, trying extension types
          $path_parts = pathinfo($cfg_web_root . "media/$unique_name");
          switch (strtolower($path_parts['extension'])) {
            case 'mpg':
            case 'mpeg':
            case 'avi':
            case 'mov':
            case 'flv':
              $getID3 = new getID3;
              $file_info = $getID3->analyze($cfg_web_root . "media/$unique_name");
              $file_width = $file_info['video']['resolution_x'];
              $file_height = $file_info['video']['resolution_y'];
              break;
            default:
              $file_width = '0';
              $file_height = '0';
              break;
          }
          break;
      }
    }
  }
  return array('filename' => $unique_name, 'width' => $file_width, 'height' => $file_height);
}

function deleteMedia($filename) {
  global $cfg_web_root;
  
  if ($filename != '') {
    if (@!unlink($cfg_web_root . 'media/' . $filename)) {
      //display_error("Failed to delete " . $filename, '', false, true);
    }
  }
}
?>