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

require '../lang/' . $language . '/std_setting/std_set_shared.php';
require '../lang/' . $language . '/paper/start.php';

$string['modifiedangoffmethod'] = 'Modifikovaná Angoffova metóda';
$string['ebelmethod'] = 'Ebelova metóda';
$string['modangoffstep1'] = 'Pomocou oranžového rozbaľovacieho zoznamu, vedľa každej úlohy, označte <strong>minimum</strong> študentov, ktoré očakávate, že v danej kategórii, odpovie správne.';
$string['step1'] = '<strong>Krok 1:</strong><br />Pri každej úlohe najprv pomocou oranžového rozbaľovacieho zoznamu vyberte <strong>obtiažnosť</strong> úlohy (ľahká, Stredná alebo Ťažká) a potom priraďte k úlohám <strong>významnosť</strong> (Zásadná, Dôležitá alebo Okrajová).';
$string['step2'] = '<strong>Krok 2: Mriežka potrebnej známky</strong><br />U každej kategórie (napr. Ľahká/Zásadná, Ľahká/Dôležitá, a pod.) určte <strong>minimálny</strong> počet kandidátov, ktorý očakávate, že v danej kategórii, odpovie správne.';
$string['step3'] = '<strong>Krok 3: Mriežka s vyznamenaním</strong><br />U každej kategórie (napr. Ľahká/Zásadná, Ľahká/Dôležitá, a pod.) určte <strong>minimálny</strong> počet kandidátov, ktorý očakávate, že v danej kategórii, odpovie správne.';
$string['gridbelow'] = 'Použite mriežku nižšie';
$string['top20'] = 'Vyberte najlepších 20%';
$string['donotapply'] = 'Nepoužívajte';
$string['easy'] = 'Jednoduchá';
$string['medium'] = 'Stredná';
$string['hard'] = 'Neľahká';
$string['essential'] = 'Zásadná';
$string['important'] = 'Dôležitá';
$string['nicetoknow'] = 'Doplnková';
$string['papermarks'] = 'známky dokumentu';
$string['reviewmarks'] = 'prehľad známok';
$string['cutscore'] = 'znížené skóre';
$string['saveexit'] = 'Uložiť &amp; ukončiť';
$string['savecontinue'] = 'Uložiť &amp; pokračovať';
$string['savebank'] = 'Uložiť body hodnotenia do banky úloh';
$string['cannotbeused'] = '<strong>Poznámka:</strong> Ebelova metóda nemôže byť použitá u úloh typu štandardné textové úlohy.';
$string['na'] = 'N/A';
$string['screen'] = 'Obrazovka';
$string['note'] = 'POZNÁMKA:';
$string['notpossibletostandard'] = 'Nie je možné u úloh typu test zhody so scenárom.';
$string['notvisible'] = '<strong>Informácia:</strong> (nie je viditeľné pre kandidátov/študentov)';
$string['reviewermsg'] = 'Toto je výpočtová úloha. Premenné sú vypočítané on-line a budú sa pre jednotlivých kandidátov líšiť. Odpoveď je však založená na jednom vzorci. Kandidáti nevidia <strong>$A</strong>, atď. Uvidia iba náhodne generované čísla.';
$string['variable'] = 'Premenná';
$string['generated'] = 'Generované';
$string['max'] = 'Maximum';
$string['min'] = 'Minimum';
$string['formula'] = 'Vzorec';
$string['tolerancefull'] = 'Odchýlka pre celkové hodnotenie';
$string['tolerancepartial'] = 'Odchýlka pre čiastočné hodnotenie';
$string['togglevariables'] = 'Premenné';
?>