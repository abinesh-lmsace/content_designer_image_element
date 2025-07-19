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
 * Question element question selector for auto-complete form element.
 *
 * @module    cdelement_question/form-question-selector
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/ajax', 'jquery'],

    function (ajax, $) {


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

                ;
                var categoryid = el.dataset.categoryid;

                if (categoryid == '') {
                    categoryid = 1;
                }

                var searchargs = {
                    query: query,
                    limitfrom: 0,
                    limitnum: 100,
                    categoryid: categoryid
                };

                var calls = [{
                    methodname: 'cdelement_question_get_question_menu', args: searchargs
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
            updateCategoryID: (cmField, fieldName, noSelectionString) => {


                const categoryField = document.querySelector('[name=' + fieldName + ']');
                var el = document.querySelector('[name=' + cmField + ']');

                const updateField = (e) => {

                    const categoryid = categoryField.value;
                    el.dataset.categoryid = categoryid;

                    if (e) {
                        el.value = '';
                        var selectionsNode = el.parentElement.querySelector('.form-autocomplete-selection') || '';
                        if (selectionsNode !== null) {
                            var span = document.createElement('span')
                            span.append(noSelectionString);

                            selectionsNode.innerHTML = span.outerHTML;
                        }
                    }
                };

                if (categoryField !== null && el !== null) {
                    updateField(false);
                    categoryField.addEventListener('change', updateField);
                }
            },


            updateVersion: function (element, parentSelector) {
                const baseElement = document.querySelector('[name="' + element + '"]');
                const parentElement = document.querySelector('[name="' + parentSelector + '"]');
                if (baseElement === null) {
                    return;
                }

                const updateField = (e) => {
                    const parentValue = parentElement.value;

                    var searchargs = {
                        // query: query,
                        questionid: parentValue
                    };

                    var calls = [{
                        methodname: 'cdelement_question_get_question_variations', args: searchargs
                    }];

                    promises = ajax.call(calls);
                    $.when.apply($.when, promises).done(function (data) {
                        baseElement.innerHTML = '';
                        console.log(data);

                        data.forEach(element => {
                            var option = document.createElement('option');
                            option.value = element.id;
                            option.innerHTML = element.name;
                            baseElement.append(option);
                        });

                    }).fail();

                    if (e) {
                        baseElement.value = '';
                        var changeEvent = new Event('change');
                        baseElement.dispatchEvent(changeEvent);
                    }
                };

                if (parentElement !== null && baseElement !== null) {
                    updateField(false);
                    parentElement.addEventListener('change', updateField);
                }
            }
        };
    });
