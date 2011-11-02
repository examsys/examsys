<?php
require '../lang/' . $language . '/include/user_search_options.inc';

$string['sendwelcomeemail'] = 'Wyślij do użytkownika list powitalny';
$string['importstudents'] = 'Importuj studentów';
$string['csvfile'] = 'Plik CSV:';
$string['import'] = 'Importuj';
$string['msg1'] = 'Rogō może skomasować ładowanie danych studentów i tworzenie nowych kont z plików CSV. <br />Pierwszy wiersz powinien być wierszem nagłówkowym zawierającym następujące pola:';
$string['msg2'] = "Dodatkowe pola 'Modules' i 'Session' mogą być dodane by zapisywać nowych studentów na wybrane moduły w tym samym czasie.";
$string['loading'] = 'Ładowanie...';
$string['followingerrors'] = 'Nie dodano żadnego użytkownika z powodu następujących błędów:';
$string['usersadded'] = 'Dodani użytkownicy';
$string['usersupdated'] = 'Istniejący użytkownicy zaktualizowani';
$string['missingcolumn'] = 'Brak kolumny \'%s\' w importowanym pliku - dodaj ją.';
$string['finished'] = 'Zakończono';
$string['loadstudents'] = 'Rogō: Ładowanie danych studentów';

$string['emailmsg1'] = '1Create new user account';//niko
$string['emailmsg2'] = '1Dear $title $surname,';
$string['emailmsg3'] = '1A new account has been created to access the online assessment and survey system TouchStone. Your personal authentication details are the same as your university log in details.';
$string['emailmsg4'] = '1Note:';
$string['emailmsg5'] = '1Never share your university username/password with anyone.';
$string['emailmsg6'] = '1Cheating in summative examinations is an academic offence and will not be tolerated.';
$string['emailmsg7'] = '1Could not send mail to <strong>$user_email</strong>.';
?>