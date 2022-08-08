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
 * The mod_resource instance list viewed event.
 *
 * @package     mod_ack
 * @copyright   2022 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_ack\ack_module;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to manage mod ack instances.
 *
 * @package     mod_ack
 * @copyright   2022 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ack_module {

    /**
     * @var array
     */
    private $fileoptions = [];

    public function __construct() {
        global $COURSE;

        $this->fileoptions = [
                'subdirs' => false,
                'maxfiles' => -1,
                'maxbytes' => $COURSE->maxbytes,
        ];
    }

    /**
     * Process the module instance object and store files,
     * ready for adding or updating the database.
     *
     * @param object $moduleinstance The original module instance object.
     * @return object $moduleinstance The updated module instance object.
     */
    private function upsert_process (object $moduleinstance): object {
        $context = \context_module::instance($moduleinstance->coursemodule);

        if ($moduleinstance->type == ACKNOWLEDGE_TYPE_TEXT) {
            // Save the text elements.
            $moduleinstance->content       = $moduleinstance->typetext['text'];
            $moduleinstance->contentformat = $moduleinstance->typetext['format'];

            // Process and store any files used in the content.
            $draftitemid = file_get_submitted_draft_itemid('typetext');

            file_save_draft_area_files($draftitemid, $context->id, 'mod_ack', 'content',
                    0, $this->fileoptions);

        } elseif ($moduleinstance->type == ACKNOWLEDGE_TYPE_FILE) {
            // Process and store any files used in the content.
            $draftitemid = file_get_submitted_draft_itemid('typefile');

            // Update the file options for the file picker.
            //TODO: array merge and new array;
            $this->fileoptions['accepted_types'] = array('document');
            $this->fileoptions['maxfiles'] = 1;

            file_save_draft_area_files($draftitemid, $context->id, 'mod_ack', 'content',
                    0, $this->fileoptions);

        } elseif ($moduleinstance->type == ACKNOWLEDGE_TYPE_URL) {
            // Process URL parameters.
            $parameters = array();

            // We only handle 100 parameters cause that's what mod_url does.
            for ($i=0; $i < 100; $i++) {
                $parameter = "parameter_$i";
                $variable  = "variable_$i";
                if (empty($moduleinstance->$parameter) or empty($moduleinstance->$variable)) {
                    continue;
                }
                $parameters[$moduleinstance->$parameter] = $moduleinstance->$variable;
            }
            $moduleinstance->parameters = serialize($parameters);

        }

        return $moduleinstance;
    }

    /**
     * Add a new instance of mod ack.
     *
     * @param object $moduleinstance An object from the add/edit activity form.
     * @return int The id of the newly added module.
     * @throws \dml_exception
     */
    public function add_instance(object $moduleinstance): int {
        global $DB;

        $moduleinstance = $this->upsert_process($moduleinstance);
        $moduleinstance->timecreated = time();

        return $DB->insert_record('ack', $moduleinstance);
    }

    /**
     * Update an existing instance of mod ack.
     *
     * @param object $moduleinstance
     * @return bool Result of database update.
     * @throws \dml_exception
     */
    public function update_instance(object $moduleinstance): bool {
        global $DB;

        $moduleinstance = $this->upsert_process($moduleinstance);
        $moduleinstance->timemodified = time();
        $moduleinstance->id = $moduleinstance->instance;

        return $DB->update_record('ack', $moduleinstance);
    }
}
