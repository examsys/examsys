<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/include/paper_types.inc';

$string['propertiestitle'] = 'Vlastnosti';
$string['edittitle'] = 'Upraviť';
$string['warning'] = 'Varovanie: názov dokumentu je už použitý pre iné hodnotenie!';
$string['availablefromyear'] = 'Rok OD je väčší ako rok DO - nelogické!';
$string['availablefrommonth'] = 'Mesiac OD je väčší ako mesiac DO - nelogické!';
$string['availablefromday'] = 'Deň OD je väčší ako deň DO - nelogické!';
$string['availablefromhour'] = 'Hodiny: Dostupnosť OD je vyššia ako dostupnosť DO  - nelogické!';
$string['availablefromminute'] = 'Minúty: Dostupnosť OD je vyššíia ako dostupnosť DO  - nelogické!';
$string['msg1'] = 'Nie sú vybrané žiadne moduly. Dokument musí byť priradený najmenej k jednému modulu.';
$string['msg2'] = 'Začiatok a koniec sumatívnej skúšky musí byť v rovnaký deň (záložka \'Bezpečnosť\' ).';
$string['msg3'] = 'U sumatívnych skúšok musí byť zadaná dĺžka trvania.\nTá zodpovedá času vymedzenému na vykonanie skúšky, bez pridaného času pre napr. dyslektických študentov.';
$string['msg4'] = 'U sumativnych skúšok musí býť zadaný akademický rok, záložka (\'Bezpečnosť\').';
$string['msg5'] = 'OSCE test musí byť priradený najmenej k jednému modulu.';
$string['msg6a'] = 'Zadali ste recenzentov, ale nezadali ste termín.';
$string['msg6'] = 'Zadali ste externých recenzentov, ale nezadali ste termín.';
$string['msg7'] = 'Zadajte, prosím, názov dokumentu.';
$string['msg8'] = 'Jedná sa o uzavretú skúšku, teda bez použitia pomôcok. Študenti nesmú používať iné zdroje (vrátane suseda), než tie ktoré sú použité v dokumente. Nie je možné používať žiadne iné elektronické zariadenia, než je počítač určený ku skúške. Slovníky <em>nie sú</em> povolené s jedinou výnimkou. Tí, ktorí <em>nemajú</em> slovenčinu ako rodný jazyk, môžu používať pre preklad do slovenčiny slovník. Za podmienky, že ani jeden z jazykov nie je predmetom skúšky. Odborné slovníky sú zakázané. Počas skúšky a ani po jej ukončení nie je dovolené z učebne odnášať akékoľvek dokumenty a poznámky. Všetky poznámky, ktoré si zapíšete v priebehu skúšky budú zhromaždené dozorujúcim personálom a zlikvidované.';


// General tab
$string['generaltab'] = 'Celkom';
$string['generalheading'] = 'Názov dokumentu, známkovanie a možnosti zobrazenia';
$string['paperdetails'] = 'Detaily dokumentu';
$string['onlyonexamday'] = '(iba v deň skúšky)';
$string['url'] = 'URL';
$string['name'] = 'Názov';
$string['type'] = 'Typ';
$string['folder'] = 'Priečinok';
$string['feedback'] = 'Komentár';
$string['displayoptions'] = 'Zobraziť možnosti';
$string['display'] = 'Zobraziť';
$string['windowed'] = 'Po oknách';
$string['fullscreen'] = 'Celá obrazovka (iba IE)';
$string['navigation'] = 'Navigácia';
$string['bidirectional'] = 'Obojsmerná';
$string['unidirectional'] = 'Jednosmerná (lineárna)';
$string['background'] = 'Pozadie';
$string['foreground'] = 'Písmo';
$string['theme'] = 'Téma';
$string['labelsnotes'] = 'Popisky/Poznámky';
$string['calculator'] = 'Kalkulačka';
$string['displaycalculator'] = 'Zobraziť kalkulačku';
$string['audio'] = 'Audio';
$string['demosoundclip'] = 'demo zvuku';
$string['marking'] = 'Známkovanie';
$string['paperpublishedwarning'] = '<strong>Paper grades have been published.</strong>&nbsp;&nbsp;&nbsp;Marking adjustment can no longer occur.';
$string['overallclassification'] = 'Celková klasifikácia';
$string['overallclass1'] = '&lt;Automaticky&gt;';
$string['overallclass2'] = 'Neuspel | Na hrane | Uspel';
$string['overallclass3'] = 'Nedostatočne | Dostatočne | Dobre | Chválitebne | Výborne';
$string['overallclass4'] = 'Neuspel | Na hrane | Uspel | Uspel s vyznamenením';
$string['overallclass5'] = 'Uspel | Neuspel';
$string['passmark'] = 'Potrebná známka';
$string['distinction'] = 'S vyznamenaním';
$string['method'] = 'Metóda';
$string['noadjustment'] = 'Neupravené';
$string['calculatrrandommark'] = 'Vypočítať náhodnú známku';
$string['stdset'] = 'Nastavenie štandardov';
$string['borderlinemethod'] = 'Hraničná metóda';
$string['ticks_crosses'] = 'Odškrtnutie/Krížiky';
$string['question_marks'] = 'Hodnotenie úlohy';
$string['hideallfeedback'] = 'Skryť všetky komentáre';
$string['correctanswerhighlight'] = 'Zvýrazniť správne odpovede';
$string['textfeedback'] = 'Text komentára';
$string['photos'] = 'Fotky';
$string['ifavailable'] = 'ak je k dispozícii';
$string['review'] = 'Prehľad';
$string['allpeerspergroup'] = 'Všetci členovia skupiny';
$string['singlereview'] = 'Jeden komentár';
$string['numberfrom'] = 'Číslo z';
$string['groupdetails'] = 'Podrobnosti skupiny';
$string['tooltip_random'] = 'Rogo vypočíta hodnotenie, aké by študent dosiahol náhodným zodpovedaním úloh. Percentuálne hodnotenie je následne tomuto prispôsobené.';
$string['tooltip_calculator'] = 'Študentom je v rámci testu k dispozícii JavaScriptová kalkulačka.';
$string['tooltip_audio'] = 'Na úvodnej stránke skúšky bude umiestnený skúšobný zvukový klip, aby si študenti mohli upraviť hlasitosť ešte pred zahájením testovania.';
$string['tooltip_osceclassification'] = 'Upozornenie: akonáhle je hodnotenie začaté, nie je možné klasifikáciu meniť.';
$string['externalid'] = 'External System ID';
$string['externalsys'] = 'External System';

// Security tab
$string['securitytab'] = 'Bezpečnosť';
$string['securityheading'] = 'Nastavenie prístupových práv, ktoré umožní zobrazenie dokumentu študentovi.';
$string['session'] = 'Relácia';
$string['password'] = 'Heslo';
$string['timezone'] = 'Časová zóna';
$string['modules'] = 'Modul(y)';
$string['duration'] = 'Trvanie';
$string['mins'] = 'minút';
$string['hrs'] = 'hodín';
$string['availablefrom'] = 'Dostupné od';
$string['to'] = 'do';
$string['restricttolabs'] = 'Obmedziť na učebne';
$string['restricttometadata'] = 'Obmedziť na metadata';
$string['na'] = 'N/A';
$string['tooltip_password'] = 'Týmto sa  pridá ďalšie prístupové heslo k testu; k heslu, ktorým sa študenti hlásia do systému. Toto heslo je možné študentom povedať až v počítačovéj učebni pred samotnou skúškou.';
$string['donotchangewarning'] = 'This paper is locked. It is recommended you only make changes if no students have attempted this paper';

// Záložka Recenzenta 
$string['reviewerstab'] = 'Recenzenti';
$string['reviewersheading'] = 'Nastavenie interných/externých recenzentov a termínov.';
$string['internalreviewers'] = 'Interní recenzenti';;
$string['externalexaminers'] = 'Externí recenzenti';
$string['deadline'] = 'Uzávierka (Konečný termín)';

// Záložka zkoušky
$string['rubrictab'] = 'Rubrika skúšky';
$string['rubricheading'] = 'Pred začiatkom sumatívnej skúšky, sa študentom zobrazí rubrika skúšky.';

// Úvodní záložka
$string['prologuetab'] = 'Úvod';
$string['prologueheading'] = 'Text zobrazený pri zahájení dokumentu v hornej časti obrazovky.';

// Záložka postskripta
$string['postscripttab'] = 'Dodatok';
$string['postscriptheading'] = "Text zobrazený po kliknutí na 'Dokončiť'.";

// Záložka Referenčního materiálu
$string['referencematerial'] = 'Referenčný materiál';
$string['referenceheading'] = 'Určiť, ktoré referenčné materiály budú v dokumente k dispozícii.';
$string['nomaterials'] = 'K tomuto dokumentu nie sú dostupné žiadne referenčné materiály.<br /><br />Referenčný materiál môže byť doplnený kliknutím na voľbu \'Referenčný materiál\' v module (<a href="" style="color:blue" onclick="launchHelp(296); return false;">viď nápoveda</a>).';

// Záložka zpětné vazby
$string['feedbackheading'] = 'Komentár dostupný študentom';
$string['feedbackwarning'] = '<strong>Poznámka:</strong> Týmto sa úlohy zverejnia, vrátane správnych odpovedí a známok študentov.';
$string['on'] = 'Zapnuté';
$string['off'] = 'Vypnuté';
$string['objectivesreport'] = 'Komentáre podľa cieľov';
$string['questionfeedback'] = 'Komentáre podľa úloh';
$string['externalexaminerfeedback'] = 'Výsledky triedy (externí skúšajúci)';
$string['externalwarning'] = 'Ak je zapnuté, externisti majú v teste prístup k celkovému prehľadu triedy.';
$string['textualfeedback'] = 'Textový komentár';
$string['above'] = 'Nad';
$string['message'] = 'Správa';
$string['answerscreensettings'] = 'Nastavenie okna odpovede';

// Záložka změn
$string['changes'] = 'Zmeny';
$string['changesheading'] = 'Zoznam zmien k aktuálnemu dokumentu.';
$string['part'] = 'Časť';
$string['old'] = 'Staré';
$string['new'] = 'Nové';
$string['date'] = 'Dátum';
$string['author'] = 'Autor';
$string['startdate'] = 'Dátum začiatku';
$string['enddate'] = 'Dátum konca';
$string['retired'] = 'Neplatný';
$string['externalreviewdeadline'] = 'Uzávierka externého recenzenta';
$string['internalreviewdeadline'] = 'Uzávierka interného recenzenta';

//Colour picker
$string['colour'] = 'Farba';
$string['themecolours'] = 'Farebné motívy';
$string['standardcolours'] = 'Štandardné farby';
$string['more'] = 'Viac...';
$string['cancel'] = 'Zrušiť';
$string['OK'] = 'OK';

$string['markingguidance'] = 'Pokyny pre skúšajúceho';
$string['cohortperformancefeedback'] = 'Komentár k výkonu skupiny';
?>