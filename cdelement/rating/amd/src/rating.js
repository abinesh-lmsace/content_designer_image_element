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
 * Initialize the rating element results.
 *
 * @module cdelement_rating/rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax', 'mod_contentdesigner/elements'],
    function (Ajax, Elements) {

        /**
         * Initialize the rating element results.
         */
        const initRating = () => {

            document.querySelectorAll('.chapters-list li.element-rating .cdelement-rating').forEach((ratingElement) => {
                const scalecontainer = ratingElement.querySelector('.rating-scale');

                if (!scalecontainer) {
                    return;
                }

                scalecontainer.addEventListener('click', (event) => {

                    const selectedoption = event.target.closest('.rating-item');

                    if (!selectedoption) {
                        return;
                    }

                    var rateid = parseInt(ratingElement.dataset.rateid, 10);
                    var contentdesignerid = parseInt(ratingElement.dataset.contentdesignerid, 10);
                    var changerating = (scalecontainer.dataset.changerating != 0) ? true : false;
                    var response = (scalecontainer.dataset.response != 0) ? true : false;

                    if (changerating || !response) {

                        var value = selectedoption.dataset.value;

                        // Remove previously selected state.
                        scalecontainer.querySelectorAll('.rating-item').forEach(option => {
                            option.classList.remove('selected');
                        });

                        // Highlight the selected rating.
                        selectedoption.classList.add('selected');

                        // Send rating data via AJAX.
                        Ajax.call([{
                            methodname: 'cdelement_rating_store_rating_response_result',
                            args: {
                                value: value,
                                rateid: rateid,
                                contentdesignerid: contentdesignerid,
                            },
                            done: function (response) {
                                if (response.result) {
                                    ratingElement.querySelector('.rating-result').innerHTML = response.result;
                                }

                                if (response.average !== null) {
                                    var ratingoption = ratingElement.querySelector(".rating-item[data-value='" +
                                        response.average + "']");

                                    scalecontainer.querySelectorAll('.rating-item').forEach(option => {
                                        option.classList.remove('average');
                                    });

                                    ratingoption.classList.add('average');
                                }

                                Elements.removeWarning();
                                Elements.refreshContent();
                                return;
                            }
                        }]);
                    }
                });
            });
        };

        /**
         * Disable the average result type field when scale is not set to non numeric.
         */
        const disbleResulttypefield = () => {
            var scale = document.querySelector('#id_elementsettingscontainer #id_scale');
            var resulttype = document.querySelector('#id_elementsettingscontainer #id_resulttype');

            if (scale !== null && scale.value == 0) {
                resulttype.querySelector('option[value="1"]').removeAttribute('disabled');
                resulttype.querySelector('option[value="2"]').setAttribute('disabled', true);
            } else {
                resulttype.querySelector('option[value="1"]').setAttribute('disabled', true);
                resulttype.querySelector('option[value="2"]').removeAttribute('disabled');
            }

            scale.addEventListener('change', (e) => {
                var val = e.target.value;
                if (val == 0) {
                    resulttype.querySelector('option[value="1"]').removeAttribute('disabled');
                    resulttype.querySelector('option[value="2"]').setAttribute('disabled', true);
                } else {
                    resulttype.querySelector('option[value="1"]').setAttribute('disabled', true);
                    resulttype.querySelector('option[value="2"]').removeAttribute('disabled');
                }
            });
        };

        return {
            init: function () {
                initRating();
            },
            disbleResulttypefield: disbleResulttypefield,
        };

    });