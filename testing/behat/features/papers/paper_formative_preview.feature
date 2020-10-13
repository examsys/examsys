@paper @javascript
Feature: Paper setup
  In order to run exams
  As a teacher
  I need to be able to preview an exam

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
    And the following "papers" exist:
      | papertitle | papertype | paperowner | modulename |
      | a formative paper | 0 | teacher | Test module |
    And the following "questions" exist:
      | user | type | leadin | scenario | options | paper | settings | display_method |
      | teacher | true_false | tf leadin | tf scenario | {"correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} | {"paper":"a formative paper","screen":"1","displaypos":"1"} | | horizontal |
      | teacher | mrq | mrq leadin | mrq scenario | [{"option_text":"option 1","correct":"n","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 2","correct":"y","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 3","correct":"y","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"}] | {"paper":"a formative paper","screen":"2","displaypos":"1"} | | |
      | teacher | mcq | mcq leadin | mcq scenario | [{"option_text":"option 1","correct":"2","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 2","correct":"2","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 3","correct":"2","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"}] | {"paper":"a formative paper","screen":"1","displaypos":"2"} | | vertical |
      | teacher | textbox | textbox 1 leadin | textbox 1 scenario | {"correct":"placeholder","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} | {"paper":"a formative paper","screen":"2","displaypos":"2"} | {"columns":"80","rows":"4","editor":"WYSIWYG","terms":"[]"} | |
      | teacher | enhancedcalc | enhancedcalc $A x $B | enhancedcalc 1 scenario | | {"paper":"a formative paper","screen":"1","displaypos":"3"} | {"tolerance_full":"0","tolerance_partial":"1","vars":{"$A":{"min":"12","max":"12","inc":"1","dec":"0"},"$B":{"min":"4","max":"4","inc":"1","dec":"0"}},"marks_correct":2,"marks_incorrect":0,"marks_partial":0.5,"dp":"0","strictdisplay":true,"strictzeros":false,"fulltoltyp":"#","parttoltyp":"#","marks_unit":0,"show_units":true,"answers":[{"formula":"$A*$B","units":""}]} | |
      | teacher | sct | sct leadin | sct scenario | [{"option_text":"very unlikely","correct":"1","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"unlikely","correct":"2","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"neither likely nor unlikely","correct":"10","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"more likely","correct":"3","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"very likely","correct":"1","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"}] | {"paper":"a formative paper","screen":"2","displaypos":"3"} | | 1 |
      | teacher | dichotomous | dichotomous leadin | dichotomous scenario | [{"option_text":"option 1","correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 2","correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 3","correct":"t","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"}] | {"paper":"a formative paper","screen":"3","displaypos":"1"} | | TF_Positive |
      | teacher | extmatch | extmach leadin | stem 1 \| stem 2 \| stem 3\|\|\|\|\|\|\| | [{"option_text":"option 1","correct":"3\|1\|2\|\|\|\|\|\|\|","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 2","correct":"3\|1\|2\|\|\|\|\|\|\|","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 3","correct":"3\|1\|2\|\|\|\|\|\|\|","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"}] | {"paper":"a formative paper","screen":"3","displaypos":"2"} | | |
      | teacher | blank | blank leadin | blank scenario | {"option_text":"Red is a [blank]colour,country,animal[/blank]. France is a [blank]country,colour,animal[/blank]","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"} | {"paper":"a formative paper","screen":"3","displaypos":"3"} | | dropdown |
      | teacher | matrix | matrix leadin | One\|Two\|Three\|\|\|\|\|\|\| | [{"option_text":"option 1","correct":"3\|2\|1\|\|\|\|\|\|\|","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 2","correct":"3\|2\|1\|\|\|\|\|\|\|","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 3","correct":"3\|2\|1\|\|\|\|\|\|\|","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"}] | {"paper":"a formative paper","screen":"1","displaypos":"4"} | | |
      | teacher | rank | rank leadin | rank scenario | [{"option_text":"option 1","correct":"1","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 2","correct":"2","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"},{"option_text":"option 3","correct":"3","marks_correct":"1","marks_incorrect":"0","marks_partial":"0"}] | {"paper":"a formative paper","screen":"2","displaypos":"4"} | | |

  @paper_preview_formative @jsevaluation
  Scenario: Preview an existing exam
    Given I login as "teacher"
    And I am on "Paper Details" page for "a formative paper"
    And I open the exam preview
    And I answer the questions:
      | position | type | answer |
      | 1 | true_false | t |
      | 2 | mcq | 3 |
      | 3 | enhancedcalc | 47 |
      | 4 | matrix | ["1", "2", "3"] |
    And I navigate paper "Next"
    And I answer the questions:
      | position | type | answer |
      | 1 | mrq | ["1","3"] |
      | 2 | textbox | some nice blurb to answer the question |
      | 3 | sct | 5 |
      | 4 | rank | ["3","2","1"] |
    And I navigate paper "Next"
    And I answer the questions:
      | position | type | answer |
      | 1 | dichotomous | ["t","f","t"] |
      | 2 | extmatch | ["3","2","1"] |
      | 3 | blank | ["colour","country"] |
    And I navigate paper "Finish"
    Then I should see your mark is "9.6 out of 22"
