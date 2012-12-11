<?php
require_once '../lang/' . $language . '/include/months.inc';
require_once '../lang/' . $language . '/question/sct_shared.php';
require_once '../lang/' . $language . '/include/paper_security.inc';

$string['survey'] = 'Ankieta';
$string['assessment'] = 'Ocena';
$string['finish'] = 'Zakończ';
$string['screen'] = 'Ekran';
$string['mark'] = 'punkt';
$string['marks'] = 'punkt/y/ów';
$string['note'] = 'Notatka';
$string['true'] = 'Prawda';
$string['false'] = 'Fałsz';
$string['yes'] = 'Tak';
$string['no'] = 'Nie';
$string['abstain'] = 'Wstrzymany';
$string['na'] = 'Żadna';
$string['unanswered'] = 'Brak odpowiedzi';
$string['unansweredquestion'] = '= pytanie bez odpowiedzi';
$string['negmarking'] = 'ujemna punktacja';
$string['bonusmark'] = 'dla prawidłowej opcji, plus %d punkt%s dodatkowy za poprawną kolejność';
$string['calculator'] = 'Kalkulator';
$string['timeremaining'] = 'Pozostały czas'; 
$string['finishnote'] = '<strong>Uwaga:</strong> Należy udzielić wszystkich odpowiedzi przed wybraniem &#145;Zakończ&#146; - powrót nie jest możliwy.';
$string['gobackpink'] = 'Po powrocie, pytania, na które nie udzielono odpowiedzi będą podświetlone na różowo.';
$string['fireexit'] = 'Ewakuacja pożarowa';
$string['pleasecomplete'] = '<strong>Uwaga:</strong> Należy udzielić wszystkich odpowiedzi przed wybraniem &#145;Ekranu %d &gt;&#146;, - powrót nie jest możliwy.';
$string['javacheck1'] = 'Czy wypełniłeś wszystkie odpowiedzi na ekranie - powrót NIE będzie jest możliwy, czy na pewno chcesz kontynuować?';
$string['javacheck2'] = "Czy na pewno chcesz finalizować? Po wybraniu 'OK' nie będziesz mógł powrócić.";
$string['error_keywords'] = 'BŁĄD: nie można odszukać unikalnego pytania dla podanych słów kluczowych';
$string['error_paper'] = 'Wskazany arkusz nie mógł być odnaleziony.';
$string['error_qtype'] = 'Nie zdefiniowano typu pytania.';
$string['holddownctrlkey'] = '(Trzymając &lt;CTRL&gt; klikaj myszą aby zaznaczyć/odznaczyć opcje)';
$string['msgselectable1'] = 'Zaznaczono zbyt dużo opcji!\n\nW tym pytaniu mogą być zaznaczone tylko';
$string['msgselectable2'] = 'elementy.';
$string['msgselectable3'] = 'Już zaznaczyłeś';
$string['msgselectable4'] = '.\n\nWybierz inny ranking.';
//ajax saving and auto saving messages
$string['saving'] = 'Zapisywanie';
$string['auto_saving'] = 'Zapisane automatycznie';
$string['auto_ok'] = 'Zapisywane automatyczne zakończone pomyślnie';
$string['savefailed'] = 'Zapisywanie nie powiodło się!';
$string['tryagain'] = 'Spróbuj ponownie po przejściu na następnej lub poprzedniej strony.</div>';
?>