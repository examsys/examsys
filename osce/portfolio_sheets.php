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
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require './osce.inc';
  
  header('Content-disposition: attachment; filename=report.xml');
  header('Content-type: text/xml');
  set_time_limit(0);
  
  echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<?mso-application progid="Word.Document"?>
<w:wordDocument xmlns:aml="http://schemas.microsoft.com/aml/2001/core" xmlns:dt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882" xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.microsoft.com/office/word/2003/wordml" xmlns:wx="http://schemas.microsoft.com/office/word/2003/auxHint" xmlns:wsp="http://schemas.microsoft.com/office/word/2003/wordml/sp2" xmlns:sl="http://schemas.microsoft.com/schemaLibrary/2003/core" w:macrosPresent="no" w:embeddedObjPresent="no" w:ocxPresent="no" xml:space="preserve"><w:ignoreSubtree w:val="http://schemas.microsoft.com/office/word/2003/wordml/sp2"/><o:DocumentProperties><o:Author>Rogo ' . $ts_version . '</o:Author><o:LastAuthor>Rogo ' . $ts_version . '</o:LastAuthor><o:Revision>1</o:Revision><o:TotalTime>1</o:TotalTime><o:Created>';
  echo date('Y-m-d', time()) . 'T' . date('H:i:s') . 'Z';
  echo '</o:Created><o:LastSaved>';
  echo date('Y-m-d', time()) . 'T' . date('H:i:s') . 'Z';
  echo '</o:LastSaved><o:Pages>2</o:Pages><o:Words>223</o:Words><o:Characters>1274</o:Characters><o:Company>' . $cfg_company . '</o:Company><o:Lines>10</o:Lines><o:Paragraphs>2</o:Paragraphs><o:CharactersWithSpaces>1495</o:CharactersWithSpaces><o:Version>12</o:Version></o:DocumentProperties><w:fonts><w:defaultFonts w:ascii="Calibri" w:fareast="Calibri" w:h-ansi="Calibri" w:cs="Times New Roman"/><w:font w:name="Times New Roman"><w:panose-1 w:val="02020603050405020304"/><w:charset w:val="00"/><w:family w:val="Roman"/><w:pitch w:val="variable"/><w:sig w:usb-0="20002A87" w:usb-1="80000000" w:usb-2="00000008" w:usb-3="00000000" w:csb-0="000001FF" w:csb-1="00000000"/></w:font><w:font w:name="Courier New"><w:panose-1 w:val="02070309020205020404"/><w:charset w:val="00"/><w:family w:val="Modern"/><w:pitch w:val="fixed"/><w:sig w:usb-0="20002A87" w:usb-1="80000000" w:usb-2="00000008" w:usb-3="00000000" w:csb-0="000001FF" w:csb-1="00000000"/></w:font><w:font w:name="Symbol"><w:panose-1 w:val="05050102010706020507"/><w:charset w:val="02"/><w:family w:val="Roman"/><w:pitch w:val="variable"/><w:sig w:usb-0="00000000" w:usb-1="10000000" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="80000000" w:csb-1="00000000"/></w:font><w:font w:name="Wingdings"><w:panose-1 w:val="05000000000000000000"/><w:charset w:val="02"/><w:family w:val="auto"/><w:pitch w:val="variable"/><w:sig w:usb-0="00000000" w:usb-1="10000000" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="80000000" w:csb-1="00000000"/></w:font><w:font w:name="Cambria Math"><w:panose-1 w:val="02040503050406030204"/><w:charset w:val="01"/><w:family w:val="Roman"/><w:notTrueType/><w:pitch w:val="variable"/><w:sig w:usb-0="00000000" w:usb-1="00000000" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="00000000" w:csb-1="00000000"/></w:font><w:font w:name="Cambria"><w:panose-1 w:val="02040503050406030204"/><w:charset w:val="00"/><w:family w:val="Roman"/><w:pitch w:val="variable"/><w:sig w:usb-0="A00002EF" w:usb-1="4000004B" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="0000009F" w:csb-1="00000000"/></w:font><w:font w:name="Calibri"><w:panose-1 w:val="020F0502020204030204"/><w:charset w:val="00"/><w:family w:val="Swiss"/><w:pitch w:val="variable"/><w:sig w:usb-0="A00002EF" w:usb-1="4000207B" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="0000009F" w:csb-1="00000000"/></w:font></w:fonts><w:lists><w:listDef w:listDefId="0"><w:lsid w:val="287539F0"/><w:plt w:val="HybridMultilevel"/><w:tmpl w:val="B3A8E99A"/><w:lvl w:ilvl="0" w:tplc="08090001"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl></w:listDef><w:listDef w:listDefId="1"><w:lsid w:val="5BAD485D"/><w:plt w:val="HybridMultilevel"/><w:tmpl w:val="BAB8AB2E"/><w:lvl w:ilvl="0" w:tplc="41107736"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="•"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Calibri" w:h-ansi="Calibri" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl></w:listDef><w:listDef w:listDefId="2"><w:lsid w:val="782C7524"/><w:plt w:val="HybridMultilevel"/><w:tmpl w:val="86667AC4"/><w:lvl w:ilvl="0" w:tplc="08090005"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="?"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl></w:listDef><w:list w:ilfo="1"><w:ilst w:val="0"/></w:list><w:list w:ilfo="2"><w:ilst w:val="2"/></w:list><w:list w:ilfo="3"><w:ilst w:val="1"/></w:list></w:lists><w:styles><w:versionOfBuiltInStylenames w:val="7"/><w:latentStyles w:defLockedState="off" w:latentStyleCount="267"><w:lsdException w:name="Normal"/><w:lsdException w:name="heading 1"/><w:lsdException w:name="heading 2"/><w:lsdException w:name="heading 3"/><w:lsdException w:name="heading 4"/><w:lsdException w:name="heading 5"/><w:lsdException w:name="heading 6"/><w:lsdException w:name="heading 7"/><w:lsdException w:name="heading 8"/><w:lsdException w:name="heading 9"/><w:lsdException w:name="caption"/><w:lsdException w:name="Title"/><w:lsdException w:name="Subtitle"/><w:lsdException w:name="Strong"/><w:lsdException w:name="Emphasis"/><w:lsdException w:name="No Spacing"/><w:lsdException w:name="List Paragraph"/><w:lsdException w:name="Quote"/><w:lsdException w:name="Intense Quote"/><w:lsdException w:name="Subtle Emphasis"/><w:lsdException w:name="Intense Emphasis"/><w:lsdException w:name="Subtle Reference"/><w:lsdException w:name="Intense Reference"/><w:lsdException w:name="Book Title"/><w:lsdException w:name="TOC Heading"/></w:latentStyles><w:style w:type="paragraph" w:default="on" w:styleId="Normal"><w:name w:val="Normal"/><w:rsid w:val="00A4626D"/><w:pPr><w:spacing w:after="200" w:line="276" w:line-rule="auto"/></w:pPr><w:rPr><wx:font wx:val="Calibri"/><w:sz w:val="22"/><w:sz-cs w:val="22"/><w:lang w:val="EN-GB" w:fareast="EN-US" w:bidi="AR-SA"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><wx:uiName wx:val="Heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:link w:val="Heading1Char"/><w:rsid w:val="00A8714C"/><w:pPr><w:keepNext/><w:spacing w:before="240" w:after="60"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:rFonts w:ascii="Cambria" w:fareast="Times New Roman" w:h-ansi="Cambria"/><wx:font wx:val="Cambria"/><w:b/><w:b-cs/><w:kern w:val="32"/><w:sz w:val="32"/><w:sz-cs w:val="32"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><wx:uiName wx:val="Heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:link w:val="Heading2Char"/><w:rsid w:val="00687E9E"/><w:pPr><w:keepNext/><w:spacing w:before="240" w:after="60"/><w:outlineLvl w:val="1"/></w:pPr><w:rPr><w:rFonts w:ascii="Cambria" w:fareast="Times New Roman" w:h-ansi="Cambria"/><wx:font wx:val="Cambria"/><w:b/><w:b-cs/><w:i/><w:i-cs/><w:color w:val="365F91"/><w:sz w:val="24"/><w:sz-cs w:val="28"/></w:rPr></w:style><w:style w:type="character" w:default="on" w:styleId="DefaultParagraphFont"><w:name w:val="Default Paragraph Font"/></w:style><w:style w:type="table" w:default="on" w:styleId="TableNormal"><w:name w:val="Normal Table"/><wx:uiName wx:val="Table Normal"/><w:rPr><wx:font wx:val="Calibri"/><w:lang w:val="EN-GB" w:fareast="EN-GB" w:bidi="AR-SA"/></w:rPr><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style><w:style w:type="list" w:default="on" w:styleId="NoList"><w:name w:val="No List"/></w:style><w:style w:type="character" w:styleId="Heading1Char"><w:name w:val="Heading 1 Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="Heading1"/><w:rsid w:val="00A8714C"/><w:rPr><w:rFonts w:ascii="Cambria" w:fareast="Times New Roman" w:h-ansi="Cambria" w:cs="Times New Roman"/><w:b/><w:b-cs/><w:kern w:val="32"/><w:sz w:val="32"/><w:sz-cs w:val="32"/><w:lang w:fareast="EN-US"/></w:rPr></w:style><w:style w:type="table" w:styleId="TableGrid"><w:name w:val="Table Grid"/><w:basedOn w:val="TableNormal"/><w:rsid w:val="00A11D0F"/><w:rPr><wx:font wx:val="Calibri"/></w:rPr><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:left w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:insideH w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:insideV w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/></w:tblBorders><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style><w:style w:type="character" w:styleId="Heading2Char"><w:name w:val="Heading 2 Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="Heading2"/><w:rsid w:val="00687E9E"/><w:rPr><w:rFonts w:ascii="Cambria" w:fareast="Times New Roman" w:h-ansi="Cambria"/><w:b/><w:b-cs/><w:i/><w:i-cs/><w:color w:val="365F91"/><w:sz w:val="24"/><w:sz-cs w:val="28"/><w:lang w:fareast="EN-US"/></w:rPr></w:style></w:styles><w:shapeDefaults><o:shapedefaults v:ext="edit" spidmax="7170"/><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1"/></o:shapelayout></w:shapeDefaults><w:docPr><w:view w:val="print"/><w:zoom w:percent="100"/><w:doNotEmbedSystemFonts/><w:proofState w:spelling="clean" w:grammar="clean"/><w:defaultTabStop w:val="720"/><w:punctuationKerning/><w:characterSpacingControl w:val="DontCompress"/><w:optimizeForBrowser/><w:validateAgainstSchema/><w:saveInvalidXML w:val="off"/><w:ignoreMixedContent w:val="off"/><w:alwaysShowPlaceholderText w:val="off"/><w:compat><w:breakWrappedTables/><w:snapToGridInCell/><w:wrapTextWithPunct/><w:useAsianBreakRules/><w:dontGrowAutofit/></w:compat><wsp:rsids><wsp:rsidRoot wsp:val="00233311"/><wsp:rsid wsp:val="0005490A"/><wsp:rsid wsp:val="001739A3"/><wsp:rsid wsp:val="00176780"/><wsp:rsid wsp:val="00233311"/><wsp:rsid wsp:val="0023732C"/><wsp:rsid wsp:val="00333355"/><wsp:rsid wsp:val="004D2343"/><wsp:rsid wsp:val="00675148"/><wsp:rsid wsp:val="00687E9E"/><wsp:rsid wsp:val="006E46D9"/><wsp:rsid wsp:val="00982992"/><wsp:rsid wsp:val="00A11D0F"/><wsp:rsid wsp:val="00A4626D"/><wsp:rsid wsp:val="00A8714C"/><wsp:rsid wsp:val="00AE50B6"/><wsp:rsid wsp:val="00AF0CB5"/><wsp:rsid wsp:val="00BF4A83"/><wsp:rsid wsp:val="00C21004"/><wsp:rsid wsp:val="00CB5B87"/><wsp:rsid wsp:val="00DB2E44"/><wsp:rsid wsp:val="00E01C70"/><wsp:rsid wsp:val="00ED0EA8"/></wsp:rsids></w:docPr><w:body>';

  $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title);
  $result->fetch();
  $result->close();

  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];
  $result = $mysqli->prepare("SELECT log4.userID, students.title, students.surname, students.first_names, log4.q_id, rating, q_parts, leadin, theme, DATE_FORMAT(log4_overall.started,\"%d/%m/%Y %H:%i\") AS started, REPLACE(feedback,'&','&amp;') AS feedback, examiners.title, examiners.surname FROM (log4, log4_overall, papers, questions, users AS students, users AS examiners) WHERE log4.userID=log4_overall.userID AND log4.q_paper=log4_overall.q_paper AND log4.userID=students.id AND log4_overall.examinerID=examiners.id AND papers.question=questions.q_id AND papers.paper=? AND log4.q_paper=? AND log4.q_id=questions.q_id AND log4_overall.started>=? AND log4_overall.started<=? AND (students.roles='Student' OR students.roles='graduate') AND log4_overall.student_grade LIKE ? ORDER BY students.surname, students.initials, log4_overall.userID, display_pos");
  $result->bind_param('iisss', $_GET['paperID'], $_GET['paperID'], $startdate, $enddate, $_GET['repdegree']);
  $result->execute();
  $result->bind_result($tmp_userID, $title, $surname, $first_names, $q_id, $rating, $q_parts, $leadin, $theme, $started, $feedback, $examiner_title, $examiner_surname);
  $old_tmp_userID = 0;
  $table_open = 0;
  $student_no = 0;
  while ($row = $result->fetch()) {
  echo "hI";
    if ($old_tmp_userID != $tmp_userID) {
      $student_no++;
      if ($old_tmp_userID != '') {
        echo '<w:p wsp:rsidR="00472B21" wsp:rsidRDefault="00472B21" wsp:rsidP="00472B21"><w:pPr><w:spacing w:after="0"/><w:rPr><w:b/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="000E6B7A" wsp:rsidRPr="000E6B7A" wsp:rsidRDefault="00472B21" wsp:rsidP="00ED0EA8"><w:pPr><w:rPr><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="000E6B7A"><w:rPr><w:b/></w:rPr><w:t>Feedback:</w:t></w:r></w:p>';
        echo '<w:p wsp:rsidR="000E6B7A" wsp:rsidRPr="00A11D0F" wsp:rsidRDefault="00273482" wsp:rsidP="00ED0EA8"><w:r><w:t>' . $old_feedback . '</w:t></w:r></w:p>';
        echo '</w:tbl></wx:sub-section>';
      }
      echo '<wx:sub-section><w:p wsp:rsidR="00A8714C" wsp:rsidRDefault="00A8714C" wsp:rsidP="0005490A"><w:pPr><w:pStyle w:val="Heading1"/></w:pPr>';
      if ($student_no > 1) echo '<w:r><w:br w:type="page"/></w:r>';
      echo '<w:r><w:t>' . $title . ' ' . $surname . ', ' . $first_names . '</w:t></w:r></w:p>';
      echo '<w:p wsp:rsidR="00687E9E" wsp:rsidRDefault="00687E9E" wsp:rsidP="00DB2E44"><w:pPr><w:tabs><w:tab w:val="left" w:pos="1134"/></w:tabs><w:spacing w:after="0"/><w:rPr><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="00687E9E"><w:rPr><w:b/></w:rPr><w:t>' . $string['osce'] . '</w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:tab/></w:r><w:r wsp:rsidRPr="00DB2E44"><w:t>' . $paper_title .  '</w:t></w:r></w:p>';
      echo '<w:p wsp:rsidR="00DB2E44" wsp:rsidRDefault="00DB2E44" wsp:rsidP="00DB2E44"><w:pPr><w:tabs><w:tab w:val="left" w:pos="1134"/></w:tabs><w:spacing w:after="0"/><w:rPr><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>' . $string['examiner'] . '</w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:tab/></w:r><w:r wsp:rsidRPr="00DB2E44"><w:t>' . $examiner_title . ' ' . $examiner_surname . '</w:t></w:r></w:p>';
      echo '<w:p wsp:rsidR="00687E9E" wsp:rsidRDefault="00687E9E" wsp:rsidP="00DB2E44"><w:pPr><w:tabs><w:tab w:val="left" w:pos="1134"/></w:tabs><w:spacing w:after="0"/><w:rPr><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="00687E9E"><w:rPr><w:b/></w:rPr><w:t>' . $string['date'] . '</w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:tab/></w:r><w:r wsp:rsidRPr="00DB2E44"><w:t>' . $started . '</w:t></w:r></w:p>';
      echo '<w:tbl><w:tblPr><w:tblW w:w="9242" w:type="dxa"/><w:tblInd w:w="108" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:left w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:insideH w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:insideV w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/></w:tblBorders><w:tblLook w:val="04A0"/></w:tblPr><w:tblGrid><w:gridCol w:w="8755"/><w:gridCol w:w="487"/></w:tblGrid>';
      $table_open = 1;
    }
    if ($theme != '') {
      if ($table_open == 1) echo '</w:tbl>';
      echo '<w:p wsp:rsidR="00A11D0F" wsp:rsidRPr="00687E9E" wsp:rsidRDefault="00A11D0F" wsp:rsidP="00687E9E"><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r wsp:rsidRPr="00687E9E"><w:t>' . $theme . '</w:t></w:r></w:p>';
      $table_open = 0;
    }
    if ($table_open == 0) {
      echo '<w:tbl><w:tblPr><w:tblW w:w="9242" w:type="dxa"/><w:tblInd w:w="108" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:left w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:bottom w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:right w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:insideH w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/><w:insideV w:val="single" w:sz="4" wx:bdrwidth="10" w:space="0" w:color="000000"/></w:tblBorders><w:tblLook w:val="04A0"/></w:tblPr><w:tblGrid><w:gridCol w:w="8755"/><w:gridCol w:w="487"/></w:tblGrid>' . "\n\n";
      $table_open = 1;
    }

    $leadin = parse_leadin_word_2003(strip_tags($leadin),$q_parts);

    // Lead-in
    echo '<w:tr wsp:rsidR="00A11D0F" wsp:rsidRPr="00A11D0F" wsp:rsidTr="00A11D0F">';
    echo '<w:tc>';
    echo '<w:tcPr><w:tcW w:w="8755" w:type="dxa"/></w:tcPr><w:p>' . wordToUtf8(trim($leadin));
    echo '</w:p>';
    echo '</w:tc>';

    $old_feedback = $feedback;

    // Rating score
    echo '<w:tc>';
    echo '<w:tcPr><w:tcW w:w="487" w:type="dxa"/></w:tcPr><w:p wsp:rsidR="00A11D0F" wsp:rsidRPr="00A11D0F" wsp:rsidRDefault="00A11D0F" wsp:rsidP="00A11D0F"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:rPr><w:rFonts w:ascii="Calibri" w:h-ansi="Calibri"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00A11D0F"><w:rPr><w:rFonts w:ascii="Calibri" w:h-ansi="Calibri"/></w:rPr><w:t>' . $rating . '</w:t></w:r></w:p>';
    echo '</w:tc>';
    echo '</w:tr>';

    $old_tmp_userID = $tmp_userID;
  }
  $result->close();
  $mysqli->close();
  if ($old_tmp_userID != '') {
    echo '<w:p wsp:rsidR="00472B21" wsp:rsidRDefault="00472B21" wsp:rsidP="00472B21"><w:pPr><w:spacing w:after="0"/><w:rPr><w:b/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="000E6B7A" wsp:rsidRPr="000E6B7A" wsp:rsidRDefault="00472B21" wsp:rsidP="00ED0EA8"><w:pPr><w:rPr><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="000E6B7A"><w:rPr><w:b/></w:rPr><w:t>Feedback:</w:t></w:r></w:p>';
    echo '<w:p wsp:rsidR="000E6B7A" wsp:rsidRPr="00A11D0F" wsp:rsidRDefault="00273482" wsp:rsidP="00ED0EA8"><w:r><w:t>' . $old_feedback . '</w:t></w:r></w:p>';
    echo '</w:tbl></wx:sub-section>';
  }
  echo '<w:sectPr wsp:rsidR="0005490A" wsp:rsidRPr="0005490A" wsp:rsidSect="00A4626D"><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="708" w:footer="708" w:gutter="0"/><w:cols w:space="708"/><w:docGrid w:line-pitch="360"/></w:sectPr></w:body></w:wordDocument>';

  function wordToUtf8($str) {
    $wordChr = array(
    "\\xe2\\x80\\xa6",       // ellipsis
    "\\xe2\\x80\\x93",       // long dash
    "\\xe2\\x80\\x94",       // long dash
    "\x96",                  // long dash
    "\x91",                  // single quote
    "\x92",                  // single quote
    "\\xe2\\x80\\x98",       // single quote opening
    "\\xe2\\x80\\x99",       // single quote closing
    "\\xe2\\x80\\x9c",       // double quote opening
    "\\xe2\\x80\\x9d",       // double quote closing
    "\\xe2\\x80\\xa2"        // dot used for bullet points
    );

    $utf8Chr = array(
        '...',
        '-',
        '-',
        '-',
        '\'',
        '\'',
        '\'',
        '\'',
        '"',
        '"',
        '*'
        );

        return str_replace($wordChr,$utf8Chr,$str);
  }
?>