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

require '../lang/' . $language . '/paper/start.php';

$string['modelanswer'] = 'Odpowiedź wzorcowa:';
$string['notvisible'] = '<strong>Informacja:</strong> (niewidoczne dla kandydatów)';
$string['reviewermsg'] = 'To jest pytanie typu obliczeniowego. Zmienne są obliczane w czasie rzeczywistym i będą odmienne dla różnych kandydatów. Odpowiedź zaś opiera się na prostym wzorze. Kandydaci nie będą widzieli wyrażeń <strong>$A</strong> i podobnych, będą tylko widzieli losowo dobierane wartości.';
$string['variable'] = 'Zmienna';
$string['generated'] = 'Wygenerowana';
$string['max'] = 'Maks.';
$string['min'] = 'Min.';
$string['qcomments'] = 'Q%d komentarze';
$string['questionok'] = 'Pytanie poprawne';
$string['minorproblems'] = 'Małe/nieliczne problemy';
$string['majorproblems'] = 'Duże/liczne problemy';
$string['deadlineexpired'] = 'Termin finalizacji upłynął'; 
$string['deadlinepassed'] = 'Można przeglądać ten sprawdzian ale nie jest możliwe dokonywanie zmian w komentarzach, jako że termin finalizacji upłynął lub minął czas rozpoczęcia egzaminu.'; 
$string['javacheck2'] = "Czy na pewno chcesz finalizować? Po wybraniu \'OK\' nie będziesz mógł powrócić.";
$string['msgselectable1'] = 'Zaznaczono zbyt dużo opcji! Zaznaczone mogą być tylko ';
$string['msgselectable2'] = ' elementy.';
$string['msgselectable3'] = 'Już zaznaczyłeś ';
$string['msgselectable4'] = '. Wybierz inny ranking.';
$string['confirmsubmit'] = 'Po odpowiedzi na wszystkie pytania na tym ekranie, NIE będzie można już tu powrócić.\nCzy na pewno kontynuować?'; 
$string['AllItemsCorrect'] = "Wszystkie elementy poprawne";
$string['formula'] = 'Wzór';
$string['tolerancefull'] = 'Tolerancja dla punktacji pełnej';
$string['tolerancepartial'] = 'Tolerancja dla punktacji ułamkowej';
?>