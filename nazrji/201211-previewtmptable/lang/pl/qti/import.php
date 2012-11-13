<?php
require '../lang/' . $language . '/include/paper_options.inc';
require 'shared.inc';
require '../lang/' . $language . '/question/edit/likert_scales.php';

$string['import'] = 'Import'; //cognate
$string['import2'] = 'Importuj';
$string['importfromqti'] = 'Importuj z QTI';
$string['file'] = 'plik';
$string['qtiimporterror'] = 'Błąd importowania pliku QTI';
$string['qtiimported'] = 'Zaimportowano plik QTI';
$string['questionproblems'] = 'Niektóre z pytań nie zostały zaimportowane poprawnie.';
$string['hadproblemsimporting'] = 'Błąd importowania %d z %d pytań.';
$string['importedquestions'] = 'Zaimportowano %d pytań.';
$string['backtopaper'] = 'Powrót do arkusza';
$string['errmsg1'] = 'Ten typ eksportu nie jest obsługiwany';
$string['errmsg2'] = 'Ten typ importu nie jest obsługiwany';

$string['invalidxml'] = '%s jest nieprawidłowym plikiem XML';
$string['invalidzip'] = 'Załadowano nieprawidłowy plik ZIP';
$string['noqtiinzip'] = 'Brak plików QTI XML w pliku ZIP';
$string['qunsupported'] = 'Pytania typu %s nie są jeszcze obsługiwane';
$string['noresponsegroups'] = 'Grupy odpowiedzi nie są jeszcze obsługiwane.';
$string['norenderextensions'] = 'Rozszerzenia Render nie są jeszcze obsługiwane.';
$string['mrq1other'] = 'Pytanie wielu odpowiedzi - 1 punkt za prawdziwą opcję z opcją \'inne\''; 
$string['nomultiplecard'] = 'Każdy zestaw etykiet jest inny i różna jest moc zbiorów, pytanie nie jest obsługiwane.';
$string['labelsetserror'] = 'Zestawy etykiet dla wszystkich opcji pytania nie są identyczne, być może należałoby je zaimportować jako elementy puste z listami rozwijanymi??';
$string['nomultiinputs'] = 'Pytania z wielokrotnymi numerycznymi danymi wejściowymi nie mogą być zaimportowane';
$string['blanktypeerror'] = 'Pytanie typu pustego bez list rozwijanych ani pól tekstowych';
$string['addingsub'] = 'Dodawanie podelementu - render_fib bez elementów pochodnych (dzieci)';
$string['posnocond'] = 'Pozytywny wynik bez warunków, nie było możliwe wypracowanie poprawnej odpowiedzi';
$string['multiplepos'] = 'Wielokrotne wynikowe wartości dodatnie, odpowiedź poprawna może być błędna';
$string['multiposmultiopt'] = 'Wielokrotne wynikowe wartości dodatnie, z wielokrotnymi opcjami odnośnie wyniku, odpowiedź poprawna może być błędna';
$string['nomatchinglabel'] = 'Nie można odnaleźć informacji pasującej do etykiety';
$string['nolikertfeedback'] = 'Rog&#333; nie zachowuje żadnych informacji zwrotnych dla pytań ze skalą Likerta, dane te zostały utracone';
$string['nocorrect'] = 'Nie jest możliwe odnalezienie poprawnej odpowiedzi';
$string['multipleconds'] = 'Znaleziono wiele warunków oceny pytania, wszystkie z wyjątkiem pierwszej zostały zignorowane';
$string['mrqnoismulti'] = 'Próba załadowania MRQ z nie ustawioną opcją <em>ismulti</em>!';
$string['importingtext'] = 'Importowanie pytania otwartego (tekstowego) z kryteriami oceny. To pytanie nie będzie automatycznie oceniane w Rog&#333;';

$string['someneg'] = 'Pewne wartości ujemne - 1 punkt za prawdziwą opcję z wartością ujemną';
$string['noneg'] = 'Brak wartości ujemych i wielokrotnych dodatnich, 1 punkt za prawdziwą opcję';

$string['qtiimport'] = 'Import QTI';
$string['imported1_2'] = 'Zaimportowano z pliku QTI 1.2';
$string['paperlocked'] = 'Arkusz zablokowany';
$string['paperlockedmsg'] = 'Ten arkusz jest zablokowany i nie może być używany.';

$string['loadingsection'] = 'Ładowanie sekcji';
$string['loadingblank'] = 'Ładowanie pustych tekstów';
$string['loadingblankdrop'] = 'Ładowanie pustych list rozwijanych';
$string['fileoutput'] = 'Dane wyjściowe pliku';