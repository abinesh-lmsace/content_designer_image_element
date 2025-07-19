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
 * Question element - Module Question dynamic form loader.
 *
 * @module    cdelement_question/questionform
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import DynamicForm from 'core_form/dynamicform';

class QuestionElement {

    constructor() {

        const formClass = '.element-question-form';

        const questionForms = document.querySelectorAll(formClass);

        questionForms.forEach((elem) => {
            this.includeDynamicForm(elem);
        });

    }

    includeDynamicForm(formElement) {
        // Initialize the form - pass the container element and the form class name.
        const dynamicForm = new DynamicForm(formElement.parentNode, 'cdelement_question\\question_form');

        dynamicForm.addEventListener(dynamicForm.events.FORM_SUBMITTED, (e) => {
            e.preventDefault();

            formElement.dispatchEvent(new CustomEvent('contentdesigner.questionformsubmitted'));
            window.location.reload();

        });

        formElement.querySelector('input[type="submit"][name="submitbutton"]')?.addEventListener('click', () => {
            formElement.querySelector('[name="finishattempt"]').value = 1;
        });

        // Update teh submitted element value as hidden element. Moodle doen't includes the submitter into the ajax form data.
        formElement.onsubmit = function (e) {
            var submitter = e.submitter;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = submitter.name;
            input.setAttribute('value', submitter.value);
            e.target.append(input);
        };
    }
}

export default {
    init: () => new QuestionElement
};
