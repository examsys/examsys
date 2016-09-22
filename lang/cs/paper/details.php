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

$configObject = Config::get_instance();

$string['start'] = 'Začátek';
$string['owner'] = 'Vlastník';
$string['question'] = 'Úloha';
$string['type'] = 'Typ';
$string['marks'] = 'Známka';
$string['modified'] = 'Upraveno';
$string['passmark'] = 'Potřebná známka';
$string['randommark'] = 'Náhodná známka';
$string['screen'] = 'Obrazovka';
$string['paperpublishedwarning'] = '<strong>Paper grades have been published.</strong>&nbsp;&nbsp;&nbsp;Marking adjustment can no longer occur.';
$string['paperlockedwarning'] = '<strong>Uzamčený dokument</strong>&nbsp;&nbsp;&nbsp;Tento dokument je v současnosti uzamčen a nelze jej upravovat.';
$string['paperlockedclick'] ='Pro více informací klikněte ZDE.';
$string['earlywarning'] = '<strong>Varování: Čas/Datum </strong>&nbsp;&nbsp;&nbsp;Začátek tohoto dokumentu je naplánován před %sam';
$string['farfuturewarning'] = '<strong>Varování: Čas/Datum </strong>&nbsp;&nbsp;&nbsp;Začátek tohoto dokumentu je naplánován s velkým předstihem na (%s)';
$string['nooptionsdefined'] = 'Úloha nemá definovanou žádnou volbu';
$string['noquestionscreen'] = '<strong>Varování:</strong> na obrazovce chybí úlohy.<br />Pokud tento dokument použijete k testování, objeví se pouze chybové hlášení!';
$string['markswarning'] = 'Okno %d má %d bodů z %d%% celkem v dokumentu. Abyste minimalizovali ztrátu bodů v případě závady na počítači, přidejte, prosím, další zalomení okna. ';
$string['duplicateoptions'] = 'Duplicitní možnosti. MCQ možnosti musí být jedinečné.';
$string['nocorrect'] = 'Nelze najít správnou odpověď';
$string['zeromarks'] = 'Varování: nastaveno nula bodů.';
$string['toomanycorrect'] = 'Mnoho správných odpovědí';
$string['answermissing'] = 'K některým volbám chybí správné odpovědi.';
$string['nolabels'] = 'K obrázku nebyly zadány popisky.';
$string['mcqsurvey'] = "MCQ s volbou 'jiný' by měl být použit jen v průzkumu";
$string['dichotomouswarning'] = '%d z %d';
$string['warning'] = 'Varování';
$string['variablenomarks'] = 'Varování: Proměnný počet bodů';
$string['paperdeleted'] = 'Dokument odstraněn';
$string['deleted_msg1'] = 'Dokument <strong>%s</strong> byl odstraněn.';
$string['deleted_msg2'] = 'Stále ještě může být obnoveno z <a href="' . $configObject->get('cfg_root_path') . '/delete/recycle_list.php" style="color:blue">Koše</a>.';
$string['deleted_msg3'] = 'Nemusíte tento dokument vlastnit, musíte jej získat od <a href="mailto:%s" style="color:blue">%s %s</a> a obnovit jej.';
$string['addscreenbreak'] = '+ zalomení obrazovky';
$string['deletescreenbreak'] = '- zalomení obrazovky';
$string['next'] = 'Další >>';
$string['na'] = 'N/A';
$string['Duplicate questions'] = 'Duplikovat Úlohu';
$string['following_questions'] = 'Následující úlohy jsou';
$string['mismatchbrackets'] = 'Nalezeny nesourodé závorky.';
$string['mismatchblanktags'] = 'Neodpovídající prázdná či rolovací políčka.';
$string['nomatchsession'] = 'Datum v Názvu Testu (%s) neodpovídá sezení testu (%s).';
$string['notsummativeexams'] = 'Nemělo by být použito k Sumativnímu testování';
$string['problemwithpaper'] = 'There was a problem loading the paper';
?>