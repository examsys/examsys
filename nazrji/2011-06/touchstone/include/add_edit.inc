<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* function used by the add question scripts
* 
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once 'errors.inc';

/**
 * Takes in an HTML string and trys to remove as much superfluous formatting as possible.
 *
 * @param mixed $sHTML
 *
 */
function clearMSOtags($sHTML) {

  if (trim($sHTML) == '<p>&nbsp;</p>' or trim($sHTML) == '<p></p>' or trim($sHTML) == '<div>&nbsp;</div>' or trim($sHTML) == '<div></div>' or trim($sHTML) == '<br />') {
    $sHTML = '';
  } else {
    if (strpos($sHTML, 'mso-') !== false or strpos($sHTML, 'MsoNormal') !== false or strpos($sHTML, '<o:p>') !== false) {
      $allowedTags = '<div><b><i><strong><em><sup><sub><table><tr><td><tbody><th>';
      $sHTML = strip_tags($sHTML, $allowedTags);
    }
    // Further processing.
    $sHTML = str_ireplace('PADDING-TOP: 0cm', '', $sHTML);
    $sHTML = str_ireplace('PADDING-BOTTOM: 0cm', '', $sHTML);
    $sHTML = str_ireplace('BACKGROUND-COLOR: transparent;', '', $sHTML);
    $sHTML = str_ireplace('<o:p></o:p>', '', $sHTML);
    $sHTML = str_ireplace('<strong></strong>', '', $sHTML);
    $sHTML = str_ireplace('COLOR: black', '', $sHTML);

    // Convert strange codes.
    $sHTML = str_ireplace('%u2013', '-', $sHTML);
    $sHTML = str_ireplace('%u2018', "'", $sHTML);
    $sHTML = str_ireplace('%u2019', "'", $sHTML);

    // Some regular expressions
    $sHTML = preg_replace('/FONT-SIZE: [0-9]+pt/','',$sHTML);
    $sHTML = preg_replace('/FONT-FAMILY: [\'a-zA-Z 0-9,]*/','',$sHTML);
    $sHTML = preg_replace('/FONT: [0-9]+pt/','',$sHTML);
    $sHTML = preg_replace('/LINE-HEIGHT: [0-9]+%/','',$sHTML);

    $sHTML = preg_replace('/lang="[\-A-Za-z]*"/', '', $sHTML);
    $sHTML = preg_replace('/mso-[\-A-Za-z]+: [_\. \'#\-A-Za-z0-9,]+/','',$sHTML);

    // Do at end.
    $sHTML = str_ireplace('  ', ' ', $sHTML);
    $sHTML = str_ireplace(' ;', '', $sHTML);
    $sHTML = str_ireplace('style="; ', 'style="', $sHTML);
    $sHTML = str_ireplace('style=""', '', $sHTML);
    $sHTML = str_ireplace('style=" "', '', $sHTML);
    $sHTML = str_ireplace('<span >', '<span>', $sHTML);
    $sHTML = str_ireplace('; ">', '">', $sHTML);
  }
  return $sHTML;
}

function insert_into_options($o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks) {
  global $mysqli;
  if ($result = $mysqli->prepare("INSERT INTO options VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)")) {
    $result->bind_param('isssssssd', $o_id, $option_text, $o_media, $o_media_width, $o_media_height, $feedback_right, $feedback_wrong, $correct, $marks);
    $result->execute();
    $result->close();
  } else {
    display_error("Option Add Error",$mysqli->error);
  }
  return $mysqli->insert_id;
}

function wysiwyg_editor($name, $replace, $content='', $width=700, $height=70) {
  global $cfg_editor_name;

  $html = '';
  
  if ($cfg_editor_name == 'tinymce') {
    $html .= "<textarea class=\"mceEditor\" id=\"$replace\" name=\"$replace\" style=\"width:" . $width . "px; height:" . $height . "px\">$content</textarea>\n";
  } else {
    //If no editor is specified, output plain textarea.
    $height = $height - 6;
    $html .= "<textarea name=\"$replace\" style=\"width:" . $width . "px; height:" . $height . "px\">$content</textarea>\n";
  }
  
  return $html;
}

function option_order($selected = '') {
  $items = array('Display Order','Alphabetic','Random');
  
  $html = "<select name=\"option_order\">\n";
  foreach ($items as $item) {
    if ($selected == strtolower($item)) {
      $html .= "<option value=\"" . strtolower($item) . "\" selected>$item</option>\n";
    } else {
      $html .= "<option value=\"" . strtolower($item) . "\">$item</option>\n";
    }
  }
  $html .= "</select>\n";
  
  if ($selected != '') {
    $html .= '<input type="hidden" name="old_option_order" value="' . $selected . '" />';
  }
  
  return $html;
}
