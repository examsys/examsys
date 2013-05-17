<?php
require_once 'shared.inc.php';

class RunAsStudentBlankTest extends PHPUnit_Extensions_SeleniumTestCase
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

    $this->open("/user_index.php?id=51357812182102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->assertTextPresent('Fill in the Blank DDL Questions');
    $this->assertTextPresent('Note: Blank 1 notes for students');
    $this->assertTextPresent('Blank 1, DDL, 1 mark, 2 blanks');
    $this->assertTextPresent('Fill in the Blank, DDL, Negative Marking');
    $this->assertTextPresent('Blank 2, DDL, 2 marks correct, -1 mark incorrect, 3 blanks');
    $this->assertTextPresent('Fill in the Blank, DDL, Mark per Question');
    $this->assertTextPresent('Blank 3, DDL, mark per question, 2 marks, 2 blanks');
    $this->assertCssCount('css=select', 8); // Include page jump DDL
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(6 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(2 marks)');

    $this->selectWindow("name=paper");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Fill in the Blank, DDL, Mark per Question, Negative Marking');
    $this->assertTextPresent('Blank 4, DDL, mark per question, 3 marks correct, -1 mark incorrect, 3 blanks');
    $this->assertTextPresent('Fill in the Blank Textbox Questions');
    $this->assertTextPresent('Note: Blank 5 notes for students');
    $this->assertTextPresent('Blank 5, textbox, 1 mark, 2 blanks');
    $this->assertTextPresent('Fill in the Blank, Textbox, Negative Marking');
    $this->assertTextPresent('Blank 6, textbox, 2 marks correct, -1 mark incorrect, 3 blanks');
    $this->assertCssCount('css=select', 4); // Include page jump DDL
    $this->assertCssCount('css=input[type="text"]', 5);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(3 marks, negative marking)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q3_mk"]', '(6 marks, negative marking)');

    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->assertTextPresent('Fill in the Blank, Textbox, Mark per Question');
    $this->assertTextPresent('Blank 7, textbox, mark per question, 2 marks, 2 blanks');
    $this->assertTextPresent('Fill in the Blank, Textbox, Mark per Question, Negative Marking');
    $this->assertTextPresent('Blank 8, textbox, 3 marks correct, -1 mark incorrect, 3 blanks');
    $this->assertCssCount('css=input[type="text"]', 5);
    $this->assertElementContainsText('//*[@id="q1_mk"]', '(2 marks)');
    $this->assertElementContainsText('//*[@id="q2_mk"]', '(3 marks, negative marking)');

    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=51357812182102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '0 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '0 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '0 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '0.0%');
  }

  public function testCompletePaperCorrect() {
    do_student_login($this, 'teststudent11', 'bkt_66Y4');

    $this->open("/user_index.php?id=51357812182102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");

    $this->select("name=q1_1", "label=colour");
    $this->select("name=q1_2", "label=country");
    $this->select("name=q2_1", "label=colour");
    $this->select("name=q2_2", "label=country");
    $this->select("name=q2_3", "label=Visual Display Unit");
    $this->select("name=q3_1", "label=colour");
    $this->select("name=q3_2", "label=country");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->select("name=q1_1", "label=colour");
    $this->select("name=q1_2", "label=country");
    $this->select("name=q1_3", "label=Visual Display Unit");
    $this->type("name=q2_1", "colour");
    $this->type("name=q2_2", "country");
    $this->type("name=q3_1", "colour");
    $this->type("name=q3_2", "country");
    $this->type("name=q3_3", "Visual Display Unit");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->type("name=q1_1", "colour");
    $this->type("name=q1_2", "country");
    $this->type("name=q2_1", "colour");
    $this->type("name=q2_2", "country");
    $this->type("name=q2_3", "Visual Display Unit");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=51357812182102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '6 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '3 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '6 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '3 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '26 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '100.0%');
  }

  public function testCompletePaperIncorrect() {
    do_student_login($this, 'teststudent12', 'rmu_74L4');

    $this->open("/user_index.php?id=51357812182102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->select("name=q1_1", "label=texture");
    $this->select("name=q1_2", "label=continent");
    $this->select("name=q2_1", "label=texture");
    $this->select("name=q2_2", "label=continent");
    $this->select("name=q2_3", "label=Video Display Unit");
    $this->select("name=q3_1", "label=country");
    $this->select("name=q3_2", "label=city");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->select("name=q1_1", "label=texture");
    $this->select("name=q1_2", "label=continent");
    $this->select("name=q1_3", "label=Video Display Unit");
    $this->type("name=q2_1", "country");
    $this->type("name=q2_2", "city");
    $this->type("name=q3_1", "texture");
    $this->type("name=q3_2", "continent");
    $this->type("name=q3_3", "Video Display Unit");
    $this->click("id=next");

    $this->waitForPageToLoad("30000");
    $this->type("name=q1_1", "country");
    $this->type("name=q1_2", "continent");
    $this->type("name=q2_1", "texture");
    $this->type("name=q2_2", "colour");
    $this->type("name=q2_3", "Vibrant Display Unit");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=51357812182102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '-1 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '-1 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '-8 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '-30.8%');
  }

  public function testCompletePaperMixed1() {
    do_student_login($this, 'teststudent13', 'hii.420R');

    $this->open("/user_index.php?id=51357812182102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->select("name=q1_1", "label=colour");
    $this->select("name=q1_2", "label=city");
    $this->select("name=q2_1", "label=texture");
    $this->select("name=q2_2", "label=country");
    $this->select("name=q2_3", "label=Vibrant Display Unit");
    $this->select("name=q3_1", "label=colour");
    $this->select("name=q3_2", "label=continent");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->select("name=q1_1", "label=colour");
    $this->select("name=q1_2", "label=country");
    $this->select("name=q1_3", "label=Video Display Unit");
    $this->type("name=q2_1", "texture");
    $this->type("name=q2_2", "country");
    $this->type("name=q3_1", "colour");
    $this->type("name=q3_2", "city");
    $this->type("name=q3_3", "Visual Display Unit");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->type("name=q1_1", "colour");
    $this->type("name=q1_2", "continent");
    $this->type("name=q2_1", "texture");
    $this->type("name=q2_2", "country");
    $this->type("name=q2_3", "Visual Display Unit");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=51357812182102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '1 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '0 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '-1 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '1 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '3 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '0 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '-1 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '3 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '11.5%');
  }

  public function testCompletePaperMixed2() {
    do_student_login($this, 'teststudent14', 'mon~61Qt');

    $this->open("/user_index.php?id=51357812182102");
    $this->click("id=start");
    $this->waitForPopUp("paper", "30000");
    $this->selectWindow("name=paper");
    $this->select("name=q1_1", "label=colour");
    $this->select("name=q1_2", "label=country");
    $this->select("name=q2_1", "label=texture");
    $this->select("name=q2_2", "label=continent");
    $this->select("name=q2_3", "label=Video Display Unit");
    $this->select("name=q3_1", "label=colour");
    $this->select("name=q3_2", "label=country");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->select("name=q1_1", "label=country");
    $this->select("name=q1_2", "label=city");
    $this->select("name=q1_3", "label=Video Display Unit");
    $this->type("name=q2_1", "colour");
    $this->type("name=q2_2", "country");
    $this->type("name=q3_1", "texture");
    $this->type("name=q3_2", "city");
    $this->type("name=q3_3", "Video Display Unit");
    $this->click("id=next");
    $this->waitForPageToLoad("30000");
    $this->type("name=q1_1", "colour");
    $this->type("name=q1_2", "country");
    $this->type("name=q2_1", "texture");
    $this->type("name=q2_2", "continent");
    $this->type("name=q2_3", "Vibrant Display Unit");
    $this->click("id=finish");
    $this->assertEquals("Are you sure you wish to finish. After clicking 'OK' you will not be able to go back.", $this->getConfirmation());
    $this->waitForPageToLoad("30000");
    $this->assertLocation($this->page_root . '/paper/finish.php?id=51357812182102&dont_record=true');

    // Individual Question Marks
    $this->assertElementContainsText('//table[2]/tbody/tr[2]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[2]/tbody/tr[5]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[2]/tbody/tr[8]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[2]/td[2]/p[3]/span', '-1 out of 3');
    $this->assertElementContainsText('//table[3]/tbody/tr[5]/td[2]/p[4]/span', '2 out of 2');
    $this->assertElementContainsText('//table[3]/tbody/tr[8]/td[2]/p[3]/span', '-3 out of 6');
    $this->assertElementContainsText('//table[4]/tbody/tr[2]/td[2]/p[3]/span', '2 out of 2');
    $this->assertElementContainsText('//table[4]/tbody/tr[5]/td[2]/p[3]/span', '-1 out of 3');

    // Overall Marks
    $this->assertElementContainsText('//div[4]/table/tbody/tr[2]/td[2]', '0 out of 26');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[3]/td[2]', '40%');
    $this->assertElementContainsText('//div[4]/table/tbody/tr[4]/td[2]', '0.0%');
  }

  public function testClassTotals() {
    do_staff_login($this);

    $this->open("/reports/class_totals.php?paperID=5&startdate=20130101000000&enddate=20230210130000&repmodule=&repcourse=%&sortby=name&module=3&folder=&percent=100&absent=0&studentsonly=1&direction=asc");

    // Individuals
    $this->assertElementContainsText('//tr[@id="res6"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res6"]/td[6]', '0%');
    $this->assertElementContainsText('//tr[@id="res6"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res7"]/td[5]', '26');
    $this->assertElementContainsText('//tr[@id="res7"]/td[6]', '100%');
    $this->assertElementContainsText('//tr[@id="res7"]/td[7]', 'Distinction');

    $this->assertElementContainsText('//tr[@id="res8"]/td[5]', '-8');
    $this->assertElementContainsText('//tr[@id="res8"]/td[6]', '-31%');
    $this->assertElementContainsText('//tr[@id="res8"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res9"]/td[5]', '3');
    $this->assertElementContainsText('//tr[@id="res9"]/td[6]', '12%');
    $this->assertElementContainsText('//tr[@id="res9"]/td[7]', 'Fail');

    $this->assertElementContainsText('//tr[@id="res10"]/td[5]', '0');
    $this->assertElementContainsText('//tr[@id="res10"]/td[6]', '0%');
    $this->assertElementContainsText('//tr[@id="res10"]/td[7]', 'Fail');
  }
}
?>