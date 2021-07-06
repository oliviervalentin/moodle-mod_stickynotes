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
 * Prints an instance of mod_stickynotes.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/../../lib/outputcomponents.php');
$PAGE->requires->js('/mod/stickynotes/assets/js_select.js');
global $DB, $USER;

// Declare optional parameters.
$edit = optional_param('edit', 0, PARAM_INT);
$create = optional_param('create', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$note = optional_param('note', 0, PARAM_INT);
$col = optional_param('col', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$ordernote = optional_param('ordernote', 0, PARAM_INT);

// These params will be passed as hidden variables later in the form.
$pageparams = array('edit' => $edit, 'create' => $create);

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

// Get the system context instance.
$systemcontext = context_system::instance();

if ($id) {
    $cm = get_coursemodule_from_id('stickynotes', $id, 0, false, MUST_EXIST);
    $moduleinstance = $DB->get_record('stickynotes', array('id' => $cm->instance), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
}

$PAGE->set_url('/mod/stickynnotes/note.php', array(
    'edit'           => $edit,
    'create'         => $create,
    'delete'         => $delete,
));

require_login(0, false);

if (!empty($create)) {
    // Case 1 : user creates a note
    // Retrieve the related coursemodule.
    if (!$cm = get_coursemodule_from_instance('stickynotes', $cm->instance)) {
        print_error('invalidcoursemodule');
    }

    // Check if the instance is part of a course.
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('invalidcourseid');
    }

    // Require a login and retrieve the modulecontext.
    require_login($course, false, $cm);
    $modulecontext = context_module::instance($cm->id);

    // Check capability.
    if (!has_capability('mod/stickynotes:createnote', $modulecontext)) {
        print_error('cannotcreatenote', 'stickynotes');
    }

    $post = new stdClass();
    $post->message = '';
    $post->create = 1;
    $post->choose_color = $moduleinstance->colors;
    $post->stickyid = $cm->instance;
    $post->stickycolid = $col;

    // Define the page title for creating form.
    $settitle = get_column_title($col);
    $pagetitle = (get_string('createnote_title', 'stickynotes')).'"'.$settitle['title'].'"';

} else if ($edit) {
    // Case 2 : user edits a note
    // Retrieve the related coursemodule.
    if (!$cm = get_coursemodule_from_instance('stickynotes', $cm->instance)) {
        print_error('invalidcoursemodule');
    }

    // Check if the instance is part of a course.
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('invalidcourseid');
    }

    // Check if note exists.
    if (!$post = $DB->get_record('stickynotes_note', array('id' => $note))) {
        print_error('cannotgetnote', 'stickynotes');
    }

    // Require a login and retrieve the modulecontext.
    require_login($course, false, $cm);
    $modulecontext = context_module::instance($cm->id);

    // Check some capabilities.
    $updateownnote = has_capability('mod/stickynotes:updateownnote', $modulecontext);
    $updateanynote = has_capability('mod/stickynotes:updateanynote', $modulecontext);

    if (!(($post->userid == $USER->id AND $updateownnote) OR $updateanynote)) {
        print_error('cannotupdatenote', 'stickynotes');
    }

    // Defines variables.
    $post->edit = $edit;
    $post->course = $course->id;
    $post->message = $post->message;
    $post->choose_color = $moduleinstance->colors;
    $post->oldrank = $post->ordernote;
    $post->oldcolumn = $post->stickycolid;

    // Define the page title for creating form.
    $pagetitle = (get_string('updatenote_title', 'stickynotes'));

} else if ($delete) {
    // Case 3 : user deletes a note.
    // Retrieve the related coursemodule.
    if (!$cm = get_coursemodule_from_instance('stickynotes', $cm->instance)) {
        print_error('invalidcoursemodule');
    }

    // Check if the instance is part of a course.
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('invalidcourseid');
    }

    // Check if note exists.
    if (!$post = $DB->get_record('stickynotes_note', array('id' => $note))) {
        print_error('cantgetnote', 'stickynotes');
    }

    // Require a login and retrieve the modulecontext.
    require_login($course, false, $cm);
    $modulecontext = context_module::instance($cm->id);

    // Check some capabilities.
    $deleteownnote = has_capability('mod/stickynotes:deleteownnote', $modulecontext);
    $deleteanynote = has_capability('mod/stickynotes:deleteanynote', $modulecontext);

    if (!(($post->userid == $USER->id AND $deleteownnote) OR $deleteanynote)) {
        print_error('cannotdeletenote', 'stickynotes');
    }

    // User has confirmed deletion : note is deleted.
    if (!empty($confirm) AND confirm_sesskey()) {
        delete_stickynote($note, $modulecontext);

        // Trigger note deleted event.
        $params = array(
            'context'  => $modulecontext,
            'objectid' => $note
            );
        $event = \mod_stickynotes\event\note_deleted::create($params);
        $event->trigger();

        $returnurl = "view.php?id=".$cm->id;
        redirect($returnurl);
    } else {
        // Shows form to confirm before delete.
        $modulecontext = context_module::instance($cm->id);
        $coursecontext = context_course::instance($course->id);
        $PAGE->navbar->add(get_string('deletenote', 'stickynotes'));
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);

        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($cm->name), 2);
        echo $OUTPUT->confirm(get_string("deletenotesure", "stickynotes"),
                "note.php?delete=$delete&confirm=$delete&id=".$cm->id."&note=".$note,
                $CFG->wwwroot . '/mod/stickynotes/view.php?id=' . $cm->id);
    }

    echo $OUTPUT->footer();
    exit;
}

// If no action is triggered, set up the form.

require_login($course, false, $cm);

$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

// Get the original note.
$postid = empty($post->id) ? null : $post->id;
$postmessage = empty($post->message) ? null : $post->message;
if (!empty($edit)) {
    $postcol = empty($post->stickycolid) ? null : $post->stickycolid;
} else {
    $postcol = $col;
}

$formarray = array(
    'id'             => $cm->id,
    'course'         => $course,
    'cm'             => $cm,
    'modulecontext'  => $modulecontext,
    'edit'           => $edit,
    'create'         => $create,
    'post'           => $post,
    'note'           => $postid,
    'message'        => $postmessage,
    'stickycolid'    => $postcol,
    'stickyid'       => $cm->instance,
    'ordernote'      => $ordernote,
);

$mformnote = new form_note('note.php', $formarray, 'post');

$mformnote->set_data(array(
        'stickycolid' => $postcol,
        'id' => $id,
    ) + $pageparams + $formarray);

// If form is cancelled, redirect activity view page.
if ($mformnote->is_cancelled()) {
    $returnurl = "view.php?id=".$id;
    redirect($returnurl);
}

// If form is submitted.
if ($fromform = $mformnote->get_data()) {

    // Redirect url in case of errors.
    if (empty($SESSION->fromurl)) {
        $errordestination = "$CFG->wwwroot/mod/stickynotes/view.php?id=$cm->id";
    } else {
        $errordestination = $SESSION->fromurl;
    }

    // If user updates a note.
    if ($fromform->edit) {
        $fromform->userid = $USER->id;
        $fromform->instance = $fromform->id;
        $fromform->id = $fromform->note;

        if ($fromform->nomove == 0) {
            $fromform->ordernote = $fromform->oldrank;
            $fromform->stickycolid = $fromform->oldcolumn;
        }

        $returnurl = "view.php?id=".$fromform->instance;
        $updatenote = update_stickynote($fromform);

         // Trigger note updated event.
        $params = array(
            'context'  => $modulecontext,
            'objectid' => $fromform->id
            );
        $event = \mod_stickynotes\event\note_updated::create($params);
        $event->trigger();

        redirect($returnurl);
        exit();
    } else if ($fromform->create) {
        // If user creates a new note.
        $fromform->userid = $USER->id;
        $returnurl = "view.php?id=".$fromform->id;
        $fromform->ordernote = $ordernote;

        // Finally, we can create note.
        $createnote = insert_stickynote($fromform);

        // Trigger note created event.
        $params = array(
            'context'  => $modulecontext,
            'objectid' => $createnote
            );
        $event = \mod_stickynotes\event\note_created::create($params);
        $event->trigger();

        redirect($returnurl);
        exit();
    }
}

// Initiate the page.
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));

// Display  header.
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

$mformnote->display();

echo $OUTPUT->footer();
