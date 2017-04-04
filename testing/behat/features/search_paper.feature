@core 
Feature: Searching Papers,People and their related Academic Sessions
   In order to check searching on Papers,People
   As a Admin/ Teacher
   I want to do search

   @javascript
   Scenario: Test search Papers and People
      Given the following "users" exist:
         | username |roles |
         | myteacher1 | Staff |
         | myteacher2 | Staff |
         | myteacher3 | Staff |
      Given the following "modules" exist:
         | moduleid | fullname |
         | m1 | m1 |
         | m2 | m2 |
         | m3 | m3 |
      Given the following "academic_year" exist:
         | calendar_year | academic_year |
         | 2015 | 2015/16 |
         | 2016 | 2016/17 |
         | 2017 | 2017/18 |
      Given the following "papers" exist:
         | papertitle | papertype | paperowner | modulename |
         | paper1 | 2 | myteacher3 | m1 |
         | paper2 | 2 | myteacher3 | m2 |
         | paper3 | 2 | myteacher3 | m3 |
      Given the following "module_teams" exist:
         | modulename | username |
         | m1 | myteacher1 |
         | m1 | myteacher2 |
         | m1 | myteacher3 |
         | m2 | myteacher1 |
         | m2 | myteacher2 |
         | m2 | myteacher3 |
         | m3 | myteacher1 |
         | m3 | myteacher2 |
         | m3 | myteacher3 |

      Given I login as "admin"
      When I go to the homepage
      Then I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      Then I click "Papers" "sub_search_menu_item"
      Then I should be on "/paper/search.php"
      Then I fill in the following:
         | searchterm | paper2 |
      Then I check "summative"
      Then I check "peerreview"
      Then I check "offline"
      Then I check "osce"
      Then I check "survey"
      Then I check "progress"
      Then I check "formative"
      And I press "Search"  
      Then I should see "paper2" "link"
      Then I log out
      Given I login as "myteacher3"
      When I go to the homepage
      Then I follow "Search"
      Then I should see submenu with following items:
         | menu_items |
         | Questions |
         | Papers |
         | People |
      When I click "Papers" "sub_search_menu_item"   
      Then I should be on "/paper/search.php"
      Then I fill in the following:
         | searchterm | paper2 |
      Then I check "summative"
      Then I check "peerreview"
      Then I check "offline"
      Then I check "osce"
      Then I check "survey"
      Then I check "progress"
      Then I check "formative"
      And I press "Search"  
      Then I should see "paper2" "link"
      Then I follow "paper2"
      Then I should see "Paper Tasks" menu section with following items
      | items |
      | Test & Preview |
      | Add Questions to Paper |
      | Edit Properties |
      | Reports |
      | Mapped Objectives |
      | Copy Paper |
      | Delete Paper |
      | Retire Paper |
      | Print Hardcopy version |
      | Import/Export |
      Then I should see "Question Tasks" menu section with following items
      | items |
      | Edit Question |
      | Information |
      | Copy onto Paper X |
      | Link to paper |
      | Remove from Paper |
      | Preview Question |