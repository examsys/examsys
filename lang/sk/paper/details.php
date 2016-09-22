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

require '../lang/' . $language . '/include/paper_options.inc';
require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/paper/new_paper2.php';

require_once '../classes/configobject.class.php';
$configObject = Config::get_instance();

$string['start'] = 'Štart';
$string['owner'] = 'Vlastník';
$string['question'] = 'Úloha';
$string['type'] = 'Typ';
$string['marks'] = 'Známka';
$string['modified'] = 'Upravené';
$string['passmark'] = 'Potrebná známka';
$string['randommark'] = 'Náhodná známka';
$string['screen'] = 'Obrazovka';
$string['paperpublishedwarning'] = '<strong>Paper grades have been published.</strong>&nbsp;&nbsp;&nbsp;Marking adjustment can no longer occur.';
$string['paperlockedwarning'] = '<strong>Uzamknutý dokument</strong>&nbsp;&nbsp;&nbsp;Tento dokument je v súčasnosti uzamknutý a nie je ho teda možné upravovať.';
$string['paperlockedclick'] ='Pre viac informácií kliknite TU.';
$string['earlywarning'] = '<strong>Varovanie: Čas/Dátum </strong>&nbsp;&nbsp;&nbsp;Začiatok tohto dokumentu je naplánovaný pred %sam';
$string['farfuturewarning'] = '<strong>Varovanie: Čas/Dátum </strong>&nbsp;&nbsp;&nbsp;Začiatok tohto dokumentu je naplánovaný s veľkým predstihom na (%s)';
$string['nooptionsdefined'] = 'Úloha nemá definovanú žiadnu voľbu';
$string['noquestionscreen'] = '<strong>Varovanie:</strong> v okne nie sú žiadne úlohy.<br />Ak budete tento dokument používať ku skúšaniu, objaví sa závada!';
$string['markswarning'] = 'Okno %d má %d bodov z %d%% celkom v dokumente. Aby ste minimalizovali stratu bodov v prípade závady na počítači, pridajte, prosím, ďalšie zalomenie okna. ';
$string['duplicateoptions'] = 'Duplicitné možnosti. MCQ možnosti musia byť jedinečné.';
$string['nocorrect'] = 'Nie je možné nájsť správnu odpoveď';
$string['zeromarks'] = 'Varovanie: nastavené nula bodov.';
$string['toomanycorrect'] = 'Mnoho správnych odpovedí';
$string['answermissing'] = 'K niektorým voľbám chýbajú správne odpovede.';
$string['nolabels'] = 'K obrázku neboli zadané popisky.';
$string['mcqsurvey'] = "MCQ s voľbou 'iný' by mal byť použitý len v prieskume";
$string['dichotomouswarning'] = '%d z %d';
$string['warning'] = 'Varovanie';
$string['variablenomarks'] = 'Varovanie: Premenlivý počet bodov';
$string['paperdeleted'] = 'Dokument odstránený';
$string['deleted_msg1'] = 'Dokument <strong>%s</strong> bol odstránený.';
$string['deleted_msg2'] = 'Stále ešte môže byť obnovené z <a href="' . $configObject->get('cfg_root_path') . '/delete/recycle_list.php" style="color:blue">Koša</a>.';
$string['deleted_msg3'] = 'Nemusíte tento dokument vlastniť, musíte ho získať od <a href="mailto:%s" style="color:blue">%s %s</a> a obnoviť ho.';
$string['addscreenbreak'] = '+ zalomenie obrazovky';
$string['deletescreenbreak'] = '- zalomenie obrazovky';
$string['next'] = 'Ďalší >>';
$string['na'] = 'N/A';
$string['Duplicate questions'] = 'Duplikovať úlohu';
$string['following_questions'] = 'Následujúce úlohy sú';
$string['mismatchbrackets'] = 'Nájdené chybne zadané zátvorky.';
$string['mismatchblanktags'] = 'Nezodpovedajúce prázdne či rolovacie políčka.';
$string['nomatchsession'] = 'Sedenie v názve testu (%s) nezodpovedá sedeniu testu (%s).';
$string['notsummativeexams'] = 'Nemali by sa používať v sumatívnej skúške';
$string['problemwithpaper'] = 'There was a problem loading the paper';
?>