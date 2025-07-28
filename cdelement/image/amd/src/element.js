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
 * CD Image Element
 *
 * @module     cdelement_image/element
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalLightBox from 'cdelement_image/local/modal/lightbox';

/**
 * Display an alert and return the promise from it.
 *
 * @private
 * @param {String} title The title of the alert
 * @param {String} body The content of the alert
 * @returns {Promise<ModalAlert>}
 */

const displayLightBox = async(body) => {

    return ModalLightBox.create({
        body,
        removeOnClose: true,
        show: true,
    })
    .then((modal) => {
        return modal;
    });
};

export const init = (instanceID) => {

    const selector = `[data-instanceid="${instanceID}"][data-elementshortname="image"]`;
    document.querySelector(selector)?.addEventListener('click', function(e) {
        const lightBox = e.target.closest('[data-modal="lightbox"]');
        if (lightBox) {
            e.preventDefault();
            displayLightBox(lightBox.dataset?.modalContent);
        }
    });
};
