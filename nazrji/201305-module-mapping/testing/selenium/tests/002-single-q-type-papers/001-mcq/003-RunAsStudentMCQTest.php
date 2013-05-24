<?php
require_once 'shared.inc.php';

class RunAsStudentMCQTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/user_index.php?id=11355244387102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Multi Choice Questions');
    $this->assertTextPresent('Note: MCQ 1 notes for students');
    $this->assertTextPresent('MCQ 1 scenario');
    $this->assertTextPresent('MCQ 1, vertical, display order, 1 mark, Option One correct');
    $this->assertTextPresent('MCQ 2, horizontal, display order, 1 mark, Option Two correct');
    $this->assertCssCount('css=input[type="radio"]', 6);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 3, DDL, display order, 2 marks, Option Two correct');
    $this->assertTextPresent('MCQ 4, vertical, alphabetic, 1 mark, Option M correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark)');
    // Order of alphabetic question
    $this->assertElementContainsText('//div/form/table/tbody/tr/td/table[2]/tbody/tr[3]/td[2]/blockquote/table/tbody/tr[1]/td[2]', 'Option B');
    $this->assertElementContainsText('//div/form/table/tbody/tr/td/table[2]/tbody/tr[3]/td[2]/blockquote/table/tbody/tr[2]/td[2]', 'Option M');
    $this->assertElementContainsText('//div/form/table/tbody/tr/td/table[2]/tbody/tr[3]/td[2]/blockquote/table/tbody/tr[3]/td[2]', 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 5, horizontal, alphabetic, 1 mark, Option B correct');
    $this->assertTextPresent('MCQ 6, DDL, alphabetic, 2 marks, Option X correct');
    $this->assertCssCount('css=input[type="radio"]', 3);
    $this->assertCssCount('css=select', 2); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    // Order of alphabetic questions
    $this->assertElementIndex('//div/form/table/tbody/tr/td/table[2]/tbody/tr[2]/td[2]/blockquote/input[@name="q1" and @value="2"]', '0');
    $this->assertElementIndex('//div/form/table/tbody/tr/td/table[2]/tbody/tr[2]/td[2]/blockquote/input[@name="q1" and @value="1"]', '2');
    $this->assertElementIndex('//div/form/table/tbody/tr/td/table[2]/tbody/tr[2]/td[2]/blockquote/input[@name="q1" and @value="3"]', '4');
    $this->assertElementContainsText('//div/form/table/tbody/tr/td/table[2]/tbody/tr[3]/td[2]/blockquote/div/select/option[2]', 'Option B');
    $this->assertElementContainsText('//div/form/table/tbody/tr/td/table[2]/tbody/tr[3]/td[2]/blockquote/div/select/option[3]', 'Option M');
    $this->assertElementContainsText('//div/form/table/tbody/tr/td/table[2]/tbody/tr[3]/td[2]/blockquote/div/select/option[4]', 'Option X');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('MCQ 7, vertical, random, 1 mark, -1 mark incorrect, Option One correct');
    $this->assertTextPresent('MCQ 8, horizontal, random, 1 mark, -2 marks incorrect, Option Two correct');
    $this->assertTextPresent('MCQ 9, DDL, random, 2 marks, -1 mark incorrect, Option Three correct');
    $this->assertCssCount('css=input[type="radio"]', 6);
    $this->assertCssCount('css=select', 2); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(1 mark, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(1 mark, negative marking)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(2 marks, negative marking)');

    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '0 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '0 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '0.00%');
  }

  public function testCompletePaperCorrect() {
    do_student_login($this, 'teststudent11', 'bkt_66Y4');

    $this->open("/user_index.php?id=11355244387102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("name=q1");
    $this->click("xpath=(//input[@name='q2'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1", "label=Option Two");
    $this->click("xpath=(//input[@name='q2'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1");
    $this->select("name=q2", "label=Option X");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1' and @value='1'])");
    $this->click("xpath=(//input[@name='q2' and @value='2'])");
    $this->select("name=q3", "label=Option Three");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '2 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '2 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '12 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '100.00%');
  }

  public function testCompletePaperIncorrect() {
    do_student_login($this, 'teststudent12', 'rmu_74L4');

    $this->open("/user_index.php?id=11355244387102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("xpath=(//input[@name='q1'])[2]");
    $this->click("xpath=(//input[@name='q2'])[1]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1", "label=Option Three");
    $this->click("xpath=(//input[@name='q2'])[1]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1'])[3]");
    $this->select("name=q2", "label=Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1' and @value='2'])");
    $this->click("xpath=(//input[@name='q2' and @value='3'])");
    $this->select("name=q3", "label=Option Two");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '-2 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '-1 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '-4 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '-33.33%');
  }

  public function testCompletePaperMixed() {
    do_student_login($this, 'teststudent13', 'hii.420R');

    $this->open("/user_index.php?id=11355244387102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->click("name=q1");
    $this->click("xpath=(//input[@name='q2'])[1]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->select("name=q1", "label=Option Three");
    $this->click("xpath=(//input[@name='q2'])[2]");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("name=q1");
    $this->select("name=q2", "label=Option M");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");

    $this->click("xpath=(//input[@name='q1' and @value='2'])");
    $this->click("xpath=(//input[@name='q2' and @value='2'])");
    $this->select("name=q3", "label=Option Three");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=11355244387102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[2]/tbody/tr[4]/td[2]/p/span', '0 out of 1');
    $this->assertElementContainsText('//table[3]/tbody/tr/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[4]/tbody/tr[3]/td[2]/p/span', '0 out of 2');
    $this->assertElementContainsText('//table[5]/tbody/tr/td[2]/p/span', '-1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[3]/td[2]/p/span', '1 out of 1');
    $this->assertElementContainsText('//table[5]/tbody/tr[5]/td[2]/p/span', '2 out of 2');

    // Overall Marks
    $this->assertElementContainsText('//div[5]/table/tbody/tr[2]/td[2]', '5 out of 12');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[5]/table/tbody/tr[4]/td[2]', '41.67%');
  }

  public function testClassTotals() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=1&startdate=20120111000000&enddate=20230113100000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&ordering=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res5"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res5"]/td[6]', '0%');
    $this->assertElementContainsText('//tr[@id="res5"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '12');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '100.00%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res7"]/td[5]', '-4');
    $this->assertElementContainsText('//tr[@id="res7"]/td[6]', '-33.33%');
    $this->assertElementContainsText('//tr[@id="res7"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res8"]/td[5]', '5');
    $this->assertElementContainsText('//tr[@id="res8"]/td[6]', '41.67%');
    $this->assertElementContainsText('//tr[@id="res8"]/td[7]', 'Pass');
  }
}
?>