<?php
$string['forgottenpassword'] = 'Utracone hasło';
$string['emailaddress'] = 'Adres Email';
$string['emailaddressinvalid'] = 'Podaj poprawny adres Email';
$string['emailaddressnotfound'] = 'Nie znaleziono tego adresu Email';
$string['passwordreset'] = 'Resetowanie hasła';
$string['emailhtml'] = <<< EMAIL_HTML
<p>Cześć %s %s,</p>
<p>Otrzymaliśmy życzenie zmiany hasła w Rog&#333;. Aby potwierdzić to życzenie kliknij na poniższy link:</p>
<p><a href="https://%s/users/reset_password.php?token=%s">Resetowanie hasła</a></p>
<p>Jeśli nie prosiłeś o resetowanie hasła prosimy abyś do nas o tym <a href="mailto:%s">napisał</a>. Twój dotychczasowy login i hasło będą nadal obowiązywały w Rog&#333;.</p>

EMAIL_HTML;
$string['couldntsendemail'] = 'Nie można było wysłać Emaila do <strong>%s</strong>';
$string['emailsentmsg'] = 'Został wysłany Email do <em>%s</em> zawierający link umożliwiający zresetowanie Twojego hasła. Link ten pozostanie aktywny przez <strong>24 godziny</strong>.';
$string['intromsg'] = 'Podaj swój adres Email, a my wyślemy tam link umożliwiający zresetowanie hasła.';
$string['send'] = 'Wyślij';
?>