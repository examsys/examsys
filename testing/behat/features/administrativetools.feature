@core
Feature: Administrative Tools
   In order to check page Administrative Tools,
   As a admin
   I want see all functions listed in this page are available 

   @javascript
   Scenario Outline: Admin tools links
      Given I login as "admin"
      Then I should see "Administrative Tools" "menu_item"
      When I follow "Administrative Tools"
      Then I should see "<linkname>"
      When I click "<linkname>" "admin_tool_link"
      Then I should see page with title "<page_title>"
      Then I move backward one page

   Examples:
         | linkname | page_title |
         | Academic Sessions | Academic Sessions |
         | OAuth Authentication | OAuth Keys |
         | Calendar | Calendar |
         | Clear Guest Accounts | Clear Guest Accounts |
         | Clear Orphan Media | Orphan Media |
         | Computer Labs | Computer Labs |
         | Courses | Courses |
         | Denied Log Warnings | Denied Log Warning | 
         | Ebel Grid Templates | Ebel Grid |
         | Faculties | Faculties |
         | LTI Keys | LTI Keys |
         | Modules | Modules |
         | Question statuses | Question Statuses |
         | Save Fail Attempts | Save Fail Attempts |
         | Schools | Schools |
         | Statistics | Statistics Reports |
         | System Errors | System Errors |
         | System Information | System Information |
         | Testing | Test |
         | User Management | User |

   Scenario: more Admin tools links
      Given I login as "admin"
      Then I should see "Administrative Tools" "menu_item"
      When I follow "Administrative Tools"
      Then I should see "Bug Reporting" "admin_tool_link"
      Then I should see "Clear Old Logs" "admin_tool_link"
      Then I should see "Clear Training" "admin_tool_link"
      Then I should see "phpinfo()" "admin_tool_link"