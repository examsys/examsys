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

require_once '../lang/' . $language . '/include/paper_types.inc';
require_once '../lang/' . $language . '/paper/new_paper1.php';
require_once '../lang/' . $language . '/include/months.inc';

$string['availability'] = 'Dostępność';
$string['summativeexamdetails'] = 'Szczegóły egzaminu końcowego';
$string['academicsession'] = 'Sesja akademicka';
$string['timezone'] = 'Strefa czasowa';
$string['from'] = 'Od';
$string['to'] = 'Do';
$string['modules'] = 'Moduł(y)';
$string['finish'] = 'Zakończ';
$string['msg4'] = 'Nie wybrano żadnego modułu. Arkusze muszą być przypisane do przynajmniej jednego modułu.';
$string['msg5'] = "Nazwa '%s' jest już wykorzystywana. Wybierz inny tytuł arkusza.";
$string['msg6'] = 'To jest egzamin typu "closed-book", w czasie którego <em>niedozwolone jest</em> korzystanie ze środków i źródeł pomocniczych (także słowników) ani pomocy osób drugich. Niedozwolone jest też używanie urządzeń elektronicznych z wyjątkiem komputera egzaminacyjnego.';

$string['barriersneeded'] = 'Potrzebne bariery';  
$string['daterequired'] = 'Wymagana data';
$string['cohortsize'] = 'Rozmiar grupy';
$string['wholecohort'] = 'cała grupa';
$string['sittings'] = 'Posiedzenia'; 
$string['campus'] = 'Kampus';
$string['notes'] = 'Notatki';
$string['mins'] = 'min.';

$string['msg7'] = 'Uwaga: Musisz określić datę przeprowadzenia egzaminu.';
$string['msg8'] = 'Uwaga: Musisz określić czas trwania egzaminu (w minutach).';
$string['msg9'] = 'Uwaga: Musisz określić rozmiar grupy.';
?>