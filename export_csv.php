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
 * Exports all notes from activity. Code based on CSV export from plugin Board.
 *
 * @package     mod_stickynotes
 * @copyright   2021 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

use mod_stickynotes\stickynotes;

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Retrieve all informations.
if ($id) {
    $cm = get_coursemodule_from_id('stickynotes', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('stickynotes', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    throw new moodle_exception(get_string('missingidandcmid', 'mod_stickynotes'));
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/stickynotes:export', $modulecontext);

// Header for a new CSV document.
header('Content-Type: text/csv;charset=utf-8');
header("Content-disposition: attachment; filename=\"" . strip_tags($moduleinstance->name).'_stickynotes_'.
date('YmdHis').'.csv' . "\"");
header("Pragma: no-cache");
header("Expires: 0");

$fp = fopen('php://output', 'w');

// Start to retrieve all columns for this instance.
$cols = $DB->get_records('stickynotes_column', array('stickyid' => $moduleinstance->id), '', '*');
$allcols = array();

// For each columns, retrieve all notes.
foreach ($cols as $col) {
    $notes = $DB->get_records('stickynotes_note', array('stickyid' => $moduleinstance->id, 'stickycolid' => $col->id),
    'ordernote', '*');

    $allnotes = new StdClass;
    $allnotes = array();
    // For each note, retrieve and define all necessary information.
    foreach ($notes as $note) {

        $allnotes[] = (object)$note;
    }
    $notescol = new StdClass;
    $notescol->columnid = $col->id;
    $notescol->title = $col->title;
    $notescol->allnotes = $allnotes;

    $allcols[] = (object)$notescol;
}
// All informations are set.

$maxnotes = 0;
$line = [];

foreach ($allcols as $col) {
    $countnotes = count($col->allnotes);
    $maxnotes = $countnotes > $maxnotes ? $countnotes : $maxnotes;

    array_push($line, $col->title);
}

fputcsv($fp, $line);

$noterow = 0;
while ($noterow < $maxnotes) {
    $line = [];
    foreach ($allcols as $col) {
        $notes = array_values($col->allnotes);
        array_push($line, isset($notes[$noterow]) ? $notes[$noterow]->message : '');
    }
    $noterow++;
    fputcsv($fp, $line);
}

fclose($fp);
exit();
