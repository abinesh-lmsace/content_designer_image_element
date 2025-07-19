// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Initializes event listeners and manages chapter completion and progress updates
 * for the content designer module. Handles user interactions such as completing
 * chapters and updating the progress bar. Supports both standard and popup formats.
 * Utilizes AJAX calls to update chapter completion status and refreshes content
 * accordingly. Ensures sticky progress bar behavior during scrolling.
 *
 * @module cdelement_chapter/chapter
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'mod_contentdesigner/elements', 'core/ajax', 'core/fragment',
    'core/templates', 'core/loadingicon', 'core/notification', 'core/str'],
    function ($, Elements, AJAX, Fragment, Templates, LoadingIcon, Notification, Str) {

        const chapterCTA = 'button.complete-chapter';

        const progressBar = 'div#contentdesigner-progressbar';

        let completionIcon, completionStr;

        var chapterInProgress = false;

        const contentWrapper = () => document.querySelector('.contentdesigner-wrapper');
        const chapters = () => document.querySelectorAll('.contentdesigner-chapter');

        const initEventListeners = () => {
            Templates.renderPix('e/tick', 'core').done(function (img) {
                completionIcon = img;
            });
            Str.get_string('completion_manual:done', 'course').done((str) => {
                completionStr = str;
            });
            // Remove previous eventlisteners on body. to support popup format.
            document.body.removeEventListener('click', completeChapterListener);
            document.body.addEventListener('click', completeChapterListener);

            document.body.removeEventListener('click', chapternavigation);
            document.body.addEventListener('click', chapternavigation);

            automaticCompletion();

            document.querySelector('#page').addEventListener('scroll', () => {
                automaticCompletion();
                stickyProgress();
            });

            // Popup format support.
            var popup = document.querySelector('body.format-popups .modal-content .modal-body');
            if (popup !== null) {
                popup.addEventListener('scroll', () => {
                    automaticCompletion();
                    stickyProgress();
                });
            }

            window.addEventListener('scroll', () => {
                automaticCompletion();
                stickyProgress();
            });

            // Listen the event form the element update.
            contentWrapper().addEventListener('elementupdate', () => {
                chapternavigation();
            });

            chapternavigation();
        };

        const completeChapterListener = (e) => {
            var completeCTA = e.target.closest(chapterCTA);
            if (completeCTA != undefined) {
                e.preventDefault();
                var chapter = completeCTA.dataset.chapterid;
                var promise = completeChapter(chapter, completeCTA);
                promise.done(() => {
                    updateProgress();
                    completeCTA.classList.remove('btn-outline-secondary');
                    completeCTA.classList.add('btn-success');
                    completeCTA.innerHTML = completionIcon + ' ' + completionStr;
                    Elements.removeWarning();
                    Elements.refreshContent();
                    let chapterlist = e.target.closest('.chapters-list');
 
                    if (chapterlist && !chapterlist.classList.contains('completed')) {
                        chapterlist.classList.add('completed');
                    }
                    // TODO: Add a additional function to support loadnext chapter works like replaceonrefresh.
                    // Until hide this loadNextchapters().
                    // Elements.loadNextChapters(chapter);
                }).catch(Notification.exception);
            }
        };

        const stickyProgress = function () {
            var progressElem = document.querySelector('.contentdesigner-progress');
            var contentWrapper = document.querySelector('.contentdesigner-content');
            if (progressElem && contentWrapper) {
                if (contentWrapper != undefined && contentWrapper.getBoundingClientRect().top < 50) {
                    contentWrapper.classList.add('sticky-progress');
                    progressElem.classList.add('fixed-top');
                } else {
                    progressElem.classList.remove('fixed-top');
                    contentWrapper.classList.remove('sticky-progress');
                }
            }
        };

        const completeChapter = (chapter, button) => {
            var promises = AJAX.call([{
                methodname: 'cdelement_chapter_update_completion',
                args: {
                    chapter: chapter,
                    cmid: Elements.contentDesignerData().cmid
                }
            }]);
            LoadingIcon.addIconToContainerRemoveOnCompletion(button, promises[0]);

            return promises[0];
        };

        const updateProgress = () => {
            var params = { cmid: Elements.contentDesignerData().cmid };
            Fragment.loadFragment('cdelement_chapter', 'update_progressbar',
                Elements.contentDesignerData().contextid, params).done((html, js) => {
                    Templates.replaceNode(progressBar, html, js);
                }).catch(Notification.exception);
        };

        /**
         * Automatically completes the current chapter when it becomes visible.
         */
        const automaticCompletion = () => {
            // Select all chapters with completion mode set to auto.
            const chapters = Array.from(
                document.querySelectorAll('.course-content-list .chapters-list[data-completionmode="1"]')
            );

            // Return if no chapters are found or if an AJAX request is already in progress.
            if (!chapters.length || chapterInProgress) {
                return;
            }

            // Loop through the chapters.
            for (let i = 0; i < chapters.length; i++) {
                const currentChapter = chapters[i];

                // Skip if the current chapter is already completed.
                if (currentChapter.classList.contains('completed')) {
                    continue;
                }

                // Check if the current chapter is in the viewport.
                if (isElementInViewport(currentChapter)) {
                    chapterInProgress = true;
                    const chapterId = currentChapter.dataset.id;
                    completion(currentChapter, chapterId);
                }
            }
        };

        const completion = (currentChapter, chapterId) => {
            // AJAX call to complete the current chapter when the next one enters the viewport
            AJAX.call([{
                methodname: 'cdelement_chapter_update_completion',
                args: {
                    chapter: chapterId,
                    cmid: Elements.contentDesignerData().cmid
                }
            }])[0].done(() => {
                currentChapter.classList.add('completed');  // Mark as completed
                updateProgress();
                Elements.removeWarning();
                Elements.refreshContent();
                currentChapter.removeEventListener('scroll', automaticCompletion);
            }).always(() => {
                chapterInProgress = false;
            }).catch(Notification.exception);

        };

        const isElementInViewport = (el) => {
            // Helper function to check if an element is visible in the viewport.
            const rect = el.getBoundingClientRect();
            return (
                rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
                rect.bottom > 0 &&
                rect.left < (window.innerWidth || document.documentElement.clientWidth) &&
                rect.right > 0
            );
        };

        // Chapter navigation.
        const chapternavigation = function () {
            chapters().forEach((chapter) => {
                chapter.addEventListener('click', function (e) {
                    const chapterid = e.target.dataset.chapter;
                    viewtochapter(chapterid);
                });
            });
        };

        // Ensure this function is defined or imported if it's in another file
        const viewtochapter = (chapterid) => {
            var chapterSelector = document.querySelector('li.chapters-list[data-id="' + chapterid + '"]');
            if (chapterSelector != undefined) {
                chapterSelector.scrollIntoView(true);
            }
        };

        return {
            init: function () {
                initEventListeners();
            },
        };
    });
