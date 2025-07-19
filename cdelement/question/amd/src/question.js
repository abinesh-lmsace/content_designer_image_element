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
 * Question element - Module to resize the question embeded frame.
 *
 * @module    cdelement_question/question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['mod_contentdesigner/elements'], function (Elements) {

    /**
     * REsize the question element frame height.
     *
     * @param {HTMLElement} questionFrame
     */
    const resizeQuestionFrame = (questionFrame) => {
        questionFrame.style.minHeight = '200px';
        var height = questionFrame.contentWindow.document.documentElement.scrollHeight;
        height += 50;
        questionFrame.style.minHeight = height + 'px';
    };

    /**
     * Update the warning message and referesh the question frame content when the question is submitted.
     *
     * @param {Integer} instanceID
     * @returns
     */
    const questionSubmitted = (instanceID) => {

        var question = document.querySelector('.element-question .element-content[data-instanceid="' + instanceID + '"]');

        if (question === null) {
            return false;
        }

        const questionFrame = question.querySelector('iframe');

        const handleLoad = () => {

            var responseFrom = questionFrame.contentDocument.querySelector('form#responseform');
            resizeQuestionFrame(questionFrame);
            responseFrom.addEventListener('contentdesigner.questionformsubmitted', function () {
                Elements.removeWarning();
                Elements.refreshContent();
            });
        };

        const questionFrameLoaded = (iframe, callback) => {

            let checkLoad = setInterval(() => {

                if (questionFrame === null) {
                    clearInterval(checkLoad);
                    return;
                }

                if (questionFrame.contentDocument === null) {
                    clearInterval(checkLoad);
                    return;
                }

                var responseFrom = questionFrame?.contentDocument.querySelector('form#responseform');

                if (iframe.contentDocument.readyState === 'complete' && responseFrom) {

                    const images = iframe.contentDocument.images;
                    let loadedImagesCount = 0;

                    const checkImagesLoaded = () => {
                        loadedImagesCount++;
                        if (loadedImagesCount === images.length) {
                            clearInterval(checkLoad);
                            callback();
                        }
                    };

                    if (images.length > 0) {
                        for (let i = 0; i < images.length; i++) {
                            if (images[i].complete) {
                                checkImagesLoaded();
                            } else {
                                images[i].addEventListener('load', checkImagesLoaded);
                                images[i].addEventListener('error', checkImagesLoaded);
                            }
                        }
                    } else {
                        clearInterval(checkLoad);
                        callback();
                    }
                }
            }, 500);
        };

        questionFrameLoaded(questionFrame, handleLoad);
    };

    /**
     * Initialize the question element results.
     */
    const initquestion = () => {
        var questions = document.querySelectorAll('.chapters-list li.element-question iframe');
        questions.forEach((question) => {
            var instanceid = question.dataset.instanceid;
            questionSubmitted(instanceid);
        });
    };

    return {
        init: function () {
            initquestion();
        }
    };
});
