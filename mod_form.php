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
        $mform->addElement('text', 'name', get_string('name', 'mod_ack'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'name', 'mod_ack');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        // Acknowledgement specific settings.
        $mform->addElement('header', 'settings', get_string('settings', 'mod_ack'));
        $mform->setExpanded('settings', true);

        // Acknowledgement types.
        $options = array(
                ACKNOWLEDGE_TYPE_TEXT => get_string('type_text', 'mod_ack'),
                ACKNOWLEDGE_TYPE_FILE => get_string('type_file', 'mod_ack'),
                ACKNOWLEDGE_TYPE_URL => get_string('type_url', 'mod_ack')
        );
        $ackselect = $mform->addElement('select', 'type', get_string('type', 'mod_ack'), $options);
        $mform->addHelpButton('type', 'type', 'mod_ack');
        $ackselect->setSelected(ACKNOWLEDGE_TYPE_TEXT);



        // Acknowledgement as file.
        $fileoptions = array(
                'accepted_types' => array('document'),
                'subdirs' => false,
                'maxfiles' => 1,
                'maxbytes' => $COURSE->maxbytes
        );
        $mform->addElement('filemanager', 'typefile', get_string('typefile', 'mod_ack'),
                null, $fileoptions);
        $mform->hideIf('typefile', 'type', 'neq', ACKNOWLEDGE_TYPE_FILE);

        // Acknowledgement as text.
        $editoroptions = array(
                'subdirs' => false,
                'maxfiles' => -1,
                'maxbytes' => $COURSE->maxbytes,
                'context' => $this->context
        );

        // We use a group with a single element to get around MDL-68540.
        $grouparray = [];
        $grouparray[] = $mform->createElement('editor', 'typetext',
                get_string('typetext', 'mod_ack'),
                null, $editoroptions);
        $mform->setType('typetext', PARAM_RAW);

        $mform->addGroup($grouparray, 'grouparr', '', array(' '), false);
        $mform->hideIf('grouparr', 'type', 'neq', ACKNOWLEDGE_TYPE_TEXT);

        // Acknowledgement as URL.
        $mform->addElement('url', 'typeurl', get_string('typeurl', 'mod_ack'),
                array('size'=>'60'), array('usefilepicker'=>true));
        $mform->setType('typeurl', PARAM_RAW_TRIMMED);
        $mform->hideIf('typeurl', 'type', 'neq', ACKNOWLEDGE_TYPE_URL);

        // Acknowledgement as text.
        $mform->addElement('textarea', 'accepttext', get_string('accepttext', 'mod_ack'),
               array('cols' => 70));
        $mform->setType('accepttext', PARAM_RAW);
        $mform->addHelpButton('accepttext', 'accepttext', 'mod_ack');
        $mform->setDefault('accepttext', get_string('accepttextmsg', 'mod_ack'));

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Handle processing and retrieval of stored data.
     *
     * @param array $defaultvalues Form defaults.
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            // Process content for editor.
            $defaultvalues['typetext']['format'] = $defaultvalues['contentformat'];
            $defaultvalues['typetext']['text']   = $defaultvalues['content'];

            // Get existing files into draft area for file picker.
            $draftitemid = file_get_submitted_draft_itemid('typefile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_ack', 'content',
                    0, array('subdirs'=>true));
            $default_values['files'] = $draftitemid;
        }
        }
    }
}
