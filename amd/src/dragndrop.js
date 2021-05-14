
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

import * as Ajax from 'core/ajax';

export const init = () => {
    window.console.log('we have been started');

    let dragged;

    document.addEventListener("dragstart", function(event) {
        window.console.log('Start dragging');

        // store a ref. on the dragged elem
        dragged = event.target;
        window.console.log(dragged);
        // make it half transparent
        event.target.style.opacity = .5;
    }, false);

    document.addEventListener("dragend", function(event) {
        // reset the transparency
        event.target.style.opacity = "";
    }, false);

    /* events fired on the drop targets */
    document.addEventListener("dragover", function(event) {
        // prevent default to allow drop
        event.preventDefault();
    }, false);

    document.addEventListener("dragenter", function(event) {
        // highlight potential drop target when the draggable element enters it
        if (event.target.className == "columnnote") {
            event.target.style.background = "#eee";
        }

    }, false);

    document.addEventListener("dragleave", function(event) {
        // reset background of potential drop target when the draggable element leaves it
        if (event.target.className == "columnnote") {
            event.target.style.background = "";
        }

    }, false);

    document.addEventListener("drop", function(event) {
        // prevent default action (open as link for some elements)
        event.preventDefault();
        // move dragged elem to the selected drop target
        if (event.target.className == "columnnote") {
            event.target.style.background = "";
            dragged.parentNode.removeChild( dragged );

            let noteid = dragged.id.replace('element', '').replace('container', '');
            let columnid = event.target.id.replace('column', '');

            event.target.appendChild( dragged );
            var promise = Ajax.call([{
                methodname: 'mod_stickynotes_changing_note_column',
                args: {noteid: noteid, newcolumnid: columnid},
            }]);
            promise[0].done(function(response) {
                window.console.log('mod_stickynotes/dragndrop success' + response);
            }).fail(function(ex) {
                window.console.log('mod_stickynotes/dragndrop erreur' + ex);
            });

        }

    }, false);
};