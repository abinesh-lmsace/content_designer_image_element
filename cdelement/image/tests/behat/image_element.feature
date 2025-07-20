@mod @mod_contentdesigner @cdelement_image @image_element  @javascript @_file_upload
Feature: Check content designer image element settings
  In order to content elements settings of multiple responses
  As a teacher
  I need to add contentdesigner activities to courses
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email 							 |
      | teacher1 | Teacher 	 | 1 				| teacher1@example.com |
      | student1 | Student 	 | 1 				| student1@example.com |
      | student2 | Student 	 | 2 				| student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user 		 | course | role 		|
      | teacher1 | C1 		| editingteacher |
      | student1 | C1 		| student |
      | student2 | C1 		| student |
    And the following "activity" exists:
      | activity | contentdesigner             |
      | name     | Demo content                |
      | intro    | Contentdesigner Description |
      | course   | C1                          |
    And I log out

  Scenario: CDElement: Image: Single image with caption and disabled lightbox
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | No                        |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student1
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should not exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"

  Scenario: CDElement: Image: Single image with caption and lightbox
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | Yes                       |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student1
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"
    And I click on "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element"
    And "//div[contains(@class, 'modal') and contains(@class, 'moodle-has-zindex') and contains(@class, 'show')]//div[contains(@class, 'modal-lightbox')]//div[contains(@class, 'modal-content')]//div[contains(@class, 'modal-body')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist

  Scenario: CDElement: Image: Multiple images with caption and disabled lightbox
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/boy-and-book.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/lms-schools.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | No                        |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student1
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should not exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"
    Then "//ol[contains(@class, 'carousel-indicators')]/li[contains(@class, 'carousel-indicator')]" "xpath_element" should exist 3 times
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And I click on ".carousel-control-next" "css_element"
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should not exist
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist

  Scenario: CDElement: Image: Multiple images with caption and lightbox enabled
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/boy-and-book.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/lms-schools.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | Yes                       |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I log out
    And I am on the "Demo content" "contentdesigner activity" page logged in as student1
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"
    Then "//ol[contains(@class, 'carousel-indicators')]/li[contains(@class, 'carousel-indicator')]" "xpath_element" should exist 3 times
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And I click on ".carousel-control-next" "css_element"
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should not exist
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I click on ".carousel-control-next" "css_element"
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should not exist
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'lms-schools.jpg')]" "xpath_element" should exist
    And I click on "//img[contains(@class, 'element-image-img') and contains(@src, 'lms-schools.jpg')]" "xpath_element"
    And "//div[contains(@class, 'modal') and contains(@class, 'moodle-has-zindex') and contains(@class, 'show')]//div[contains(@class, 'modal-lightbox')]//div[contains(@class, 'modal-content')]//div[contains(@class, 'modal-body')]//img[contains(@class, 'element-image-img') and contains(@src, 'lms-schools.jpg')]" "xpath_element" should exist

  Scenario: CDElement: Image: Single image in popup format
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | No                        |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I am on the "Course 1" course page
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_format" to "Pop up activities"
    And I press "Save and display"
    And I log out
    And I am on the "Course 1" course page logged in as student1
    And I click on "Demo content" "link" in the "General" "section"
    And "Demo content" "dialogue" should be visible
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should not exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"

  Scenario: CDElement: Image: Single image in popup format with lightbox
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | Yes                       |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I am on the "Course 1" course page
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "id_format" to "Pop up activities"
    And I press "Save and display"
    And I log out
    And I am on the "Course 1" course page logged in as student1
    And I click on "Demo content" "link" in the "General" "section"
    And "Demo content" "dialogue" should be visible
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"
    And I click on "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element"
    And "//div[contains(@class, 'modal') and contains(@class, 'moodle-has-zindex') and contains(@class, 'show')]//div[contains(@class, 'image-container')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist

  Scenario: CDElement: Image: Multiple images in popup format without lightbox
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/boy-and-book.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/lms-schools.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | No                        |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I log out
    And I am on the "Course 1" course page logged in as student1
    And I click on "Demo content" "link" in the "General" "section"
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should not exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"
    Then "//ol[contains(@class, 'carousel-indicators')]/li[contains(@class, 'carousel-indicator')]" "xpath_element" should exist 3 times
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And I click on ".carousel-control-next" "css_element"
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should not exist
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist

  Scenario: CDElement: Image: Multiple images in popup format with lightbox
    Given I am on the "Demo content" "contentdesigner activity" page logged in as teacher1
    And I click on "Content editor" "link"
    And I click on ".contentdesigner-addelement .fa-plus" "css_element"
    And I click on ".elements-list li[data-element=image]" "css_element" in the ".modal-body" "css_element"
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/elearning-course.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/boy-and-book.jpg" file to "Images" filemanager
    And I upload "/mod/contentdesigner/cdelement/image/tests/fixtures/lms-schools.jpg" file to "Images" filemanager
    And I set the following fields to these values:
    | Caption          | E-learning Caption        |
    | Show in lightbox | Yes                       |
    | Title            | Image element Title       |
    | Description      | Image element Description |
    | showdescription  | 1                         |
    And I press "Create element"
    And I log out
    And I am on the "Course 1" course page logged in as student1
    And I click on "Demo content" "link" in the "General" "section"
    And "//img[contains(@class, 'element-image-img') and @data-lightbox='image-gallery']" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And "//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I should see "E-learning Caption" in the ".element-image-caption" "css_element"
    Then "//ol[contains(@class, 'carousel-indicators')]/li[contains(@class, 'carousel-indicator')]" "xpath_element" should exist 3 times
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should exist
    And I click on ".carousel-control-next" "css_element"
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'boy-and-book.jpg')]" "xpath_element" should not exist
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should exist
    And I click on ".carousel-control-next" "css_element"
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'elearning-course.jpg')]" "xpath_element" should not exist
    And "//div[contains(@class, 'image-container') and contains(@class, 'carousel-item') and contains(@class, 'active')]//img[contains(@class, 'element-image-img') and contains(@src, 'lms-schools.jpg')]" "xpath_element" should exist
    And I click on "//img[contains(@class, 'element-image-img') and contains(@src, 'lms-schools.jpg')]" "xpath_element"
    And "//div[contains(@class, 'modal') and contains(@class, 'moodle-has-zindex') and contains(@class, 'show')]//div[contains(@class, 'modal-lightbox')]//div[contains(@class, 'modal-content')]//div[contains(@class, 'modal-body')]//img[contains(@class, 'element-image-img') and contains(@src, 'lms-schools.jpg')]" "xpath_element" should exist
