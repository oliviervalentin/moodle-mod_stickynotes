
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
 * JavaScript for the drag'n'drop functionnality.
 *
 * @package   mod_stickynotes
 * @copyright 2021 Sébastien Mehr <sebmehr.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Sortable from 'sortablejs';
import * as Ajax from 'core/ajax';

export const init = () => {
    window.console.log('we have been started');

    var columnnotes = document.getElementsByClassName('columnnote');

    Array.from(columnnotes).forEach((column) => {
        // Do stuff here
        var sortable = Sortable.create(column, {
            group: "columnnote",
            draggable: ".stickynotemod",
            pull: "true",

            onEnd: function (/**Event*/evt) {

                if ((evt.from === evt.to) && (evt.oldDraggableIndex === evt.newDraggableIndex)) {
                    return;
                 } else {
                    var noteid = parseInt(evt.item.id.replace('element', '').replace('container', ''));
                    var oldcolumnid = parseInt(evt.from.id.replace('column', ''));
                    var newcolumnid = parseInt(evt.to.id.replace('column', ''));
                    var oldindex = evt.oldDraggableIndex + 1;
                    var newindex = evt.newDraggableIndex + 1;
                    var promise = Ajax.call([{
                        methodname: 'mod_stickynotes_changing_note_position',
                        args: { noteid: noteid,
                                oldcolumnid: oldcolumnid,
                                newcolumnid: newcolumnid,
                                oldindex: oldindex,
                                newindex: newindex
                            },
                    }]);
                    promise[0].done(function(response) {
                        window.console.log('mod_stickynotes/dragndrop success' + JSON.stringify(response));
                    }).fail(function(ex) {
                        window.console.log('mod_stickynotes/dragndrop erreur' + JSON.stringify(ex));
                    });
                }
            },
        });
    });
 };