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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

ini_set('short_open_tag', 1);

function GetVar($name, $default = '')
{
    if (array_key_exists($name, $_GET) && $_GET[$name] != '') {
        return $_GET[$name];
    }
    if (array_key_exists($name, $_POST) && $_POST[$name] != '') {
        return $_POST[$name];
    }

    return $default;
}

function explode_no_empty($delimiter, $string)
{
    $result = array();

    $items = explode($delimiter, $string);
    foreach ($items as $item) {
        $item = trim($item);

        if ($item) {
            $result[] = $item;
        }
    }

    return $result;
}

function StripForTitle($in)
{
    $in = trim($in);
    $in = strip_tags($in);
    $in = str_ireplace('"', '', $in);
    $in = str_ireplace("'", '', $in);
    $in = str_ireplace('&nbsp;', ' ', $in);
    $in = str_ireplace("\n", ' ', $in);
    $in = str_ireplace("\r", ' ', $in);
    $in = str_ireplace("\t", ' ', $in);
    while (mb_strpos($in, '  ') > 0) {
        $in = str_replace('  ', ' ', $in);
    }

    return $in;
}

function RemoveEmptyHTMLTags($in)
{
    return preg_replace('#<(\w+)[^>]*>\s*</\1>#im', '', $in);
}

// will compare $array1 and $array2, and remove any items with a common key and put it in $common. Value that goes in $common will come from $array1
function RemoveCommonInArray(&$array1, &$array2, &$common)
{
    // build a list of common keys
    $commonkeys = array();
    foreach ($array1 as $key => $value) {
        // does key exist in 2?
        if (array_key_exists($key, $array2)) {
            $commonkeys[] = $key;
        }
    }

    // process common keys
    foreach ($commonkeys as $key) {
        // copy into $common
        $common[$key] = $array1[$key];

        // remove from both arrays
        unset($array1[$key]);
        unset($array2[$key]);
    }
}

// splits a string into parts and adds the array of its results to $array[]
function ExplodeToArray(&$array, $string, $delim = '|')
{
    if (stripos($string, '<br') > 0) {
        $str2 = str_replace('<br>', ' ', $string);
        $str2 = str_replace('<br />', ' ', $str2);
        ExplodeToArray($array, $str2, $delim);

        $str2 = str_replace('<br>', '', $string);
        $str2 = str_replace('<br />', '', $str2);
        ExplodeToArray($array, $str2, $delim);
    }
    $output = array();
    $arr = explode($delim, $string);
    foreach ($arr as $value) {
        $value = mb_strtolower($value);
        $output[] = $value;
    }

    $array[] = $output;
}

// will return true if the $array_to_match matches an array in $source_arrays
// if $extra_value_list is set then will try matching with each value in it as an extra value
function MatchArraySet(&$source_arrays, &$array_to_match, &$extra_value_list = array())
{
    foreach ($source_arrays as & $source) {
        $match = true;
        reset($array_to_match);
        foreach ($source as $sword) {
            //echo "Comparing source word $sword against dest word " . current($array_to_match)  . "<br>";
            if (current($array_to_match) != $sword) {
                $match = false;
                break;
            }
            next($array_to_match);
        }

        //echo "Compared source, and has $match<br>";

        // still have a match, if count same then return true
        if ($match) {
            if (count($source) == count($array_to_match)) {
                //echo "Matched on <br>";
                //print_p($source);
                return true;
            }

            // 1 extra item in array to match, compare it agains extra_value_list
            if (count($source) + 1 == count($array_to_match)) {
                $extra_match = end($array_to_match);

                $match = false;

                foreach ($extra_value_list as $extra_value) {
                    if ($extra_match == $extra_value) {
                        //echo "Matched on " . implode("|",$source) . " with '$extra_match' as an extra value<br>";
                        return true;
                    }
                }
            }
        }
    }

    /*echo "No match for : <br>";
    print_p($array_to_match);
    echo "In:";
    print_p($source_arrays);
    echo "Abstain values:";
    print_p($extra_value_list);*/

    return false;
}

// returns if an item is in the array or not
function ItemInArray(&$array, $item)
{
    foreach ($array as & $ai) {
        if ($ai == $item) {
            return true;
        }
    }

    return false;
}

function LogForQuestion($id)
{
    global $result;
    $errors_load = (count($result['load']['errors']) > 0 && isset($result['load']['errors'][$id])) ? $result['load']['errors'][$id] : array();
    $warnings_load = (count($result['load']['warnings']) > 0 && isset($result['load']['warnings'][$id])) ? $result['load']['warnings'][$id] : array();
    $errors_save = (count($result['save']['errors']) > 0 && isset($result['save']['errors'][$id])) ? $result['save']['errors'][$id] : array();
    $warnings_save = (count($result['save']['warnings']) > 0 && isset($result['save']['warnings'][$id])) ? $result['save']['warnings'][$id] : array();
    if (count($errors_load) == 0 && count($warnings_load) == 0 && count($errors_save) == 0 && count($warnings_save) == 0) {
        echo "<div style='color:#008000'>Success</div>";
    }

    if (count($errors_load) > 0) {
        echo "<div style='color:#C00000;font-weight:bold;'>Error:</div>";
        foreach ($errors_load as $error) {
            echo "<div style='color:#c00000'>" . $error . '</div>';
        }
    } elseif (count($errors_save) > 0) {
        echo "<div style='color:#C00000;font-weight:bold;'>Error:</div>";
        foreach ($errors_save as $error) {
            echo "<div style='color:#c00000'>" . $error . '</div>';
        }
    } else {
        // only show warnings if no errors
        if (count($warnings_load) > 0) {
            echo "<div style='color:#0000ff;font-weight:bold;'>Warning:</div>";
            foreach ($warnings_load as $warn) {
                echo "<div style='color:#0000ff'>" . $warn . '</div>';
            }
        }

        if (count($warnings_save) > 0) {
            echo "<div style='color:#0000ff;font-weight:bold;'>Warning:</div>";
            foreach ($warnings_save as $warn) {
                echo "<div style='color:#0000ff'>" . $warn . '</div>';
            }
        }
    }
}

function CleanFileName($Raw)
{
    $Raw = trim($Raw);
    $RemoveChars = array("([\40])", '([^a-zA-Z0-9-])', '(-{2,})');
    $ReplaceWith = array('-', '_', '-');
    return preg_replace($RemoveChars, $ReplaceWith, $Raw);
}

function DetectQTIVersion($filename)
{

    return 'qti12';
}

function FindQuestion(&$questions, $id)
{
    $id = trim($id);
    foreach ($questions as & $question) {
        if ($question->load_id == $id) {
            return $question;
        }

        if ($question->save_id == $id) {
            return $question;
        }
    }
    echo "<H1>Unable to find $id</H1>";
    //print_p($questions,false);
}

function ConvertType($type)
{
    if ($type == 'info') {
        return 'Information Block';
    }
    if ($type == 'blank') {
        return 'Fill-in-the-Blank';
    }
    if ($type == 'calculation') {
        return 'Calculation';
    }
    if ($type == 'dichotomous') {
        return 'Dichotomous';
    }
    if ($type == 'extmatch') {
        return 'Extended Matching';
    }
    if ($type == 'hotspot') {
        return 'Image Hotspot';
    }
    if ($type == 'labelling') {
        return 'Labelling';
    }
    if ($type == 'likert') {
        return 'Likert Scale';
    }
    if ($type == 'matrix') {
        return 'Matrix';
    }
    if ($type == 'mcq') {
        return 'Multiple Choice';
    }
    if ($type == 'mrq') {
        return 'Multiple Response';
    }
    if ($type == 'rank') {
        return 'Ranking';
    }
    if ($type == 'textbox') {
        return 'Text Box';
    }

    return $type;
}

function GenerateMediaType($filename)
{
    $ext = mb_strtolower(mb_substr($filename, mb_strrpos($filename, '.') + 1));

    return 'image/' . $ext;
}

function for_id($in)
{
    $in = strip_tags($in);
    $in = str_replace(' ', '_', $in);
    $in = str_replace('"', '_', $in);
    $in = str_replace("'", '_', $in);
    $in = str_replace('’', '_', $in);

    return $in;
}

function MakeValidHTML($in, $trim = 0)
{
    // remove any closing tags at start just in case
    if ($trim == 0) {
        $in = trim($in);
    }
    if (mb_substr($in, 0, 2) == '</') {
        $in = mb_substr($in, mb_strpos($in, '>') + 1);
    }

    $in = '<div>XXX-START-XXX' . $in . 'XXX-END-XXX</div>';

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML(mb_convert_encoding($in, 'HTML-ENTITIES', 'UTF-8'));

    $in = $doc->saveHTML();

    // Required specifically for Likert Scales but mostly harmless
    $in = str_replace('<br>', '<br />', $in);

    $in = mb_substr($in, mb_strpos($in, 'XXX-START-XXX') + 13);
    $in = mb_substr($in, 0, mb_strpos($in, 'XXX-END-XXX'));
    if ($trim == 0) {
        $in = trim($in);
    }
    // remove any closing tags at start just in case
    if (mb_substr($in, 0, 2) == '</') {
        $in = mb_substr($in, mb_strpos($in, '>') + 1);
    }

    return $in;
}

function RemoveLoneP($in)
{
    // some qti files have a lone . at end of <p> or <span> tags, remove it
    $append = '';
    if (mb_substr($in, mb_strlen($in) - 2, 2) == '>.') {
        $in = mb_substr($in, 0, mb_strlen($in) - 1);
        $append = '.';
    }

    //echo htmlentities("IN :!$in!")."<br>";
    $in = trim($in);
    if (mb_substr($in, 0, 1) == '<' && mb_substr($in, mb_strlen($in) - 1, 1) == '>' && mb_strlen($in) > 10) {
        $opentag = mb_strtolower(mb_substr($in, 1, mb_strpos($in, '>') - 1));
        $opentag = trim(mb_substr($opentag, 0, mb_strpos($opentag, ' ')));
        $closetag = mb_substr($in, mb_strrpos($in, '<') + 2);
        $closetag = mb_strtolower(mb_substr($closetag, 0, mb_strlen($closetag) - 1));

        if ($opentag != $closetag) {
            return $in . $append;
        }

        if ($opentag != 'p' && $opentag != 'div' && $opentag != 'span') {
            return $in . $append;
        }

        $middletext = mb_substr($in, mb_strpos($in, '>') + 1);
        $middletext = mb_substr($middletext, 0, mb_strrpos($middletext, '<'));

        if (mb_strpos(' ' . $middletext, '<' . $opentag . '>') > 0) {
            return $in . $append;
        }

        return $middletext . $append;
    }

    return $in . $append;
}

function OrderToStr($no)
{
    switch ($no) {
        case 0:
            return '-';
        case 9990:
            return 'N/A';
        case 1:
            return '1st';
        case 2:
            return '2nd';
        case 3:
            return '3rd';
        default:
            return $no . 'th';
    }
}

function RemoveStNdRd($in)
{
    $in = mb_strtolower(trim($in));

    if ($in == 'na' || $in == 'n/a') {
        return 9990;
    }

    if ($in == '-' || $in == 'blank') {
        return 0;
    }

    $in = str_replace('st', '', $in);
    $in = str_replace('nd', '', $in);
    $in = str_replace('rd', '', $in);
    $in = str_replace('th', '', $in);

    return $in;
}

function MonthToNumeric($month)
{
    $month = mb_strtolower(mb_substr($month, 0, 3));
    if ($month == 'jan') {
        return 1;
    }
    if ($month == 'feb') {
        return 2;
    }
    if ($month == 'mar') {
        return 3;
    }
    if ($month == 'apr') {
        return 4;
    }
    if ($month == 'may') {
        return 5;
    }
    if ($month == 'jun') {
        return 6;
    }
    if ($month == 'jul') {
        return 7;
    }
    if ($month == 'aug') {
        return 8;
    }
    if ($month == 'sep') {
        return 9;
    }
    if ($month == 'oct') {
        return 10;
    }
    if ($month == 'nov') {
        return 11;
    }
    if ($month == 'dec') {
        return 12;
    }

    return 0;
}

function GetAuthorName($userid)
{

    $db = new Database();
    $db->SetTable('users');
    $db->AddField('surname');
    $db->AddField('title');
    $db->AddField('first_names');
    $db->AddWhere('id', $userid, 'i');
    $user = $db->GetSingleRow();

    return $user['surname'] . ', ' . $user['title'] . ' ' . $user['first_names'];
}

function IsAdminUser($userid)
{

    $db = new Database();
    $db->SetTable('users');
    $db->AddField('*');
    $db->AddWhere('id', $userid, 'i');
    $user = $db->GetSingleRow();

    if (mb_strpos(' ' . $user['roles'], 'SysAdmin') > 0) {
        return true;
    }

    return false;
}
