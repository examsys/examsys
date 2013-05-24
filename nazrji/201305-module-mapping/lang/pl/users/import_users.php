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

require '../lang/' . $language . '/include/user_search_options.inc';

$string['sendwelcomeemail'] = 'Wyślij do użytkownika list powitalny';
$string['csvfile'] = 'Plik CSV:';
$string['import'] = 'Importuj';
$string['msg1'] = 'Nowe konta użytkowników (pracowników lub studentów) mogą być utworzone na podstawie danych z pliku CSV. <br />Pierwszy wiersz powinien być wierszem nagłówkowym zawierającym następujące pola:'; 
$string['msg2'] = "Można dodać pola 'Modules' i 'Session' co pozwali zapisywać nowych studentów na wybrane moduły.";
$string['loading'] = 'Ładowanie...';
$string['followingerrors'] = 'Nie dodano żadnego użytkownika z powodu następujących błędów:';
$string['usersadded'] = 'Dodani użytkownicy';
$string['usersupdated'] = 'Zaktualizowani istniejący użytkownicy';
$string['missingcolumn'] = 'Brak kolumny \'%s\' w importowanym pliku - dodaj ją.';
$string['finished'] = 'Zakończono';

$string['emailmsg1'] = 'Utwórz nowe konto użytkownika';
$string['emailmsg2'] = '';
$string['emailmsg3'] = 'Utworzone zostało nowe konto w Rogō - systemie elektronicznego ankietowania i egzaminowania. Szczegóły Twego osobistego uwierzytelniania są identyczne jak szczegóły logowania do Twojego konta uniwersyteckiego.';
$string['emailmsg4'] = 'Uwaga:';
$string['emailmsg5'] = 'Nigdy nie ujawniaj nikomu swego loginu i hasła.';
$string['emailmsg6'] = 'Oszukiwanie na egzaminie końcowym jest wykroczeniem akademickim i nie będzie tolerowane.';
$string['emailmsg7'] = 'Nie można było wysłać Emaila na ';
?>