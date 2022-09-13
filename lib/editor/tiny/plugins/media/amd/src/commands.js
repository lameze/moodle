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
 * Tiny Media commands.
 *
 * @module      tiny_media/commands
 * @copyright   2022 Huong Nguyen <huongnv13@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';
import {
    component,
    imageButtonName,
    videoButtonName,
} from './common';
import {MediaImage} from './image';
import {MediaEmbed} from './embed';

const isImage = (node) => node.nodeName.toLowerCase() === 'img';
const isVideo = (node) => node.nodeName.toLowerCase() === 'video' || node.nodeName.toLowerCase() === 'audio';

export const getSetup = async() => {
    const [
        imageButtonText,
        videoButtonText,
    ] = await Promise.all([
        getString('imagebuttontitle', component),
        getString('videobuttontitle', component),
    ]);

    return (editor) => {
        const mediaImage = new MediaImage(editor);
        const mediaEmbed = new MediaEmbed(editor);
        const imageIcon = 'image';
        const videoIcon = 'embed';

        // Register the Menu Button as a toggle.
        // This means that when highlighted over an existing Media Image element it will show as toggled on.
        editor.ui.registry.addToggleButton(imageButtonName, {
            icon: imageIcon,
            tooltip: imageButtonText,
            onAction: () => {
                mediaImage.displayDialogue();
            },
            onSetup: api => {
                return editor.selection.selectorChangedWithUnbind(
                    'img:not([data-mce-object]):not([data-mce-placeholder]),figure.image',
                    api.setActive
                ).unbind;
            }
        });

        editor.ui.registry.addMenuItem(imageButtonName, {
            icon: imageIcon,
            text: imageButtonText,
            onAction: () => {
                mediaImage.displayDialogue();
            }
        });

        editor.ui.registry.addContextToolbar(imageButtonName, {
            predicate: isImage,
            items: imageButtonName,
            position: 'node',
            scope: 'node'
        });

        editor.ui.registry.addContextMenu(imageButtonName, {
            update: isImage,
        });

        // Register the Menu Button as a toggle.
        // This means that when highlighted over an existing Media Video element it will show as toggled on.
        editor.ui.registry.addToggleButton(videoButtonName, {
            icon: videoIcon,
            tooltip: videoButtonText,
            onAction: () => {
                mediaEmbed.displayDialogue();
            },
            onSetup: api => {
                return editor.selection.selectorChangedWithUnbind(
                    'video:not([data-mce-object]):not([data-mce-placeholder]),' +
                    'audio:not([data-mce-object]):not([data-mce-placeholder])',
                    api.setActive
                ).unbind;
            }
        });

        editor.ui.registry.addMenuItem(videoButtonName, {
            icon: videoIcon,
            text: videoButtonText,
            onAction: () => {
                mediaImage.displayDialogue();
            }
        });

        editor.ui.registry.addContextToolbar(videoButtonName, {
            predicate: isVideo,
            items: videoButtonName,
            position: 'node',
            scope: 'node'
        });

        editor.ui.registry.addContextMenu(videoButtonName, {
            update: isVideo,
        });
    };
};
