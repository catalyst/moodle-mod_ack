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
 * Library of interface functions and constants.
 *
 * @package     mod_ack
 * @copyright   2022 Matt Porritt <mattp@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Acknowledgement types.
define('ACKNOWLEDGE_TYPE_TEXT', 1);
define('ACKNOWLEDGE_TYPE_FILE', 2);
define('ACKNOWLEDGE_TYPE_URL', 3);

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function ack_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_ack into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_ack_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function ack_add_instance($moduleinstance, $mform = null) {
    global $DB;

    if ($moduleinstance->acktype == ACKNOWLEDGE_TYPE_TEXT) {
        // Save the text elements.
        $moduleinstance->content       = $moduleinstance->acktypetext['text'];
        $moduleinstance->contentformat = $moduleinstance->acktypetext['format'];

        // Process and store any files used in the content.

    } elseif ($moduleinstance->acktype == ACKNOWLEDGE_TYPE_FILE) {

    } elseif ($moduleinstance->acktype == ACKNOWLEDGE_TYPE_URL) {

    }

    $moduleinstance->accepttext = $moduleinstance->ackaccepttext;
    $moduleinstance->timecreated = time();
    $id = $DB->insert_record('ack', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_ack in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_ack_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function ack_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('ack', $moduleinstance);
}

/**
 * Removes an instance of the mod_ack from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function ack_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('ack', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('ack', array('id' => $id));

    return true;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_ack
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function ack_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_ack file areas.
 *
 * @package     mod_ack
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found.
 */
function ack_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_ack file areas.
 *
 * @package     mod_ack
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_ack's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function ack_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}
