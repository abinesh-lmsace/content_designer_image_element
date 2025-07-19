# About Content Designer

## Content Designer - Free version

Content Designer is a specialized activity plugin for Moodle designed to enhance instructional design and support collaborative content creation. It organizes content into chapters, each containing customizable elements such as headings, paragraphs, and rich text. These elements can be tailored with options for appearance, animations, and responsiveness, enabling the creation of dynamic and engaging content. To ensure consistency, Content Designer supports global styling, allowing elements to be aligned with organizational branding guidelines for a cohesive and professional look that reflects the organization’s identity.

## Content Designer Pro - Paid version

Content Designer Pro expands on the basic version by offering additional elements and advanced features. It includes a variety of content types like polls for feedback, Questions, and Video Time activities to integrate videos directly into the chapters. The Pro version enhances navigation by adding elements such as a table of contents for easy tracking, along with customizable chapter completion modes, where students can manually or automatically mark chapters as completed. These features provide more flexibility and interactivity, improving both the design and the learner experience.

Additionally, it includes activity completion with a condition that ensures users finish the content and meet the mandatory requirements for elements in the content designer.

# Installation and initial setup

## Installation

You can install the Content designer plugin using the Moodle plugin installer. Here are the steps to follow:

1. Download the "**Content designer**" plugin from the Moodle plugins repository or <a href="https://bdecent.de/product/content-designer-basic/">Content Designer</a> in Bdecent site.
2. Log in to your Moodle site as an administrator.
3. Go to "*`Site administration > Plugins > Install plugins`*".
4. Upload the downloaded plugin ZIP file.
5. Follow the prompts to install the plugin.
6. Once the Content designer plugin is installed, you can configure it by going to Site Administration > Plugins > Activity Modules > Content designer.

Alternatively, you can also install the Content designer plugin manually. Here are the steps to follow:

1. Download the "**Content designer**" plugin from the Moodle plugins repository or <a href="https://bdecent.de/product/content-designer-basic/">Content Designer</a> in Bdecent site..
2. Unzip the downloaded file.
3. Upload the Content designer folder to the moodle/mod directory on your Moodle server.
4. Log in to your Moodle site as an administrator.
5. Go to "*`Site administration > Notifications`*".
6. Follow the prompts to install the plugin.

# Key Features

**Robust content editor:**

Content Designer is an easy-to-use tool for creating and managing learning content. It lets users combine text, images, videos, and interactive elements to make the content engaging. With features like collaborative editing and a chapter-based structure, it helps organize content and allows users to edit and customize the content to fit their needs.

**Appearance:**

Creating a good user experience involves clear headings, animations for dynamic effects, and appealing backgrounds. Responsive settings make content look great on any device, while scrolling effects and popups add interactivity. Using global styles ensures everything matches the organization’s branding for a consistent look.

**Tracking and Completion Requirements:**

Tracking and completion requirements help manage learner progress. Students may need to complete tasks like quizzes or videos before moving on, with a progress bar showing their status. Activities can be marked complete when opened or based on specific conditions. Detailed reports track progress and time spent on each activity.

**Element Types:**

Different elements help organize and improve your content. Headings structure text for clarity, while paragraphs keep it readable. Rich text allows for formatting and adding media like images and videos. H5Ps bring in interactive elements, polls gather feedback, and questions offer quizzes. Video Time activities let you include video-based content in your chapters.

**Navigation:**

The outro element concludes the content by displaying images or messages to students. A table of contents shows all chapters, helping students track progress and resume easily. Navigation controls can block access to future content until mandatory sections are completed. Chapter completion can be manual or automatic, based on the material finished.


# Content Editor
It is the workspace where instructional designers create, organize, and customize course module content within the Content Designer module. It provides a user-friendly interface with tools for adding and managing elements, ensuring the design aligns with learning objectives and branding standards.

To access the content editor page, go to the activity page and click the "Content Editor" link in the secondary navigation. The Editor page will then appear.<br>
    - "*`Course > Content Designer Activity > Content Editor`*"

1. **Insert Element Popup**

    To add elements on the edit page, click the Add (Plus icon). This will open the Insert Element Popup, where selecting an element will display its settings and add it to the edit page.

![content-element-popup](https://github.com/user-attachments/assets/aa0a196f-bdbc-4d58-ab18-a3dfa48907c2)

2. **Element Management**

    Add, edit, reorder, and delete elements such as chapters, headings, paragraphs, images, videos, and interactive H5P content.
    Key features include:

    - **Add (Circle Plus Icon)** Create new elements such as chapters, headings, paragraphs, rich text, video time, or interactive H5P content **by clicking the light grey circle with the plus icon**.
    - **Edit:** To modify existing elements to update or improve content as needed.
    - **Edit Title:** To modify the title of the content element and reflect the changes, click on the pencil icon to edit it inline.
    - **Delete:** Remove elements that are no longer required or relevant to the course module.
    - **Reorder:** Change the sequence of elements to adjust the flow of content within the activity.
    - **Visibility:** Control the visibility of elements, allowing you to hide or show content based on requirements or stages of the course module.
    - **Duplicate:** Copy elements to easily reuse or create similar content across different sections.
    - **Report (Poll):** Generate detailed reports for polls to analyze responses, participation, and trends.

![content-element-editor](https://github.com/user-attachments/assets/76875864-5291-4399-8f72-801082656d26)

# Global Configuration Options

Content Designer includes global configuration options that allow organizations to standardize and streamline the design of their content. These configurations enable users to apply consistent styling, such as typography, color schemes, and animations, across all elements and chapters.

To access the global settings for Content Designer, you need to log in as an administrator:<br>
   - "*`Dashboard > Site Administration > Plugins > Activity Modules > Content Designer`*"

From here, you can adjust the global configuration options to standardize the design and functionality of content across the platform.

## Content designer module global settings

The Content Designer Module Settings allow administrators and instructional designers to customize the functionality and behavior of the Content Designer activity.

1. **Navigation Method**

    The navigation method in Content Designer determines how learners interact with and progress through the course module content. It defines whether learners must follow a predefined sequence or have the freedom to navigate the course module as they choose.

    - **Sequential Navigation (default)**

        In this method, users must complete tasks in a predefined order. They are not able to skip over any mandatory steps and must follow the sequence until all elements are completed. This ensures that the user follows a structured flow.

    - **Free**

        Here, users have the flexibility to move through tasks in any order. They can skip over mandatory steps or return to them later as needed, providing a more flexible approach to completing the process.

![general-activity-global-setting](https://github.com/user-attachments/assets/c4f19002-03de-4d42-8e93-bd6f1b0eceae)

## Element general settings
The Element General Settings in Content Designer allow you to control the default behavior and appearance of content elements within a chapter. These settings provide flexibility in customizing the way elements such as text, images, videos, and interactive components behave and appear across the course module.

1. **Visibility**

    This setting determines whether a specific element is visible to users.
    - **Visible:** The element is shown to users by default and is accessible during their interaction.
    - **Hidden:** The element is not displayed to users.

2. **Margin**

    The margin refers to the space outside an element, creating distance between the element and surrounding content or other elements. Adjusting the margin allows you to control the spacing around an element, helping to position it relative to other elements and ensuring a balanced layout.

3. **Padding**

    The space between the content of an element (like text, images, or other elements) and its borders. Adjusting padding allows you to control the amount of internal spacing, which improves readability, aesthetics, and alignment within your designs.

4. **Background color/gradient (above)**

    This feature allows you to add a color or gradient effect on top of the existing background, creating an overlay that enhances the visual style of the element. This is particularly useful when you want to create a layered effect or add subtle color transitions without altering the base background.

5. **Background color/gradient (below)**

    This feature in Content Designer allows you to apply a background color or gradient specifically to the area below the content of an element. This helps create visual separation, depth, or an appealing design effect without affecting the content itself.

6. **Background image**

    The Background Image feature allows you to set an image as the background of an element. This image will be displayed behind the content of the element, creating a visually rich backdrop that enhances the design.

7. **Animation**

    This feature allows you to apply an entrance animation to an element. This animation is triggered when the element first appears on the screen, either as the user scrolls into view or interacts with the content. It helps create a dynamic and engaging experience for users by drawing attention to new or important content as it loads.

8. **Duration**

    The Duration setting controls how long the entrance animation takes to complete, determining the speed at which the animation occurs. This setting is crucial for adjusting the pacing and emphasis of the animation, allowing you to create a more dynamic or subtle effect based on your design needs.

9. **Delay**

    This settings allows you to control the timing of when the entrance animation begins after the element comes into view. This feature is useful when you want to create a more controlled, timed effect, allowing the element to wait for a specific duration before it starts animating.

10. **Direction**

    The Direction setting allows you to apply scrolling effects to an element, making it move as the user scrolls through the page. This can create a dynamic, interactive experience that draws attention to specific content and adds a sense of motion to your design.

11. **Speed**

    The speed setting allows you to control how fast or slow an element moves when a scrolling effect is applied. This setting is crucial for fine-tuning the user experience, as it determines the pace at which the element will move as the user scrolls down the page.

12. **Viewport**

    The Viewport setting allows you to define when the scrolling effect should start based on how much of the element is visible within the user's viewport. This gives you more control over when the animation or scrolling effect is triggered, enhancing the user experience by ensuring the effect occurs at the most appropriate moment.

13. **Hide on desktop**

    The Hide on Desktop allows you to hide a specific element when the page is viewed on a desktop screen.

14. **Hide on tablet**

    The Hide on Tablet option allows you to hide a specific element when the page is viewed on a tablet screen.

15. **Hide on mobile**

    The Hide on Mobile option allows you to hide a specific element when the page is viewed on a mobile screen.

![Element-general-global-setting](https://github.com/user-attachments/assets/8d0dd5d6-2b79-449a-8aa2-da4106ca4412)

# Global Elements setting
The Global Elements Settings allow you to define the default behavior and appearance of various content elements across all chapters and activities, ensuring consistency and alignment with your organization's design standards. These settings control the layout, styling, and functionality of each element type, and can be customized globally for a unified learning experience.

## Chapter
Click on the <a href="https://github.com/bdecentgmbh/moodle-mod_contentdesigner/wiki#chapter-1">**Chapter**</a> element settings to view and customize its specific options, allowing you to create and organize the chapter within your course module design.

![chapter-global-setting](https://github.com/user-attachments/assets/06a895e6-ada8-424c-a012-87bd5c0f7549)

## Heading
Click on the <a href="https://github.com/bdecentgmbh/moodle-mod_contentdesigner/wiki#heading-1">**Heading**</a> element settings to view and customize its specific options, allowing you to adjust the textual element used to introduce or organize sections of content within a chapter or activity.

![heading-global-setting](https://github.com/user-attachments/assets/0c0b9741-8c9f-4aa9-b37e-1e9d49a6bc49)

## Interactive content (H5P)
H5P allows the creation of rich, interactive content such as quizzes, games, videos, presentations, and interactive learning objects. These elements are highly customizable and designed to enhance learner interaction and improve content engagement.

1. **Mandatory**

    The Mandatory setting allows you to specify whether completing an element is required to unlock the next one in the sequence. This helps in controlling the flow of content and guiding learners through the material in a structured manner.
        - **Yes:** the user must finish the current element before they can move on to the next.
        - **No:** the next element is available regardless of whether the current one is completed.
    This setting is useful for creating a sequential flow, guiding users through the content step-by-step.

2. **Save state**

    The Save State feature automatically saves the user's progress, allowing them to return later and pick up right where they left off.

3. **Save state frequency**

    The Save State Frequency setting determines how often the user's progress is saved, measured in seconds. This ensures that the user's current state is updated regularly for a seamless return experience.

![h5p-global-setting](https://github.com/user-attachments/assets/e8bdc627-bec3-4ef4-8e60-1eda5b1b1d0b)

## Paragraph
Click on the <a href="https://github.com/bdecentgmbh/moodle-mod_contentdesigner/wiki#paragraph-1">**Paragraph**</a> element settings to view and customize its specific options, allowing instructional designers to add detailed explanations, descriptions, or narratives that support the learning objectives of the course module.

![paragraph-global-setting](https://github.com/user-attachments/assets/63b61811-a1ca-43df-a5a7-38d2f9a63ad6)

## Outro
Click on the <a href="https://github.com/bdecentgmbh/moodle-mod_contentdesigner/wiki#outro-1">**Outro**</a> element settings to view and customize its specific options, allowing you to create the final element or section within a chapter or activity that provides closure to the content.

![outro-global-setting](https://github.com/user-attachments/assets/66483a06-1831-4aba-bbfe-54a16ecd0394)

## Question (Pro)
A **Question** in Content Designer refers to an interactive element used to assess learner understanding and engagement with the course module content. Questions can be included at various points throughout a course module to reinforce key concepts, check comprehension, or provide learners with opportunities to apply what they have learned.

1. **How the question behaves**

    It determines how questions are presented, answered, and how the system handles responses.
    Refer to the Moodle documentation on <a href="https://docs.moodle.org/405/en/Question_behaviours#How_questions_behave">Question Behaviours.</a>
    - **Adaptive mode**
    - **Adaptive mode (no penalties)**
    - **Deferred feedback**
    - **Deferred feedback with CBM**
    - **Immediate feedback**
    - **Immediate feedback with CBM**
    - **Interactive with multiple tries**

2. **Mandatory**

    The Mandatory setting allows you to specify whether completing an element is required to unlock the next one in the sequence. This helps in controlling the flow of content and guiding learners through the material in a structured manner.
    - **Yes:** the user must finish the current element before they can move on to the next.
    - **No:** the next element is available regardless of whether the current one is completed.

3. **Whether correct**

    Whether correct decides if students should be shown whether their response to an embedded question was correct.
    - **Shown:** students will see feedback like "Correct," "Partially correct," or "Incorrect," along with colored highlights indicating their answer's correctness.
    - **Not Shown:** this feedback and highlighting are not displayed, meaning students won't immediately know if their answer was right or wrong.

4. **Marks**

    It determines whether numerical mark information should be displayed for embedded questions.
    - **Not shown:** No mark information is displayed.
    - **Show max mark only:** Only the maximum possible mark for the question is shown.
    - **Show mark and max:** Both the student's current mark and the maximum possible mark are displayed.

5. **Decimal places in grades**

    It determines how many digits should be shown after the decimal point when displaying grades for embedded questions. This setting allows you to control the level of precision in the grades displayed, such as showing no decimal places, one decimal place, or more, depending on the grading system used.

6. **Specific feedback**

    It controls whether personalized feedback based on the student's response is displayed for embedded questions.
    - **Shown:** The student will see feedback about their answer, indicating whether it was correct, partially correct, or incorrect.
    - **Not shown:** No feedback will be displayed, so the student won't receive specific comments on their response.

7. **General feedback**

    General feedback determines whether overall feedback for the question is displayed by default in embedded questions.
    - **Shown:** The student will see general feedback after answering the question, which can include explanations, tips, or additional information.
    - **Not shown:** General feedback will not be displayed, so the student will not receive any additional information after answering the question.

8. **Right answer**

    Right answer determines whether the correct answer is automatically displayed by default for embedded questions. It is recommended to use general feedback for explanations rather than showing the correct answer directly, as this approach promotes deeper learning and understanding.
    - **Shown:** The correct answer will be displayed automatically after the question is answered.
    - **Not shown:** The correct answer will not be displayed, encouraging question authors to provide explanations in the general feedback instead.

9. **Response history**

    It determines whether a table displaying the history of a student's responses is shown by default for embedded questions.
    - **Shown:** Students can view a table that records their previous responses, attempts, and any changes made during the activity.
    - **Not shown:** The response history table will not be displayed, so students will not have access to a record of their past attempts.

10. **Force language**

    This setting allows a specific language to be enforced within a Moodle course module. When enabled, the course module interface will appear in the selected language for all participants, regardless of the language preferences set in their personal profiles.
    Refer to the Moodle documentation on <a href="https://docs.moodle.org/405/en/Course_settings#Force_language">Force Language</a>

![question-global-setting](https://github.com/user-attachments/assets/48d60287-1bc6-45f7-9939-c55ccb98f6dd)

## Table of contents (Pro)
Click on the <a href="https://github.com/bdecentgmbh/moodle-mod_contentdesigner/wiki#table-of-contents">**Table of Contents (TOC)**</a> element settings to view and customize its specific options, an element that provides an overview of the structure of a course module or chapter.

![TOC-global-setting](https://github.com/user-attachments/assets/1ba2acd8-698f-49c8-bbb0-6ad607db9d14)


# Content designer module settings

The Content Designer Module Settings allow administrators and instructional designers to customize the functionality and behavior of the Content Designer activity.

1. **Navigation Method**

    The navigation method in Content Designer determines how learners interact with and progress through the course module content. It defines whether learners must follow a predefined sequence or have the freedom to navigate the course module as they choose.

    - **Sequential Navigation (default):** In this method, users must complete tasks in a predefined order. They are not able to skip over any mandatory steps and must follow the sequence until all elements are completed. This ensures that the user follows a structured flow.
    - **Free:** Here, users have the flexibility to move through tasks in any order. They can skip over mandatory steps or return to them later as needed, providing a more flexible approach to completing the process.

2. **Completion conditions**

    Set specific requirements for marking the Content Designer activity as complete.

    - **Completed when user finish the content:** The activity is marked complete when the user goes through and finishes all the provided content.
    - **Complete Mandatory Elements:** Users must complete all elements marked as mandatory to satisfy this condition.

![activity-completion-condition](https://github.com/user-attachments/assets/f1e7a21a-6e28-4ef4-b51c-c1d673503c22)

# Elements setting
Element settings in Content Designer are used to control the appearance, behavior, and structure of specific content elements within a course module. These settings allow for the customization of individual elements like headings, paragraphs, videos, chapters, and more, ensuring that each element fits the course's module design and instructional needs.

**Content Designer Progress Bar:**
The Content Designer Progress Bar is a visual indicator that helps learners track their chapter progress within an activity. With a sticky position feature, the progress bar remains visible as learners scroll through the content, providing real-time updates on completed and pending tasks.

![activity-progress-bar](https://github.com/user-attachments/assets/a41326c0-587f-4c7e-a821-f69655600be1)

## Chapter
A Chapter in Content Designer is a primary organizational unit for structuring learning content. It groups related elements, such as headings, paragraphs, videos, and interactive components, into a cohesive section that learners can navigate through. Chapters help break down complex content into manageable, focused segments, making the learning experience more structured and easier to follow.

1. **Visibility**

    This setting determines whether a specific element is visible to users.
    - ***Visible:*** The element is shown to users by default and is accessible during their interaction.
    - ***Hidden:*** The element is not displayed to users.

2. **Display title**

    The Display Title setting allows you to control whether the chapter's title is shown to learners or not.

3. **Completion mode**

    The Completion Mode setting defines how chapters are marked as completed by learners.
    - ***Manual mode:*** The students must click a "Mark as Complete" button to indicate they've finished the chapter.
    - ***Automatic mode:*** The chapters are automatically marked as complete when the student scrolls past them, and as soon as the next chapter becomes visible, the previous one is marked as complete.

![chapter](https://github.com/user-attachments/assets/fae4e950-371d-42c6-bec9-50beb17b5ad8)

## Interactive content (H5P)
H5P allows the creation of rich, interactive content such as quizzes, games, videos, presentations, and interactive learning objects. These elements are highly customizable and designed to enhance learner interaction and improve content engagement.

1. **Mandatory**

    The Mandatory setting allows you to specify whether completing an element is required to unlock the next one in the sequence. This helps in controlling the flow of content and guiding learners through the material in a structured manner.
    - **Yes:** the user must finish the current element before they can move on to the next.
    - **No:** the next element is available regardless of whether the current one is completed.
    This setting is useful for creating a sequential flow, guiding users through the content step-by-step.

    ![h5p](https://github.com/user-attachments/assets/64eb9f3d-2297-4ce2-a6f1-a71532f92f10)

## Heading
A Heading in Content Designer refers to a textual element used to introduce or organize sections of content within a chapter or activity. Headings help structure the content and provide learners with clear visual cues that guide them through the course module material.

1. **Heading**

    The Heading option allows you to choose between two heading types to structure your content effectively.
    - **Main Heading (H2):** It is a larger, prominent heading ideal for main sections or titles.
    - **Sub Heading (H3):** It is slightly smaller and suited for subsections or supporting information.

2. **Target**

    The Target setting determines how a link attached to a heading will open when clicked.
    - **Same Window:** Opens the link in the current browser tab, replacing the existing page.
    - **New Window:** Opens the link in a new browser tab, allowing users to view the link without leaving the current page.

3. **Horizontal Alignment**

    The Horizontal Alignment setting allows you to position text horizontally within an element.
    - **Left:** Aligns the text to the left side.
    - **Center:** Centers the text within the element.
    - **Right:** Aligns the text to the right side.

4. **Vertical Alignment**

    The Vertical Alignment setting allows you to position text vertically within an element.
    - **Top:** Aligns the text to the top of the element.
    - **Middle:** Centers the text vertically within the element.
    - **Bottom:** Aligns the text to the bottom of the element.

![heading](https://github.com/user-attachments/assets/905ba9b3-4abd-42c1-8e0b-cda405bcbec6)

## Paragraph
A Paragraph in Content Designer is a content element used to present blocks of text within a chapter or activity. It enables instructional designers to provide detailed explanations, descriptions, or narratives that align with and support the course module's learning objectives.

1. **Horizontal Alignment**

    The Horizontal Alignment setting allows you to position text horizontally within an element.
    - **Left:** Aligns the text to the left side.
    - **Center:** Centers the text within the element.
    - **Right:** Aligns the text to the right side.

2. **Vertical Alignment**

    The Vertical Alignment setting allows you to position text vertically within an element.
    - **Top:** Aligns the text to the top of the element.
    - **Middle:** Centers the text vertically within the element.
    - **Bottom:** Aligns the text to the bottom of the element.

![paragraph](https://github.com/user-attachments/assets/a2def453-2345-4d25-94d8-ddacd1d99bed)

## Question
A Question in Content Designer refers to an interactive element used to assess learner understanding and engagement with the course module content. Questions can be included at various points throughout a course module to reinforce key concepts, check comprehension, or provide learners with opportunities to apply what they have learned.

1. **Category**

    Questions are organized into categories to help manage and structure content effectively. By default, each course has one category named "Default". You can view a hierarchy of subcategories nested within parent categories for better organization. Use the "Select a category" drop-down menu to choose a category from the Moodle system, a Moodle category, or a specific Moodle course.

2. **Question**

    Choose a question from the list of available questions within the selected category.

3. **Version**

    This setting includes only the "Always latest" option, which ensures that the Moodle quiz automatically reflects the most recent modifications, keeping the content consistently up to date.

![question-version](https://github.com/user-attachments/assets/f8e3f50c-8f09-4139-a0e0-d457f6d2ad3c)

4. **How the question behaves**

    It determines how questions are presented, answered, and how the system handles responses.
    Refer to the Moodle documentation on <a href="https://docs.moodle.org/405/en/Question_behaviours#How_questions_behave">Question Behaviours.</a>
    - **Adaptive mode**
    - **Adaptive mode (no penalties)**
    - **Deferred feedback**
    - **Deferred feedback with CBM**
    - **Immediate feedback**
    - **Immediate feedback with CBM**
    - **Interactive with multiple tries**

5. **Mandatory**

    The Mandatory setting allows you to specify whether completing an element is required to unlock the next one in the sequence. This helps in controlling the flow of content and guiding learners through the material in a structured manner.
    - **Yes:** the user must finish the current element before they can move on to the next.
    - **No:** the next element is available regardless of whether the current one is completed.

![question](https://github.com/user-attachments/assets/87957fdf-183d-4c9b-adc9-424b91cbdfeb)

## Poll
A Poll in Content Designer is an interactive element that allows learners or course participants to provide feedback or share their opinions on a specific question or topic. Polls are commonly used to gather data from learners, engage them in discussions, or get an understanding of their preferences and views on particular aspects of the course module.

![poll](https://github.com/user-attachments/assets/68a2ca22-d4ee-4d76-869d-56eaa79f7e26)

1. **Question**

    Enter the main question or topic for the poll. This will be displayed to students above the options for selection, guiding them to provide their responses based on the question posed.

2. **Option**

    Enter the various response choices for the poll. These options will be presented to students as selectable answers, allowing them to choose one or more responses based on the question or topic posed in the poll. You can add multiple options (e.g., Option 1, Option 2, etc.) depending on the type of poll you're creating.

3. **Number of Selectable Options**

    This setting allows you to control how many options students can choose in a poll. Setting it to "0" means unlimited choices (students can select all options).
    If set to a number between 1 and n, students can only select up to that specific number of options.

4. **Results**

    Decide when and how the poll results will be shown to students. This could include displaying the results immediately after submission or at a later time, depending on your preference for transparency and engagement.
    - **Hidden:** Results will not be shown to students after they submit their poll response.
    - **Displayed always:** Results will always be visible to students, even before they submit their answers.
    - **Displayed after their own rating:** Results will only be displayed after students have rated their own response or submitted their answer.

5. **Update Rating**

    This option allows you to determine if the poll results or responses should affect any ratings or feedback within the course module. You can configure whether responses are used to update the learner's progress or score.
    - **Enabled:** Poll results or responses will be used to update ratings or feedback within the course module.
    - **Disabled:** Poll results will not affect any ratings or course module feedback.

6. **After submission message**

    Enter a custom message that will appear to students after they submit their poll response. This message can be used to thank them for participating, provide further instructions, or give additional context about the poll results.

7. **Mandatory**

    This setting determines whether completing the poll is required before the user can proceed to the next section or activity.
    - **Yes:** The user must complete the poll before moving on to the next activity or chapter.
    - **No:** The user can skip the poll and continue to the next activity without completing it.

![poll-view](https://github.com/user-attachments/assets/bad6dcb7-c1f0-48d6-ba08-486614359ce7)

## Poll Report

The Poll Report provides a summary of how users answered each question in a poll. It includes statistics such as poll options, the number of responses and the users who selected each option. You can view the poll reports by clicking the calendar icon of the Poll element on the Content Editor page.

![poll-report](https://github.com/user-attachments/assets/f09bff7d-afc0-48d8-a20f-b308a83de85a)

## Rich Text

Use the rich text editor to create and format content with a variety of styling options, including text formatting, lists, links, and media. The editor also supports file uploads, allowing you to add images, videos, and other media to enrich your content.

![rich-text](https://github.com/user-attachments/assets/dec1b328-6e9a-4c92-acf6-b0c1b609d0c8)

## Video Time

The Videotime element in Content Designer allows instructional designers to integrate video-based activities into the course module structure. These settings provide flexibility in how video content is presented and interacted with by learners.

![video-time](https://github.com/user-attachments/assets/edfc48a9-9120-4752-b3a4-11360612bb5e)

1. **Course**

    Select the specific course from the list of available courses that includes the video for the Videotime activity. This ensures that the video is correctly linked to the relevant course content, offering learners a smooth and consistent learning experience.

    - **The course will only appear in this option if the Videotime module has been added to that course.**

    - **When the user is not enrolled in the selected course that has the VideoTime activity, only a message will be displayed without the "Enrol me" button informing the user to enroll in the course. If self-enrollment is enabled, the "Enrol me" button will be available, allowing the user to enroll directly.**

![videotime-enrol-option](https://github.com/user-attachments/assets/762b797d-0c9d-41b6-b35a-3901ff999fee)

2. **Videotime Modules**

    Select the specific activity from the list of available Videotime modules. This allows you to choose the relevant video-based activity that will be linked to the course, ensuring that the correct video content is presented to the learners for that activity.

3. **Mandatory**

    This setting determines whether the Videotime activity is required for learners to complete before proceeding to the next activity.
    - **Yes:** The learner must watch the video in full before moving on to the next section or module in the course.
    - **No:** The learner can skip the video and continue with the course module without watching it.

![video-time](https://github.com/user-attachments/assets/ce7780f6-847e-4ad8-a6e1-ea5aade74d29)

## Table of contents
A Table of Contents (TOC) in Content Designer is a navigational element that provides an overview of the structure of a chapter. It lists all the sections, chapters, and key content elements within the course, allowing learners to quickly locate and navigate to specific parts of the material.

![TOC](https://github.com/user-attachments/assets/a841c184-a030-425f-a7a6-83f2696564bd)

1. **Intro text**

    The introduction text provides an overview of the Table of Contents, helping users understand what the activity includes. It offers a brief explanation of the chapters or sections covered and serves as a guide for navigation.

2. **Call to action**

    This feature adds a dynamic button that adapts based on the user's progress in the activity:
    - **Start Now:** Directs users to the first chapter if no chapters have been completed.
    - **Resume:** Appears when some chapters are completed, guiding users to the next incomplete chapter.
    - **Review:** Becomes available once all chapters are complete, providing a link back to the first chapter for a recap.

This setting helps users navigate the content efficiently based on their current progress.

![TOC-view](https://github.com/user-attachments/assets/ca88c247-be2d-4d17-8bfa-5a4039c3625d)

### TOC Sticky State

1. **Sticky table of contents**

    This feature enhances navigation by keeping the Table of Contents visible as users scroll through the page. You can configure it as follows:
    - **Disabled:** The TOC remains in its original position and does not move as you scroll.
    - **Enabled:** The TOC becomes sticky and stays visible below the navbar as you scroll down.
    - **Enabled When Scrolling Up:** The TOC becomes sticky only when you scroll up after it has moved out of view, providing quick access to navigation while reviewing content.

2. **Chapter titles in sticky state**

    These settings determine whether chapter titles remain visible when the Table of Contents (TOC) is in a sticky position. Options include:
     - **Visible (Default):** Chapter titles are always displayed in the sticky state, ensuring users can see them at all times.
     - **Hidden:** Chapter titles are never shown when the TOC is sticky, reducing visual clutter.
     - **Hidden on Mobile:** Chapter titles are hidden in the sticky state only on mobile devices but remain visible on larger screens for better navigation.

3. **Activity title in sticky state**

    These settings manage the visibility of the activity title when the Table of Contents (TOC) is in a sticky position. Options include:
    - **Visible (Default):** The activity title is always displayed in the sticky state, ensuring users can see it at all times.
    - **Hidden:** The activity title is never shown in the sticky state, minimizing distractions.
    - **Hidden on Mobile:** The activity title is hidden in the sticky state only on mobile devices but remains visible on larger screens for better navigation.

![sticky-TOC-element](https://github.com/user-attachments/assets/ae168392-0686-477f-acfe-e51045f3dedc)

**TOC Progress bar:**

The TOC progress bar visually tracks the student’s chapter progress through the course, updating as they complete chapters. It remains visible in the sticky position, even when the table of contents (TOC) block is hidden during page scrolling, ensuring it remains easily accessible for students to monitor their progress without distractions.is in its sticky position, ensuring it remains easily accessible.


## Outro
The Outro in Content Designer is the final element or section within a chapter or activity that provides closure to the content. The outro serves as a conclusion and helps learners reflect on what they have learned while guiding them to the next phase of the course module.

![outro](https://github.com/user-attachments/assets/f91a4802-769a-41c7-9f16-f2f4b49fa528)

1. **Image**

    Upload an image to display in the Outro section. This image will appear at the end of the content, adding a visual element that complements the closing message or theme, and helps reinforce the course's module overall impact.

2. **Content**

    The Content setting allows you to enter the main text for an element. This text will be displayed as body content, typically used for providing details, descriptions, or additional information within the content layout.

3. **Primary Button**

    The Primary Button setting allows you to customize the button displayed within the element. You have several options:
    - **Disabled:** The primary button is hidden by default and does not appear.
    - **Custom:** Displays a primary button where you can enter custom text and a URL for the button’s link.
    - **Next:** Displays a "Next" button that links to the next activity in the course sequence.
    - **Back to Course:** Displays a button that redirects to the course overview page.
    - **Back to Section:** Displays a button that links back to the current activity's section within the course.

4. **Secondary button**

    The Secondary Button setting allows you to customize a secondary button within the element, offering several options:
    - **Disabled:** The secondary button is hidden by default and will not be shown.
    - **Custom:** Displays a secondary button where you can enter custom text and a URL for the button's link.
    - **Next:** Displays a "Next" button that links to the next activity in the course sequence.
    - **Back to Course:** Displays a button that redirects to the course overview page.
    - **Back to Section:** Displays a button that links back to the current activity's section within the course.

![outro-image](https://github.com/user-attachments/assets/a15b684b-bb81-4869-a9f1-2d8a72b07239)

## Content Designer Reports

Content Designer Reports provide valuable insights into how students interact with learning materials. These reports track student progress, showing completed activities and the last access time for each activity. They help instructors monitor learner engagement and ensure that all required tasks are finished. Reports can be customized to focus on specific students, offering a detailed overview of performance and progress throughout the course module.

![content-designer-report](https://github.com/user-attachments/assets/a3877d80-1056-410d-8546-c93dcac0abd4)
