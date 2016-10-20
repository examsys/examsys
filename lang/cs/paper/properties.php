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
$string['edittitle'] = 'Upravit';
$string['warning'] = 'Varování: název dokumentu je již použit pro jiné hodnocení!';
$string['availablefromyear'] = 'Rok OD je větší než rok DO - nelogické!';
$string['availablefrommonth'] = 'Měsíc OD je větší než měsíc DO - nelogické!';
$string['availablefromday'] = 'Den OD je větší než den DO - nelogické!';
$string['availablefromhour'] = 'Hodiny: Dostupnost OD je vyšší než dostupnost DO  - nelogické!';
$string['availablefromminute'] = 'Minuty: Dostupnost OD je vyšší než dostupnost DO  - nelogické!';
$string['msg1'] = 'Nejsou vybrány žádné moduly. Dokument musí být přiřazen nejméně k jednomu modulu.';
$string['msg2'] = 'Začátek a konec sumativní zkoušky musí být ve stejný den (záložka \'Přístupová práva\' ).';
$string['msg3'] = 'U sumativních zkoušek musí být zadána délka trvání.\nTa odpovídá času vyměřenému k vykonání zkoušky, bez přidaného času pro např. dyslektické studenty.';
$string['msg4'] = 'U sumativních zkoušek musí být zadán akademický rok, záložka (\'Přístupová práva\').';
$string['msg5'] = 'OSCE test musí být přiřazen nejméně k jednomu modulu.';
$string['msg6a'] = 'Zadali jste recenzenty, ale nezadali jste lhůtu.';
$string['msg6'] = 'Zadali jste externí recenzenty, ale nezadali jste lhůtu.';
$string['msg7'] = 'Zadejte, prosím, název dokumentu.';
$string['msg8'] = 'Jedná se o uzavřenou zkoušku, tedy bez použití pomůcek. Studenti nesmí používat jiných zdrojů (včetně souseda), než kterých je užito v dokumentu. Nelze používat žádná jiná elektronická zařízení, než počítač ke zkoušce určený. Slovníky <em>nejsou</em> povoleny s jedinou výjimkou. Ti, kteří <em>nemají</em> češtinu jako rodný jazyk, mohou používat pro překlad do češtiny slovník. Za podmínky, že ani jeden z jazyků není předmětem zkoušky. Odborné slovníky jsou zakázány. Během zkoušky a ani po jejím ukončení není dovoleno z učebny odnášet jakékoliv dokumenty a poznámky. Veškeré poznámky, které si zapíšete v průběhu zkoušky budou shromážděny Dohlížejícím a zlikvidovány.';


// General tab
$string['generaltab'] = 'Celkem';
$string['generalheading'] = 'Název dokumentu, známkování a možnosti zobrazení';
$string['paperdetails'] = 'Detaily dokumentu';
$string['onlyonexamday'] = '(pouze v den zkoušky)';
$string['url'] = 'URL';
$string['name'] = 'Název';
$string['type'] = 'Typ';
$string['folder'] = 'Složka';
$string['feedback'] = 'Komentář';
$string['displayoptions'] = 'Zobrazit volby';
$string['display'] = 'Zobrazit';
$string['windowed'] = 'Po oknech';
$string['fullscreen'] = 'Celá obrazovka (pouze IE)';
$string['navigation'] = 'Navigace';
$string['bidirectional'] = 'Obousměrná';
$string['unidirectional'] = 'Jednosměrná (lineární)';
$string['background'] = 'Pozadí';
$string['foreground'] = 'Písmo';
$string['theme'] = 'Téma';
$string['labelsnotes'] = 'Popisky/Poznámky';
$string['calculator'] = 'Kalkulačka';
$string['displaycalculator'] = 'Zobrazit kalkulačku';
$string['audio'] = 'Audio';
$string['demosoundclip'] = 'demo zvuku';
$string['marking'] = 'Známkování';
$string['paperpublishedwarning'] = '<strong>Paper grades have been published.</strong>&nbsp;&nbsp;&nbsp;Marking adjustment can no longer occur.';
$string['overallclassification'] = 'Celková klasifikace';
$string['overallclass1'] = '&lt;Automaticky&gt;';
$string['overallclass2'] = 'Neuspěl | Na hraně | Uspěl';
$string['overallclass3'] = 'Nedostatečně | Dostatečně | Dobře | Chvalitebně | Výborně';
$string['overallclass4'] = 'Neuspěl | Na hraně | Uspěl | Uspěl s vyznamenáním';
$string['overallclass5'] = 'Uspěl | Neuspěl';
$string['passmark'] = 'Potřebná známka';
$string['distinction'] = 'S vyznamenáním';
$string['method'] = 'Metoda';
$string['noadjustment'] = 'Neupraveno';
$string['calculatrrandommark'] = 'Vypočítat náhodnou známku';
$string['stdset'] = 'Nastavení standardů';
$string['borderlinemethod'] = 'Hraniční metoda';
$string['ticks_crosses'] = 'Odškrtnutí/Křížky';
$string['question_marks'] = 'Hodnocení úlohy';
$string['hideallfeedback'] = 'Skrýt veškeré komentáře';
$string['correctanswerhighlight'] = 'Zvýraznit správné odpovědi';
$string['textfeedback'] = 'Text komentáře';
$string['photos'] = 'Fotky';
$string['ifavailable'] = 'pokud je k dispozici';
$string['review'] = 'Přehled';
$string['allpeerspergroup'] = 'Všichni členové skupiny';
$string['singlereview'] = 'Jedena recenze';
$string['numberfrom'] = 'Číslo z';
$string['groupdetails'] = 'Podrobnosti skupiny';
$string['tooltip_random'] = 'Rogo vypočítá ohodnocení, jakého by student dosáhl náhodným zodpovězením úloh. Procentuální hodnocení je následně tomuto uzpůsobeno.';
$string['tooltip_calculator'] = 'Studentům je v rámci testu k dispozici JavaScriptová kalkulačka.';
$string['tooltip_audio'] = 'Na úvodní stránce Zkoušky bude umístěn zkušební zvukový klip, aby si studenti mohli upravit hlasitost ještě před zahájením testování.';
$string['tooltip_osceclassification'] = 'Upozornění: jakmile je hodnocení zahájeno, nelze klasifikaci měnit.';
$string['externalid'] = 'External System ID';
$string['externalsys'] = 'External System';

// Security tab
$string['securitytab'] = 'Bezpečnost';
$string['securityheading'] = 'Nastavení přístupových práv, jež umožní zobrazení dokumentu studentovi.';
$string['session'] = 'Relace';
$string['password'] = 'Heslo';
$string['timezone'] = 'Časová zóna';
$string['modules'] = 'Modul(y)';
$string['duration'] = 'Trvání';
$string['mins'] = 'minut';
$string['hrs'] = 'hodin';
$string['availablefrom'] = 'Dostupné od';
$string['to'] = 'do';
$string['restricttolabs'] = 'Omezit na učebny';
$string['restricttometadata'] = 'Omezit na metadata';
$string['na'] = 'N/A';
$string['tooltip_password'] = 'Tímto se  přidá další přístupové heslo k testu; k heslu, kterým se studenti hlásí do systému. Toto heslo lze studentům sdělit až  v počítačové učebně před zkouškou samotnou.';
$string['donotchangewarning'] = 'This paper is locked. It is recommended you only make changes if no students have attempted this paper';

// Záložka Recenzenta 
$string['reviewerstab'] = 'Recenzenti';
$string['reviewersheading'] = 'Nastavení interních/externích recenzentů a termínů.';
$string['internalreviewers'] = 'Interní recenzenti';;
$string['externalexaminers'] = 'Externí recenzenti';
$string['deadline'] = 'Uzávěrka';

// Záložka zkoušky
$string['rubrictab'] = 'Rubrika zkoušky';
$string['rubricheading'] = 'Před zahájením sumativní zkoušky, se studentům zobrazí Rubrika zkoušky.';

// Úvodní záložka
$string['prologuetab'] = 'Úvod';
$string['prologueheading'] = 'Text zobrazený při zahájení dokumentu v horní části obrazovky.';

// Záložka postskripta
$string['postscripttab'] = 'Dodatek';
$string['postscriptheading'] = "Text zobrazený po kliknutí na 'Konec'.";

// Záložka Referenčních materiálů
$string['referencematerial'] = 'Referenční materiály';
$string['referenceheading'] = 'Určit, které referenční materiály budou v dokumentu k dispozici.';
$string['nomaterials'] = 'K tomuto dokumentu nejsou dostupné žádné referenční materiály.<br /><br />Referenční materiál může být doplněn kliknutím na volbu \'Referenční materiál\' v modulu (<a href="" style="color:blue" onclick="launchHelp(296); return false;">viz nápověda</a>).';

// Záložka zpětné vazby
$string['feedbackheading'] = 'Komentář dostupný studentům';
$string['feedbackwarning'] = '<strong>Poznámka:</strong> Tímto se úlohy zveřejní, včetně správných odpovědí a známek studentů.';
$string['on'] = 'Zapnuto';
$string['off'] = 'Vypnuto';
$string['objectivesreport'] = 'Komentář dle cílů';
$string['questionfeedback'] = 'Komentáře dle úloh';
$string['externalexaminerfeedback'] = 'Výsledky třídy (externí zkoušející)';
$string['externalwarning'] = 'Pokud zapnuto, externisté mají u testu přístup k Celkovému přehledu třídy..';
$string['textualfeedback'] = 'Textový Komentář';
$string['above'] = 'Nad';
$string['message'] = 'Zpráva';
$string['answerscreensettings'] = 'Nastavení okna Odpovědi';

// Záložka změn
$string['changes'] = 'Změny';
$string['changesheading'] = 'Seznam změn k aktuálnímu dokumentu.';
$string['part'] = 'Část';
$string['old'] = 'Staré';
$string['new'] = 'Nové';
$string['date'] = 'Datum';
$string['author'] = 'Autor';
$string['startdate'] = 'Datum startu';
$string['enddate'] = 'Datum konce';
$string['retired'] = 'Neplatný';
$string['externalreviewdeadline'] = 'Uzávěrka externího recenzenta';
$string['internalreviewdeadline'] = 'Uzávěrka interního recenzenta';

//Colour picker
$string['colour'] = 'Barva';
$string['themecolours'] = 'Barevné motivy';
$string['standardcolours'] = 'Standardní barvy';
$string['more'] = 'Více...';
$string['cancel'] = 'Zrušit';
$string['OK'] = 'OK';

$string['markingguidance'] = 'Pokyny pro zkoušejícího';
$string['cohortperformancefeedback'] = 'Komentář k výkonu skupiny';
?>