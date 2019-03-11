@core
Feature: Login
  In order to allow access to Rogo
  As a user
  I should be able to login

  Scenario: Admin user login
    Given I login as "admin"
    Then I should see "Administrative Tools" "menu_item"

  @javascript
  Scenario: Admin user login using JavaScript
    Given I login as "admin"
    Then I should see "Administrative Tools" "menu_item"

  Scenario: Student user login
    Given the following "users" exist:
      | username |
      | student1 |
    When I login as "student1"
    Then I should see "No exams found"

  @javascript
  Scenario: Student user login with JavaScript
    Given the following "users" exist:
      | username |
      | student1 |
    When I login as "student1"
    Then I should see "No exams found"

  Scenario: Internal reviewer user login
    Given the following "users" exist:
      | username | roles | grade |
      | internal1 | Internal Reviewer | Staff Internal Reviewer |
    When I login as "internal1"
    Then I should see "Papers for Review"

  @javascript
  Scenario: Internal reviewer user login with JavaScript
    Given the following "users" exist:
      | username | roles | grade |
      | internal1 | Internal Reviewer | Staff Internal Reviewer |
    When I login as "internal1"
    Then I should see "Papers for Review"

  Scenario: External examiner user login
    Given the following "users" exist:
      | username | roles | grade |
      | external1 | External Examiner | Staff External Examiner |
    When I login as "external1"
    Then I should see "Pre-Exam Review Papers"

  @javascript
  Scenario: External examiner user login with JavaScript
    Given the following "users" exist:
      | username | roles | grade |
      | external1 | External Examiner | Staff External Examiner |
    When I login as "external1"
    Then I should see "Pre-Exam Review Papers"

  Scenario: Invigilator user login
    Given the following "users" exist:
      | username | roles | grade |
      | invig1 | Invigilator | Invigilator |
    When I login as "invig1"
    Then I should see "Emergency Numbers"

  @javascript
  Scenario: Invigilator user login with JavaScript
    Given the following "users" exist:
      | username | roles | grade |
      | invig1 | Invigilator | Invigilator |
    When I login as "invig1"
    Then I should see "Emergency Numbers"

  Scenario: Standard Setter user login
    Given the following "users" exist:
      | username | roles | grade |
      | stdset1 | Staff,Standards Setter | Standards Setter |
    When I login as "stdset1"
    Then I should see "My Modules"

  @javascript
  Scenario: Standard Setter  user login with JavaScript
    Given the following "users" exist:
      | username | roles | grade |
      | stdset1 | Staff,Standards Setter | Standards Setter |
    When I login as "stdset1"
    Then I should see "My Modules"