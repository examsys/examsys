@core @backend
Feature: Get parameters passed to PHP
  In order to safely get parameters passed to the page
  As a developer
  I need to ensure it is found safely and is validated.

  Scenario Outline: Optional parameters
    Given the following parameters have been passed:
      | name | value | method |
      | id | 5 | GET |
      | function | test | POST |
      | numbers | array:1,2,3,4; | POST |
      | letters | array:test,value; | GET |
    When I look for the optional "<method>" parameter "<name>" as a "<type>"
    Then the clean result should be "<output>"

    Examples:
      | name | method | type | output |
      | id | GET | INT | 5 |
      | id | REQUEST | INT | 5 |
      | id | POST | INT | null |
      | id | GET | IP_ADDRESS | null |
      | function | GET | ALPHA | null |
      | function | REQUEST | ALPHA | test |
      | function | POST | ALPHA | test |
      | none | GET | RAW | null |
      | none | POST | RAW | null |
      | none | REQUEST | RAW | null |
      | numbers | REQUEST | INT | array:1,2,3,4; |
      | numbers | POST | ALPHA | array:null,null,null,null; |
      | numbers | GET | ALPHA | null |
      | letters | REQUEST | ALPHA | array:test,value; |

  Scenario Outline: Require parameters that are set
    Given the following parameters have been passed:
      | name | value | method |
      | id | 5 | GET |
      | function | test | POST |
      | numbers | array:1,2,3,4; | POST |
      | letters | array:test,value; | GET |
    When I look for the required "<method>" parameter "<name>" as a "<type>"
    Then the clean result should be "<output>"

    Examples:
      | name | method | type | output |
      | id | GET | INT | 5 |
      | id | REQUEST | INT | 5 |
      | function | REQUEST | ALPHA | test |
      | function | POST | ALPHA | test |
      | numbers | REQUEST | INT | array:1,2,3,4; |
      | letters | REQUEST | ALPHA | array:test,value; |

  Scenario Outline: Require a parameter that has not been passed with a valid value
    Given the following parameters have been passed:
      | name | value | method |
      | test | 5 | GET |
      | numbers | array:1,2,3,4; | POST |
    When I look for the required "<method>" parameter "<name>" as a "<type>" there should be an exception

    Examples:
      | name | method | type |
      | id | GET | RAW |
      | id | POST | RAW |
      | id | REQUEST | RAW |
      | test | POST | INT |
      | test | GET | IP_ADDRESS |
      | numbers | REQUEST | ALPHA |
