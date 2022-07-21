<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_ack configuration form.
 *
 * @package     mod_ack
 * @copyright   2022 Matt Porritt <mattp@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_ack
 * @copyright   2022 Matt Porritt <mattp@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_ack_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $COURSE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('ackname', 'mod_ack'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'ackname', 'mod_ack');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        // Acknowledgement specific settings.
        $mform->addElement('header', 'acksettings', get_string('acksettings', 'mod_ack'));
        $mform->setExpanded('acksettings', true);

        // Acknowledgement types.
        $options = array(
                ACKNOWLEDGE_TYPE_TEXT => get_string('acktype_text', 'mod_ack'),
                ACKNOWLEDGE_TYPE_FILE => get_string('acktype_file', 'mod_ack'),
                ACKNOWLEDGE_TYPE_URL => get_string('acktype_url', 'mod_ack')
        );
        $ackselect = $mform->addElement('select', 'acktype', get_string('acktype', 'mod_ack'), $options);
        $mform->addHelpButton('acktype', 'acktype', 'mod_ack');
        $ackselect->setSelected(ACKNOWLEDGE_TYPE_TEXT);

        // Acknowledgement as file.
        $fileoptions = array(
                'accepted_types' => array('document'),
                'subdirs' => false,
                'maxfiles' => 1,
                'maxbytes' => $COURSE->maxbytes
        );
        $mform->addElement('filemanager', 'acktypefile', get_string('acktypefile', 'mod_ack'),
                null, $fileoptions);
        $mform->hideIf('acktypefile', 'acktype', 'neq', ACKNOWLEDGE_TYPE_FILE);

        // Acknowledgement as text.
        $editoroptions = array(
                'subdirs' => false,
                'maxfiles' => -1,
                'maxbytes' => $COURSE->maxbytes,
                'context' => $this->context
        );
        $mform->addElement('editor', 'acktypetext', get_string('acktypetext', 'mod_ack'),
                null, $editoroptions);
        $mform->setType('acktypetext', PARAM_RAW);
        $mform->hideIf('acktypetext', 'acktype', 'neq', ACKNOWLEDGE_TYPE_TEXT);

        // Acknowledgement as URL.
        $mform->addElement('url', 'acktypeurl', get_string('acktypeurl', 'mod_ack'),
                array('size'=>'60'), array('usefilepicker'=>true));
        $mform->setType('acktypeurl', PARAM_RAW_TRIMMED);
        $mform->hideIf('acktypeurl', 'acktype', 'neq', ACKNOWLEDGE_TYPE_URL);

        // Acknowledgement as text.
        $mform->addElement('textarea', 'ackaccepttext', get_string('ackaccepttext', 'mod_ack'),
               array('cols' => 60));
        $mform->setType('ackaccepttext', PARAM_RAW);
        $mform->addHelpButton('ackaccepttext', 'ackaccepttext', 'mod_ack');
        $mform->setDefault('ackaccepttext', get_string('ackaccepttextmsg', 'mod_ack'));

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
