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
 * AMD module for expanding/collapsing the contact list personal settings panel.
 *
 * @module     local_contactlist/contactlist
 * @copyright  2026 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as ajaxCall} from 'core/ajax';

/**
 * Persist the expanded/collapsed preference for the settings panel.
 *
 * @param {boolean} expanded
 */
const savePreference = (expanded) => {
    ajaxCall([{
        methodname: 'core_user_update_user_preferences',
        args: {
            preferences: [{
                type: 'local_contactlist_settings_expanded',
                value: expanded ? '1' : '0',
            }],
        },
    }]);
};

/**
 * Initialise the settings panel behaviour.
 *
 * @param {string} hideText
 * @param {string} showText
 */
export const init = (hideText, showText) => {

    const collapseEl  = document.getElementById('contactlist-settings-collapse');
    const toggleText  = document.getElementById('contactlist-toggle-text');
    const toggleIcon  = document.getElementById('contactlist-toggle-icon');

    if (collapseEl && toggleText && toggleIcon) {
        collapseEl.addEventListener('show.bs.collapse', () => {
            toggleText.textContent = hideText;
            toggleIcon.classList.remove('fa-chevron-down');
            toggleIcon.classList.add('fa-chevron-up');
            savePreference(true);
        });

        collapseEl.addEventListener('hide.bs.collapse', () => {
            toggleText.textContent = showText;
            toggleIcon.classList.remove('fa-chevron-up');
            toggleIcon.classList.add('fa-chevron-down');
            savePreference(false);
        });
    }

    const useDefaultEl = document.getElementById('contactlist-usedefault');
    const visibEl      = document.getElementById('contactlist-visib');

    if (useDefaultEl && visibEl) {
        const syncDisabled = () => {
            visibEl.disabled = useDefaultEl.checked;
        };
        syncDisabled();
        useDefaultEl.addEventListener('change', syncDisabled);
    }

    const roleFilterEl = document.getElementById('contactlist-role-filter');
    if (roleFilterEl) {
        roleFilterEl.addEventListener('change', () => {
            roleFilterEl.closest('form').submit();
        });
    }

    const profileLink = document.getElementById('contactlist-profile-link');
    if (profileLink) {
        let profileTabOpened = false;
        profileLink.addEventListener('click', () => {
            profileTabOpened = true;
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && profileTabOpened) {
                window.location.reload();
            }
        });
    }
};
