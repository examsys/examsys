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

$string['systemupdate'] = 'Aktualizacja systemu';
$string['actionrequired'] = 'Wymagane działania';
$string['readonly'] = "Nie zapomnij nadać plikowi <strong>/include/load_config.php</strong> atrybut 'readonly'! (chmod 444)";
$string['finished'] = 'Zakończono!';
$string['couldnotwrite'] = 'Błąd: nie można zapisać pliku konfiguracyjnego!';
$string['msg1'] = 'Ten skrypt aktualizuje struktury bazy danych w celu uzgodnienia ich z nowym kodem %s. Uruchamianie go wiele razy nie spowoduje szkód, gdyż przed zapisaniem zmian sprawdza ona istniejącą strukturę bazy danych.';
$string['msg2'] = 'Skrypt aktualizujący wymaga loginu i hasła administratora MySQL w celu aktualizacji bazy danych, użytkowników i tablic. Podana nazwa użytkownika nie jest zapisywana na serwerze i wykorzystywana jest tylko przez ten skrypt.';
$string['databaseadminuser'] = 'Administrator bazy danych';
$string['dbusername'] = 'Nazwa użytkownika bazy danych';
$string['dbpassword'] = 'Hasło użytkownika bazy danych';
$string['onlinehelpsystems'] = 'Systemy pomocy Online';
$string['updatestaffhelp'] = 'Aktualizuj Pomoc dla Kadry'; 
$string['updatestudenthelp'] = 'Aktualizuj Pomoc dla Studentów'; 
$string['startupdate'] = 'Rozpocznij aktualizację';
$string['warning1'] = 'Ta aktualizacja wymaga aby plik /config/config.inc.php był zapisywalny.';
$string['warning2'] = 'Przypisz plik do webserwera (chown) i zmień jego atrybuty na 644 (chmod)';
$string['warning3'] = 'Ta aktualizacja wymaga aby katalog /config był zapisywalny.';
$string['warning4'] = 'Przypisz plik do webserwera (chown) i zmień jego atrybuty na 744 (chmod)';
$string['updatefromversion'] = 'Aktualizacja z wersji';
$string['home'] = 'Strona główna';
$string['startingupdate'] = 'Rozpoczynanie aktualizacji'; 
?>