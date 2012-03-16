<?php
require '../lang/' . $language . '/include/months.inc';
require '../lang/' . $language . '/include/paper_types.inc';

$string['displaycalculator-1'] = 'Stary kalkulator - calc98';
$string['displaycalculator0'] = 'Bez kalkulatora';
$string['displaycalculator1'] = 'Kalkulator naukowy';
$string['displaycalculator2'] = 'Prosty kalkulator';

$string['propertiestitle'] = 'Właściwości';
$string['edittitle'] = 'Edytuj';
$string['warning'] = 'Ostrzeżenie: nazwa arkusza jest już wykorzystywana w innym teście!';
$string['availablefromyear'] = 'Zaplanowany rok \'od\' jest późniejszy niż rok \'do\' co jest nielogiczne!';
$string['availablefrommonth'] = 'Zaplanowany miesiąc \'od\' jest późniejszy niż miesiąc \'do\' co jest nielogiczne!';
$string['availablefromday'] = 'Zaplanowany dzień \'od\' jest późniejszy niż dzień \'do\' co jest nielogiczne!';
$string['msg1'] = 'Nie wybrano modułu. Arkusz musi być przypisany do co najmniej jednego modułu.';
$string['msg2'] = 'Data ropoczęcia i zakończenia egzaminu końcowego musi być ta sama (zakładka \'Prawa dostępu\').';
$string['msg3'] = 'Czas trwania egzaminu końcowego musi być określony.\nPowinien to być normalny czas trwania z wyłączeniem czasu dodatkowego dla osób studentów z dysleksją.';
$string['msg4'] = 'Rok akademicki dla egzaminu końcowego musi być określony (zakładka \'Prawa dostępu\').';
$string['msg5'] = 'OSCE muszą być przypisane do co najmniej jednego modułu.';
$string['msg6'] = 'Wybrano egzaminatorów zewnętrznych, jednak nie określono terminu finalizacji.';
$string['msg7'] = 'Proszę podać nazwę arkusza.';
$string['msg8'] = 'Proszę podać nazwę arkusza. To jest egzamin typu \"closed-book\", w czasie którego <em>niedozwolone jest</em> korzystanie ze środków i źródeł pomocniczych (także słowników) ani pomocy osób drugich. Niedozwolone jest też używanie urządzeń elektronicznych z wyjątkiem komputera egzaminacyjnego.';

// General tab
$string['generaltab'] = 'Ogólne';
$string['generalheading'] = 'Nazwa arkusza, punktacja i opcje prezentacji';
$string['paperdetails'] = 'Szczegóły arkusza';
$string['onlyonexamday'] = '(tylko w dniu egzaminu)';
$string['url'] = 'URL';
$string['name'] = 'Nazwa';
$string['type'] = 'Typ';
$string['folder'] = 'Folder';
$string['feedback'] = 'Odzew';
$string['objectivesreport'] = 'Odzew dot. celów';
$string['questionfeedback'] = 'Odzew dot. pytań';
$string['displayoptions'] = 'Opcje prezentacji';
$string['display'] = 'Prezentacja';
$string['windowed'] = 'w oknie';
$string['fullscreen'] = 'Pełnoekranowa (tylko Internet Explorer)';
$string['navigation'] = 'Nawigacja';
$string['bidirectional'] = 'dwukierunkowa';
$string['unidirectional'] = 'jednokierunkowa (liniowa)';
$string['background'] = 'Tło';
$string['foreground'] = 'Pierwszy plan';
$string['theme'] = 'Motyw';
$string['labelsnotes'] = 'Etykiety/Notatki';
$string['calculator'] = 'Kalkulator';
$string['displaycalculator'] = 'Kalkulator ekranowy';
$string['audio'] = 'Audio';
$string['demosoundclip'] = 'testowy plik dźwiękowy';
$string['marking'] = 'Punktacja';
$string['passmark'] = 'Liczba punktów na zaliczenie';
$string['distinction'] = 'Wyróżnienie';
$string['method'] = 'Metoda';
$string['noadjustment'] = 'Brak wzoru';
$string['calculatrrandommark'] = "Oszacuj punkty na 'chybił-trafił'";
$string['stdset'] = 'Wyznaczony standard';
$string['ticks_crosses'] = 'Haczyki/Krzyżyki';
$string['question_marks'] = 'Punktacja pytania';
$string['hideallfeedback'] = 'Ukryj odzew<br />jeśli nie odpowiedziano';
$string['correctanswerhighlight'] = 'Wyróżnienie poprawnej odpowiedzi';
$string['textfeedback'] = 'Odzew tekstowy';

// Security tab
$string['securitytab'] = 'Bezpieczeństwo';
$string['securityheading'] = 'Kontrola praw dostępu studentów do arkuszy.';
$string['session'] = 'Sesja';
$string['password'] = 'Hasło';
$string['timezone'] = 'Strefa czasowa';
$string['modules'] = 'Moduł(y)';
$string['duration'] = 'Czas trwania';
$string['mins'] = 'min.';
$string['availablefrom'] = 'Dostępne od';
$string['to'] = 'do';
$string['restricttolabs'] = 'Ogranicz do pracowni';
$string['restricttometadata'] = 'Ogranicz do metadanych';
$string['na'] = 'Brak';//data

// Reviewers tab
$string['reviewerstab'] = 'Recenzenci';
$string['reviewersheading'] = 'Lista recenzentów wewnętrznych i zewnętrznych z terminami finalizacji.';
$string['internalreviewers'] = 'Wewnętrzni recenzenci';
$string['externalexaminers'] = 'Zewnętrzni egzaminatorzy';
$string['deadline'] = 'Termin finalizacji:';

// Exam Rubric tab
$string['rubrictab'] = 'Rubryka egzaminu';
$string['rubricheading'] = 'Rubryka egzaminu prezentowana studentowi przed rozpoczęciem egzaminu końcowego';

// Prologue tab
$string['prologuetab'] = 'Wstęp';
$string['prologueheading'] = 'Tekst wyświetlany u góry ekranu 1 po rozpoczęciu pracy z arkuszem.';

// Postscript tab
$string['postscripttab'] = 'Zakończenie';
$string['postscriptheading'] = "Tekst wyświetlany po tym jak student kliknie 'Zakończ'.";
?>