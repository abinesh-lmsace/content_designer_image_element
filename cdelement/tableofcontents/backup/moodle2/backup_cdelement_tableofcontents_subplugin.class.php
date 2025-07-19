<?php
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
 * This file contains the backup code for the cdelement_tableofcontents plugin.
 *
 * @package   cdelement_tableofcontents
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides the information to backup table of contents elements.
 */
class backup_cdelement_tableofcontents_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to tableofcontents element.
     *
     * @return backup_subplugin_element
     */
    protected function define_contentdesigner_subplugin_structure() {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('cdelement_tableofcontents', ['id'], [
            'contentdesignerid', 'title', 'visible', 'intro', 'introformat', 'actiontstatus', 'stickytype', 'chaptervisible',
            'modtitlevisible', 'timecreated', 'timemodified',
        ]);

        $options = new backup_nested_element('cdelement_tableofcontentsopitons');
        $tableofcontentsopitons = new backup_nested_element('cdelement_tableofcontents_options', ['id'], [
            'element', 'instance', 'margin', 'padding', 'abovecolorbg', 'abovegradientbg', 'bgimage', 'belowcolorbg',
            'belowgradientbg', 'animation', 'duration', 'delay', 'direction', 'speed', 'viewport', 'hidedesktop', 'hidetablet',
            'hidemobile', 'timecreated', 'timemodified',
        ]);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        $subplugin->add_child($options);
        $options->add_child($tableofcontentsopitons);

        // Set source to populate the data.
        $subpluginelement->set_source_table('cdelement_tableofcontents', ['contentdesignerid' => backup::VAR_PARENTID]);
        $subpluginelement->annotate_ids('tableofcontents_instanceid', 'id');
        $subpluginelement->annotate_files('mod_contentdesigner', 'cdelement_tableofcontents_intro', null);

        $sql = "SELECT cop.* FROM {contentdesigner_options} cop
        JOIN {cdelement_tableofcontents} etoc ON etoc.id = cop.instance
        JOIN {contentdesigner_elements} cee ON cee.id = cop.element
        WHERE etoc.contentdesignerid = :contentdesignerid";

        $tableofcontentsopitons->set_source_sql($sql, ['contentdesignerid' => backup::VAR_PARENTID]);
        $tableofcontentsopitons->annotate_files('mod_contentdesigner', 'tableofcontentselementbg', null);

        return $subplugin;
    }

}
