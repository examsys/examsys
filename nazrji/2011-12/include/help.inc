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

function wysiwyg_editor($name,$replace,$content) {
  global $cfg_editor_name;

  $html = '';
  
  if ($cfg_editor_name == 'innovastudio') {
    $html .= "<textarea id=\"$replace\" name=\"$replace\" style=\"width:100%; height:400px\">$content</textarea>\n";
    $html .= "<script>\n";
    $html .= "var $name = new InnovaEditor(\"$name\");\n";
    $html .= $name . ".mode=\"XHTMLBody\";\n";
    $html .= $name . ".useTagSelector=false;\n";
    $html .= $name . ".useBR=false;\n";
    $html .= $name . ".width=\"100%\";\n";
    $html .= $name . ".height=getSize();\n";
    $html .= $name . ".arrCustomButtons=[['UploadCaptivate','window.open(\"./addCaptivate.php?" . str_replace('\'','%27',$_SERVER['QUERY_STRING']) . "\",500,300)','Add Captivate','btnCaptivate.gif'],['UploadImage','window.open(\"./addImage.php?" . str_replace('\'','%27',$_SERVER['QUERY_STRING']) . "\",500,300)','Add Image','btnImage.gif']];\n";
    $html .= $name . '.features=["Cut","Copy","PasteText","PasteWord","|","Undo","|","Bold","Italic","Underline","|","Superscript","Subscript","|","JustifyLeft","JustifyCenter","JustifyRight","JustifyFull","|","Numbering","Bullets","|","Hyperlink","UploadImage","UploadCaptivate","Table","|","XHTMLSource"];';
    $html .= "\n";
    $html .= $name . '.arrStyle = [["BODY",false,"","background-color:white; color:black; margin-left:20px; margin-right:20px; font-family:Arial,sans-serif; font-size:85%; line-height:150%"],["h1",false,"","font-size:150%; color:black; font-family:Verdana,sans-serif"],["h2",false,"","font-size:130%; color:#f27000; font-family:Verdana,sans-serif"],["p",false,"","color:#484848"],["td",false,"","color:#484848"],["div",false,"","color:#484848"],["ul",false,"","list-style:square outside; color:#FF9900"],["table",false,"","font-size:100%"],[".subheading",false,"","font-weight:bold; font-style:italic"]];';
    $html .= "\n";
    $html .= $name . ".btnStyles = true;\n";
    $html .= $name . ".REPLACE(\"$replace\");\n";
    $html .= "</script>\n";
  } elseif ($cfg_editor_name == 'tinymce') {
    $html .= "<textarea class=\"mceEditor\" id=\"$replace\" name=\"$replace\" style=\"width:100%; height:400px\">$content</textarea>\n";
  } else {
    //If no editor is specified, output plain textarea.
    $html .= "<textarea name=\"$replace\" style=\"width:100%; height:400px\">$content</textarea>\n";
  }
  
  return $html;
}
?>
