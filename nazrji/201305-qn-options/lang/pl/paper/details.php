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

require '../lang/' . $language . '/include/paper_options.inc';
require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/paper/new_paper2.php';

$string['start'] = 'Start'; //cognate
$string['owner'] = 'Właściciel';
$string['question'] = 'Pytanie';
$string['type'] = 'Typ';
$string['marks'] = 'Punkty';
$string['modified'] = 'Zmodyfikowano';
$string['passmark'] = 'Liczba punktów<br />na zaliczenie';
$string['randommark'] = "punktów na 'chybił-trafił'";
$string['screen'] = 'Ekran';
$string['paperlockedwarning'] = '<strong>Arkusz zablokowany</strong>&nbsp;&nbsp;&nbsp;Ten arkusz jest teraz zablokowany i nie może być modyfikowany.';
$string['paperlockedclick'] ='Kliknij po więcej informacji';
$string['earlywarning'] = '<strong>Ostrzeżenie dot. czasu/daty</strong>&nbsp;&nbsp;&nbsp;Ten arkusz planowany jest na wcześniej niż %sam';
$string['farfuturewarning'] = '<strong>Ostrzeżenie dot. czasu/daty</strong>&nbsp;&nbsp;&nbsp;Ten arkusz planowany jest na daleką przyszłość (%s)';
$string['unlock'] = 'Odblokuj';
$string['nooptionsdefined'] = 'Dla pytania nie zdefiniowano żadnych opcji';
$string['noquestionscreen'] = '<strong>Uwaga:</strong> na tym ekranie nie ma żadnych pytań.<br />Spowoduje to błąd podczas testowania pytania!';
$string['markswarning'] = 'Ekran %d ma %d punkty/ów, co stanowi %d%% całej liczby punktów tego arkusza. Wprowadź dodatkowe przerwy ekranowe w celu zminimalizowania start danych w przypadku zawieszenia komputera.';// Please insert additional screen breaks to minimise data loss in the event of a computer crash.';
$string['nocorrect'] = 'Nie określono poprawnej odpowiedzi';
$string['zeromarks'] = 'Uwaga: ustawiono brak punktacji.';
$string['toomanycorrect'] = 'Zbyt dużo opcji poprawnych';
$string['answermissing'] = 'Brakuje poprawnych odpowiedzi dla niektórych opcji.';
$string['nolabels'] = 'Do obrazu nie dodano etykiet.'; 
$string['mcqsurvey'] = "Pytania wielokrotnego wyboru z opcją 'inne' powinny być tylko używane w ankietach";
$string['dichotomouswarning'] = '%d z %d';
$string['warning'] = 'Uwaga';
$string['variablenomarks'] = 'Uwaga: Zmienna liczba punktów';
$string['export12'] = 'Eksport 1.2'; //cognate
$string['import'] = 'Import'; //cognate
$string['paperdeleted'] = 'Arkusz usunięty';
$string['deleted_msg1'] = 'Arkusz <strong>%s</strong> został usunięty.';
$string['deleted_msg2'] = 'Może być nadal odzyskany z <a href="' . $configObject->get('cfg_root_path') . '/delete/recycle_list.php" style="color:blue">kosza</a>.';
$string['deleted_msg3'] = 'Nie jesteś właścicielem tego arkusza, musisz skontaktować się z <a href="mailto:%s" style="color:blue">%s %s</a> by go odzyskać.';
$string['addscreenbreak'] = 'Wstaw podzielnik ekranu';  
$string['deletescreenbreak'] = 'Usuń podzielnik ekranu';
$string['next'] = 'Dalej >>';  
$string['na'] = 'Brak'; 
?>