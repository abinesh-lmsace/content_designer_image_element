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
 * Videotime instance selector for auto-complete form element.
 *
 * @module    cdelement_videotime/form-videotime-selector
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/ajax', 'jquery', 'mod_contentdesigner/elements',
    'media_videojs/video-lazy', 'mod_videotime/player', 'core/notification'],
    function (ajax, $, Elements, videoJS, VimeoPlayer, Notification) {

        return {
            // Public variables and functions.
            processResults: function (selector, data) {

                // Mangle the results into an array of objects.
                var results = [];
                var i = 0;
                for (i = 0; i < data.length; i++) {
                    results.push({ value: data[i].id, label: data[i].name });
                }
                return results;
            },

            transport: function (selector, query, success, failure) {
                var el = document.querySelector(selector);

                // Build the query.
                var promises = null;

                if (typeof query === "undefined") {
                    query = '';
                }

                var courseid = el.dataset.courseid;

                if (courseid == '') {
                    courseid = 1;
                }

                var searchargs = {
                    query: query,
                    limitfrom: 0,
                    limitnum: 100,
                    courseid: courseid
                };

                var calls = [{
                    methodname: 'cdelement_videotime_get_instance_menu', args: searchargs
                }];

                // Go go go!
                promises = ajax.call(calls);
                $.when.apply($.when, promises).done(function (data) {
                    success(data);
                }).fail(failure);
            },

            /**
             * Update the course id.
             *
             * @param {*} cmField
             * @param {*} fieldName
             */
            updateCourseID: (cmField, fieldName) => {
                const courseField = document.querySelector('[name=' + fieldName + ']');
                var el = document.querySelector('[name=' + cmField + ']');

                const updateField = (e) => {
                    const courseID = courseField.value;
                    el.dataset.courseid = courseID;

                    if (e) {
                        el.value = '';
                        var changeEvent = new Event('change');
                        el.dispatchEvent(changeEvent);
                    }
                };

                if (courseField !== null && el !== null) {
                    updateField(false);
                    courseField.addEventListener('change', updateField);
                }
            },

            /**
             * Update the next elements.
             *
             * Runs the verification of current elemnet and updte the contents if element completed.
             *
             * @param {Integer} videoTimeID
             * @param {Integer} instanceID
             */
            updateNextElements: function (videoTimeID, instanceID) {

                let selector = Elements.selectors.elementContent;
                selector += '[data-elementshortname="videotime"][data-instanceid="' + instanceID + '"]';

                var contentElement;
                var player;
                var intervalCheck;


                var videoJSInterval = setInterval(() => {

                    contentElement = document.querySelector(selector + ' .video-js, ' + selector + ' .vimeo-embed');

                    if (contentElement) {
                        player = videoJS.getPlayer(contentElement) || new VimeoPlayer(contentElement);
                        if (player !== null) {
                            clearInterval(videoJSInterval);
                            player.on('pause', verifyCompletion);
                            player.on('ended', verifyCompletion);
                        }
                    }
                }, 1000);

                const verifyCompletion = () => {

                    var promises = ajax.call([{
                        methodname: 'cdelement_videotime_verify_completion',
                        args: { instanceid: videoTimeID }
                    }]);

                    promises[0].done((result) => {
                        if (result) {
                            clearInterval(intervalCheck);
                            Elements.removeWarning();
                            Elements.refreshContent();
                        }
                        return;
                    }).fail(Notification.exeception);
                };

                // Verify the completion on every 10 seconds. untill the completion.
                intervalCheck = setInterval(verifyCompletion, 1000 * 10);
            }
        };
    });
