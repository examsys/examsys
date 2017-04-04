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
* function used by the add question scripts
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once 'errors.php';

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

/**
 * Create wysiwyg editor.
 * 
 * @param string $name - name for editor 
 * @param string $replace - id for editor
 * @param string $content - current content
 * @param string $type - TinyMCE only - type of editor i.e. mceEditorSimple/mceEditor
 * @param int $width width of textarea
 * @param int $height  height of textarea
 * @return html of editor
 */
function wysiwyg_editor($name, $replace, $content='', $type = '', $width=756, $height=120) {
  global $cfg_editor_name;

  $html = '';

  $content = htmlspecialchars($content);

  if ($cfg_editor_name == 'tinymce') {
    if ($type == '') {
      $type = 'mceEditor';
    }
    $html .= "<textarea class=\"$type\" id=\"$replace\" name=\"$replace\" style=\"width:" . $width . "px; height:" . $height . "px; margin: 0\" rows=\"5\" cols=\"20\">$content</textarea>\n";
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

function render_changes($changes, &$string) {
  $cutoff = 70;
  $i = 0;
  if (count($changes) > 0) {
    $html = <<< HTML
        <table class="data">
          <thead>
            <tr>
              <th class="data-small">{$string['date']}</th>
              <th class="data-small">{$string['action']}</th>
              <th>{$string['section']}</th>
              <th class="data-small">{$string['old']}</th>
              <th class="data-small">{$string['new']}</th>
              <th class="data-small">{$string['editor']}</th>
            </tr>
          </thead>
          <tbody>

HTML;

    foreach($changes as $change) {
      $alt = ($i++ % 2 == 1) ? ' class="alt"' : '';
      $change['old'] = strip_tags($change['old']);
      $change['new'] = strip_tags($change['new']);
      $change_old = $change['old'];
      $change_old_more = '';
      $change_new = $change['new'];
      $change_new_more = '';
      if (strlen($change['old']) > $cutoff) {
        $split_pos = strrpos(substr($change['old'], 0, $cutoff), ' ');
        $change_old = substr($change['old'], 0, $split_pos);
        $change_old_more = substr($change['old'], $split_pos);
        $change_old_more = "<span class=\"more-ellip\">&hellip;</span><span class=\"more-text\">{$change_old_more}</span><br /><a href=\"#\" class=\"more\">{$string['showmore']}</a>";
      }
      if (strlen($change['new']) > $cutoff) {
        $split_pos = strrpos(substr($change['new'], 0, $cutoff), ' ');
        $change_new = substr($change['new'], 0, $split_pos);
        $change_new_more = substr($change['new'], $split_pos);
        $change_new_more = "<span class=\"more-ellip\">&hellip;</span><span class=\"more-text\">{$change_new_more}</span><br /><a href=\"#\" class=\"more\">{$string['showmore']}</a>";
      }
			if ($change['section'] == $string['markscorrect'] or $change['section'] == $string['marksincorrect'] or $change['section'] == $string['markspartial']) {
				$change['section'] = '<span style="color:#C00000">' . $change['section'] . '</span>';
			}
      $html .= <<< HTML
            <tr{$alt}>
              <td>{$change['date']}</td>
              <td>{$change['action']}</td>
              <td>{$change['section']}</td>
              <td>{$change_old}{$change_old_more}</td>
              <td>{$change_new}{$change_new_more}</td>
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
        <p>{$string['nochangesrecorded']}</p>

HTML;

  }

  return $html;
}

function render_comments($comments, &$string) {
  if (count($comments) > 0) {
    $html = <<< HTML
        <table class="data" summary="{$string['reviewerscomments']}">
          <thead>
            <tr>
              <th class="data-tiny"></th>
              <th class="data-medium">{$string['reviewer']}</th>
              <th>{$string['comments']}</th>
              <th class="data-small">{$string['actiontaken']}</th>
              <th>{$string['internalresponse']}</th>
            </tr>
        </thead>
        <tbody>

HTML;

    $i = 0;
    foreach ($comments as $id => $comment) {
      switch ($comment['category']) {
        case 1:
          $status = array('ok', $string['ok']);
          break;
        case 2:
          $status = array('minor', $string['minor']);
          break;
        case 3:
          $status = array('major', $string['major']);
          break;
        default:
          $status = array('cannot', $string['cannot']);
          break;
      }
      $img_status = $status[0];

      $alt = ($i % 2 == 0) ? ' class="alt"' : '';
      $comment_str = ($comment['comment'] == '') ? $string['nocomments'] : nl2br($comment['comment']);
      $type_translated = $string[strtolower($comment['type'])];

      $html .= <<< HTML
  					<tr{$alt}>
              <td><img src="../../artwork/{$img_status}_comment.png" width="16" height="16" alt="{$status[1]}" /></td>
              <td>{$comment['name']}<br /><span class="note">{$type_translated}</span></td>
              <td>{$comment_str}</td>
              <td>
                <select name="actions[]">

HTML;

      $actions = array('Not actioned' => $string['notactioned'], 'Read - disagree' => $string['readdisagree'], 'Read - actioned' => $string['readactioned']);

      foreach ($actions as $action => $act_text) {
        $sel = ($comment['action'] == $action) ? ' selected="selected"' : '';
        $html .= <<< HTML
      						<option value="{$action}"{$sel}>{$act_text}</option>

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
        <p>{$string['commentsmsg']}</p>

HTML;
  }

  return $html;
}

