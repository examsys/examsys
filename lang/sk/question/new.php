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

require $configObject->get('cfg_web_root') . 'lang/' . $language . '/include/question_types.inc';

$string['newquestion'] = 'Nová úloha';
$string['area_desc'] = 'Umožňuje študentom, ako ich odpoveď, vyznačiť určitú plochu.';
$string['enhancedcalc_desc'] = 'Numerická odpoveď založená na úlohe s náhodnými premennými.';
$string['dichotomous_desc'] = 'Prezentácia viacerých úloh typu áno/nie.';
$string['extmatch_desc'] = 'Prezentácia viacerých scenárov zdieľajúcich spoločnú sadu možností odpovedí.';
$string['blank_desc'] = 'Textový odstavec s vloženými prázdnymi políčkami, ktorá študent vyplní.';
$string['info_desc'] = 'Nie je úlohou ako takou - avšak poskytuje študentovi informácie, ktoré mu pomôžu so zvyškom testových úloh.';
$string['matrix_desc'] = 'V maticovom zobrazení k sebe priraďte otázky a odpovede.';
$string['hotspot_desc'] = 'Študent musí kliknúť na správnu časť obrázka. V jednej úlohe môže byť 1 a viac oblastí.';
$string['labelling_desc'] = 'Študent musí presunúť popisky ku správnym zástupným symbolom na obrázku.';
$string['likert_desc'] = 'Psychometrické stupnice pre použitie pri prieskumoch.';
$string['mcq_desc'] = 'Vyber jednu variantu z mnohých.';
$string['mrq_desc'] = 'Vyber niekoľko variánt z mnohých.';
$string['keyword_based_desc'] = "Táto úloha je kontajnerom pre súbor \"zdrojových\" úloh, závislých na zadanom kľúčovom slove, z ktorých jedna bude študentovi náhodne vybraná.";
$string['random_desc'] = "Táto úloha je kontajnerom pre súbor \"zdrojových\" úloh, z ktorých jedna bude študentovi náhodne vybraná.";
$string['rank_desc'] = 'Ohodnoťte sadu zoradených možností.';
$string['sct_desc'] = 'Úlohy vyhodnocujúce schopnosť interpretácie klinických údajov.';
$string['textbox_desc'] = 'Textové polia zachytávajúce študentove odpovede. Môžu byť použité pri výzkumoch a hodnotení. Odpovede v textových poliach vyžadujú ručné hodnotenie učiteľom.';
$string['true_false_desc'] = 'Úloha, na ktorú sa odpovedá áno/nie.';
?>