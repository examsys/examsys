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

function wysiwyg_editor($name, $replace, $content='', $width=695, $height=70) {
  global $cfg_editor_name;

  $html = '';
  
  if ($cfg_editor_name == 'tinymce') {
    $html .= "<textarea class=\"mceEditor\" id=\"$replace\" name=\"$replace\" style=\"width:" . $width . "px; height:" . $height . "px; margin: 0\">$content</textarea>\n";
  } else {
    //If no editor is specified, output plain textarea.
    $height = $height - 6;
    $html .= "<textarea name=\"$replace\" style=\"width:" . $width . "px; height:" . $height . "px\">$content</textarea>\n";
  }
  
  return $html;
}

function option_order($selected = '') {
  $items = array('Display Order','Alphabetic','Random');
  
  $html = "<select id=\"option_order\" name=\"option_order\">\n";
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

function render_changes($changes) {
  $i = 0;
  if (count($changes) > 0) {
    $html = <<< HTML
        <table class="data">
          <thead>
            <tr>
              <th class="data-small">Date</th>
              <th class="data-small">Action</th>
              <th>Section</th>
              <th class="data-small">Old</th>
              <th class="data-small">New</th>
              <th class="data-small">Editor</th>
            </tr>
          </thead>
          <tbody>

HTML;

    foreach($changes as $change) {
      $alt = ($i++ % 2 == 1) ? ' class="alt"' : '';
      $html .= <<< HTML
            <tr{$alt}>
              <td>{$change['date']}</td>
              <td>{$change['action']}</td>
              <td>{$change['section']}</td>
              <td>{$change['old']}</td>
              <td>{$change['new']}</td>
              <td>{$change['user']}</td>
            </tr>

HTML;
    }
  
    $html .= <<< HTML

  				</tbody>
        </table>
        
HTML;
  } else {
    // Shouldn't happen if we're recording question creation as a change
    $html = <<< HTML
        <p>No changes recorded for this question</p>

HTML;
  
  }
  
  return $html;
}

function render_comments($comments) {
  if(count($comments) > 0) {
    $html = <<< HTML
        <table class="data" summary="Reviewer's comments">
          <thead>
            <tr>
              <th class="data-tiny"></th>
              <th class="data-medium">Reviewer</th>
              <th>Comments</th>
              <th class="data-small">Action&nbsp;Taken</th>
              <th>Internal Response</th>
            </tr>
        </thead>
        <tbody>

HTML;

    $i = 0;
    foreach ($comments as $id => $comment) {
      switch ($comment['category']) {
        case 1:
          $status = 'OK';
          break;
        case 2:
          $status = 'Minor';
          break;
        case 3:
          $status = 'Major';
          break;
      }
      $img_status = strtolower($status);
      
      $alt = ($i % 2 == 0) ? ' class="alt"' : '';
      $comment_str = ($comment['comment'] == '') ? 'No comments' : nl2br($comment['comment']);
      
      $html .= <<< HTML
  					<tr{$alt}>
              <td><img src="../../artwork/{$img_status}_comment.png" width="16" height="16" alt="{$status}" /></td>
              <td>{$comment['name']}<br /><span class="note">{$comment['type']}</span></td>
              <td><span class="note">{$comment_str}</span></td>
              <td>
                <select name="actions[]">

HTML;
    
      $actions = array('Not actioned', 'Read - disagree', 'Read - actioned');
      
      foreach ($actions as $action) {
        $sel = ($comment['action'] == $action) ? ' selected="selected"' : '';
        $html .= <<< HTML
      						<option value="{$action}"{$sel}>{$action}</option>

HTML;
      }
    
      $html .= <<< HTML
                  </select>
                <input type="hidden" name="comment_ids[]" value="{$id}" />
              </td>
              <td>
                <textarea cols="60" rows="3" name="responses[]">{$comment['response']}</textarea>
              </td>
            </tr>

HTML;
      $i++;
    }
    
    $html .= <<< HTML
            </tbody>
        </table>

HTML;
  } else {
    $html = <<< HTML
        <p>No comments recorded for this question.</p>

HTML;
  }
  
  return $html;
}

