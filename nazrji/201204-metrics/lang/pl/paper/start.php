<?php
require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/question/sct_shared.php';

$string['survey'] = 'Ankieta';
$string['assessment'] = 'Ocena';
$string['finish'] = 'Zakończ';
$string['screen'] = 'Ekran';
$string['mark'] = 'punkt';
$string['marks'] = 'punkty/ów';
$string['note'] = 'Uwaga';
$string['true'] = 'Prawda';
$string['false'] = 'Fałsz';
$string['yes'] = 'Tak';
$string['no'] = 'Nie';
$string['abstain'] = 'Wstrzymany';
$string['na'] = 'Brak'; 
$string['unanswered'] = 'Brak odpowiedzi';
$string['unansweredquestion'] = '= pytanie bez odpowiedzi';
$string['negmarking'] = 'ujemna punktacja';
$string['bonusmark'] = 'dla prawidłowej opcji, plus %d punkt%s dodatkowy za poprawną kolejność';
$string['calculator'] = 'Kalkulator';
$string['finishnote'] = '<strong>Uwaga:</strong> Należy wypełnić wszystkie odpowiedzi przed wybraniem &#145;Zakończ&#146; - powrót nie jest możliwy.';
$string['gobackpink'] = 'Po powrocie, pytania, na które nie udzielono odpowiedzi będą podświetlone na różowo.';
$string['fireexit'] = 'Ewakuacja pożarowa';
$string['pleasecomplete'] = '<strong>Uwaga:</strong> Należy wypełnić wszystkie odpowiedzi przed wybraniem &#145;Ekranu %d &gt;&#146;, - powrót nie jest możliwy.';
$string['javacheck1'] = 'Czy wypełniłeś wszystkie odpowiedzi na ekranie - powrót NIE będzie jest możliwy, czy na pewno chcesz kontynuować?';
$string['javacheck2'] = "Czy na pewno chcesz finalizować? Po wybraniu 'OK' nie będziesz mógł powrócić.";
$string['error_keywords'] = 'BŁĄD: nie można odszukać unikalnego pytania dla podanych słów kluczowych';
$string['error_paper'] = 'Wskazany arkusz nie mógł być odnaleziony.';
$string['specificpassword'] = 'Do tego arkusza przypisane jest specyficzne hasło.';
$string['denied_location'] = 'Dostęp do tego arkusza nie jest dozwolony z aktualnej lokalizacji.';
$string['error_time'] = 'Arkusz, który chcesz zobaczyć dostępny jest tylko pomiędzy %s i %s';
$string['error_module'] = 'Ten arkusz nie występuje w żadnym module.';
$string['error_metadata'] = 'Metadane użytkownika nie są zgodne <strong>%s: %s</strong>';
$string['holddownctrlkey'] = '(Trzymając &lt;CTRL&gt; klikaj myszą aby zaznaczyć/odznaczyć opcje)';
$string['msgselectable1'] = 'Zaznaczono zbyt dużo opcji!\n\nW tym pytaniu mogą być zaznaczone tylko';
$string['msgselectable2'] = 'elementy.';
$string['msgselectable3'] = 'Już zaznaczyłeś';
$string['msgselectable4'] = '.\n\nWybierz inny ranking.';
$string['notregistered'] = '%s %s (%s) nie jest zarejestrowany na <strong>%s</strong> w <strong>%s</strong>.';
//ajax saving and auto saving messages
$string['saving'] = "Saving";
$string['auto_saving'] = "Auto saved";
$string['saving_failed_try_again'] = "Saving failed please try again";
?>