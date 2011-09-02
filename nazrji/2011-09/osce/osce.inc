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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

function parse_leadin_word_2003($text,$parts) {
  global $q_id;
  $count = 0;
  $calss = 'part';

  $text = str_replace("\n",'',$text);
  $text = str_replace("\r",'',$text);
  $text = str_replace("\t",'',$text);
  $text = str_replace('<strong>','',$text);
  $text = str_replace('</strong>','',$text);
  $text = str_replace('<ul>','',$text);
  $text = str_replace('</ul>','',$text);
  $text = str_replace('<div>','',$text);
  $text = str_replace('</div>','<w:br/>',$text);

  $starts = explode('[',$text);
  $count = count($starts);
  if($parts == '') {
    $value = str_pad('',$count-1,'0');
  } else {
    $value = $parts;
  }

  $tmptext = '';
  if($count > 1) {
    for($i = 0; $i < $count; $i++) {

      if($i > 0) {
        if($value{$i-1} == 1) {
          $tmptext .= "<w:r><w:rPr><w:color w:val=\"00B050\"/><w:u w:val=\"single\"/></w:rPr><w:t>";
        } else {
          $tmptext .= "<w:r><w:rPr></w:rPr><w:t>";
        }

        $tmptext .= ltrim($starts[$i]);

        if($i < ($count -1)) {
	      if(substr($starts[$i],-1) != ']' OR substr($starts[$i],-1) != 'li>') {
	        $end_tags = '</w:t></w:r>';
	        $rep_tags = '</w:t></w:r><w:r><w:t>';
	      } else {
	        $end_tags = '</w:t></w:r>';
	        $rep_tags = '';
	      }
	    } else {
	      $end_tags = '</w:t></w:r>';
	      $rep_tags = '';
        }

        $tmptext .= $end_tags;
        $tmptext = preg_replace('/\]/'," $rep_tags",$tmptext);
      } else {
        if(trim($starts[$i]) != '' AND trim($starts[$i]) != '<li>') {
          $tmptext .= '<w:r><w:t>|' . trim(htmlspecialchars($starts[$i])) . '|</w:t></w:r></w:p><w:p>';
        }
      }
    }
    $x = 0;
    $tmptext = str_replace('<li>','',$tmptext,$x);
    //$tmptext = str_replace('</li>','|',$tmptext,$x);
    if($x > 0) {
      $tmptext = str_replace('<w:r><w:rPr><w:color w:val="00B050"/><w:u w:val="single"/></w:rPr><w:t>','<w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="\xE2\x80\xA2"/><wx:font wx:val="Calibri"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:rPr><w:color w:val="00B050"/><w:u w:val="single"/></w:rPr><w:t>' . "\n\n",$tmptext);
      $tmptext = str_replace('<w:r><w:rPr></w:rPr><w:t>','<w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="\xE2\x80\xA2"/><wx:font wx:val="Calibri"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:t>' . "\n\n",$tmptext);
      $tmptext = str_replace('</li></w:t></w:r>','</w:t></w:r></w:p><w:p>',$tmptext);
    }

    return $tmptext;
  } else {
    return  '<w:r><w:t>' . $text . '</w:t></w:r>';
  }

}

function parse_leadin($text,$parts) {
  global $q_id;
  $count = 0;
  $calss = 'part';

  $starts = explode('[',$text);
  $count = count($starts);
  if($parts == '') {
    $value = str_pad('',$count-1,'0');
  } else {
    $value = $parts;
  }
  $tmptext = '';
  if($count > 1) {
    $tmptext .= $starts[0];
    for($i = 1; $i < $count; $i++) {
      if($value{$i-1} == 1) {
         $class = 'part_ok';
      } else {
        $class = 'part';
      }
      $tmptext .= "<span id=\"" . $q_id . "_" . ($i) . "\" class=\"$class\" onclick=\"subpart(this);\">";
      $tmptext .= $starts[$i];
    }
    $tmptext = preg_replace('/\]/','</span>',$tmptext);

    $tmptext .= "\n<input id=\"" . $q_id . "_parts\" name=\"" . $q_id . "_parts\" type=\"hidden\" value=\"$value\" />\n";

    return $tmptext;
  } else {
    return $text;
  }

}

?>