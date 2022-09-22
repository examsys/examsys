<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$menuNo = 0;

function makeMenu($options)
{

    global $menuNo, $configObject;
    // Work out the width of the menu
    $longest_string = 0;
    foreach ($options as $title => $url) {
        if (mb_strlen($title) > $longest_string) {
            $longest_string = mb_strlen($title);
        }
    }
    $width = ($longest_string * 7) + 35;
    if ($width < 60) {
        $width = 60;
    }  // Set a reasonable minumum width


    $line = 0;
    $myoptions = array();
    $myurls = array();
    foreach ($options as $title => $url) {
        $tmp_parts = explode('[BR]', $title);
        if (count($tmp_parts) > 1) {
            $title = $tmp_parts[1];
        } else {
            $title = $tmp_parts[0];
        }
        $myoptions[] = $title;
        $line++;
    }

    $line = 0;
    foreach ($options as $title => $url) {
        $myurls[] = $url;
        $line++;
    }

    echo "<div id='popupmenu$menuNo' data-myOptions='" . json_encode($myoptions) . "' data-myURLs='" . json_encode($myurls) . "'></div>";
    $line = 0;
    echo "<div id=\"popup$menuNo\" class=\"popup\" style=\"width:" . $width . "px; z-index:30\">\n";
    if (count($options) > 20) {
        echo '<div data-menuno="' . $menuNo . '" id="' . $menuNo . "_up\" class=\"popupitem scrollup\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/submenu_up_off.png\" width=\"9\" height=\"5\" alt=\"down\" border=\"0\" />&nbsp;</div>\n";
    }
    foreach ($options as $title => $url) {
        $itemID = $menuNo . '_' . $line;
        if (mb_substr($url, 0, 1) == '-') {
            echo "<div class=\"popupitemline\" id=\"$itemID\">1<hr noshade=\"noshade\" style=\"height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0\" /></div>\n";
        } elseif (mb_substr($url, 0, 1) == '#') {
            echo "<div class=\"popupitembold\" id=\"$itemID\">" . mb_substr($url, 1) . "</div>\n";
        } else {
            if (mb_stripos($url, 'JavaScript:') === false) {
                echo "<div class=\"popupitem\" id=\"$itemID\" onclick=\"window.location='$url'\">/$title</div>\n";
            } else {
                echo "<div class=\"popupitem\" id=\"$itemID\" onclick=\"$url\">/$title</div>\n";
            }
        }
        $line++;
        if ($line > 19) {
            break;
        }
    }
    if (count($options) > 20) {
        echo '<div data-menuno="' . $menuNo . '" id="' . $menuNo . "_down\" class=\"popupitem scrolldown\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/submenu_down_on.png\" width=\"9\" height=\"5\" alt=\"down\" border=\"0\" />&nbsp;</div>\n";
    }

    echo "</div>\n";
    $menuNo++;
}
