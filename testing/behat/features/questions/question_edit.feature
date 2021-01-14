@questions @javascript
Feature: Question edit
  In order to run exams
  As a teacher
  I need to be able to edit questions from the question bank

  Background:
    Given the following "modules" exist:
      | moduleid | fullname |
      | TEST1001 | Test module |
    And the following "users" exist:
      | username | roles |
      | teacher | Staff |
    And the following "module team members" exist:
      | moduleid | username |
      | TEST1001 | teacher |
    And the following "module keywords" exist:
      | moduleid | keyword |
      | TEST1001 | test |
      | TEST1001 | key |
      | TEST1001 | words |
    And the following "questions" exist:
      | user | type | leadin | scenario | correct | marks_correct | marks_incorrect | modules | locked |
      | teacher | true_false | tf 1 leadin | tf 1 scenario | true | 1 | 0 | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | marks_correct | marks_incorrect | num_options | correct_options | modules | locked |
      | mrq | teacher | mrq leadin | mrq scenario | 1 | 0 | 3 | 2,3 | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | correct | marks_correct | marks_incorrect | num_options | correct_options | display_method | modules | locked |
      | mcq | teacher | mcq leadin | mcq scenario | 1 | 1 | 0 | 3 | 2 | vertical | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | correct | marks_correct | marks_incorrect | columns | rows | editor | modules | locked |
      | textbox | teacher | textbox 1 leadin | textbox 1 scenario | placeholder | 1 | 0 | 90 | 5 | WYSIWYG | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | marks_correct | marks_incorrect | marks_partial | tolerance_full | tolerance_partial | variables | formula | modules | locked |
      | enhancedcalc | teacher | enhancedcalc $A x $B | enhancedcalc 1 scenario | 2 | 0 | 0.5 | 0 | 1 | {"$A":{"min":"12","max":"12","inc":"1","dec":"0"},"$B":{"min":"4","max":"4","inc":"1","dec":"0"}} | $A*$B | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | marks_correct | marks_incorrect | experts | modules | locked |
      | sct | teacher | sct leadin | sct scenario | 1 | 0 | 1,2,10,3,1 | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | marks_correct | marks_incorrect | num_options | correct_options | display_method | modules | locked |
      | dichotomous | teacher | dichotomous leadin | dichotomous scenario | 1 | 0 | 3 | 1,2,3 | TF_Positive | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | marks_correct | marks_incorrect | num_options | correct_order | modules | locked |
      | rank | teacher | rank leadin | rank scenario | 1 | 0 | 3 | 1,2,3 | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | scenario | correct | marks_correct | marks_incorrect | display_method | blanks | modules | locked |
      | blank | teacher | blank 1 leadin | blank 1 scenario | placeholder | 1 | 0 | dropdown | Red is a [blank]colour,country,animal[/blank]. France is a [blank]country,colour,animal[/blank] | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | marks_correct | marks_incorrect | num_options | correct_options | num_stems | modules | locked |
      | extmatch | teacher | extmatch leadin | 1 | 0 | 3 | 3,1,2 | 3 | ["TEST1001"] | 2018-01-01 01:00:00 |
    And the following "questions" exist:
      | type | user | leadin | marks_correct | marks_incorrect | num_options | correct_options | num_stems | modules | locked |
      | matrix | teacher | matrix leadin | 1 | 0 | 3 | 3,2,1 | 3 | ["TEST1001"] | 2018-01-01 01:00:00 |

  @question_edit_locked_keyword_true_false
  Scenario: Add a keyword to an existing true_false question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "tf 1 leadin" "question_bank_item"
    And I add keyword "test"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_mrq
  Scenario: Add a keyword to an existing mrq question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "mrq leadin" "question_bank_item"
    And I add keyword "key"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_mcq
  Scenario: Add a keyword to an existing mcq question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "mcq leadin" "question_bank_item"
    And I add keyword "words"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_textbox
  Scenario: Add a keyword to an existing textbox question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "textbox 1 leadin" "question_bank_item"
    And I add keyword "test"
    And I add keyword "words"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_enhancedcalc
  Scenario: Add a keyword to an existing enhancedcalc question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "enhancedcalc" "question_bank_item"
    And I add keyword "test"
    And I add keyword "key"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_sct
  Scenario: Add a keyword to an existing sct question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "sct leadin" "question_bank_item"
    And I add keyword "test"
    And I add keyword "key"
    And I add keyword "words"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_dichotomous
  Scenario: Add a keyword to an existing dichotomous question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "dichotomous leadin" "question_bank_item"
    And I add keyword "test"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_rank
  Scenario: Add a keyword to an existing rank question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "rank leadin" "question_bank_item"
    And I add keyword "key"
    And I add keyword "words"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_blank
  Scenario: Add a keyword to an existing blank question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "blank 1 leadin" "question_bank_item"
    And I add keyword "test"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_extmatch
  Scenario: Add a keyword to an existing extmatch question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "extmatch leadin" "question_bank_item"
    And I add keyword "words"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"

  @question_edit_locked_keyword_matrix
  Scenario: Add a keyword to an existing matrix question
    Given I login as "teacher"
    And I follow "TEST1001"
    And I follow "All Questions"
    And I double click "matrix leadin" "question_bank_item"
    And I add keyword "key"
    And I click "Save" "button"
    Then I should see "Question Bank" "paper_title"
