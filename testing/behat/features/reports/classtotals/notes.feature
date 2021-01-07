@classtotals @note @javascript
Feature: Add student note in the class totals report
  In order to record issues that happended during an exam
  As a Admin/ Teacher
  I want to add a student note

  Background:
    Given the following "users" exist:
      | username | roles | sid |
      | student1 | Student | 987654321 |
    And the following "modules" exist:
      | moduleid | fullname |
      | m1 | m1 |
    And the following "module enrolment" exist:
      | sid | modulecode |
      | 987654321 | m1 |
    And the following "papers" exist:
      | type | papertitle | paperowner | modulename | startdate | calendaryear |
      | progress | paper1 | admin | m1 | 2020-02-27 12:00:00 | 2020 |
      | progress | paper2 | admin | m1 | 2020-02-25 12:00:00 | 2020 |
    And the following "paper note" exist:
      | paper | note | user | author |
      | paper2 | a note made by me | student1 | admin |

  Scenario: Add student note via class totals report
    Given I login as "admin"
    And I run report "Class Totals" for "paper1" with filters:
      | absent | 1 |
      | moduleid | m1 |
      | startdate | 20200227120000 |
    And I view a students "987654321" note
    And I add a note "class totals note"
    And I view a students "987654321" note
    Then I should see "class totals note"

  Scenario: Update student note via class totals report
    Given I login as "admin"
    And I run report "Class Totals" for "paper2" with filters:
      | absent | 1 |
      | moduleid | m1 |
      | startdate | 20200227120000 |
    And I view a students "987654321" note
    And I should see "a note made by me"
    And I add a note "updated class totals note"
    And I view a students "987654321" note
    Then I should see "updated class totals note"
