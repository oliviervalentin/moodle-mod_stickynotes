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
        case FEATURE_COMPLETION_HAS_RULES:
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
 * Extend navigation.
 * @param object $settings
 * @param object $stickynotesnode
 */
function stickynotes_extend_settings_navigation($settings, $stickynotesnode) {
    global $PAGE;

    if (has_capability('mod/stickynotes:export', $PAGE->cm->context)) {
        $node = navigation_node::create(get_string('export', 'stickynotes'),
                new moodle_url('/mod/stickynotes/export_csv.php', array('id' => $PAGE->cm->id)),
                navigation_node::TYPE_SETTING,
                new pix_icon('i/export', ''));
        $stickynotesnode->add_node($node);
    }
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

        if (isset($stickyid->create)) {
            $stickyid->color = 'color1';
        }

        $mform->addElement('text', 'message', get_string('message', 'stickynotes'), 'maxlength="100" size="48"');
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');

        // If color choice is enabled.
        if ($stickyid->choose_color == 1) {

            // First, array the 6 colors defined for this activity.
            $configcolor = array (
                'color1',
                'color2',
                'color3',
                'color4',
                'color5',
                'color6'
            );
            // Second, retrieve colors settings for this instance.
            $retrievecolors = $DB->get_record('stickynotes', array('id' => $stickyid->stickyid), '*', MUST_EXIST);

            $colorarray = array();
            foreach ($configcolor as $color) {
                if ($retrievecolors->$color == 1) {
                    // If a color is used in instance, design a colored square and add meaning if define.
                    $thiscolor = "<div style=\"width:50px;background-color:".get_config('mod_stickynotes', $color)
                    ."\">&nbsp;</div>&nbsp;";
                    $thiscolor .= $DB->get_field('stickynotes', $color.'_meaning', array('id' => $stickyid->stickyid));
                    $thiscolor .= "\n";
                    // Create a radio button to choose color.
                    $colorarray[] = $mform->createElement('radio', 'color', '', $thiscolor, $color);
                }
            }
            $mform->setDefault('color', $stickyid->color);
            $mform->addGroup($colorarray, 'colorarr', get_string('choosecolorbuttons', 'stickynotes'), array('<br />'), false);
        } else {
            // Else, default color for note is always color 1.
            $mform->addElement('hidden', 'color');
            $mform->setType('color', PARAM_TEXT);
            $mform->setDefault('color',  'color1');
        }

        if (isset($stickyid->edit)) {
            // If editing note, display menu to change column.

            // Does the note stays at its place ?
            $mform->addElement('advcheckbox', 'nomove', get_string('nomove', 'stickynotes'));
            $mform->addHelpButton('nomove', 'nomove', 'stickynotes');
            $mform->setDefault('nomove',  '0');

            $req = $DB->get_records('stickynotes_column', array('stickyid' => $stickyid->stickyid), 'id', 'id,title');
            $options = [];

            foreach ($req as $new) {
                $options[$new->id] = $new->title;
            }

            $mform->disabledIf('stickycolid', 'nomove', '1');
            $mform->addElement('select', 'stickycolid', get_string('changecolumn', 'stickynotes'), $options);
            $mform->setType('stickycolid', PARAM_INT);

            $optionsorder = [];
            $mform->disabledIf('selectorder', 'nomove', '1');
            $mform->addElement('select', 'selectorder', get_string('selectorder', 'stickynotes'), $optionsorder);
            $mform->setType('selectorder', PARAM_INT);
            $mform->setDefault('selectorder', 0);

            $mform->addElement('hidden', 'ordernote');
            $mform->setType('ordernote', PARAM_INT);

            $mform->addElement('hidden', 'oldrank');
            $mform->setType('oldrank', PARAM_INT);
            $mform->setDefault('oldrank', $stickyid->oldrank);

            $mform->addElement('hidden', 'oldcolumn');
            $mform->setType('oldcolumn', PARAM_INT);
            $mform->setDefault('oldcolumn', $stickyid->oldcolumn);
        } else {
            // Else, hide column select and create ordernote select.
            $sql = 'SELECT ordernote, message FROM {stickynotes_note} WHERE stickycolid = ? ORDER BY ordernote';
            $paramsdb = array($stickyid->stickycolid);
            $dbresult = $DB->get_records_sql($sql, $paramsdb);

            $createorder[0] = get_string('lastplace', 'stickynotes');
            $createorder[1] = get_string('firstplace', 'stickynotes');

            foreach ($dbresult as $move) {
                $neworder = $move->ordernote + 1;
                $createorder[$neworder] = get_string('after', 'stickynotes')." '".$move->message."'";
            }

            $mform->addElement('select', 'ordernote', get_string('selectorder', 'stickynotes'), $createorder);
            $mform->setType('ordernote', PARAM_INT);
            $mform->setDefault('ordernote', 1000);

            $mform->addElement('hidden', 'stickycolid');
            $mform->setType('stickycolid', PARAM_INT);
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'oldrank');
        $mform->setType('oldrank', PARAM_INT);

        $mform->addElement('hidden', 'oldcolumn');
        $mform->setType('oldcolumn', PARAM_INT);

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
function insert_stickynote($data, $moduleinstance, $course, $cm) {
    global $DB, $USER;
    $id = required_param('id', PARAM_INT);
    $data = (object)$data;
    $data->timecreated = time();

     // Check ordernote.
    if ($data->ordernote == 0) {
        // If ordernote is zero, user creates note at the end of the column.
        // Calculate order : order of last note + 1.
        $sql = 'SELECT ordernote FROM {stickynotes_note} WHERE stickycolid = ? ORDER BY ordernote DESC LIMIT 1';
        $paramsdb = array($data->stickycolid);
        $dbresult = $DB->get_field_sql($sql, $paramsdb);
        $data->ordernote = $dbresult + 1;
    } else {
        // User creates a note at a specific place.
        // First, all notes following are moved of one place BEFORE creating new note.
        $sql = 'SELECT id, ordernote FROM {stickynotes_note} WHERE stickycolid = ? AND ordernote >= ? ORDER BY ordernote';
        $paramsdb = array($data->stickycolid, $data->ordernote);
        $dbresult = $DB->get_records_sql($sql, $paramsdb);
        foreach ($dbresult as $note) {
            $updatenotes = (object)$note;
            $updatenotes->ordernote = $note->ordernote + 1;
            $resnotes = $DB->update_record('stickynotes_note', $updatenotes);
        }
    }
    // Finally, create the new note.
    $res = $DB->insert_record('stickynotes_note', $data);

    // Activates completion checking.
    $completion = new \completion_info($course);
    if ($completion->is_enabled($cm) && $moduleinstance->completionstickynotes) {
        $completion->update_state($cm);
    }

    return $res;
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

    // First, retrieve all notes following the moved note BEFORE updating !
    $sql = 'SELECT id, ordernote FROM {stickynotes_note} WHERE stickycolid = ? AND ordernote >= ? AND id != ? ORDER BY ordernote';
    $paramsdb = array($data->stickycolid, $data->ordernote, $data->note);
    $dbresult = $DB->get_records_sql($sql, $paramsdb);

    // Now we can update the note at its new place.
    $res = $DB->update_record('stickynotes_note', $data);

    // Finally, all notes following are moved of one place.
    foreach ($dbresult as $note) {
        $updatenotes = (object)$note;
        $updatenotes->ordernote = $note->ordernote + 1;
        $resnotes = $DB->update_record('stickynotes_note', $updatenotes);
    }

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

    return true;
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
function delete_stickynote($note, $modulecontext, $moduleinstance, $course, $cm) {
    global $DB;
    if (!$DB->delete_records('stickynotes_note', array('id' => $note))) {
        $result = false;
    }

    // Activates completion checking.
    $completion = new \completion_info($course);
    if ($completion->is_enabled($cm) && $moduleinstance->completionstickynotes) {
        $completion->update_state($cm);
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
  * Search column title.
  * @param int $col  Column id.
  * @return array
  */
function get_column_title($col) {
    global $DB;
    $record = $DB->get_record('stickynotes_column', array('id' => $col));
    if (!$record) {
        return;
    } else {
        $column['title'] = $record->title;
    }
    return $column;
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
 * Defines action to trigger for a "Like" vote type.
 *
 * This function is related to "Like" vote.
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

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the Sticky Notes activity.
 *
 * @param $mform the course reset form that is being built.
 */
function stickynotes_reset_course_form_definition($mform) {
    $mform->addElement('header', 'stickynotesheader', get_string('modulenameplural', 'stickynotes'));

    $mform->addElement('advcheckbox', 'reset_stickynotes_all',
            get_string('resetstickynotesall', 'stickynotes'));

    $mform->addElement('advcheckbox', 'reset_stickynotes_notes',
            get_string('resetstickynotesnotes', 'stickynotes'));
    $mform->disabledIf('reset_stickynotes_notes', 'reset_stickynotes_all', 'checked');

    $mform->addElement('advcheckbox', 'reset_stickynotes_votes',
            get_string('resetstickynotesvotes', 'stickynotes'));
    $mform->disabledIf('reset_stickynotes_votes', 'reset_stickynotes_all', 'checked');
    $mform->disabledIf('reset_stickynotes_votes', 'reset_stickynotes_notes', 'checked');
}

/**
 * Course reset form defaults.
 * @return array the defaults.
 */
function stickynotes_reset_course_form_defaults($course) {
    return array('reset_stickynotes_all' => 1,
                 'reset_stickynotes_notes' => 1,
                 'reset_stickynotes_votes' => 1);
}

/**
 * Actual implementation of the reset course functionality, delete all contents,
 * or only notes, or only votes.
 *
 * If delete all is selected, a start column will be created.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function stickynotes_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'stickynotes');
    $status = array();

    $sql = "SELECT sn.id FROM {stickynotes} sn WHERE sn.course=".$data->courseid."";

    // Remove all contents - columns, notes and votes.
    if (!empty($data->reset_stickynotes_all)) {
        // Delete all columns, notes and votes queries. 
        $res_columns = $DB->delete_records_select('stickynotes_column', "stickyid IN ($sql)");
        $res_notes = $DB->delete_records_select('stickynotes_note', "stickyid IN ($sql)");
        $res_votes = $DB->delete_records_select('stickynotes_vote', "stickyid IN ($sql)");

        // Now columns are deleted, create a new default column for each activity.
        $res_activities = $DB->get_records_sql($sql);
        foreach ($res_activities as $recreate_column) {
            $new = new stdClass();
            $new->stickyid = $recreate_column->id;
            $new->title = get_string('new_column_title', 'stickynotes');
            insert_column($new);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeallresponse', 'stickynotes'), 'error'=>false);
    }

    // Remove notes and votes. Columns stay.
    if (!empty($data->reset_stickynotes_notes)) {
        $res_notes = $DB->delete_records_select('stickynotes_note', "stickyid IN ($sql)");
        $res_votes = $DB->delete_records_select('stickynotes_vote', "stickyid IN ($sql)");
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removenotesandvotesresponse', 'stickynotes'), 'error'=>false);
    }

    // Remove votes only
    if (!empty($data->reset_stickynotes_votes)) {
        $res_votes = $DB->delete_records_select('stickynotes_vote', "stickyid IN ($sql)");
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removevotesresponse', 'stickynotes'), 'error'=>false);
    }

    return $status;
}

/**
 * Add a get_coursemodule_info function in case any stickynotes type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function stickynotes_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, completionstickynotes';
    if (!$stickynotes = $DB->get_record('stickynotes', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $stickynotes->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('stickynotes', $stickynotes, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionstickynotes'] = $stickynotes->completionstickynotes;
    }

    return $result;
}

/**
 * Obtains the automatic completion state for this stickynotes on any conditions
 * in stickynotes settings
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not. (If no conditions, then return
 *   value depends on comparison type)
 */
function stickynotes_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $stickynotesid = $cm->instance;

    if (!$stickynotes = $DB->get_record('stickynotes', ['id' => $stickynotesid])) {
        throw new \moodle_exception('Unable to find stickynotes activity with id ' . $stickynotesid);
    }

    $params = ['userid' => $userid, 'stickyid' => $stickynotesid];
    $sql = "SELECT COUNT(*)
                FROM {stickynotes_note} sn
                JOIN {stickynotes_column} sc ON sn.stickycolid = sc.id
                WHERE sn.userid = :userid
                AND sc.stickyid = :stickyid";

    if ($stickynotes->completionstickynotes) {
        $stickynotes = $DB->get_field_sql($sql, $params);
        if ($stickynotes) {
            return ($stickynotes >= $stickynotes->completionstickynotes) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        } else {
            return COMPLETION_INCOMPLETE;
        }
    }
    return $type;
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the stickynotes module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_stickynotes_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionstickynotes':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionstickynotesdesc', 'mod_stickynotes', $val);
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}

 /**
  * Updates locks parameters.
  * @param object $data  Datas from the form
  * @return post The id of the activity.
  */
  function update_lock($instance, $lock, $lockvalue) {
    global $DB;
    $data = (object)$data;
    $data->id = $instance;
    $data->$lock = $lockvalue;

    $res = $DB->update_record('stickynotes', $data);

    return true;
}
