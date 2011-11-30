<?php
require '../lang/' . $language . '/include/user_search_options.inc';

$string['impmodtitle'] = 'Rogō: Import modułów';
$string['problem'] = 'Problem - ';
$string['problemv0'] = 'wartość 0: Brak problemu, plik załadowano.';
$string['problemv1'] = 'wartość 1: Załadowany plik miał większy rozmiar niż przewidziano w upload_max_filesize w php.ini.';
$string['problemv2'] = 'wartość 2: Załadowany plik miał większy rozmiar niż przewidziano w MAX_FILE_SIZE w html-form.';
$string['problemv3'] = 'wartość 3: Plik częściowo załadowany.';
$string['problemv4'] = 'wartość 4: Nie załadowano pliku.';
$string['problemvx'] = 'Inny problem: ';

$string['sendwelcomeemail'] = 'Wyślij do użytkownika list powitalny';
$string['importmodules'] = 'Importuj moduły';
$string['csvfile'] = 'Plik CSV:';
$string['import'] = 'Importuj';
$string['msg1'] = "Plik CSV powinien być w formacie eksportowym SATURN. Każdy plik CSV powinien zawierać dane wszystkich studentów zarejestrowanych przy katedrze. (Dane można uzyskać z systemu SATURN przez polecenie: 'Student Exports / Modules II / Faculty of Medicine')";
$string['msg2'] = "Wskaż plik CSV, który chcesz załadować";
$string['addingmodules'] = 'Dodawanie modułów z...';
$string['missingusers'] = 'Brakujący użytkownicy';
$string['modulesadded'] = 'Dodane moduły';
?>