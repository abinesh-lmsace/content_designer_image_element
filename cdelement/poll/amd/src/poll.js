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
 * Initialize the poll element results.
 *
 * @module cdelement_poll/poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax', 'mod_contentdesigner/elements', 'core/notification', 'core/fragment', 'core/templates'],
    function (Ajax, Elements, notification, Fragment, Templates) {

        /**
         * Initialize the poll element results.
         */
        const initpoll = () => {
            var polls = document.querySelectorAll('.chapters-list li.element-poll .poll-section');
            polls.forEach((poll) => {
                var maxselection = poll.dataset.selectcount;
                var updaterating = poll.dataset.updaterating;
                var checkboxes = poll.querySelectorAll('input[name="answer[]"]');
                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', function () {
                        var checkedcount = poll.querySelectorAll('input[name="answer[]"]:checked').length;
                        if (checkedcount >= maxselection && maxselection >= 1) {
                            checkboxes.forEach(function (box) {
                                if (!box.checked) {
                                    box.disabled = true;
                                }
                            });
                        } else {
                            checkboxes.forEach(function (box) {
                                box.disabled = false;
                            });
                        }
                    });
                });

                var submitbtn = poll.querySelector('form .submit');
                submitbtn.addEventListener('click', () => {
                    // Collect checked option values.
                    var optionids = [];
                    var checkedboxes = poll.querySelectorAll('input[name="answer[]"]:checked, input[name="answer"]:checked');
                    checkedboxes.forEach(function (checkbox) {
                        optionids.push(checkbox.value);
                    });

                    var chartarea = poll.nextElementSibling;

                    Ajax.call([{
                        methodname: 'cdelement_poll_store_poll_result',
                        args: {
                            optionids: optionids, // Options ID.
                            pollid: parseInt(poll.dataset.pollid), // Poll ID.
                            instanceid: parseInt(poll.dataset.instanceid), // Content designer ID.
                        },
                        done: function (response) {

                            if (response) {
                                var args = {
                                    cmid: Elements.contentDesignerData().cmid,
                                    pollid: parseInt(poll.dataset.pollid), // Poll ID.
                                };

                                Fragment.loadFragment('cdelement_poll', 'update_pollchart',
                                    Elements.contentDesignerData().contextid, args).done((html, js) => {
                                        Templates.replaceNodeContents(chartarea, html, js);
                                    }).catch(notification.exception);

                                var checkboxes = poll.querySelectorAll('input[name="answer[]"], input[name="answer"]');

                                if (updaterating != 1) {
                                    poll.querySelector('.submit').disabled = true;
                                    checkboxes.forEach((checkbox) => {
                                        checkbox.disabled = true;
                                    });
                                } else {
                                    poll.querySelector('.submit').disabled = false;
                                    checkboxes.forEach(function (checkbox) {
                                        var checkedcount = poll.querySelectorAll('input[name="answer[]"]:checked').length;
                                        if (checkedcount >= maxselection && maxselection >= 1) {
                                            if (!checkbox.checked) {
                                                checkbox.disabled = true;
                                            }
                                        }
                                    });
                                }

                                Elements.removeWarning();
                                Elements.refreshContent();
                                return;
                            }
                        }
                    }]);
                });
            });
        };

        return {
            init: function () {
                initpoll();
            }
        };
    });
