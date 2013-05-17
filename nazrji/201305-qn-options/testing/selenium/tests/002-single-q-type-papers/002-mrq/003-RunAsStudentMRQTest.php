<?php
require_once 'shared.inc.php';

class RunAsStudentMRQTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $install_type;
  protected $page_root;

  protected function setUp() {
    $this->install_type = get_install_type();
    $this->page_root = get_root_url();

    $this->setBrowser("*firefox");
    $this->setBrowserUrl($this->page_root . '/');
  }

  public function testQuestionPresenceAndOrderPlusUnanswered() {
    do_student_login($this, 'teststudent10', 'jgl!34Z^');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 1 notes for students');
    $this->assertTextPresent('MRQ 1 scenario');
    $this->assertTextPresent('MRQ 1, display order, mark per option, 1 mark, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 2, alphabetic, mark per option, 1 mark, Option B and Option P correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    // Order of alphabetic question
    $this->assertElementContainsText('id=2_1', 'Option B');
    $this->assertElementContainsText('id=2_2', 'Option M');
    $this->assertElementContainsText('id=2_3', 'Option P');
    $this->assertElementContainsText('id=2_4', 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 3, random, mark per option, 2 marks, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 4, display order, mark per question, 2 marks, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(4 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 5, alphabetic, mark per question, 1 mark, Option M and Option X correct');
    $this->assertTextPresent('MRQ 6, random, mark per question, 2 marks, Option Two and Option Three correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    // Order of alphabetic question
    $this->assertElementContainsText('id=1_1', 'Option B');
    $this->assertElementContainsText('id=1_2', 'Option M');
    $this->assertElementContainsText('id=1_3', 'Option P');
    $this->assertElementContainsText('id=1_4', 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Multi Response Questions');
    $this->assertTextPresent('Note: MRQ 7 notes for students');
    $this->assertTextPresent('MRQ 7 scenario');
    $this->assertTextPresent('MRQ 7, display order, mark per option, 1 mark correct, -1 mark incorrect, Option One and Option Four correct');
    $this->assertTextPresent('MRQ 8, alphabetic, mark per option, 2 marks correct, -1 mark incorrect, Option B and Option X correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(4 marks, negative marking)');
    // Order of alphabetic question
    $this->assertElementContainsText('id=2_1', 'Option B');
    $this->assertElementContainsText('id=2_2', 'Option M');
    $this->assertElementContainsText('id=2_3', 'Option P');
    $this->assertElementContainsText('id=2_4', 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 9, random, mark per option, 1 mark correct, -0.5 marks incorrect, Option One and Option Three correct');
    $this->assertTextPresent('MRQ 10, display order, mark per question, 2 marks correct, -1 mark incorrect, Option One and Option Two correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MRQ 11, alphabetic, mark per question, 1 mark correct, -1 mark incorrect, Option M and Option B correct');
    $this->assertTextPresent('MRQ 12, random, mark per question, 3 marks correct, -2 marks incorrect, Option Two and Option Four correct');
    $this->assertCssCount('css=input[type="checkbox"]', 8);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(3 marks, negative marking)');
    // Order of alphabetic question
    $this->assertElementContainsText('id=1_1', 'Option B');
    $this->assertElementContainsText('id=1_2', 'Option M');
    $this->assertElementContainsText('id=1_3', 'Option P');
    $this->assertElementContainsText('id=1_4', 'Option X');

    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '-2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '-9 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '-33.3%');
  }

  public function testCompletePaperCorrect() {
    do_student_login($this, 'teststudent11', 'bkt_66Y4');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("id=q1_1");
    $this->click("id=q1_3");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_4");
    $this->click("id=q2_1");
    $this->click("id=q2_2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_3");
    $this->click("id=q2_2");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_4");
    $this->click("id=q2_2");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_2");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '4 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '3 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '27 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '100.0%');
  }

  public function testCompletePaperIncorrect() {
    do_student_login($this, 'teststudent12', 'rmu_74L4');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("id=q1_2");
    $this->click("id=q1_4");
    $this->click("id=q2_1");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_3");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_2");
    $this->click("id=q1_4");
    $this->click("id=q2_4");
    $this->click("id=q2_1");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_4");
    $this->click("id=q1_2");
    $this->click("id=q2_3");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_4");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_3");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '-2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '-9 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '-33.3%');
  }

  public function testCompletePaperMixed() {
    do_student_login($this, 'teststudent13', 'hii.420R');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_2");
    $this->click("id=q1_3");
    $this->click("id=q2_1");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_2");
    $this->click("id=q1_4");
    $this->click("id=q2_2");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_4");
    $this->click("id=q2_1");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_2");
    $this->click("id=q2_2");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q1_2");
    $this->click("id=q2_3");
    $this->click("id=q2_1");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '1 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '-2 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0.5 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '3.5 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '13.0%');
  }

  public function testCompletePaperPartialAnswers() {
    do_student_login($this, 'teststudent14', 'mon~61Qt');

    $this->open("/user_index.php?id=21355414508102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("id=q1_1");
    $this->click("id=q2_1");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_2");
    $this->click("id=q2_2");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q2_4");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_2");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_1");
    $this->click("id=q2_3");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("id=q1_4");
    $this->click("id=q2_2");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=21355414508102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[2]/span', '1 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 4');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[2]/td[2]/p[2]/span', '-2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr[4]/td[2]/p/span', '1 out of 4');
    $this->assertElementContainsText('//table[6]/tbody/tr/td[2]/p/span', '0.5 out of 2');
    $this->assertElementContainsText('//table[6]/tbody/tr[3]/td[2]/p/span', '-1 out of 2');
    $this->assertElementContainsText('//table[7]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[7]/tbody/tr[3]/td[2]/p/span', '-2 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[7]/table/tbody/tr[2]/td[2]', '-3.5 out of 27');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[7]/table/tbody/tr[4]/td[2]', '-13.0%');
  }

  public function testClassTotals() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=2&startdate=20120113000000&enddate=20230117150000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '-9');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '-33%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res7"]/td[5]', '27');
    $this->assertElementContainsText('//tr[@id="res7"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res7"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res8"]/td[5]', '-9');
    $this->assertElementContainsText('//tr[@id="res8"]/td[6]', '-33%');
    $this->assertElementContainsText('//tr[@id="res8"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res9"]/td[5]', '3.5');
    $this->assertElementContainsText('//tr[@id="res9"]/td[6]', '13%');
    $this->assertElementContainsText('//tr[@id="res9"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res10"]/td[5]', '-3.5');
    $this->assertElementContainsText('//tr[@id="res10"]/td[6]', '-13%');
    $this->assertElementContainsText('//tr[@id="res10"]/td[7]', 'Fail');
  }
}
?>