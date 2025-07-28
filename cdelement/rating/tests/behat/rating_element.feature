@mod @mod_contentdesigner @cdelement_rating  @javascript
Feature: Check content designer rating element settings
  In order to content elements settings of multiple responses
  As a teacher
  I need to add contentdesigner activities to courses
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
      | student3 | Student | 3 | student3@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
    And the following "activity" exists:
      | activity    | contentdesigner              |
      | name        | Demo content                 |
      | intro       | Contentdesigner Description  |
      | course      | C1                           |
    And I log out

  Scenario: Add a numeric rating element with disabled type results
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=rating]" "css_element" in the ".modal-body" "css_element"
    And I set the following fields to these values:
      | Scale | Numeric |
      | Choose the numeric count | 10 |
      | Content | Lorem Ipsum is simply dummy text of the printing and typesetting industry. |
      | Label | Test label 1 |
      | Show results after submission | Disabled |
      | Mandatory | 1 |
      | Title | Numeric scale rating element |
    And I press "Create element"
    And I click on "Content Designer" "link"
    Then I should see "Lorem Ipsum" in the ".chapter-elements-list li.element-item" "css_element"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student1
    Then I should see "Please rate to see how others have rated" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    And I click on ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-scale.numeric-block .rating-item[data-value='5'] a.rating-scale-option" "css_element"
    And ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale.numeric-block .rating-item[data-value='5'].selected" "css_element" should exist

  Scenario: Add a non-numeric scale rating element with count type results
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=rating]" "css_element" in the ".modal-body" "css_element"
    And I set the following fields to these values:
      | Scale | Separate and Connected ways of knowing |
      | Content | Lorem Ipsum is simply dummy text of the printing and typesetting industry. |
      | Label | Test label |
      | Show results after submission | Count |
      | Mandatory | 1 |
      | Title | Non numeric scale rating element |
    And I press "Create element"
    And I click on "Content Designer" "link"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student1
    And I should see "Please rate to see how others have rated" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    And I click on ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale .rating-item[data-value='1'] a.rating-scale-option" "css_element"
    Then ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale .rating-item[data-value='1'].selected.average" "css_element" should exist
    And I should see "Most students selected the same as you \"(Mostly separate knowing)\"" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student2
    And I should see "Please rate to see how others have rated" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    And I click on ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale .rating-item[data-value='3'] a.rating-scale-option" "css_element"
    Then ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale .rating-item[data-value='3'].selected" "css_element" should exist
    And I should see "Most students selected \"Mostly separate knowing\"" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    Then ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale .rating-item[data-value='1'].average" "css_element" should exist

  Scenario: Add a numeric scale rating element with average type results
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=rating]" "css_element" in the ".modal-body" "css_element"
    And I set the following fields to these values:
      | Scale | Numeric |
      | Choose the numeric count | 10 |
      | Content | Lorem Ipsum is simply dummy text of the printing and typesetting industry. |
      | Label | Test label  |
      | Show results after submission | Average |
      | Mandatory | 1 |
      | Title | Numeric scale rating element |
    And I press "Create element"
    And I click on "Content Designer" "link"
    And I am on the "Demo content" "contentdesigner activity" page logged in as student1
    Then I should see "Please rate to see how others have rated" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    And I click on ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-scale.numeric-block .rating-item[data-value='5'] a.rating-scale-option" "css_element"
    And ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale.numeric-block .rating-item[data-value='5'].selected" "css_element" should exist
    And I should see "The average response was 5 points which is the same as your response" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student2
    And I should see "Please rate to see how others have rated" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    And I click on ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-scale.numeric-block .rating-item[data-value='9'] a.rating-scale-option" "css_element"
    And ".chapters-list:nth-child(2) li.element-item.element-rating:nth-child(1) .cdrating-block .rating-scale.numeric-block .rating-item[data-value='9'].selected" "css_element" should exist
    And I should see "The average response was 7 points, 2 points lower than your response (9)" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"

  Scenario: Rating element general settings
    When I log in as "admin"
    And I navigate to "Grades > Variables" in site administration
    And I click on "Create variable" "button"
    And I set the following fields to these values:
      | Fullname | Learning experience |
      | Shortname | LE |
      | content | Lorem Ipsum is simply dummy text of the printing and typesetting industry. |
      | Type | 0 |
      | Course categories | Category 1 |
      | Status | 1 |
    And I press "Save changes"
    And I click on "Create variable" "button"
    And I set the following fields to these values:
      | Fullname | Management experience |
      | Shortname | ME |
      | content | Lorem Ipsum is simply dummy text of the printing and typesetting industry. |
      | Type | 2 |
      | Course categories | Category 1 |
      | Status | 1 |
    And I press "Save changes"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=rating]" "css_element" in the ".modal-body" "css_element"
    And I set the following fields to these values:
      | Scale | Numeric |
      | Choose the numeric count | 10 |
      | content | Lorem Ipsum is simply dummy text of the printing and typesetting industry. |
      | Change rating | 1 |
      | Label | Test label  |
      | Variables | Learning experience, Management experience |
      | Show results after submission | Average |
      | Mandatory | 1 |
      | Title | Rating element general settings |
      | Padding   | 20 |
      | Margin    | 10 |
      | Animation | Slide in from right|
      | Duration | Fast |
      | Delay    | 1000 |
    And I press "Create element"
    And I click on "Content Designer" "link"
    Then I should see "Please rate to see how others have rated" in the ".chapters-list:nth-child(2) li.element-item.element-rating .cdrating-block .rating-result-block .rating-result" "css_element"
    Then ".chapter-elements-list li.element-rating .general-options[data-entranceanimation='{\"animation\":\"slideInRight\",\"duration\":\"fast\",\"delay\":\"1000\"}']" "css_element" should exist
