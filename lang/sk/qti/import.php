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
require 'shared.inc';
require '../lang/' . $language . '/question/edit/likert_scales.php';

$string['import'] = 'Importovať';
$string['import2'] = 'Importovať';
$string['importfromqti'] = 'Importovať z QTI';
$string['file'] = 'Súbor';
$string['qtiimporterror'] = 'Počas importu Vášho QTI súboru došlo k chybe.';
$string['qtiimported'] = 'Váš QTI súbor bol importovaný';
$string['questionproblems'] = 'Niektoré z Vašich úloh neboli naimportované správne.';
$string['hadproblemsimporting'] = '%d z %d úloh malo problém s importom.';
$string['importedquestions'] = 'Importovaných %d úloh.';
$string['backtopaper'] = 'Späť na dokument';
$string['errmsg1'] = 'Tento typ exportu nie je podporovaný';
$string['errmsg2'] = 'Tento typ importu nie je podporovaný';
$string['invalidxml'] = '%s je neplatný XML súbor';
$string['invalidzip'] = 'Nahraný neplatný Zip súbor';
//$string['invalidzip2'] = '%s je neplatným XML souborem';
$string['noqtiinzip'] = 'V Zip súbore nie sú žiadne QTI XML súbory';
$string['qunsupported'] = 'Typ úlohy %s nie je podporovaný';
$string['noresponsegroups'] = 'Skupiny nie sú v súčasnosti podporované.';
$string['norenderextensions'] = 'Render extensions nie sú v súčasnosti podporované.';
$string['mrq1other'] = 'Viacnásobná odpoveď - 1 známka za správnu odpoveď';
$string['nomultiplecard'] = 'Všetky štítky sú odlišné a majú viac kardinalít, úloha nie je v Rogo podporovaná. &#333;.';
$string['labelsetserror'] = 'Sady štítkov úlohy nie sú totožné, možno by bolo lepšie toto importovat ako prázdne rolovacie políčka?';
$string['nomultiinputs'] = 'Úlohy s mnohopočetnými numerickými vstupmi nie je možné naimporotvať';
$string['blanktypeerror'] = 'Úlohe doplňovacieho typu chýba rozbaľovacia ponuka alebo vkladaný text';
$string['addingsub'] = 'Pridanie podbodov - render_fib bezdetný';
$string['posnocond'] = 'Pozitívny výsledok bez podmienky/stavu, nie je možné určiť správnu odpoveď.';
$string['multiplepos'] = 'Vo výsledku viac pozitívnych hodnôt, správna odpoveď môže byť zlá.';
$string['multiposmultiopt'] = 'Viacero pozitívnych výsledkov s mnohopočetnými možnosťami odpovede, správna odpoveď môže byť zlá.';
$string['nomatchinglabel'] = 'Nie je možné nájsť informácie o priradzovaní štítkov.';
$string['nolikertfeedback'] = 'Rog&#333; Komentár k Likertovej stupnici sa neuchováva, a je teda stratený.';
$string['nocorrect'] = 'Nie je možné nájsť správnu odpoveď';
$string['multipleconds'] = 'Nájdených viacero podmienok hodnotiacich úlohu, ignorovať všetky okrem prvej.';
$string['mrqnoismulti'] = 'Pokúšate sa načítať MRQ bez multisetu!';
$string['importingtext'] = 'Import textu úlohy s podmienkami hodnotenia. Nebude automaticky oznámkované. Rog&#333;';
$string['someneg'] = 'Niekoľko negativít - 1 známka za každú správnu voľbu s negativitou';
$string['noneg'] = 'Žiadne negativity a niekoľko pozitivít - 1 známka za každú správnu voľbu';

$string['qtiimport'] = 'Importovať QTI ';
$string['imported1_2'] = 'Importované zo súboru QTI 1.2';
$string['paperlocked'] = 'Dokument je uzamknutý';
$string['paperlockedmsg'] = 'Tento dokument je v súčasnosti uzamknutý a nie je ho možné upravovať.';

$string['loadingsection'] = 'Načítanie sekcie';
$string['loadingblank'] = 'Načítanie prázdneho reťazca';
$string['loadingblankdrop'] = 'Načítanie prázdneho rozbaľovacieho zoznamu ';
$string['fileoutput'] = 'Výstup do súboru ';

$string['type'] = 'Typ dokumentu';
?>