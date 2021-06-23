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
 * A javascript module that enhances a button and text container to support copy-to-clipboard functionality.
 *
 * @module     core/copy_to_clipboard
 * @copyright  2021 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {get_string as getString} from 'core/str';
import {add as addToast} from 'core/toast';
import Notification from 'core/notification';

/**
 * Enhances a button and text container to support copy-to-clipboard functionality.
 *
 * @method enhance
 * @param {string} triggerId The ID of the element (e.g. a button) that triggers the copying of the text inside the container.
 * @param {string} containerId The ID of the element (e.g. a text input, text area, span, etc.) that contains the text to be copied
 *                             to the clipboard.
 * @param {object} [successMessage] Lang string key-component pair for the success message that is shown after the text is
 *                                successfully copied to the clipboard.
 * @param {string} [successMessage.messageKey=textcopiedtoclipboard] The language string key for the success message.
 * @param {string} [successMessage.messageComponent=core] The component area for the success message.
 *
 * @example <caption>Enabling copy-to-clipboard functionality for a button and text input.</caption>
 *
 * import {enhance as copyToClipboardEnhance} from 'core/copy_to_clipboard';
 * copyToClipboardEnhance('buttonId', 'textInputId', {
 *     messageKey: 'somelangstringkey',
 *     messageComponent: 'mod_example'
 * });
 */
export const enhance = (
    triggerId,
    containerId,
    {
        messageKey = 'textcopiedtoclipboard',
        messageComponent = 'core',
    } = {}
) => {
    const trigger = document.getElementById(triggerId);

    trigger.addEventListener('click', e => {
        e.preventDefault();
        const container = document.getElementById(containerId);
        let textToCopy = null;

        if (container.value) {
            // For containers which are form elements (e.g. text area, text input), get the element's value.
            textToCopy = container.value;
        } else if (container.innerText) {
            // For other elements, try to use the innerText attribute.
            textToCopy = container.innerText;
        }

        let messagePromise;
        if (textToCopy !== null) {
            // Copy the text from the container to the clipboard.
            messagePromise = navigator.clipboard.writeText(textToCopy).then(() => {
                return getString(messageKey, messageComponent);
            });
        } else {
            // Unable to find container value or inner text.
            messagePromise = getString('unabletocopytoclipboard', 'core');
        }

        // Show toast message.
        messagePromise.then(message => {
            return addToast(message, {});
        }).catch(e => {
            Notification.exception(e);
        });
    });
};
