<?php
require_once '../lang/' . $language . '/include/paper_types.inc';
require_once '../lang/' . $language . '/paper/new_paper1.php';
require_once '../lang/' . $language . '/include/months.inc';

$string['availability'] = 'Dostępność';
$string['academicsession'] = 'Sesja akademicka';
$string['timezone'] = 'Strefa czasowa';
$string['from'] = 'Od';
$string['to'] = 'Do';
$string['modules'] = 'Moduł(y)';
$string['finish'] = 'Zakończ';
$string['msg4'] = 'Nie wybrano żadnego modułu. Arkusze muszą być przypisane do przynajmniej jednego modułu.';
$string['msg5'] = "Nazwa '%s' jest już wykorzystywana. Wybierz inny tytuł arkusza.";
$string['msg6'] = 'To jest egzamin typu "closed-book", w czasie którego <em>niedozwolone jest</em> korzystanie ze środków i źródeł pomocniczych (także słowników) ani pomocy osób drugich. Niedozwolone jest też używanie urządzeń elektronicznych z wyjątkiem komputera egzaminacyjnego.';

$string['barriersneeded'] = 'Barriers Needed';  // Niko, from here downwards
$string['daterequired'] = 'Date required';
$string['cohortsize'] = 'Cohort Size';
$string['sittings'] = 'Sittings';
$string['campus'] = 'Campus';
$string['notes'] = 'Notes';
$string['mins'] = 'mins';

$string['msg7'] = 'WARNING: You must specify which date you require the exam to run in.';
$string['msg8'] = 'WARNING: You must specify a duration in minutes that the exam will last.';
$string['msg9'] = 'WARNING: You must specify a size for the cohort.';
?>