<?php
require '../lang/' . $language . '/include/user_search_options.inc';

$string['sendwelcomeemail'] = 'Wyślij do użytkownika list powitalny';
$string['csvfile'] = 'Plik CSV:';
$string['import'] = 'Importuj';
$string['msg1'] = 'Nowe konta użytkowników (pracowników lub studentów) mogą być utworzone na podstawie danych z pliku CSV. Pierwszy wiersz powinien być wierszem nagłówkowym zawierającym następujące pola:'; 
$string['msg2'] = "Dodatkowe pola 'Modules' i 'Session' mogą być dodane by zapisywać nowych studentów na wybrane moduły w tym samym czasie.";
$string['loading'] = 'Ładowanie...';
$string['followingerrors'] = 'Nie dodano żadnego użytkownika z powodu następujących błędów:';
$string['usersadded'] = 'Dodani użytkownicy';
$string['usersupdated'] = 'Istniejący użytkownicy zaktualizowani';
$string['missingcolumn'] = 'Brak kolumny \'%s\' w importowanym pliku - dodaj ją.';
$string['finished'] = 'Zakończono';

$string['emailmsg1'] = 'Utwórz nowe konto użytkownika';
$string['emailmsg2'] = '';
$string['emailmsg3'] = 'Utworzone zostało nowe konto w Rogō - systemie elektronicznego ankietowania i egzaminowania. Szczegóły Twego osobistego dostępu są identyczne jak szczegóły logowania do Twojego konta uniwersyteckiego.';
$string['emailmsg4'] = 'Uwaga:';
$string['emailmsg5'] = 'Nigdy nie ujawniaj nikomu swego loginu i hasła.';
$string['emailmsg6'] = 'Oszukiwanie na egzaminie końcowym jest wykroczeniem akademickim i nie będzie tolerowane.';
$string['emailmsg7'] = 'Nie można było wysłać Emaila do.';
?>