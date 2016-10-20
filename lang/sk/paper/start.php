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

//HTML5 part
require_once '../lang/' . $language . '/question/edit/hotspot_correct.txt';
require_once '../lang/' . $language . '/question/edit/area.txt';
require_once '../lang/' . $language . '/paper/hotspot_answer.txt';
require_once '../lang/' . $language . '/paper/hotspot_question.txt';
require_once '../lang/' . $language . '/paper/label_answer.txt';
$jstring = $string; //to pass it to JavaScript HTML5 modules
//HTML5 part

require_once '../lang/' . $language . '/include/months.inc';
require_once '../lang/' . $language . '/question/sct_shared.php';
require_once '../lang/' . $language . '/include/paper_security.inc';

$string['survey'] = 'Prieskum';
$string['assessment'] = 'Hodnotená skúška';
$string['finish'] = 'Dokončiť';
$string['screen'] = 'Obrazovka';
$string['clarificationscreen'] = 'Obrazovka %s z %s';
$string['mark'] = 'Hodnotenie';
$string['marks'] = 'Hodnotenia';
$string['note'] = 'Poznámka';
$string['true'] = 'Pravda';
$string['false'] = 'Nepravda';
$string['yes'] = 'Áno';
$string['no'] = 'Nie';
$string['abstain'] = 'Zdržalo sa';
$string['na'] = 'Nie je k dispozícii';
$string['unanswered'] = 'Nezodpovedané';
$string['unansweredquestion'] = 'Nezodpovedaná úloha';
$string['negmarking'] = 'Záporné známkovanie';
$string['bonusmark'] = 'Ku správnym odpovediam pridať s %d bonusom %s celkom správnej odpovede';
$string['calculator'] = 'Kalkulačka';
$string['timeremaining'] = 'Zostávajúci čas';
$string['finishnote'] = '<strong>Poznámka:</strong> Pred kliknutím na tlačidlo &#145;Dokončiť&#146; zodpovedajte, prosím, všetky úlohy, potom sa už nebudete môcť vrátiť k ich vypracovaniu.';
$string['gobackpink'] = 'Ak sa vrátite, nezodpovedané úlohy budú ružovo zvýraznene.';
$string['fireexit'] = 'Núdzový východ';
$string['pleasecomplete'] = '<strong>Poznámka:</strong> Pred kliknutím na tlačidlo &#145;Dokončiť&#146; zodpovedajte, prosím, všetky úlohy, potom sa už nebudete môcť vrátiť k ich vypracovaniu.';
$string['javacheck1'] = 'Dokončili ste všetky úlohy v tomto okne? Ísť späť už nebude možné.\nSte si naozaj istý/istá, že chcete pokračovať?';
$string['javacheck2'] = "Ste si naozaj istý/istá, že chcete skončiť? Po kliknutí na tlačítko 'OK' sa už nebudete môcť vrátiť.";
$string['error_random'] = 'Chyba: nie je možné nájsť úlohu akejkoľvek zostavy otázok';
$string['error_keywords'] = 'CHYBA: nie je možné nájsť unikátnu úlohu ku kľúčovému slovu';
$string['error_paper'] = 'Nie je možné nájsť požadovaný dokument.';
$string['error_qtype'] = 'Nie je nastavený typ úlohy.';
$string['holddownctrlkey'] = '(Stlačte klávesu &lt;CTRL&gt;a potom pre prepnutie medzi voľbou zapnuté/vypnuté kliknite na tlačítko myši )';
$string['msgselectable1'] = 'Vybrané príliš veľa možností! Iba \n\n';
$string['msgselectable2'] = 'Položky, ktoré môžu byť vybrané v tejto úlohe.';
$string['msgselectable3'] = 'Už ste vybrali.';
$string['msgselectable4'] = '.\n\nVyberte, prosím, iné miesto.';
//ajax ukládací a autoukládací zpráva
$string['saving'] = 'Ukladanie';
$string['auto_saving'] = 'Automatické ukladanie...';
$string['auto_ok'] = 'Automatické ukladanie dokončené';
$string['savefailed'] = 'Ukladanie zlyhalo';
$string['tryagain'] = 'Skúste, prosím, znova: Prechodom na predchádzajúce alebo následujúce okno.</div>';

$string['question'] = 'Úloha';
$string['questionclarification'] = 'Objasnenie úlohy';
$string['other'] = 'Ostatné';
$string['answer_to'] = 'odpovedať';
$string['decimal_places'] = 'desatinné miesta';
$string['significant_figures'] = 'platné číslice';
$string['forcesave'] = 'Váš čas vypršal a Vaše odpovede boli uložené. ';
?>