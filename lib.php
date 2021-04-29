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
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("{$CFG->dirroot}/lib/navigationlib.php");
global $COURSE, $OUTPUT, $PAGE, $CFG;;


/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function stickynotes_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_stickynotes into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_stickynotes_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function stickynotes_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('stickynotes', $moduleinstance);

    $col = new stdClass();
    $col->stickyid = $id;
    $col->title = get_string('new_column_title', 'stickynotes');

    $defaultcolumn = $DB->insert_record('stickynotes_column', $col);

    return $id;
}

/**
 * Updates an instance of the mod_stickynotes in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_stickynotes_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function stickynotes_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('stickynotes', $moduleinstance);
}

/**
 * Removes an instance of the mod_stickynotes from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function stickynotes_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('stickynotes', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('stickynotes', array('id' => $id));
    $DB->delete_records('stickynotes_column', array('stickyid' => $id));
    $DB->delete_records('stickynotes_note', array('stickyid' => $id));
    $DB->delete_records('stickynotes_vote', array('stickyid' => $id));

    return true;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_stickynotes
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function stickynotes_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_stickynotes file areas.
 *
 * @package     mod_stickynotes
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
function stickynotes_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_stickynotes file areas.
 *
 * @package     mod_stickynotes
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_stickynotes's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function stickynotes_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}

/**
 * Extends the global navigation tree by adding mod_stickynotes nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $stickynotesnode An object representing the navigation tree node.
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function stickynotes_extend_navigation($stickynotesnode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the mod_stickynotes settings.
 *
 * This function is called when the context for the page is a mod_stickynotes module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@see settings_navigation}
 * @param navigation_node $stickynotesnode {@see navigation_node}
 */
function stickynotes_extend_settings_navigation($settingsnav, $stickynotesnode = null) {
}


/**
 * Generates form to create or update a note.
 */
class form_note extends moodleform {

     /**
      * Form definition for notes.
      *
      * @return void
      */
    public function definition() {
        global $CFG, $USER, $DB;
        $mform = $this->_form;
        $stickyid     = $this->_customdata['post'];
        if ($stickyid->create = 1) {
            $stickyid->color = 1;
        }

        $mform->addElement('text', 'message', get_string('message', 'stickynotes'), 'size="48"');
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');

        if ($stickyid->choose_color == 1) {

            // If choosing color enabled, display menu to choose color.
            $req = $DB->get_records_select_menu('stickynotes_colors', null, null, 'id', '*');
            $mform->addElement('select', 'color', get_string('choosecolor', 'stickynotes'), $req);
            if ($stickyid->edit = 1) {
                $mform->setDefault('color', $stickyid->color);
            }
            $mform->setType('color', PARAM_INT);
        } else {

            // Else, default color for note.
            $mform->addElement('hidden', 'color');
            $mform->setType('color', PARAM_INT);
        }

        if (isset($stickyid->stickyid)) {

            // If editing note, display menu to change column.
            $req = $DB->get_records_select_menu('stickynotes_column', "stickyid =".$stickyid->stickyid, null, 'id', 'id,title');
            $mform->addElement('select', 'stickycolid', get_string('changecolumn', 'stickynotes'), $req);
        } else {
            // Else, default value for column.
            $mform->addElement('hidden', 'stickycolid');
            $mform->setType('stickycolid', PARAM_INT);
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        // Are we creating a note?
        $mform->addElement('hidden', 'create');
        $mform->setType('create', PARAM_INT);

        // Are we editing a note?
        $mform->addElement('hidden', 'edit');
        $mform->setType('edit', PARAM_INT);

        // Instance id.
        $mform->addElement('hidden', 'stickyid');
        $mform->setType('stickyid', PARAM_INT);

        // Stickynote id.
        $mform->addElement('hidden', 'note');
        $mform->setType('note', PARAM_INT);

        $this->add_action_buttons(true, get_string('validate', 'stickynotes'));
    }

    /**
     * Form validation.
     *
     * @param array $data  data from the form.
     * @param array $files files uplaoded.
     *
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['message'])) {
            $errors['message'] = get_string('erroremptymessage', 'stickynotes');
        }

        return $errors;
    }
}
/**
 * Generates form to create or update column.
 */
class form_column extends moodleform {

    /**
     * Form definition for columns.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $USER;
        $mform = $this->_form;

        $mform->addElement('text', 'title', get_string('title', 'stickynotes'), 'size="48"');
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('maximumchars', '', 20), 'maxlength', 20, 'client');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        // Are we creating a column?
        $mform->addElement('hidden', 'create');
        $mform->setType('create', PARAM_INT);

        // Are we editing a column?
        $mform->addElement('hidden', 'edit');
        $mform->setType('edit', PARAM_INT);

        // Instance id.
        $mform->addElement('hidden', 'stickyid');
        $mform->setType('stickyid', PARAM_INT);

        // Column id.
        $mform->addElement('hidden', 'col');
        $mform->setType('col', PARAM_INT);

        $this->add_action_buttons(true, get_string('validate', 'stickynotes'));
    }

    /**
     * Form validation.
     *
     * @param array $data  data from the form.
     * @param array $files files uplaoded.
     *
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['title'])) {
            $errors['title'] = get_string('erroremptytitle', 'stickynotes');
        }

        return $errors;
    }
}
 /**
  * Creates a new note.
  * @param stdClass $data  Datas from the form.
  *
  * @return bool True if successful, false otherwise.
  */
function insert_stickynote($data) {
    global $DB, $USER;
    $id = required_param('id', PARAM_INT);
    $data = (object)$data;
    $data->timecreated = time();

    $DB->insert_record('stickynotes_note', $data);
    return true;
}
 /**
  * Updates a note
  * @param object $data  Datas from the form
  *
  * @return $post The id of the activity.
  */
function update_stickynote($data) {
    global $DB, $USER;
    $data = (object)$data;
    $data->timemodified = time();
    $res = $DB->update_record('stickynotes_note', $data);

    $post = new StdClass;
    $post->id = $data->instance;

    return $post;
}
 /**
  * Creates a new column.
  * @param object $data  Datas from the form
  * @return bool True if successful, false otherwise.
  */
function insert_column($data) {
    global $DB, $USER;
    $id = required_param('id', PARAM_INT);

    $data = (object)$data;

    // Count numbers of column for this activity.
    $options = array('stickyid' => $data->stickyid);
    $count = $DB->count_records('stickynotes_column', $options);

    $last = $count + 1;
    $data->column_order    = $last;

    $DB->insert_record('stickynotes_column', $data);

    return true;
}
 /**
  * Updates a column.
  * @param object $data  Datas from the form
  * @return post The id of the activity.
  */
function update_column($data) {
    global $DB, $USER;
    $data = (object)$data;
    $data->id = $data->col;

    $res = $DB->update_record('stickynotes_column', $data);

    $post = new StdClass;
    $post->id = $data->instance;

    return $post;
}
 /**
  * Deletes a column.
  * @param int $col  Column id
  * @param int $modulecontext  Activity id
  * @return bool True if successful, false otherwise.
  */
function delete_column($col, $modulecontext) {
    global $DB;
    if (!$DB->delete_records('stickynotes_column', array('id' => $col))) {
        $result = false;
    }
}
 /**
  * Deletes a note.
  * @param int $note  Note id
  * @param int $modulecontext  Activity id
  * @return bool True if successful, false otherwise.
  */
function delete_stickynote($note, $modulecontext) {
    global $DB;
    if (!$DB->delete_records('stickynotes_note', array('id' => $note))) {
        $result = false;
    }
}
 /**
  * Count number of notes created by a given user in activity.
  * @param int $userid  user id.
  * @param int $modulecontext activity id.
  * @return int Number of notes created by user.
  */
function stickynote_count_notes($userid, $modulecontext) {
    global $DB;
    $count = $DB->count_records('stickynotes_note', array ('userid' => $userid, 'stickyid' => $modulecontext));
    return $count;
}
 /**
  * Count number of votes created for a given note.
  * @param int $note  sticky note id.
  * @return int Number of votes of user in given activity.
  */
function stickynote_count_votes($note) {
    global $DB;
    $count = $DB->count_records('stickynotes_vote', array ('stickynoteid' => $note));
     return $count;
}
 /**
  * Defines icon to display for a note for a "Like" vote type.
  *
  * This function is related to the "Like" vote.
  * Checks if user has alreadey voted or not, and if he hasn't voted,
  * defines if he has reached or not the max votes limit.
  *
  * @param int $userid user id.
  * @param int $note sticky note id
  * @param int $limit activity setting (vote limit is enabled if 1)
  * @param int $max activity setting (max number of votes per user)
  * @param int $instance activity ID
  * @return int myvote  return if user has voted or not.
  * @return int limitedvote  return if user has reached limit.
  * @return int action  return action to trigger if user clicks on heart.
  */
function stickynote_get_vote_like($userid, $note, $limit, $max, $instance) {
    global $DB, $USER;

    $post = $DB->get_record('stickynotes_vote', array('userid' => $userid, 'stickynoteid' => $note));
    // If User has already voted for this note, display full icon to unvote.
    if ($post) {
        $params['myvote'] = 1;
        $params['limitedvote'] = 0;
        // Action : delete vote.
        $params['action'] = 'del';
    } else {
        // If no votes detected.
        if ($limit == 1) {
            // If vote has max limit, count votes for this user.
            $check = $DB->count_records('stickynotes_vote', array ('userid' => $userid, 'stickyid' => $instance));
            if ($check >= $max) {
                // If user has reached max votes, icon is grey.
                $params['myvote'] = 0;
                $params['limitedvote'] = 1;
                $params['action'] = 'n';
            } else {
                // If limit is note reached, user can vote for this note.
                $params['myvote'] = 0;
                $params['limitedvote'] = 0;
                $params['action'] = 'add';
            }
        } else {
            // Else, user can vote.
            $params['myvote'] = 0;
            $params['limitedvote'] = 0;
            $params['action'] = 'add';
        }
    }
    return $params;
}
/**
 * Defines action to trigge for a "Like" vote type.
 *
 * This function is related to the "Like" vote.
 * Triggers action if user is voting or retiring his vote.
 *
 * @param int $userid user id.
 * @param int $note sticky note id
 * @param int $action definess if user adds or delete vote
 * @param int $instance  activity id
 * @return bool True if successful, false otherwise.
 */
function stickynote_do_vote_like($userid, $note, $action, $instance) {
    global $DB, $USER;

    $data = new StdClass;

    if ($action == "add") {
        $data->userid = $userid;
        $data->vote = 1;
        $data->stickynoteid = $note;
        $data->stickyid = $instance;
         $data->timecreated = time();

        if (!$DB->insert_record('stickynotes_vote', $data)) {
            $result = false;
        }
    } else if ($action == "del") {
        $data->userid = $userid;
        $data->stickynoteid = $note;

        if (!$DB->delete_records('stickynotes_vote', array('userid' => $data->userid, 'stickynoteid' => $data->stickynoteid))) {
            $result = false;
        }
    }

    return true;
}