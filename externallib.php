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
 * External stickynotes API
 *
 * @package    mod_stickynotes
 * @copyright  2021 Sébastien Mehr <sebmehr.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * Stickynotes functions
 * @copyright 2021 Sébastien Mehr <sebmehr.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_stickynotes_external extends external_api {

    public static function changing_note_position($noteid, $oldcolumnid, $newcolumnid, $oldindex, $newindex) {
        global $DB;

        $params = self::validate_parameters(self::changing_note_position_parameters(),
                array('noteid' => $noteid, 'oldcolumnid' => $oldcolumnid, 'newcolumnid' => $newcolumnid,
        'oldindex' => $oldindex, 'newindex' => $newindex));

        $newdata = new stdClass();
        $newdata->id = $noteid;
        $newdata->stickycolid = $newcolumnid;
        $newdata->ordernote = $newindex;

        try {
            $transaction = $DB->start_delegated_transaction();

            $paramdb = array($newcolumnid, $newindex);
            $DB->execute("UPDATE {stickynotes_note} SET ordernote = ordernote + 1
                            WHERE stickycolid = ? AND ordernote >= ?", $paramdb);

            $DB->update_record('stickynotes_note', $newdata);

            if ($oldcolumnid != $newcolumnid) {
                $paramdb = array($oldcolumnid, $oldindex);
                $DB->execute("UPDATE {stickynotes_note} SET ordernote = ordernote - 1
                                WHERE stickycolid = ? AND ordernote > ?", $paramdb);
            }

            $transaction->allow_commit();

        } catch (Exception $e) {
            $transaction->rollback($e);
        }

        $sql = 'SELECT id, stickycolid FROM {stickynotes_note} WHERE id = ?';
        $paramsdb = array($noteid);
        $dbresult = $DB->get_records_sql($sql, $paramsdb);

        return $dbresult;
    }

    public static function changing_note_position_parameters() {
        return new external_function_parameters(
            array(
                'noteid' => new external_value(PARAM_INT, VALUE_REQUIRED),
                'oldcolumnid' => new external_value(PARAM_INT, VALUE_REQUIRED),
                'newcolumnid' => new external_value(PARAM_INT, VALUE_REQUIRED),
                'oldindex' => new external_value(PARAM_INT, VALUE_REQUIRED),
                'newindex' => new external_value(PARAM_INT, VALUE_REQUIRED),
            )
        );
    }

    public static function changing_note_position_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, VALUE_REQUIRED),
                    'stickycolid' => new external_value(PARAM_INT, VALUE_REQUIRED),
                )
            )
        );

    }

    /**
     * Returns welcome message
     * @return array = array('' => , ); welcome message
     */
    public static function get_notes_column_select($id) {
        global $USER;
        global $DB;
        global $CFG;

        $params = self::validate_parameters(
            self::get_notes_column_select_parameters(),
                array('id' => $id)
        );

        $sql = 'SELECT id, ordernote, message FROM {stickynotes_note} WHERE stickycolid = ? ORDER BY ordernote';
        $paramsdb = array($id);
        $dbresult = $DB->get_records_sql($sql, $paramsdb);

        $return[0] = array(
            'ordernote' => '1',
            'message' => get_string('firstplace', 'stickynotes')
        );

        foreach ($dbresult as $move) {
            $neworder = $move->ordernote + 1;
            $return[] = array('message' => get_string('after', 'stickynotes')." '".$move->message."'", 'ordernote' => $neworder);
        }
        return json_encode(array_values($return));
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_notes_column_select_parameters() {
        return new external_function_parameters(
          array(
              'id' => new external_value(PARAM_INT, "id")
              )
        );
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_notes_column_select_returns() {
        return new external_value(PARAM_RAW, 'The updated JSON output');
    }
}