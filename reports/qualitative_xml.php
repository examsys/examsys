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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';
$paperID = check_var('paperID', 'GET', true, false, true);
// Get some paper properties
  $propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
header('Pragma: public');
header('Content-disposition: attachment; filename=report.xml');
header('Content-type: text/xml');
set_time_limit(0);
$paper = str_replace('&', '&amp;', $propertyObj->get_paper_title());
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
echo '<?mso-application progid="Word.Document"?><w:wordDocument xmlns:w="http://schemas.microsoft.com/office/word/2003/wordml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:sl="http://schemas.microsoft.com/schemaLibrary/2003/core" xmlns:aml="http://schemas.microsoft.com/aml/2001/core" xmlns:wx="http://schemas.microsoft.com/office/word/2003/auxHint" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:dt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882" xmlns:st1="urn:schemas-microsoft-com:office:smarttags" w:macrosPresent="no" w:embeddedObjPresent="no" w:ocxPresent="no" xml:space="preserve"><o:SmartTagType o:namespaceuri="urn:schemas-microsoft-com:office:smarttags" o:name="City"/><o:SmartTagType o:namespaceuri="urn:schemas-microsoft-com:office:smarttags" o:name="place"/><o:DocumentProperties><o:Title>';
echo StringUtils::wordToUtf8($paper);
$tmp_start = date_utils::rogoToDisplay($_GET['startdate']);
$tmp_end = date_utils::rogoToDisplay($_GET['enddate']);
echo '</o:Title><o:Author>ExamSys ' . $configObject->get_setting('core', 'rogo_version') . '</o:Author>';
echo '<o:Description>' . sprintf($string['period'], $tmp_start, $tmp_end) . '</o:Description>';
echo '<o:LastAuthor>ExamSys ' . $configObject->get_setting('core', 'rogo_version') . '</o:LastAuthor><o:Revision>1</o:Revision><o:TotalTime>0</o:TotalTime><o:Created>';
echo date('Y-m-d', time()) . 'T' . date('H:i:s') . 'Z';
echo '</o:Created><o:LastSaved>';
echo date('Y-m-d', time()) . 'T' . date('H:i:s') . 'Z';
echo '</o:LastSaved><o:Pages>1</o:Pages><o:Company>' . $configObject->get_setting('core', 'misc_company') . '</o:Company>';
echo '</o:DocumentProperties><w:fonts><w:defaultFonts w:ascii="Times New Roman" w:fareast="Times New Roman" w:h-ansi="Times New Roman" w:cs="Times New Roman"/><w:font w:name="Wingdings"><w:panose-1 w:val="05000000000000000000"/><w:charset w:val="02"/><w:family w:val="Auto"/><w:pitch w:val="variable"/><w:sig w:usb-0="00000000" w:usb-1="10000000" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="80000000" w:csb-1="00000000"/></w:font></w:fonts><w:lists><w:listDef w:listDefId="0"><w:lsid w:val="29EE5463"/><w:plt w:val="Multilevel"/><w:tmpl w:val="6602D62E"/><w:lvl w:ilvl="0"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="720"/></w:tabs><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="1440"/></w:tabs><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="2160"/></w:tabs><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="2880"/></w:tabs><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="3600"/></w:tabs><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="4320"/></w:tabs><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="5040"/></w:tabs><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="5760"/></w:tabs><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="6480"/></w:tabs><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl></w:listDef><w:listDef w:listDefId="1"><w:lsid w:val="2A164306"/><w:plt w:val="Multilevel"/><w:tmpl w:val="051E8EB6"/><w:lvl w:ilvl="0"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="720"/></w:tabs><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="1440"/></w:tabs><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="2160"/></w:tabs><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="2880"/></w:tabs><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="3600"/></w:tabs><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="4320"/></w:tabs><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="5040"/></w:tabs><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="5760"/></w:tabs><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="6480"/></w:tabs><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl></w:listDef><w:listDef w:listDefId="2"><w:lsid w:val="599C005E"/><w:plt w:val="Multilevel"/><w:tmpl w:val="7F1851E6"/><w:lvl w:ilvl="0"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="720"/></w:tabs><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="1440"/></w:tabs><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="2160"/></w:tabs><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="2880"/></w:tabs><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="3600"/></w:tabs><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="4320"/></w:tabs><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="5040"/></w:tabs><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="5760"/></w:tabs><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:tabs><w:tab w:val="list" w:pos="6480"/></w:tabs><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/><w:sz w:val="20"/></w:rPr></w:lvl></w:listDef><w:list w:ilfo="1"><w:ilst w:val="2"/></w:list><w:list w:ilfo="2"><w:ilst w:val="0"/></w:list><w:list w:ilfo="3"><w:ilst w:val="1"/></w:list></w:lists><w:styles><w:versionOfBuiltInStylenames w:val="4"/><w:latentStyles w:defLockedState="off" w:latentStyleCount="156"/><w:style w:type="paragraph" w:default="on" w:styleId="Normal"><w:name w:val="Normal"/><w:rsid w:val="003D0E85"/><w:rPr><wx:font wx:val="Times New Roman"/><w:sz w:val="24"/><w:sz-cs w:val="24"/><w:lang w:val="EN-GB" w:fareast="EN-GB" w:bidi="AR-SA"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><wx:uiName wx:val="Heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:rsid w:val="003D0E85"/><w:pPr><w:pStyle w:val="Heading1"/><w:keepNext/><w:spacing w:before="240" w:after="60"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:b/><w:b-cs/><w:kern w:val="32"/><w:sz w:val="32"/><w:sz-cs w:val="32"/></w:rPr></w:style><w:style w:type="character" w:default="on" w:styleId="DefaultParagraphFont"><w:name w:val="Default Paragraph Font"/><w:semiHidden/></w:style><w:style w:type="table" w:default="on" w:styleId="TableNormal"><w:name w:val="Normal Table"/><wx:uiName wx:val="Table Normal"/><w:semiHidden/><w:rPr><wx:font wx:val="Times New Roman"/></w:rPr><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style><w:style w:type="list" w:default="on" w:styleId="NoList"><w:name w:val="No List"/><w:semiHidden/></w:style><w:style w:type="paragraph" w:styleId="NormalWeb"><w:name w:val="Normal (Web)"/><w:basedOn w:val="Normal"/><w:rsid w:val="003D0E85"/><w:pPr><w:pStyle w:val="NormalWeb"/><w:spacing w:before="100" w:before-autospacing="on" w:after="100" w:after-autospacing="on"/></w:pPr><w:rPr><wx:font wx:val="Times New Roman"/></w:rPr></w:style></w:styles><w:docPr><w:view w:val="print"/><w:zoom w:percent="100"/><w:displayBackgroundShape/><w:doNotEmbedSystemFonts/><w:proofState w:spelling="clean" w:grammar="clean"/><w:attachedTemplate w:val=""/><w:defaultTabStop w:val="720"/><w:characterSpacingControl w:val="DontCompress"/><w:optimizeForBrowser/><w:allowPNG/><w:targetScreenSz w:val="1024x768"/><w:validateAgainstSchema/><w:saveInvalidXML w:val="off"/><w:ignoreMixedContent w:val="off"/><w:alwaysShowPlaceholderText w:val="off"/><w:compat><w:breakWrappedTables/><w:snapToGridInCell/><w:wraptextWithPunct/><w:useAsianBreakRules/><w:useWord2002TableStyleRules/></w:compat></w:docPr>';
// Document body text here.
  echo '<w:body><wx:sect><wx:sub-section>';
echo '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>' . StringUtils::wordToUtf8($paper) . '</w:t></w:r></w:p>';
$result = $mysqli->prepare("SELECT question FROM papers, questions WHERE papers.question = questions.q_id AND q_type != 'info' AND paper = ? ORDER BY screen, display_pos");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($question);
while ($result->fetch()) {
    $paper_structure[] = $question;
}
  $result->close();
$old_leadin = '';
$old_screen = 1;
$old_q_id = 0;
$comment_flag = 1;
$startdate = $_GET['startdate'];
$enddate = $_GET['enddate'];
$q_no = 0;
$sql = <<< SQL
SELECT DISTINCT l.screen, p.display_pos, q.theme, u.username, l.q_id, REPLACE(q.leadin,'&','&amp;') AS leadin, REPLACE(l.user_answer,'&','&amp;') AS user_answer
FROM log3 l INNER JOIN log_metadata lm ON l.metadataID = lm.id
INNER JOIN papers p ON p.question = l.q_id AND p.screen = l.screen AND p.paper = lm.paperID
INNER JOIN questions q ON l.q_id = q.q_id
INNER JOIN users u ON lm.userID = u.id,
user_roles ur JOIN roles r ON ur.roleid = r.id
WHERE p.paper = ?
AND lm.student_grade LIKE ?
AND lm.year LIKE ?
AND q.q_type = 'textbox'
AND lm.started >= ? AND lm.started <= ?
AND u.id = ur.userid
AND r.name IN ('Student', 'graduate')
ORDER BY l.screen, p.display_pos
SQL;
$result = $mysqli->prepare($sql);
$result->bind_param('issss', $_GET['paperID'], $_GET['repcourse'], $_GET['repyear'], $startdate, $enddate);
$result->execute();
$result->bind_result($screen, $displaypos, $theme, $tmp_username, $q_id, $leadin, $user_answer);
while ($result->fetch()) {
    if ($old_q_id != $q_id or $old_screen < $screen) {
        $comment_flag = 0;
        do {
            $q_no++;
        } while ($q_id != $paper_structure[$q_no - 1] and $q_no < 6000);
        echo '<w:p><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:b/><w:b-cs/><w:color w:val="000000"/><w:sz w:val="22"/><w:sz-cs w:val="22"/></w:rPr><w:t>';
        echo $q_no . '. ' . StringUtils::wordToUtf8(strip_tags($leadin));
        echo '</w:t></w:r></w:p>';
    }
    $user_answer = str_replace('<', '&lt;', $user_answer);
    $user_answer = str_replace('>', '&gt;', $user_answer);
    $response = trim(mb_strtolower($user_answer));
    if ($response != null and $response != 'n/a' and strlen($response) > 1) {
        $buffer = '';
        for (
            $character = 0; $character < strlen($user_answer); $character++
        ) {
            if (ord($user_answer[$character]) > 31 and ord($user_answer[$character]) < 127) {
                $buffer .= $user_answer[$character];
            }
        }
        echo '<w:p><w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="1"/><wx:t wx:val="·" wx:wTabBefore="360" wx:wTabAfter="270"/><wx:font wx:val="Symbol"/></w:listPr><w:spacing w:before="100" w:before-autospacing="on" w:after="100" w:after-autospacing="on"/><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:color w:val="000000"/><w:sz w:val="22"/><w:sz-cs w:val="22"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:color w:val="000000"/><w:sz w:val="22"/><w:sz-cs w:val="22"/></w:rPr><w:t>' . StringUtils::wordToUtf8($buffer) . '</w:t></w:r></w:p>';
        $comment_flag = 1;
    }
    $old_leadin = $leadin;
    $old_screen = $screen;
    $old_q_id = $q_id;
}
  $dateformat = $configObject->get('cfg_long_date_php');
echo '<w:sectPr><w:hdr w:type="odd"><w:p><w:pPr><w:tabs><w:tab w:val="right" w:pos="9000"/></w:tabs><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>' . StringUtils::wordToUtf8($paper) . '</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:tab wx:wTab="5475" wx:tlc="none" wx:cTlc="121"/><w:t>' . date($dateformat) . '</w:t></w:r></w:p></w:hdr><w:ftr w:type="odd"><w:p><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>- </w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:fldChar w:fldCharType="begin"/></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:instrText> PAGE </w:instrText></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:fldChar w:fldCharType="separate"/></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:noProof/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>1</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:fldChar w:fldCharType="end"/></w:r><w:r><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t> -</w:t></w:r></w:p></w:ftr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1418" w:bottom="1134" w:left="1418" w:header="709" w:footer="709" w:gutter="0"/><w:cols w:space="708"/><w:docGrid w:line-pitch="360"/></w:sectPr></wx:sub-section></wx:sect></w:body></w:wordDocument>';
$mysqli->close();
